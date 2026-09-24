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

use CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Additive, tracked import of structured content (#2145, #2173).
 *
 * Reads a JSON payload — never SQL — and inserts through the same
 * `AdminModel::save()` path the admin UI uses, so validation, junction
 * tables (teachers/scriptures) and `#__assets` all stay consistent without
 * hand-rolled insert logic. Every row and file created is recorded in the
 * import-set manifest (#2172, {@see Cwmimportmanifest}) as it is created, so
 * a later import can be found again and cleanly removed.
 *
 * Deliberately narrow: only the sections in {@see ALLOWED_SECTIONS} are
 * recognised, and `#__bsms_templatecode` is not one of them. Template code
 * is PHP that Proclaim writes into the site and the front end executes
 * (#2099) — demo content has no reason to carry it, and refusing the
 * section outright removes that surface rather than depending on
 * validation to catch a bad payload.
 *
 * Everything is validated in one pre-flight pass before anything is
 * created. Discovering a bad file path only after the teacher, series and
 * message rows already exist would leave a rejected import's content
 * sitting in the database — an untrusted archive gets exactly one clean
 * refusal, not a partially-applied one.
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
    private const array ALLOWED_SECTIONS = ['teachers', 'series', 'messages', 'files'];

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
     * @param   array   $payload    Decoded JSON: `teachers`, `series`, `messages`, `files` — see ALLOWED_SECTIONS.
     * @param   string  $sourceDir  Directory the payload's `files[].source` paths are relative to.
     *
     * @return  array{teachers: int, series: int, messages: int, files: int}  Counts of what was created.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function import(string $tag, array $payload, string $sourceDir): array
    {
        if (trim($tag) === '') {
            throw new \InvalidArgumentException('An import tag is required.');
        }

        if (Cwmimportmanifest::exists($tag)) {
            throw new \RuntimeException(\sprintf(
                'An import tagged "%s" already exists; refusing to run it again.',
                $tag
            ));
        }

        $this->validate($payload, $sourceDir);

        $summary = ['teachers' => 0, 'series' => 0, 'messages' => 0, 'files' => 0];

        // Source-id => newly assigned target id. Every foreign key below is
        // resolved through this map, never trusted as a target-site id
        // directly — a fixture's ids are only meaningful within itself.
        // validate() already confirmed every reference resolves, so a
        // lookup miss here would be this importer's own bug, not bad input.
        $teacherIds = [];
        $serieIds   = [];

        foreach ($payload['teachers'] ?? [] as $teacher) {
            $teacherIds[(int) $teacher['id']] = $this->importTeacher($tag, $teacher, $summary);
        }

        foreach ($payload['series'] ?? [] as $serie) {
            $teacherId = isset($serie['teacher_id']) ? $teacherIds[(int) $serie['teacher_id']] : 0;

            $serieIds[(int) $serie['id']] = $this->importSerie($tag, $serie, $teacherId, $summary);
        }

        foreach ($payload['messages'] ?? [] as $message) {
            $this->importMessage($tag, $message, $teacherIds, $serieIds, $summary);
        }

        if (!empty($payload['files'])) {
            $this->importFiles($tag, $payload['files'], $sourceDir, $summary);
        }

        return $summary;
    }

    /**
     * Check the whole payload before anything is created.
     *
     * @param   array   $payload    See {@see import()}.
     * @param   string  $sourceDir  See {@see import()}.
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function validate(array $payload, string $sourceDir): void
    {
        $unknown = array_diff(array_keys($payload), self::ALLOWED_SECTIONS);

        if ($unknown !== []) {
            throw new \RuntimeException(
                'Unrecognised import section(s): ' . implode(', ', $unknown)
                . '. Refusing rather than importing content this importer does not explicitly understand.'
            );
        }

        $teacherIds = $this->validateSourceIds($payload['teachers'] ?? [], 'teachers', 'teachername');
        $serieIds   = $this->validateSourceIds($payload['series'] ?? [], 'series', 'series_text');

        // Checked here, not left for Table::store()'s unique-key failure to
        // catch, because that failure would arrive after earlier entries in
        // the same import already exist — exactly the partial-import problem
        // this whole pre-flight pass exists to prevent (a re-run under a
        // fresh tag, or a re-import of a set #2174 partly kept, would
        // otherwise leave a collided teacher behind alongside a failed
        // series).
        $this->validateNoNameCollisions($payload['teachers'] ?? [], '#__bsms_teachers', 'teachername', 'teachers');
        $this->validateNoNameCollisions($payload['series'] ?? [], '#__bsms_series', 'series_text', 'series');

        foreach ($payload['series'] ?? [] as $i => $serie) {
            if (isset($serie['teacher_id']) && !isset($teacherIds[(int) $serie['teacher_id']])) {
                throw new \RuntimeException(\sprintf(
                    'series[%d].teacher_id %s does not match any teachers[].id.',
                    $i,
                    $serie['teacher_id']
                ));
            }
        }

        foreach ($payload['messages'] ?? [] as $i => $message) {
            if (trim((string) ($message['studytitle'] ?? '')) === '') {
                throw new \RuntimeException(\sprintf('messages[%d] is missing studytitle.', $i));
            }

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
            // mint new ones — resolving here, up front, means a fixture
            // naming a topic this site doesn't have is a validation error,
            // not a silently-created, unmanifested topic row (see the
            // topics handling note on importMessage()).
            foreach ((array) ($message['topics'] ?? []) as $topicText) {
                if ($this->resolveTopicId((string) $topicText) === null) {
                    throw new \RuntimeException(\sprintf(
                        'messages[%d] references topic "%s", which does not exist on this site.',
                        $i,
                        $topicText
                    ));
                }
            }
        }

        if (!empty($payload['files'])) {
            $this->validateFiles($payload['files'], $sourceDir);
        }
    }

    /**
     * Every entry has a positive, unique `id`, and its title field is non-empty.
     *
     * @param   array   $entries    `teachers[]` or `series[]`.
     * @param   string  $section    Section name, for error messages.
     * @param   string  $titleKey   The required non-empty field.
     *
     * @return  array<int, true>  The valid source ids seen, as lookup keys.
     *
     * @since  __DEPLOY_VERSION__
     */
    private function validateSourceIds(array $entries, string $section, string $titleKey): array
    {
        $seen = [];

        foreach ($entries as $i => $entry) {
            $id = $entry['id'] ?? null;

            if (!\is_int($id) || $id <= 0) {
                throw new \RuntimeException(\sprintf('%s[%d] is missing a positive integer id.', $section, $i));
            }

            if (isset($seen[$id])) {
                throw new \RuntimeException(\sprintf('%s[%d] reuses source id %d.', $section, $i, $id));
            }

            if (trim((string) ($entry[$titleKey] ?? '')) === '') {
                throw new \RuntimeException(\sprintf('%s[%d] is missing %s.', $section, $i, $titleKey));
            }

            $seen[$id] = true;
        }

        return $seen;
    }

    /**
     * Validate every `files[]` entry without writing anything.
     *
     * @param   array   $files      The payload's `files` section.
     * @param   string  $sourceDir  Directory `source` paths are relative to.
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function validateFiles(array $files, string $sourceDir): void
    {
        $realSourceDir = realpath($sourceDir);

        if ($realSourceDir === false) {
            throw new \RuntimeException(\sprintf('Import source directory "%s" does not exist.', $sourceDir));
        }

        foreach ($files as $i => $file) {
            $source = (string) ($file['source'] ?? '');
            $dest   = (string) ($file['dest'] ?? '');

            if ($source === '' || $dest === '') {
                throw new \RuntimeException(\sprintf('files[%d] is missing source or dest.', $i));
            }

            $extension = strtolower(pathinfo($dest, \PATHINFO_EXTENSION));

            if (!\in_array($extension, self::IMAGE_EXTENSIONS, true)) {
                throw new \RuntimeException(\sprintf('File "%s" has a disallowed extension.', $dest));
            }

            $realSource = realpath($realSourceDir . '/' . $source);

            if ($realSource === false || !str_starts_with($realSource, $realSourceDir . \DIRECTORY_SEPARATOR)) {
                throw new \RuntimeException(\sprintf(
                    'Import file source "%s" was not found in the import package.',
                    $source
                ));
            }

            if (Cwmthumbnail::resolveWithinAllowedPaths($dest) === false) {
                throw new \RuntimeException(\sprintf(
                    'File destination "%s" is outside the allowed image paths.',
                    $dest
                ));
            }

            if (is_file(Cwmthumbnail::resolveWithinAllowedPaths($dest))) {
                throw new \RuntimeException(\sprintf('"%s" already exists; refusing to overwrite it.', $dest));
            }
        }
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
        $model = $this->model('Cwmteacher');

        $data = [
            'id'          => 0,
            'teachername' => trim((string) $teacher['teachername']),
            'alias'       => (string) ($teacher['alias'] ?? ''),
            'title'       => (string) ($teacher['title'] ?? ''),
            'information' => (string) ($teacher['information'] ?? ''),
            // Path-validated file placement is handled separately by
            // importFiles(); the model's own thumbnail pipeline is not
            // exercised here (see the epic follow-up in #2145).
            'image'        => '',
            'published'    => (int) ($teacher['published'] ?? 1),
            'access'       => (int) ($teacher['access'] ?? 1),
            'language'     => '*',
            'contact'      => 0,
            'social_links' => '',
            // NOT NULL with no default (verified against a live schema, not
            // just install.mysql.utf8.sql — they've drifted). The admin form
            // always submits this, even empty, which is what normally masks
            // it; a programmatically-built $data array has to supply it
            // explicitly or Table::store() fails under strict SQL mode.
            'address' => '',
        ];

        if (!$model->save($data)) {
            throw new \RuntimeException(\sprintf(
                'Failed to import teacher "%s": %s',
                $data['teachername'],
                $model->getError() ?: 'unknown error'
            ));
        }

        $newId = (int) $model->getState($model->getName() . '.id');

        Cwmimportmanifest::recordRow($tag, '#__bsms_teachers', $newId);
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
        $model = $this->model('Cwmserie');

        $data = [
            'id'          => 0,
            'series_text' => trim((string) $serie['series_text']),
            'alias'       => (string) ($serie['alias'] ?? ''),
            'teacher'     => $teacherId,
            'description' => (string) ($serie['description'] ?? ''),
            'image'       => '',
            'published'   => (int) ($serie['published'] ?? 1),
            'access'      => (int) ($serie['access'] ?? 1),
            'language'    => '*',
        ];

        if (!$model->save($data)) {
            throw new \RuntimeException(\sprintf(
                'Failed to import series "%s": %s',
                $data['series_text'],
                $model->getError() ?: 'unknown error'
            ));
        }

        $newId = (int) $model->getState($model->getName() . '.id');

        Cwmimportmanifest::recordRow($tag, '#__bsms_series', $newId);
        $summary['series']++;

        return $newId;
    }

    /**
     * @param   string   $tag         The import tag.
     * @param   array    $message     One `messages[]` entry.
     * @param   int[]    $teacherIds  Source teacher id => new teacher id.
     * @param   int[]    $serieIds    Source series id => new series id.
     * @param   array    $summary     Running summary, updated in place.
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function importMessage(string $tag, array $message, array $teacherIds, array $serieIds, array &$summary): void
    {
        $model = $this->model('Cwmmessage');

        $teachers = [];

        foreach ((array) ($message['teacher_ids'] ?? []) as $sourceTeacherId) {
            $teachers[] = ['teacher_id' => $teacherIds[(int) $sourceTeacherId]];
        }

        $sourceSerieId = (int) ($message['series_id'] ?? 0);
        $seriesId      = $sourceSerieId > 0 ? $serieIds[$sourceSerieId] : 0;

        $data = [
            'id'          => 0,
            'studytitle'  => trim((string) $message['studytitle']),
            'alias'       => (string) ($message['alias'] ?? ''),
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
            // Resolved to numeric ids, not the raw text tags — validate()
            // already confirmed every one matches an existing topic.
            // CwmmessageModel::saveTopics() treats a numeric tag as an
            // existing id and a text tag it can't match as instructions to
            // create a new topic (via a model the importer otherwise never
            // touches); passing ids keeps that path closed and keeps every
            // topic this study ends up with already accounted for outside
            // the manifest, exactly as a demo set should be.
            $data['topics'] = array_map(
                fn (string $text): int => $this->resolveTopicId($text),
                $message['topics']
            );
        }

        if (!$model->save($data)) {
            throw new \RuntimeException(\sprintf(
                'Failed to import message "%s": %s',
                $data['studytitle'],
                $model->getError() ?: 'unknown error'
            ));
        }

        $newId = (int) $model->getState($model->getName() . '.id');

        Cwmimportmanifest::recordRow($tag, '#__bsms_studies', $newId);
        $summary['messages']++;
    }

    /**
     * Copy and record every `files[]` entry. Already validated by
     * {@see validateFiles()} — this only re-derives the same paths to act on them.
     *
     * @param   string    $tag        The import tag.
     * @param   array     $files      The payload's `files` section.
     * @param   string    $sourceDir  Directory `source` paths are relative to.
     * @param   array     $summary    Running summary, updated in place.
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function importFiles(string $tag, array $files, string $sourceDir, array &$summary): void
    {
        $realSourceDir = (string) realpath($sourceDir);

        foreach ($files as $file) {
            $source = (string) $file['source'];
            $dest   = (string) $file['dest'];

            $realSource   = (string) realpath($realSourceDir . '/' . $source);
            $resolvedDest = Cwmthumbnail::resolveWithinAllowedPaths($dest);

            if ($resolvedDest === false) {
                // validateFiles() already rejected this shape; unreachable
                // unless the payload changed between the two calls.
                throw new \RuntimeException(\sprintf('File destination "%s" is outside the allowed image paths.', $dest));
            }

            $destDir = \dirname($resolvedDest);

            if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
                throw new \RuntimeException(\sprintf('Could not create directory for "%s".', $dest));
            }

            if (!copy($realSource, $resolvedDest)) {
                throw new \RuntimeException(\sprintf('Could not copy file to "%s".', $dest));
            }

            Cwmimportmanifest::recordFile($tag, $dest);
            $summary['files']++;
        }
    }

    /**
     * Resolve topic text to an existing topic's id.
     *
     * @param   string  $text  The topic text to match, verbatim.
     *
     * @return  ?int
     *
     * @since  __DEPLOY_VERSION__
     */
    private function resolveTopicId(string $text): ?int
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $id = $db->setQuery(
            $db->createQuery()
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__bsms_topics'))
                ->where($db->quoteName('topic_text') . ' = :text')
                ->bind(':text', $text, ParameterType::STRING)
        )->loadResult();

        return $id !== null ? (int) $id : null;
    }

    /**
     * Refuse a name this table already has, rather than letting the eventual
     * `Table::store()` unique-key failure surface it after earlier entries
     * in the same import have already been created.
     *
     * @param   array   $entries  `teachers[]` or `series[]`.
     * @param   string  $table    `#__`-prefixed table to check.
     * @param   string  $column   The title column (`teachername` / `series_text`).
     * @param   string  $section  Section name, for error messages.
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function validateNoNameCollisions(array $entries, string $table, string $column, string $section): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        foreach ($entries as $i => $entry) {
            $name = trim((string) $entry[$column]);

            $exists = (int) $db->setQuery(
                $db->createQuery()
                    ->select('COUNT(*)')
                    ->from($db->quoteName($table))
                    ->where($db->quoteName($column) . ' = :name')
                    ->bind(':name', $name, ParameterType::STRING)
            )->loadResult() > 0;

            if ($exists) {
                throw new \RuntimeException(\sprintf(
                    '%s[%d] "%s" already exists on this site.',
                    $section,
                    $i,
                    $name
                ));
            }
        }
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
}
