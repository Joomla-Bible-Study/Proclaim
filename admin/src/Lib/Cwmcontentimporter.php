<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Lib;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Administrator\Helper\CwmstudytopicHelper;
use CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Additive, tracked import of structured content.
 *
 * Reads a JSON payload — never SQL — and inserts through the same
 * `AdminModel::save()` path the admin UI uses, so validation, junction
 * tables (teachers/scriptures) and `#__assets` all stay consistent without
 * hand-rolled insert logic. Every row and file created is recorded in the
 * import-set manifest ({@see Cwmimportmanifest}) as it is created, so
 * a later import can be found again and cleanly removed.
 *
 * Deliberately narrow: only the sections in {@see ALLOWED_SECTIONS} are
 * recognised, and `#__bsms_templatecode` is not one of them. Template code
 * is PHP that Proclaim writes into the site and the front end executes —
 * demo content has no reason to carry it, and refusing the
 * section outright removes that surface rather than depending on
 * validation to catch a bad payload.
 *
 * What "clean refusal" actually means here, precisely: every predictable
 * problem with the payload itself (bad shape, an unresolved reference, a
 * name collision, a bad file path) is checked in one pre-flight pass before
 * anything is created, so a bad *payload* is refused as a whole. What it
 * does not mean is a transaction — content plugins fire on every save
 * (Smart Search reindexing, schemaorg, the action log), and this project has
 * learned the hard way that a wrapping DB transaction does not reliably
 * survive that. So an *unpredictable* failure (a plugin throwing, a DB error
 * mid-run) can still leave a partially-created set behind — but every row
 * created before the failure is guaranteed to be in the manifest under this
 * tag (see the try/catch in each `import*()` method), so the tag stays a
 * complete, accurate description of what exists and a manifest-driven
 * remover can always clear it.
 *
 * @package  Proclaim.Admin
 * @since    __DEPLOY_VERSION__
 */
class Cwmcontentimporter
{
    /**
     * The only top-level payload keys this importer understands. Anything
     * else is refused rather than silently ignored or blindly trusted.
     *
     * @var string[]
     * @since  __DEPLOY_VERSION__
     */
    private const array ALLOWED_SECTIONS = ['teachers', 'series', 'messages', 'mediafiles', 'files'];

    /**
     * The only `#__bsms_servers.type` a `mediafiles[].server_name` may
     * resolve to.
     *
     * `CwmmediafileTable::delete()` always calls the resolved server's addon
     * `deleteFile()` — base `CWMAddon::deleteFile()` is a no-op, but
     * `CWMAddonLocal` overrides it to actually `unlink()` a real file when
     * the server's own `delete_files` param is on. Restricting this importer
     * to `legacy` — confirmed both `detectMetadata()`-safe (see
     * `MEDIA_PARAM_ALLOWLIST`) and `deleteFile()`-inert, and the type all
     * three seeded servers already use — means neither an import nor its
     * removal can ever reach that `unlink()`, regardless of what any given
     * site has configured on its own servers.
     *
     * @var string[]
     * @since  __DEPLOY_VERSION__
     */
    private const array ALLOWED_SERVER_TYPES = ['legacy'];

    /**
     * `mediafiles[].params` keys this importer will forward to
     * `CwmmediafileModel::save()`. Positive allowlist, not a denylist — a key
     * this list omits is refused outright by {@see validateMediaParams()},
     * not merely dropped, so a payload cannot reach anything the
     * list-writer did not explicitly consider. In particular
     * `create_live_broadcast` and every `live_*` key are absent on purpose:
     * {@see CwmmediafileModel::save()} treats those as a live command to call
     * a real platform API and create a real broadcast, which demo/import
     * content must never trigger.
     *
     * @var string[]
     * @since  __DEPLOY_VERSION__
     */
    private const array MEDIA_PARAM_ALLOWLIST = [
        'filename', 'size', 'mime_type', 'media_hours', 'media_minutes', 'media_seconds',
        'link_type', 'player', 'popup', 'media_image', 'mediacode', 'playerwidth', 'playerheight',
        'autostart', 'special',
    ];

    /**
     * Extensions a `files` entry's destination may use. No `.php`, no
     * executables — this is a filesystem write from data that arrived over
     * the network (or, for now, from a local fixture).
     *
     * @var string[]
     * @since  __DEPLOY_VERSION__
     */
    private const array IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    /**
     * Every alias this importer creates must already look like one —
     * lowercase, hyphen-separated segments. Required (not derived via
     * `ApplicationHelper::stringURLSafe()`) for two reasons: it gives the
     * orphan-row lookup in {@see recordIfOrphaned()} a stable, unambiguous
     * key, and `stringURLSafe()` transliterates through
     * `Factory::getLanguage()`, which needs a fully-booted application this
     * class is written to run without.
     *
     * @since  __DEPLOY_VERSION__
     */
    private const string ALIAS_PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    /**
     * Matches the `import_tag` column's `VARCHAR(64)`.
     *
     * @since  __DEPLOY_VERSION__
     */
    private const string TAG_PATTERN = '/^[a-z0-9][a-z0-9._-]{0,63}$/';

    /**
     * Overrides `bootComponent('com_proclaim')->getMVCFactory()`. Exists so
     * tests can hand in a factory wired from the real service provider —
     * `Factory::getApplication()->bootComponent()` falls back to a
     * `LegacyComponent` outside a real Joomla site (JPATH_ROOT not pointing
     * at one, as in this repository's own PHPUnit harness), and that stub
     * cannot resolve namespaced models.
     *
     * @since  __DEPLOY_VERSION__
     */
    private ?MVCFactoryInterface $factory;

    /**
     * @param   ?MVCFactoryInterface  $factory  See {@see $factory}. Null uses the real component in production.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __construct(?MVCFactoryInterface $factory = null)
    {
        $this->factory = $factory;
    }

    /**
     * Import a tagged content set.
     *
     * @param   string  $tag        Identifies this import, e.g. `demo-v1`. Refused if already used.
     * @param   array   $payload    Decoded JSON: `teachers`, `series`, `messages`, `mediafiles`, `files` —
     *                              see ALLOWED_SECTIONS.
     * @param   string  $sourceDir  Directory the payload's `files[].source` paths are relative to.
     *
     * @return  array{teachers: int, series: int, messages: int, mediafiles: int, files: int}
     *          Counts of what was created.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function import(string $tag, array $payload, string $sourceDir): array
    {
        if (preg_match(self::TAG_PATTERN, $tag) !== 1) {
            throw new \InvalidArgumentException(\sprintf(
                'Import tag "%s" must match %s.',
                $tag,
                self::TAG_PATTERN
            ));
        }

        if (Cwmimportmanifest::exists($tag)) {
            throw new \RuntimeException(\sprintf(
                'An import tagged "%s" already exists; refusing to run it again.',
                $tag
            ));
        }

        $plan = $this->validate($payload, $sourceDir);

        $summary = ['teachers' => 0, 'series' => 0, 'messages' => 0, 'mediafiles' => 0, 'files' => 0];

        // Source-id => newly assigned target id. Every foreign key below is
        // resolved through this map, never trusted as a target-site id
        // directly — a fixture's ids are only meaningful within itself.
        // validate() already confirmed every reference resolves, so a
        // lookup miss here would be this importer's own bug, not bad input.
        $teacherIds = [];
        $serieIds   = [];
        $messageIds = [];

        foreach ($payload['teachers'] ?? [] as $teacher) {
            $teacherIds[(int) $teacher['id']] = $this->importTeacher($tag, $teacher, $summary);
        }

        foreach ($payload['series'] ?? [] as $serie) {
            $teacherId = !empty($serie['teacher_id']) ? $teacherIds[(int) $serie['teacher_id']] : 0;

            $serieIds[(int) $serie['id']] = $this->importSerie($tag, $serie, $teacherId, $summary);
        }

        foreach ($payload['messages'] ?? [] as $message) {
            $messageIds[(int) $message['id']] = $this->importMessage(
                $tag,
                $message,
                $teacherIds,
                $serieIds,
                $plan['topics'],
                $summary
            );
        }

        // Ids this run has itself recorded in the manifest so far — passed by
        // reference into importMediaFile() so a failed save's orphan-recovery
        // query (see recordOrphanedMediaFiles()) can tell "the row this call
        // just orphaned" from "a sibling media file on the same study an
        // earlier, successful call in this loop already recorded".
        $recordedMediaFileIds = [];

        foreach ($payload['mediafiles'] ?? [] as $mediafile) {
            $this->importMediaFile($tag, $mediafile, $messageIds, $plan['mediaServers'], $recordedMediaFileIds, $summary);
        }

        foreach ($plan['files'] as $file) {
            $this->importFile($tag, $file, $summary);
        }

        return $summary;
    }

    /**
     * Check the whole payload before anything is created.
     *
     * @param   array   $payload    See {@see import()}.
     * @param   string  $sourceDir  See {@see import()}.
     *
     * @return  array{topics: array<string, int>, mediaServers: array<string, int>,
     *          files: list<array{source: string, dest: string}>}
     *          Work already done during validation, reused instead of repeated during import.
     *
     * @since  __DEPLOY_VERSION__
     */
    private function validate(array $payload, string $sourceDir): array
    {
        $unknown = array_diff(array_keys($payload), self::ALLOWED_SECTIONS);

        if ($unknown !== []) {
            throw new \RuntimeException(
                'Unrecognised import section(s): ' . implode(', ', $unknown)
                . '. Refusing rather than importing content this importer does not explicitly understand.'
            );
        }

        $this->validateShape($payload);

        $teacherIds = $this->validateEntries($payload['teachers'] ?? [], '#__bsms_teachers', 'teachername', 'teachers');
        $serieIds   = $this->validateEntries($payload['series'] ?? [], '#__bsms_series', 'series_text', 'series');

        foreach ($payload['series'] ?? [] as $i => $serie) {
            if (!empty($serie['teacher_id']) && !isset($teacherIds[(int) $serie['teacher_id']])) {
                throw new \RuntimeException(\sprintf(
                    'series[%d].teacher_id %s does not match any teachers[].id.',
                    $i,
                    $serie['teacher_id']
                ));
            }
        }

        $messageIds = $this->validateEntries($payload['messages'] ?? [], '#__bsms_studies', 'studytitle', 'messages');

        $topicMap = [];

        foreach ($payload['messages'] ?? [] as $i => $message) {
            $seriesId = (int) ($message['series_id'] ?? 0);

            if ($seriesId > 0 && !isset($serieIds[$seriesId])) {
                throw new \RuntimeException(\sprintf(
                    'messages[%d].series_id %d does not match any series[].id.',
                    $i,
                    $seriesId
                ));
            }

            foreach ((array) ($message['teacher_ids'] ?? []) as $teacherId) {
                if (!isset($teacherIds[(int) $teacherId])) {
                    throw new \RuntimeException(\sprintf(
                        'messages[%d].teacher_ids references unknown teacher id %s.',
                        $i,
                        $teacherId
                    ));
                }
            }

            // Demo content references topics already in use, it does not
            // mint new ones. Built once here and reused by importMessage()
            // rather than re-queried, so a topic resolved during validation
            // cannot vanish by the time it's needed (see recordIfOrphaned()
            // for how the rest of this class treats that kind of gap).
            foreach ((array) ($message['topics'] ?? []) as $topicText) {
                $topicText = (string) $topicText;

                if (!isset($topicMap[$topicText])) {
                    $topicId = CwmstudytopicHelper::findTopicIdByText($topicText);

                    if ($topicId === 0) {
                        throw new \RuntimeException(\sprintf(
                            'messages[%d] references topic "%s", which does not exist on this site.',
                            $i,
                            $topicText
                        ));
                    }

                    $topicMap[$topicText] = $topicId;
                }
            }
        }

        $mediaServers = $this->validateMediaFiles($payload['mediafiles'] ?? [], $messageIds);

        $files = $this->validateFiles($payload['files'] ?? [], $sourceDir);

        return ['topics' => $topicMap, 'mediaServers' => $mediaServers, 'files' => $files];
    }

    /**
     * Structural checks that have nothing to do with what the ids reference —
     * every section is a list of arrays, and every field this importer
     * iterates over is the type it's about to be used as. Catching this here
     * turns a malformed payload into one clean refusal instead of a
     * TypeError escaping from inside the create loop after earlier entries
     * already exist.
     *
     * @param   array  $payload  See {@see import()}.
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function validateShape(array $payload): void
    {
        foreach (self::ALLOWED_SECTIONS as $section) {
            if (!isset($payload[$section])) {
                continue;
            }

            if (!array_is_list($payload[$section])) {
                throw new \RuntimeException(\sprintf('"%s" must be a list.', $section));
            }

            foreach ($payload[$section] as $i => $entry) {
                if (!\is_array($entry)) {
                    throw new \RuntimeException(\sprintf('%s[%d] must be an object.', $section, $i));
                }
            }
        }

        foreach ($payload['series'] ?? [] as $i => $serie) {
            if (isset($serie['teacher_id']) && !\is_int($serie['teacher_id'])) {
                throw new \RuntimeException(\sprintf('series[%d].teacher_id must be an integer.', $i));
            }
        }

        foreach ($payload['messages'] ?? [] as $i => $message) {
            if (isset($message['series_id']) && !\is_int($message['series_id'])) {
                throw new \RuntimeException(\sprintf('messages[%d].series_id must be an integer.', $i));
            }

            foreach (['teacher_ids', 'topics', 'scriptures'] as $listField) {
                if (isset($message[$listField]) && !array_is_list($message[$listField])) {
                    throw new \RuntimeException(\sprintf('messages[%d].%s must be a list.', $i, $listField));
                }
            }
        }

        foreach ($payload['mediafiles'] ?? [] as $i => $mediafile) {
            if (!isset($mediafile['study_id']) || !\is_int($mediafile['study_id'])) {
                throw new \RuntimeException(\sprintf('mediafiles[%d].study_id must be an integer.', $i));
            }

            if (!isset($mediafile['server_name']) || !\is_string($mediafile['server_name'])
                || trim($mediafile['server_name']) === ''
            ) {
                throw new \RuntimeException(\sprintf('mediafiles[%d].server_name is required.', $i));
            }

            if (!isset($mediafile['params']) || !\is_array($mediafile['params'])) {
                throw new \RuntimeException(\sprintf('mediafiles[%d].params must be an object.', $i));
            }

            if (isset($mediafile['podcast_id'])) {
                if (!array_is_list($mediafile['podcast_id'])) {
                    throw new \RuntimeException(\sprintf('mediafiles[%d].podcast_id must be a list.', $i));
                }

                foreach ($mediafile['podcast_id'] as $podcastId) {
                    if (!\is_int($podcastId)) {
                        throw new \RuntimeException(\sprintf('mediafiles[%d].podcast_id entries must be integers.', $i));
                    }
                }
            }
        }
    }

    /**
     * Every entry has a positive, unique `id`; a required, non-empty title
     * field that does not collide with an existing row's; and a valid,
     * unique alias that does not collide with an existing row's.
     *
     * Both the title and the alias are checked, not just one — they are
     * two different constraints. `alias` is a real `UNIQUE` column on
     * `#__bsms_teachers` (not on series/studies, but checked uniformly
     * here anyway, since a collision there is still a collision); the
     * title-field check exists because the model's own validation refuses
     * a duplicate *name* independently of the alias (live-tested: an
     * import failed on "A teacher named ... already exists" with a
     * colliding name and a distinct alias).
     *
     * @param   array   $entries  `teachers[]`, `series[]`, or `messages[]`.
     * @param   string  $table    `#__`-prefixed table the entries will be created in.
     * @param   string  $titleKey The required non-empty field (`teachername` / `series_text` / `studytitle`).
     * @param   string  $section  Section name, for error messages.
     *
     * @return  array<int, true>  The valid source ids seen, as lookup keys.
     *
     * @since  __DEPLOY_VERSION__
     */
    private function validateEntries(array $entries, string $table, string $titleKey, string $section): array
    {
        $db = $this->db();

        $seenIds     = [];
        $seenTitles  = [];
        $seenAliases = [];

        foreach ($entries as $i => $entry) {
            $id = $entry['id'] ?? null;

            if (!\is_int($id) || $id <= 0) {
                throw new \RuntimeException(\sprintf('%s[%d] is missing a positive integer id.', $section, $i));
            }

            if (isset($seenIds[$id])) {
                throw new \RuntimeException(\sprintf('%s[%d] reuses source id %d.', $section, $i, $id));
            }

            $seenIds[$id] = true;

            $title = trim((string) ($entry[$titleKey] ?? ''));

            if ($title === '') {
                throw new \RuntimeException(\sprintf('%s[%d] is missing %s.', $section, $i, $titleKey));
            }

            if (isset($seenTitles[$title])) {
                throw new \RuntimeException(\sprintf('%s[%d] reuses %s "%s".', $section, $i, $titleKey, $title));
            }

            $seenTitles[$title] = true;

            $alias = (string) ($entry['alias'] ?? '');

            if (preg_match(self::ALIAS_PATTERN, $alias) !== 1) {
                throw new \RuntimeException(\sprintf(
                    '%s[%d].alias "%s" must match %s.',
                    $section,
                    $i,
                    $alias,
                    self::ALIAS_PATTERN
                ));
            }

            if (isset($seenAliases[$alias])) {
                throw new \RuntimeException(\sprintf('%s[%d] reuses alias "%s".', $section, $i, $alias));
            }

            $seenAliases[$alias] = true;

            $titleCollision = (int) $db->setQuery(
                $db->createQuery()
                    ->select('COUNT(*)')
                    ->from($db->quoteName($table))
                    ->where($db->quoteName($titleKey) . ' = :title')
                    ->bind(':title', $title, ParameterType::STRING)
            )->loadResult() > 0;

            if ($titleCollision) {
                throw new \RuntimeException(\sprintf('%s[%d] "%s" already exists on this site.', $section, $i, $title));
            }

            $aliasCollision = (int) $db->setQuery(
                $db->createQuery()
                    ->select('COUNT(*)')
                    ->from($db->quoteName($table))
                    ->where($db->quoteName('alias') . ' = :alias')
                    ->bind(':alias', $alias, ParameterType::STRING)
            )->loadResult() > 0;

            if ($aliasCollision) {
                throw new \RuntimeException(\sprintf('%s[%d] alias "%s" already exists on this site.', $section, $i, $alias));
            }
        }

        return $seenIds;
    }

    /**
     * Validate every `mediafiles[]` entry and resolve each one's `server_name`
     * to a real, existing `#__bsms_servers` row.
     *
     * `#__bsms_mediafiles` has no `alias` column, so this cannot reuse
     * {@see validateEntries()} — there is nothing to check for a title/alias
     * collision against, and the reference shape is different besides:
     * `study_id` is a payload-internal reference (resolved through
     * `$messageIds`, exactly like `series_id` on a message), but
     * `server_name` and `podcast_id` name rows that must already exist on
     * *this* site, because servers and the seeded podcast are configuration
     * this importer does not create — they stay in `install.mysql.utf8.sql`
     * as reference data a fresh install needs to function, unlike the demo
     * content this importer replaces.
     *
     * @param   array              $mediafiles  The payload's `mediafiles` section.
     * @param   array<int, true>   $messageIds  Valid source message ids, from {@see validateEntries()}.
     *
     * @return  array<string, int>  Every `server_name` referenced, resolved to its real server id —
     *          reused by {@see importMediaFile()} so import does not re-run the same lookup.
     *
     * @since  __DEPLOY_VERSION__
     */
    private function validateMediaFiles(array $mediafiles, array $messageIds): array
    {
        $db = $this->db();

        $serverIds = [];

        foreach ($mediafiles as $i => $mediafile) {
            $studyId = (int) $mediafile['study_id'];

            if (!isset($messageIds[$studyId])) {
                throw new \RuntimeException(\sprintf(
                    'mediafiles[%d].study_id %d does not match any messages[].id.',
                    $i,
                    $studyId
                ));
            }

            $serverName = trim((string) $mediafile['server_name']);

            if (!isset($serverIds[$serverName])) {
                $matches = $db->setQuery(
                    $db->createQuery()
                        ->select($db->quoteName(['id', 'type']))
                        ->from($db->quoteName('#__bsms_servers'))
                        ->where($db->quoteName('server_name') . ' = :serverName')
                        ->bind(':serverName', $serverName, ParameterType::STRING)
                )->loadObjectList();

                if (\count($matches) !== 1) {
                    throw new \RuntimeException(\sprintf(
                        'mediafiles[%d].server_name "%s" must match exactly one existing server on this site'
                        . ' (found %d).',
                        $i,
                        $serverName,
                        \count($matches)
                    ));
                }

                if (!\in_array($matches[0]->type, self::ALLOWED_SERVER_TYPES, true)) {
                    throw new \RuntimeException(\sprintf(
                        'mediafiles[%d].server_name "%s" is a "%s" server — only %s servers are importable.',
                        $i,
                        $serverName,
                        $matches[0]->type,
                        implode(', ', self::ALLOWED_SERVER_TYPES)
                    ));
                }

                $serverIds[$serverName] = (int) $matches[0]->id;
            }

            foreach ((array) ($mediafile['podcast_id'] ?? []) as $podcastId) {
                $podcastId = (int) $podcastId;

                $exists = (int) $db->setQuery(
                    $db->createQuery()
                        ->select('COUNT(*)')
                        ->from($db->quoteName('#__bsms_podcast'))
                        ->where($db->quoteName('id') . ' = :podcastId')
                        ->bind(':podcastId', $podcastId, ParameterType::INTEGER)
                )->loadResult() > 0;

                if (!$exists) {
                    throw new \RuntimeException(\sprintf(
                        'mediafiles[%d].podcast_id references podcast #%d, which does not exist on this site.',
                        $i,
                        $podcastId
                    ));
                }
            }

            $this->validateMediaParams((array) $mediafile['params'], $i);
        }

        return $serverIds;
    }

    /**
     * Every `mediafiles[].params` this importer will accept must already
     * satisfy {@see CWMAddon::needsDetection()}'s all-false condition — a
     * non-empty `filename`, `size` of at least 1000, a non-empty `mime_type`,
     * and at least one non-zero duration component. Confirmed live (see the
     * PR) that pre-populating these short-circuits `detectRemoteMetadata()`
     * before any network call, which matters because `CwmmediafileModel`
     * unconditionally runs detection for a new record — an import must never
     * make an outbound HTTP call for content nobody asked it to fetch.
     *
     * Also refuses `create_live_broadcast` and any `live_*` key outright,
     * rather than relying on the model's own gates (opted-in, empty id,
     * addon support, direct stream mode) to keep it inert — a payload should
     * not be able to get close to that trigger at all.
     *
     * @param   array  $params  One `mediafiles[].params` entry.
     * @param   int    $index   Its index in `mediafiles[]`, for the error message.
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function validateMediaParams(array $params, int $index): void
    {
        if (\array_key_exists('create_live_broadcast', $params)) {
            throw new \RuntimeException(\sprintf(
                'mediafiles[%d].params must not include "create_live_broadcast" —'
                . ' import never creates a live broadcast.',
                $index
            ));
        }

        foreach (array_keys($params) as $key) {
            if (\is_string($key) && str_starts_with($key, 'live_')) {
                throw new \RuntimeException(\sprintf(
                    'mediafiles[%d].params must not include "%s" — live-broadcast params are refused.',
                    $index,
                    $key
                ));
            }
        }

        // Refused, not merely dropped — an allowlisted key silently missing
        // is a payload bug that should surface as loudly as an unrecognised
        // section does (see this class's own docblock).
        $unknown = array_diff(array_keys($params), self::MEDIA_PARAM_ALLOWLIST);

        if ($unknown !== []) {
            throw new \RuntimeException(\sprintf(
                'mediafiles[%d].params has unrecognised key(s): %s.',
                $index,
                implode(', ', $unknown)
            ));
        }

        if (trim((string) ($params['filename'] ?? '')) === '') {
            throw new \RuntimeException(\sprintf('mediafiles[%d].params.filename is required.', $index));
        }

        $size = $params['size'] ?? null;

        if (!\is_int($size) || $size < 1000) {
            throw new \RuntimeException(\sprintf(
                'mediafiles[%d].params.size must be an integer of at least 1000 — required so the save'
                . ' never triggers remote metadata detection (see CWMAddon::needsDetection()).',
                $index
            ));
        }

        if (trim((string) ($params['mime_type'] ?? '')) === '') {
            throw new \RuntimeException(\sprintf('mediafiles[%d].params.mime_type is required.', $index));
        }

        $hours   = (int) ($params['media_hours'] ?? 0);
        $minutes = (int) ($params['media_minutes'] ?? 0);
        $seconds = (int) ($params['media_seconds'] ?? 0);

        if ($hours === 0 && $minutes === 0 && $seconds === 0) {
            throw new \RuntimeException(\sprintf(
                'mediafiles[%d].params must set a non-zero media_hours, media_minutes or media_seconds —'
                . ' required for the same network-free reason as size/mime_type.',
                $index
            ));
        }
    }

    /**
     * Validate every `files[]` entry and resolve its paths, without writing
     * anything — the resolved pairs are reused by {@see importFile()} rather
     * than re-derived, so there is exactly one place either path is computed.
     *
     * @param   array   $files      The payload's `files` section.
     * @param   string  $sourceDir  Directory `source` paths are relative to.
     *
     * @return  list<array{source: string, dest: string}>  `source` is the real, resolved source path;
     *          `dest` is the real, resolved (but not-yet-existing) destination path.
     *
     * @since  __DEPLOY_VERSION__
     */
    private function validateFiles(array $files, string $sourceDir): array
    {
        if ($files === []) {
            return [];
        }

        $realSourceDir = realpath($sourceDir);

        if ($realSourceDir === false) {
            throw new \RuntimeException(\sprintf('Import source directory "%s" does not exist.', $sourceDir));
        }

        $resolved      = [];
        $seenDestPaths = [];

        foreach ($files as $i => $file) {
            $source = (string) ($file['source'] ?? '');
            $dest   = (string) ($file['dest'] ?? '');

            if ($source === '' || $dest === '') {
                throw new \RuntimeException(\sprintf('files[%d] is missing source or dest.', $i));
            }

            $destExtension = strtolower(pathinfo($dest, \PATHINFO_EXTENSION));

            if (!\in_array($destExtension, self::IMAGE_EXTENSIONS, true)) {
                throw new \RuntimeException(\sprintf('File "%s" has a disallowed extension.', $dest));
            }

            $realSource = realpath($realSourceDir . '/' . $source);

            if ($realSource === false || !str_starts_with($realSource, $realSourceDir . \DIRECTORY_SEPARATOR)) {
                throw new \RuntimeException(\sprintf(
                    'Import file source "%s" was not found in the import package.',
                    $source
                ));
            }

            $sourceExtension = strtolower(pathinfo($source, \PATHINFO_EXTENSION));

            if ($destExtension !== $sourceExtension) {
                throw new \RuntimeException(\sprintf(
                    'files[%d] destination extension ".%s" does not match source extension ".%s".',
                    $i,
                    $destExtension,
                    $sourceExtension
                ));
            }

            // Pure finfo/getimagesize — no application/language dependency,
            // so this is safe to run in a bare (test) harness too.
            $validation = Cwmthumbnail::validate($realSource);

            if (!$validation['valid']) {
                throw new \RuntimeException(\sprintf('files[%d] "%s": %s', $i, $source, $validation['error']));
            }

            $resolvedDest = Cwmthumbnail::resolveWithinAllowedPaths($dest);

            if ($resolvedDest === false) {
                throw new \RuntimeException(\sprintf(
                    'File destination "%s" is outside the allowed image paths.',
                    $dest
                ));
            }

            if (isset($seenDestPaths[$resolvedDest])) {
                throw new \RuntimeException(\sprintf('files[%d] dest "%s" is claimed by more than one entry.', $i, $dest));
            }

            $seenDestPaths[$resolvedDest] = true;

            if (is_file($resolvedDest)) {
                throw new \RuntimeException(\sprintf('"%s" already exists; refusing to overwrite it.', $dest));
            }

            $resolved[] = ['source' => $realSource, 'dest' => $resolvedDest, 'manifestPath' => $dest];
        }

        return $resolved;
    }

    /**
     * @param   string   $tag      The import tag.
     * @param   array    $teacher  One `teachers[]` entry.
     * @param   array    $summary  Running summary, updated in place.
     *
     * @return  int  The new teacher's id.
     *
     * @since  __DEPLOY_VERSION__
     */
    private function importTeacher(string $tag, array $teacher, array &$summary): int
    {
        $alias = (string) $teacher['alias'];

        $data = [
            'id'          => 0,
            'teachername' => trim((string) $teacher['teachername']),
            'alias'       => $alias,
            'title'       => (string) ($teacher['title'] ?? ''),
            'information' => (string) ($teacher['information'] ?? ''),
            // Path-validated file placement is handled separately by
            // importFile(); the model's own thumbnail pipeline is not
            // exercised here.
            'image'        => '',
            'published'    => (int) ($teacher['published'] ?? 1),
            'access'       => (int) ($teacher['access'] ?? 1),
            'language'     => '*',
            'contact'      => 0,
            'social_links' => '',
            // NOT NULL with no default (verified against a live schema, not
            // just install.mysql.utf8.sql — they've drifted).
            'address' => '',
        ];

        $newId = $this->saveAndRecover('Cwmteacher', $data, $tag, '#__bsms_teachers', $alias, 'teacher');

        $summary['teachers']++;

        return $newId;
    }

    /**
     * @param   string   $tag        The import tag.
     * @param   array    $serie      One `series[]` entry.
     * @param   int      $teacherId  The remapped id of the series' primary teacher, or 0.
     * @param   array    $summary    Running summary, updated in place.
     *
     * @return  int  The new series' id.
     *
     * @since  __DEPLOY_VERSION__
     */
    private function importSerie(string $tag, array $serie, int $teacherId, array &$summary): int
    {
        $alias = (string) $serie['alias'];

        $data = [
            'id'          => 0,
            'series_text' => trim((string) $serie['series_text']),
            'alias'       => $alias,
            'teacher'     => $teacherId,
            'description' => (string) ($serie['description'] ?? ''),
            'image'       => '',
            'published'   => (int) ($serie['published'] ?? 1),
            'access'      => (int) ($serie['access'] ?? 1),
            'language'    => '*',
        ];

        $newId = $this->saveAndRecover('Cwmserie', $data, $tag, '#__bsms_series', $alias, 'series');

        $summary['series']++;

        return $newId;
    }

    /**
     * @param   string             $tag         The import tag.
     * @param   array              $message     One `messages[]` entry.
     * @param   int[]              $teacherIds  Source teacher id => new teacher id.
     * @param   int[]              $serieIds    Source series id => new series id.
     * @param   array<string, int> $topicMap    Topic text => id, built once by {@see validate()}.
     * @param   array              $summary     Running summary, updated in place.
     *
     * @return  int  The new message's id — collected by {@see import()} into `$messageIds` so
     *          {@see importMediaFile()} can resolve `mediafiles[].study_id` the same way every
     *          other cross-section reference in this class is resolved.
     *
     * @since  __DEPLOY_VERSION__
     */
    private function importMessage(string $tag, array $message, array $teacherIds, array $serieIds, array $topicMap, array &$summary): int
    {
        $alias    = (string) $message['alias'];
        $teachers = [];

        foreach ((array) ($message['teacher_ids'] ?? []) as $sourceTeacherId) {
            $teachers[] = ['teacher_id' => $teacherIds[(int) $sourceTeacherId]];
        }

        $sourceSerieId = (int) ($message['series_id'] ?? 0);
        $seriesId      = $sourceSerieId > 0 ? $serieIds[$sourceSerieId] : 0;

        $data = [
            'id'          => 0,
            'studytitle'  => trim((string) $message['studytitle']),
            'alias'       => $alias,
            'studydate'   => (string) ($message['studydate'] ?? Factory::getDate()->toSql()),
            'studyintro'  => (string) ($message['studyintro'] ?? ''),
            'studytext'   => (string) ($message['studytext'] ?? ''),
            'series_id'   => $seriesId,
            'messagetype' => (string) ($message['messagetype'] ?? '1'),
            'published'   => (int) ($message['published'] ?? 1),
            'access'      => (int) ($message['access'] ?? 1),
            'language'    => '*',
            'image'       => '',
        ];

        // Each of these is only sent when the fixture actually provided it —
        // CwmmessageModel::save() treats "not submitted" (null/absent) and
        // "submitted empty" differently, replacing every existing row for
        // the study only when the key is present at all.
        if ($teachers !== []) {
            $data['teachers'] = $teachers;
        }

        if (!empty($message['scriptures'])) {
            $data['scriptures'] = $message['scriptures'];
        }

        if (!empty($message['topics'])) {
            $data['topics'] = array_map(
                static fn (string $text): int => $topicMap[$text],
                $message['topics']
            );
        }

        $newId = $this->saveAndRecover('Cwmmessage', $data, $tag, '#__bsms_studies', $alias, 'message');

        $summary['messages']++;

        return $newId;
    }

    /**
     * Save through a model, recording the new row in the manifest — even on
     * failure, if the row exists anyway.
     *
     * `AdminModel::save()` runs `Table::store()` *before* dispatching
     * `onContentAfterSave`, and only catches `\Exception`, not every
     * `\Throwable`. So a save can write the row and then still report
     * failure (a plugin threw and save() returns false) or let an `Error`
     * escape entirely — either way, the ordinary "record after a successful
     * save" path below never runs, and the row would exist as an untracked
     * orphan a manifest-driven remover could never find. Guarding against
     * that here, rather than wrapping the whole import in a transaction, is
     * a deliberate choice — see this class's own docblock for why a
     * transaction does not actually protect against this class of failure.
     *
     * The lookup-by-alias this relies on is unambiguous because
     * {@see validateEntries()} already confirmed the alias collides with
     * nothing on this table before any creation began.
     *
     * @param   string  $modelName    Model name, e.g. `Cwmteacher`.
     * @param   array   $data         The data to save, with `id => 0` for a new row.
     * @param   string  $tag          The import tag.
     * @param   string  $table        `#__`-prefixed table the row belongs to.
     * @param   string  $alias        The row's alias, already validated unique.
     * @param   string  $entityLabel  Singular noun for the failure message (e.g. `teacher`).
     *
     * @return  int  The new row's id.
     *
     * @since  __DEPLOY_VERSION__
     */
    private function saveAndRecover(
        string $modelName,
        array $data,
        string $tag,
        string $table,
        string $alias,
        string $entityLabel
    ): int {
        $model = $this->model($modelName);

        try {
            $ok = $model->save($data);
        } catch (\Throwable $e) {
            $this->recordIfOrphaned($tag, $table, $alias);

            throw $e;
        }

        if (!$ok) {
            $error = $model->getError() ?: 'unknown error';

            $this->recordIfOrphaned($tag, $table, $alias);

            throw new \RuntimeException(\sprintf('Failed to import %s "%s": %s', $entityLabel, $alias, $error));
        }

        $newId = (int) $model->getState($model->getName() . '.id');

        Cwmimportmanifest::recordRow($tag, $table, $newId);

        return $newId;
    }

    /**
     * @param   string  $tag    The import tag.
     * @param   string  $table  `#__`-prefixed table to check.
     * @param   string  $alias  The alias to look up.
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function recordIfOrphaned(string $tag, string $table, string $alias): void
    {
        $db = $this->db();

        $id = $db->setQuery(
            $db->createQuery()
                ->select($db->quoteName('id'))
                ->from($db->quoteName($table))
                ->where($db->quoteName('alias') . ' = :alias')
                ->bind(':alias', $alias, ParameterType::STRING)
        )->loadResult();

        if ($id !== null) {
            Cwmimportmanifest::recordRow($tag, $table, (int) $id);
        }
    }

    /**
     * Save one `mediafiles[]` entry through `CwmmediafileModel`, recording it
     * in the manifest.
     *
     * Cannot reuse {@see saveAndRecover()}/{@see recordIfOrphaned()} — both
     * assume an `alias` column, and `#__bsms_mediafiles` has none. Orphan
     * recovery here instead looks at everything currently on the study and
     * subtracts what `$recordedMediaFileIds` already accounts for — see
     * {@see recordOrphanedMediaFiles()}.
     *
     * @param   string             $tag                    The import tag.
     * @param   array              $mediafile              One `mediafiles[]` entry.
     * @param   int[]              $messageIds             Source message id => new study id.
     * @param   array<string, int> $serverIds              `server_name` => real server id, from {@see validate()}.
     * @param   array<int, true>   $recordedMediaFileIds   Ids this import has already recorded, by reference —
     *                                                      updated here so a later failure's orphan recovery
     *                                                      does not re-record this call's own success.
     * @param   array              $summary                Running summary, updated in place.
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function importMediaFile(
        string $tag,
        array $mediafile,
        array $messageIds,
        array $serverIds,
        array &$recordedMediaFileIds,
        array &$summary
    ): void {
        $studyId  = $messageIds[(int) $mediafile['study_id']];
        $serverId = $serverIds[trim((string) $mediafile['server_name'])];

        $data = [
            'id'         => 0,
            'study_id'   => $studyId,
            'server_id'  => $serverId,
            'podcast_id' => array_map('intval', (array) ($mediafile['podcast_id'] ?? [])),
            'published'  => (int) ($mediafile['published'] ?? 1),
            'access'     => (int) ($mediafile['access'] ?? 1),
            'language'   => '*',
            'params'     => array_intersect_key((array) $mediafile['params'], array_flip(self::MEDIA_PARAM_ALLOWLIST)),
        ];

        $model = $this->model('Cwmmediafile');

        try {
            $ok = $model->save($data);
        } catch (\Throwable $e) {
            $this->recordOrphanedMediaFiles($tag, $studyId, $recordedMediaFileIds);

            throw $e;
        }

        if (!$ok) {
            $error = $model->getError() ?: 'unknown error';

            $this->recordOrphanedMediaFiles($tag, $studyId, $recordedMediaFileIds);

            throw new \RuntimeException(\sprintf('Failed to import media file for study #%d: %s', $studyId, $error));
        }

        $newId = (int) $model->getState($model->getName() . '.id');

        Cwmimportmanifest::recordRow($tag, '#__bsms_mediafiles', $newId);
        $recordedMediaFileIds[$newId] = true;

        $summary['mediafiles']++;
    }

    /**
     * Record every media file on a study that this import has not already
     * recorded — the no-alias equivalent of {@see recordIfOrphaned()}.
     *
     * A study can carry more than one media file, so "everything on the
     * study" is not by itself the orphan: a sibling an earlier, successful
     * call in the same import already recorded must not be re-recorded
     * (harmless, since `Cwmimportmanifest::recordRow()` is idempotent per
     * row, but re-querying it every failure would be wasted work). Excluding
     * `$recordedMediaFileIds` leaves exactly the row this failed call itself
     * created before failing, if any.
     *
     * @param   string           $tag                   The import tag.
     * @param   int              $studyId                The study id to check.
     * @param   array<int, true> $recordedMediaFileIds   Ids already recorded, by reference — updated here.
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function recordOrphanedMediaFiles(string $tag, int $studyId, array &$recordedMediaFileIds): void
    {
        $db = $this->db();

        $ids = $db->setQuery(
            $db->createQuery()
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__bsms_mediafiles'))
                ->where($db->quoteName('study_id') . ' = :studyId')
                ->bind(':studyId', $studyId, ParameterType::INTEGER)
        )->loadColumn();

        foreach ($ids as $id) {
            $id = (int) $id;

            if (!isset($recordedMediaFileIds[$id])) {
                Cwmimportmanifest::recordRow($tag, '#__bsms_mediafiles', $id);
                $recordedMediaFileIds[$id] = true;
            }
        }
    }

    /**
     * Copy and record one already-validated, already-resolved `files[]` entry.
     *
     * @param   string  $tag      The import tag.
     * @param   array   $file     One entry from {@see validateFiles()}'s return value.
     * @param   array   $summary  Running summary, updated in place.
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function importFile(string $tag, array $file, array &$summary): void
    {
        $destDir = \dirname($file['dest']);

        if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
            throw new \RuntimeException(\sprintf('Could not create directory for "%s".', $file['manifestPath']));
        }

        if (!copy($file['source'], $file['dest'])) {
            throw new \RuntimeException(\sprintf('Could not copy file to "%s".', $file['manifestPath']));
        }

        Cwmimportmanifest::recordFile($tag, $file['manifestPath']);
        $summary['files']++;
    }

    /**
     * @param   string  $name  Model name, e.g. `Cwmteacher`.
     *
     * @return  AdminModel
     *
     * @since  __DEPLOY_VERSION__
     */
    private function model(string $name): AdminModel
    {
        $factory = $this->factory ?? Factory::getApplication()->bootComponent('com_proclaim')->getMVCFactory();

        $model = $factory->createModel($name, 'Administrator', ['ignore_request' => true]);

        if (!$model instanceof AdminModel) {
            throw new \RuntimeException(\sprintf(
                'Could not create the "%s" model — the component may not be fully booted.',
                $name
            ));
        }

        return $model;
    }

    /**
     * @return  DatabaseInterface
     *
     * @since  __DEPLOY_VERSION__
     */
    private function db(): DatabaseInterface
    {
        return Factory::getContainer()->get(DatabaseInterface::class);
    }
}
