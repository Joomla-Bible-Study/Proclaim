<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;

/**
 * Schema.org JSON-LD structured data helper.
 *
 * Pure static methods that accept item data and return JSON-LD arrays
 * for injection into frontend page heads.
 *
 * @since  10.1.0
 */
class CwmschemaorgHelper
{
    /**
     * Build CreativeWork JSON-LD for a single sermon detail page.
     *
     * @param   object  $item      Sermon item from model
     * @param   string  $url       Canonical page URL
     * @param   string  $siteName  Site name for publisher
     *
     * @return array JSON-LD data array
     *
     * @since 10.1.0
     */
    public static function buildSermonDetail(object $item, string $url, string $siteName): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type'    => 'CreativeWork',
            'name'     => $item->studytitle ?? '',
            'url'      => $url,
        ];

        // Description from studyintro
        $desc = self::cleanDescription($item->studyintro ?? '');

        if ($desc !== '') {
            $data['description'] = $desc;
        }

        // Date published
        if (!empty($item->studydate) && $item->studydate !== '0000-00-00 00:00:00') {
            $data['datePublished'] = self::toIso8601($item->studydate);
        }

        // Author (primary teacher)
        if (!empty($item->teachername)) {
            $data['author'] = [
                '@type' => 'Person',
                'name'  => $item->teachername,
            ];
        }

        // Series
        if (!empty($item->series_text)) {
            $series = [
                '@type' => 'CreativeWorkSeries',
                'name'  => $item->series_text,
            ];

            if (!empty($item->series_id) && (int) $item->series_id > 0) {
                $series['url'] = self::buildAbsoluteUrl(
                    'index.php?option=com_proclaim&view=cwmseriesdisplay&id=' . (int) $item->series_id
                );
            }

            $data['isPartOf'] = $series;
        }

        // Topics
        $topics = self::parseTopics($item->topic_text ?? '');

        if ($topics !== []) {
            $data['about'] = $topics;
        }

        // Message type as genre
        if (!empty($item->messageType)) {
            $data['genre'] = $item->messageType;
        }

        // Location
        if (!empty($item->location_text)) {
            $data['locationCreated'] = [
                '@type' => 'Place',
                'name'  => $item->location_text,
            ];
        }

        // Publisher
        if ($siteName !== '') {
            $data['publisher'] = [
                '@type' => 'Organization',
                'name'  => $siteName,
            ];
        }

        return $data;
    }

    /**
     * Build ItemList JSON-LD for the sermons list page.
     *
     * @param   array   $items     Array of sermon items
     * @param   string  $pageUrl   Canonical page URL
     * @param   string  $siteName  Site name
     *
     * @return array JSON-LD data array
     *
     * @since 10.1.0
     */
    public static function buildSermonList(array $items, string $pageUrl, string $siteName): array
    {
        $data = [
            '@context'      => 'https://schema.org',
            '@type'         => 'ItemList',
            'url'           => $pageUrl,
            'numberOfItems' => \count($items),
        ];

        if ($siteName !== '') {
            $data['name'] = $siteName;
        }

        $listItems = [];

        foreach ($items as $position => $item) {
            $listItem = [
                '@type'    => 'ListItem',
                'position' => $position + 1,
            ];

            $sermonData = [
                '@type' => 'CreativeWork',
                'name'  => $item->studytitle ?? '',
            ];

            if (!empty($item->id)) {
                $sermonData['url'] = self::buildAbsoluteUrl(
                    'index.php?option=com_proclaim&view=cwmsermon&id=' . (int) $item->id
                );
            }

            if (!empty($item->studydate) && $item->studydate !== '0000-00-00 00:00:00') {
                $sermonData['datePublished'] = self::toIso8601($item->studydate);
            }

            if (!empty($item->teachername)) {
                $sermonData['author'] = [
                    '@type' => 'Person',
                    'name'  => $item->teachername,
                ];
            }

            $listItem['item'] = $sermonData;
            $listItems[]      = $listItem;
        }

        if ($listItems !== []) {
            $data['itemListElement'] = $listItems;
        }

        return $data;
    }

    /**
     * Build Person JSON-LD for a teacher detail page.
     *
     * @param   object  $item  Teacher item from model
     * @param   string  $url   Canonical page URL
     *
     * @return array JSON-LD data array
     *
     * @since 10.1.0
     */
    public static function buildTeacherDetail(object $item, string $url): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type'    => 'Person',
            'name'     => $item->teachername ?? '',
            'url'      => $url,
        ];

        // Job title
        if (!empty($item->title)) {
            $data['jobTitle'] = $item->title;
        }

        // Description from short bio or information
        $desc = self::cleanDescription($item->short ?? $item->information ?? '');

        if ($desc !== '') {
            $data['description'] = $desc;
        }

        // Image — use raw path, not rendered HTML
        $imagePath = $item->teacher_image ?? $item->image ?? '';

        if (\is_string($imagePath) && $imagePath !== '' && !str_contains($imagePath, '<')) {
            $data['image'] = Uri::root() . ltrim($imagePath, '/');
        }

        // Social links (sameAs)
        $sameAs = self::collectSocialLinks($item);

        if ($sameAs !== []) {
            $data['sameAs'] = $sameAs;
        }

        return $data;
    }

    /**
     * Build CreativeWorkSeries JSON-LD for a series detail page.
     *
     * @param   object  $item      Series item (single object despite $this->items name in view)
     * @param   array   $studies   Array of study items belonging to this series
     * @param   string  $url       Canonical page URL
     * @param   string  $siteName  Site name for publisher
     *
     * @return array JSON-LD data array
     *
     * @since 10.1.0
     */
    public static function buildSeriesDetail(object $item, array $studies, string $url, string $siteName): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type'    => 'CreativeWorkSeries',
            'name'     => $item->series_text ?? '',
            'url'      => $url,
        ];

        // Description
        $desc = self::cleanDescription($item->description ?? '');

        if ($desc !== '') {
            $data['description'] = $desc;
        }

        // Series image
        $imagePath = $item->series_thumbnail ?? '';

        if (\is_string($imagePath) && $imagePath !== '' && !str_contains($imagePath, '<')) {
            $data['image'] = Uri::root() . ltrim($imagePath, '/');
        }

        // Teacher as author
        if (!empty($item->teachername)) {
            $data['author'] = [
                '@type' => 'Person',
                'name'  => $item->teachername,
            ];
        }

        // Publisher
        if ($siteName !== '') {
            $data['publisher'] = [
                '@type' => 'Organization',
                'name'  => $siteName,
            ];
        }

        // Studies as hasPart
        $parts = [];

        foreach ($studies as $study) {
            $part = [
                '@type' => 'CreativeWork',
                'name'  => $study->studytitle ?? '',
            ];

            if (!empty($study->id)) {
                $part['url'] = self::buildAbsoluteUrl(
                    'index.php?option=com_proclaim&view=cwmsermon&id=' . (int) $study->id
                );
            }

            if (!empty($study->studydate) && $study->studydate !== '0000-00-00 00:00:00') {
                $part['datePublished'] = self::toIso8601($study->studydate);
            }

            $parts[] = $part;
        }

        if ($parts !== []) {
            $data['hasPart'] = $parts;
        }

        return $data;
    }

    /**
     * Build ItemList JSON-LD for the teachers list page.
     *
     * @param   array   $items     Array of teacher items
     * @param   string  $pageUrl   Canonical page URL
     * @param   string  $siteName  Site name
     *
     * @return array JSON-LD data array
     *
     * @since 10.3.0
     */
    public static function buildTeacherList(array $items, string $pageUrl, string $siteName): array
    {
        $data = [
            '@context'      => 'https://schema.org',
            '@type'         => 'ItemList',
            'url'           => $pageUrl,
            'numberOfItems' => \count($items),
        ];

        if ($siteName !== '') {
            $data['name'] = $siteName;
        }

        $listItems = [];

        foreach ($items as $position => $item) {
            $listItem = [
                '@type'    => 'ListItem',
                'position' => $position + 1,
            ];

            $personData = [
                '@type' => 'Person',
                'name'  => $item->teachername ?? '',
            ];

            if (!empty($item->id)) {
                $personData['url'] = self::buildAbsoluteUrl(
                    'index.php?option=com_proclaim&view=cwmteacher&id=' . (int) $item->id
                );
            }

            if (!empty($item->title)) {
                $personData['jobTitle'] = $item->title;
            }

            $imagePath = $item->teacher_image ?? $item->teacher_thumbnail ?? '';

            if (\is_string($imagePath) && $imagePath !== '' && !str_contains($imagePath, '<')) {
                $personData['image'] = Uri::root() . ltrim($imagePath, '/');
            }

            $listItem['item'] = $personData;
            $listItems[]      = $listItem;
        }

        if ($listItems !== []) {
            $data['itemListElement'] = $listItems;
        }

        return $data;
    }

    /**
     * Build ItemList JSON-LD for the series list page.
     *
     * @param   array   $items     Array of series items
     * @param   string  $pageUrl   Canonical page URL
     * @param   string  $siteName  Site name
     *
     * @return array JSON-LD data array
     *
     * @since 10.3.0
     */
    public static function buildSeriesList(array $items, string $pageUrl, string $siteName): array
    {
        $data = [
            '@context'      => 'https://schema.org',
            '@type'         => 'ItemList',
            'url'           => $pageUrl,
            'numberOfItems' => \count($items),
        ];

        if ($siteName !== '') {
            $data['name'] = $siteName;
        }

        $listItems = [];

        foreach ($items as $position => $item) {
            $listItem = [
                '@type'    => 'ListItem',
                'position' => $position + 1,
            ];

            $seriesData = [
                '@type' => 'CreativeWorkSeries',
                'name'  => $item->series_text ?? '',
            ];

            if (!empty($item->id)) {
                $seriesData['url'] = self::buildAbsoluteUrl(
                    'index.php?option=com_proclaim&view=cwmseriesdisplay&id=' . (int) $item->id
                );
            }

            $desc = self::cleanDescription($item->description ?? '');

            if ($desc !== '') {
                $seriesData['description'] = $desc;
            }

            $imagePath = $item->series_thumbnail ?? '';

            if (\is_string($imagePath) && $imagePath !== '' && !str_contains($imagePath, '<')) {
                $seriesData['image'] = Uri::root() . ltrim($imagePath, '/');
            }

            if (!empty($item->teachername)) {
                $seriesData['author'] = [
                    '@type' => 'Person',
                    'name'  => $item->teachername,
                ];
            }

            $listItem['item'] = $seriesData;
            $listItems[]      = $listItem;
        }

        if ($listItems !== []) {
            $data['itemListElement'] = $listItems;
        }

        return $data;
    }

    /**
     * Check if Joomla's Schema.org system plugin has stored schema for an item.
     *
     * When true, the system plugin will output the schema in its @graph —
     * our standalone inject() should be skipped to avoid duplicates.
     *
     * @param   int     $itemId   The item ID
     * @param   string  $context  The admin form context (e.g., 'com_proclaim.cwmmessage')
     *
     * @return  bool  True if the system plugin has per-item schema stored
     *
     * @since   10.3.0
     */
    /**
     * Get the organization name for Schema.org publisher/worksFor.
     *
     * Uses the admin param `org_name` if set, otherwise falls back to site name.
     *
     * @return  string  Organization name
     *
     * @since   10.3.0
     */
    public static function getOrgName(): string
    {
        try {
            $params  = Cwmparams::getAdmin()->params;
            $orgName = $params->get('org_name', '');

            if ($orgName !== '') {
                return $orgName;
            }

            return Factory::getApplication()->get('sitename', '');
        } catch (\Throwable $e) {
            CwmDebug::error('getOrganizationName failed', $e, 'schemaorg');

            return '';
        }
    }

    public static function hasJoomlaSchema(int $itemId, string $context): bool
    {
        if ($itemId <= 0) {
            return false;
        }

        try {
            $db    = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
            $query = $db->createQuery()
                ->select('COUNT(*)')
                ->from($db->quoteName('#__schemaorg'))
                ->where($db->quoteName('itemId') . ' = ' . $itemId)
                ->where($db->quoteName('context') . ' = ' . $db->quote($context));
            $db->setQuery($query);

            return (int) $db->loadResult() > 0;
        } catch (\Throwable $e) {
            CwmDebug::error('hasJoomlaSchema query failed', $e, 'schemaorg');

            return false;
        }
    }

    /**
     * Inject a JSON-LD array into the document head.
     *
     * When $itemId and $context are provided, checks if Joomla's system
     * schema.org plugin already has per-item schema stored — if so, skips
     * injection to avoid duplicate structured data.
     *
     * @param   array   $data     JSON-LD data array
     * @param   int     $itemId   Optional item ID for duplicate check
     * @param   string  $context  Optional admin form context (e.g., 'com_proclaim.cwmmessage')
     *
     * @return void
     *
     * @since 10.1.0
     */
    public static function inject(array $data, int $itemId = 0, string $context = ''): void
    {
        if ($data === []) {
            return;
        }

        // Skip if Joomla's system plugin already has per-item schema for this item
        if ($itemId > 0 && $context !== '' && self::hasJoomlaSchema($itemId, $context)) {
            return;
        }

        try {
            $document    = Factory::getApplication()->getDocument();
            $prettyPrint = \defined('JDEBUG') && JDEBUG ? JSON_PRETTY_PRINT : 0;
            // No JSON_UNESCAPED_SLASHES: a "</script" sequence in any string value
            // (e.g. studytitle, which bypasses InputFilter via filter="trim") would
            // otherwise survive verbatim into the JSON and terminate this <script>
            // tag early, letting the rest be parsed as HTML/JS. JSON_HEX_TAG rewrites
            // every '<'/'>' as a \uXXXX escape so no literal angle bracket survives.
            $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | $prettyPrint);
        } catch (\Exception $e) {
            return;
        }

        $document->addCustomTag(
            '<script type="application/ld+json">' . "\n" . $json . "\n" . '</script>'
        );
    }

    /**
     * Strip HTML tags and truncate to ~200 characters for description fields.
     *
     * @param   string  $text  Raw text (may contain HTML)
     *
     * @return string Cleaned text
     *
     * @since 10.1.0
     */
    public static function cleanDescription(string $text): string
    {
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = trim(preg_replace('/\s+/', ' ', $text));

        if (mb_strlen($text) > 200) {
            $text = mb_substr($text, 0, 197) . '...';
        }

        return $text;
    }

    /**
     * Convert a date string to ISO 8601 format.
     *
     * @param   string  $date  Date string
     *
     * @return string ISO 8601 date
     *
     * @since 10.1.0
     */
    private static function toIso8601(string $date): string
    {
        try {
            return (new \DateTime($date))->format('c');
        } catch (\Exception $e) {
            return $date;
        }
    }

    /**
     * Parse comma-separated topic text into an array of strings.
     *
     * @param   string  $topicText  Comma-separated topics
     *
     * @return string[] Array of topic strings
     *
     * @since 10.1.0
     */
    private static function parseTopics(string $topicText): array
    {
        if ($topicText === '') {
            return [];
        }

        $topics = array_map('trim', explode(',', $topicText));

        return array_values(array_filter($topics, static fn (string $t): bool => $t !== ''));
    }

    /**
     * Build an absolute URL from a Joomla SEF route.
     *
     * @param   string  $route  Internal Joomla route
     *
     * @return string Absolute URL
     *
     * @since 10.1.0
     */
    private static function buildAbsoluteUrl(string $route): string
    {
        // Route::_() can return null for unroutable input (missing
        // application, unknown component, etc.). Treat that as an empty
        // path rather than passing null to ltrim(), which is deprecated
        // in PHP 8.1+.
        $path = Route::_($route) ?? '';

        return rtrim(Uri::root(), '/') . '/' . ltrim($path, '/');
    }

    /**
     * Collect valid social/web links from a teacher item into a sameAs array.
     *
     * @param   object  $item  Teacher item
     *
     * @return string[] Array of URLs
     *
     * @since 10.1.0
     */
    private static function collectSocialLinks(object $item): array
    {
        $links = [];

        // Prefer the social_links subform JSON — populated by the PreachIT
        // importer and the newer admin UI, which the legacy link1-3 columns
        // are not. Mirrors buildTeacherSchemaFromRow()'s resolution order.
        if (!empty($item->social_links) && \is_string($item->social_links)) {
            try {
                $decoded = json_decode($item->social_links, true, 512, JSON_THROW_ON_ERROR);

                foreach ($decoded as $link) {
                    if (!empty($link['url']) && filter_var($link['url'], FILTER_VALIDATE_URL)) {
                        $links[] = $link['url'];
                    }
                }
            } catch (\Throwable) {
                // Malformed JSON — fall through to the legacy fields below.
            }
        }

        if (empty($links)) {
            $fields = ['facebooklink', 'twitterlink', 'bloglink', 'website', 'link1', 'link2', 'link3'];

            foreach ($fields as $field) {
                $value = $item->$field ?? '';

                if (\is_string($value) && $value !== '' && filter_var($value, FILTER_VALIDATE_URL)) {
                    $links[] = $value;
                }
            }
        }

        return $links;
    }

    /**
     * Sync mode: only create entries for items that have no schema row.
     *
     * @since 10.3.0
     */
    public const SYNC_NEW = 'new';

    /**
     * Sync mode: update entries that haven't been manually edited (hash match).
     *
     * @since 10.3.0
     */
    public const SYNC_SMART = 'smart';

    /**
     * Sync mode: overwrite all entries regardless of manual edits.
     *
     * @since 10.3.0
     */
    public const SYNC_FORCE = 'force';

    /**
     * Backfill the `author` field on a sermon's schema row from the
     * teacher junction. Used after `saveTeachers()` because the schemaorg
     * system plugin fires `onContentAfterSave` *during* `parent::save()`,
     * before the junction has been written — so the auto-generated
     * schema never had a chance to query teacher names.
     *
     * Only fills `author` when it's missing/empty, so a user-customized
     * value entered on the Schema tab is preserved across saves. To
     * fully refresh from current teachers, the user can click Reset
     * Schema (which calls `syncOne()`).
     *
     * @param   int  $sermonId  Sermon (study) primary key
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public static function ensureSermonAuthor(int $sermonId): void
    {
        if ($sermonId <= 0) {
            return;
        }

        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $context = 'com_proclaim.cwmmessage';

        $query = $db->createQuery()
            ->select($db->quoteName(['id', 'schema']))
            ->from($db->quoteName('#__schemaorg'))
            ->where($db->quoteName('itemId') . ' = ' . $sermonId)
            ->where($db->quoteName('context') . ' = ' . $db->quote($context));
        $row = $db->setQuery($query)->loadObject();

        if (!$row) {
            return;
        }

        try {
            $schema = json_decode((string) $row->schema, true, 512, JSON_THROW_ON_ERROR) ?: [];
        } catch (\JsonException) {
            return;
        }

        // Author already set — preserve it (user may have customized).
        if (!empty($schema['author']['name'])) {
            return;
        }

        $tQuery = $db->createQuery()
            ->select($db->quoteName('t.teachername'))
            ->from($db->quoteName('#__bsms_teachers', 't'))
            ->innerJoin(
                $db->quoteName('#__bsms_study_teachers', 'st') . ' ON '
                . $db->quoteName('st.teacher_id') . ' = ' . $db->quoteName('t.id')
            )
            ->where($db->quoteName('st.study_id') . ' = ' . $sermonId)
            ->order($db->quoteName('st.ordering') . ' ASC');
        $names = $db->setQuery($tQuery)->loadColumn() ?: [];

        if (empty($names)) {
            return;
        }

        $schema['author'] = ['@type' => 'Person', 'name' => implode(', ', $names)];

        try {
            $entry         = new \stdClass();
            $entry->id     = (int) $row->id;
            $entry->schema = json_encode($schema, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            $db->updateObject('#__schemaorg', $entry, 'id');
        } catch (\JsonException) {
            // Re-encode failed — leave the row alone.
        }
    }

    /**
     * Force-sync schema.org data for a single item by ID and context.
     *
     * @param   int     $itemId   Item ID
     * @param   string  $context  Context (e.g., 'com_proclaim.cwmmessage')
     *
     * @return  bool  True if synced
     *
     * @since   10.3.0
     */
    public static function syncOne(int $itemId, string $context): bool
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $contextMap = [
            'com_proclaim.cwmmessage' => '#__bsms_studies',
            'com_proclaim.teacher'    => '#__bsms_teachers',
            'com_proclaim.serie'      => '#__bsms_series',
        ];

        $table = $contextMap[$context] ?? null;

        if ($table === null) {
            return false;
        }

        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName($table))
            ->where($db->quoteName('id') . ' = ' . $itemId);
        $item = $db->setQuery($query)->loadObject();

        if ($item === null) {
            return false;
        }

        $schemaType = match ($context) {
            'com_proclaim.cwmmessage' => 'Sermon',
            'com_proclaim.teacher'    => 'Teacher',
            'com_proclaim.serie'      => 'Series',
            default                   => null,
        };

        if ($schemaType === null) {
            return false;
        }

        // Build schema using context-specific logic
        $schema = match ($context) {
            'com_proclaim.cwmmessage' => self::buildSermonSchemaFromRow($db, $item),
            'com_proclaim.teacher'    => self::buildTeacherSchemaFromRow($item),
            'com_proclaim.serie'      => self::buildSeriesSchemaFromRow($item),
            default                   => null,
        };

        if ($schema === null) {
            return false;
        }

        self::upsertSchemaRow($db, $itemId, $context, $schemaType, $schema);

        return true;
    }

    /**
     * Number of items processed per bulk-sync chunk.
     *
     * Sized so a chunk stays comfortably inside a default `max_execution_time`
     * even on slow shared hosting, since each chunk is one HTTP round trip.
     *
     * @since 10.5.6
     */
    public const SYNC_CHUNK_SIZE = 100;

    /**
     * Ordered list of the item types a full sync walks through.
     *
     * @since 10.5.6
     */
    private const SYNC_TYPES = ['messages', 'teachers', 'series'];

    /**
     * Bulk-sync every published message, teacher and series in one call.
     *
     * Drives `syncChunk()` to completion internally. On a large site prefer
     * calling `syncChunk()` from the caller's own loop (the admin UI does, over
     * successive AJAX requests) so no single request has to finish the whole job.
     *
     * @param   string  $mode  Sync mode (new, smart, force)
     *
     * @return  array{messages: int, teachers: int, series: int, skipped: int}
     *
     * @since   10.3.0
     */
    public static function syncAll(string $mode = self::SYNC_SMART): array
    {
        $counts = ['messages' => 0, 'teachers' => 0, 'series' => 0, 'skipped' => 0];
        $type   = self::SYNC_TYPES[0];
        $lastId = 0;
        $rounds = 0;

        // Bounds the walk if a cursor ever stops advancing, mirroring the cap the
        // admin JS puts on its own chunk loop. Generous enough that no real
        // library reaches it: SYNC_CHUNK_SIZE items per round.
        $maxRounds = 100000;

        do {
            $result = self::syncChunk($mode, $type, $lastId);

            foreach ($counts as $key => $value) {
                $counts[$key] = $value + ($result['counts'][$key] ?? 0);
            }

            $type   = $result['type'];
            $lastId = $result['lastId'];
            $rounds++;
        } while (!$result['done'] && $rounds < $maxRounds);

        return $counts;
    }

    /**
     * Sync one bounded batch of items and report where to resume.
     *
     * The cursor is a (`type`, `lastId`) pair walking messages, then teachers,
     * then series in primary-key order. Keyset paging rather than OFFSET means a
     * concurrent insert cannot make the walk skip or repeat a row.
     *
     * Deliberately not wrapped in a transaction. Each item's schema row is
     * independently valid, so a run cut short by a timeout leaves consistent
     * data that the returned cursor can resume from — which is what finding 6 of
     * issue #1562 wanted "no rollback" to buy. A transaction spanning thousands
     * of upserts would instead hold row locks for the length of the whole sync.
     *
     * @param   string  $mode    Sync mode (new, smart, force)
     * @param   string  $type    Cursor type: messages, teachers or series
     * @param   int     $lastId  Highest primary key already processed for $type
     * @param   int     $limit   Maximum items to process in this batch
     *
     * @return  array{type: string, lastId: int, done: bool, counts: array{messages: int, teachers: int, series: int, skipped: int}}
     *
     * @since   10.5.6
     */
    public static function syncChunk(
        string $mode = self::SYNC_SMART,
        string $type = 'messages',
        int $lastId = 0,
        int $limit = self::SYNC_CHUNK_SIZE
    ): array {
        $counts = ['messages' => 0, 'teachers' => 0, 'series' => 0, 'skipped' => 0];

        $position = array_search($type, self::SYNC_TYPES, true);

        // An unrecognised cursor type can only come from a tampered or stale
        // request. Report completion rather than silently restarting the walk,
        // which would let a caller's loop run forever.
        if ($position === false) {
            return ['type' => $type, 'lastId' => $lastId, 'done' => true, 'counts' => $counts];
        }

        $db     = Factory::getContainer()->get(DatabaseInterface::class);
        $lastId = max(0, $lastId);
        $limit  = max(1, $limit);

        $result = match ($type) {
            'messages' => self::syncMessages($db, $mode, $lastId, $limit),
            'teachers' => self::syncTeachers($db, $mode, $lastId, $limit),
            'series'   => self::syncSeries($db, $mode, $lastId, $limit),
        };

        $counts[$type]     = $result['synced'];
        $counts['skipped'] = $result['skipped'];

        // Short batch means this type is exhausted; hand the cursor to the next
        // type, or report done when there is no next type.
        if ($result['exhausted']) {
            $next = self::SYNC_TYPES[$position + 1] ?? null;

            return [
                'type'   => $next ?? $type,
                'lastId' => $next === null ? $result['lastId'] : 0,
                'done'   => $next === null,
                'counts' => $counts,
            ];
        }

        return ['type' => $type, 'lastId' => $result['lastId'], 'done' => false, 'counts' => $counts];
    }

    /**
     * Load the `#__schemaorg` rows for a set of items in one query.
     *
     * Serves both halves of the per-item work in one query: the stored schema
     * `shouldSyncStored()` needs, and the row id `upsertSchemaRow()` needs to
     * choose insert vs update.
     *
     * @param   DatabaseInterface  $db       Database instance
     * @param   string             $context  Context string
     * @param   int[]              $itemIds  Item primary keys
     *
     * @return  array<int, object>  Keyed by itemId, each with `id` and `schema`
     *
     * @since   10.5.6
     */
    private static function prefetchSchemaRows(DatabaseInterface $db, string $context, array $itemIds): array
    {
        if ($itemIds === []) {
            return [];
        }

        $query = $db->createQuery()
            ->select([$db->quoteName('id'), $db->quoteName('itemId'), $db->quoteName('schema')])
            ->from($db->quoteName('#__schemaorg'))
            ->where($db->quoteName('context') . ' = ' . $db->quote($context))
            ->whereIn($db->quoteName('itemId'), $itemIds);

        $rows = [];

        foreach ($db->setQuery($query)->loadObjectList() ?: [] as $row) {
            $rows[(int) $row->itemId] = $row;
        }

        return $rows;
    }

    /**
     * Sync one batch of published messages.
     *
     * @param   DatabaseInterface  $db      Database instance
     * @param   string             $mode    Sync mode
     * @param   int                $lastId  Highest primary key already processed
     * @param   int                $limit   Maximum items in this batch
     *
     * @return  array{synced: int, skipped: int, lastId: int, exhausted: bool}
     *
     * @since   10.3.0
     */
    private static function syncMessages(DatabaseInterface $db, string $mode, int $lastId, int $limit): array
    {
        $context = 'com_proclaim.cwmmessage';
        $synced  = 0;
        $skipped = 0;

        $query = $db->createQuery()
            ->select([
                $db->quoteName('m.id'),
                $db->quoteName('m.studytitle'),
                $db->quoteName('m.studyintro'),
                $db->quoteName('m.studydate'),
                $db->quoteName('m.modified'),
                $db->quoteName('m.image'),
                $db->quoteName('m.series_id'),
                $db->quoteName('m.messagetype'),
                $db->quoteName('m.location_id'),
            ])
            ->from($db->quoteName('#__bsms_studies', 'm'))
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('m.id') . ' > ' . $lastId)
            ->order($db->quoteName('m.id') . ' ASC');
        $db->setQuery($query, 0, $limit);
        $messages = $db->loadObjectList() ?: [];

        if ($messages === []) {
            return ['synced' => 0, 'skipped' => 0, 'lastId' => $lastId, 'exhausted' => true];
        }

        $ids      = array_map(static fn ($msg) => (int) $msg->id, $messages);
        $existing = self::prefetchSchemaRows($db, $context, $ids);

        // Narrow to the rows this mode will actually write before prefetching
        // their related data, so a Smart Sync over a mostly-untouched site does
        // not pay for lookups it will throw away.
        $due = [];

        foreach ($messages as $msg) {
            $stored = $existing[(int) $msg->id]->schema ?? null;

            if (!self::shouldSyncStored(self::isSchemaManuallyEdited($stored), $mode)) {
                $skipped++;
                continue;
            }

            $due[] = $msg;
        }

        $maps = self::prefetchSermonMaps($db, $due);

        foreach ($due as $msg) {
            $schema = self::buildSermonSchemaFromRow($db, $msg, $maps);

            self::upsertSchemaRow(
                $db,
                (int) $msg->id,
                $context,
                'Sermon',
                $schema,
                (int) ($existing[(int) $msg->id]->id ?? 0)
            );
            $synced++;
        }

        return [
            'synced'    => $synced,
            'skipped'   => $skipped,
            'lastId'    => (int) end($messages)->id,
            'exhausted' => \count($messages) < $limit,
        ];
    }

    /**
     * Sync one batch of published teachers.
     *
     * @param   DatabaseInterface  $db      Database instance
     * @param   string             $mode    Sync mode
     * @param   int                $lastId  Highest primary key already processed
     * @param   int                $limit   Maximum items in this batch
     *
     * @return  array{synced: int, skipped: int, lastId: int, exhausted: bool}
     *
     * @since   10.3.0
     */
    private static function syncTeachers(DatabaseInterface $db, string $mode, int $lastId, int $limit): array
    {
        return self::syncSimpleBatch(
            $db,
            $mode,
            $lastId,
            $limit,
            '#__bsms_teachers',
            'com_proclaim.teacher',
            'Teacher',
            static fn (object $row): array => self::buildTeacherSchemaFromRow($row)
        );
    }

    /**
     * Sync one batch of published series.
     *
     * @param   DatabaseInterface  $db      Database instance
     * @param   string             $mode    Sync mode
     * @param   int                $lastId  Highest primary key already processed
     * @param   int                $limit   Maximum items in this batch
     *
     * @return  array{synced: int, skipped: int, lastId: int, exhausted: bool}
     *
     * @since   10.3.0
     */
    private static function syncSeries(DatabaseInterface $db, string $mode, int $lastId, int $limit): array
    {
        return self::syncSimpleBatch(
            $db,
            $mode,
            $lastId,
            $limit,
            '#__bsms_series',
            'com_proclaim.serie',
            'Series',
            static fn (object $row): array => self::buildSeriesSchemaFromRow($row)
        );
    }

    /**
     * Sync one batch of a table whose schema builder needs no related lookups.
     *
     * @param   DatabaseInterface  $db          Database instance
     * @param   string             $mode        Sync mode
     * @param   int                $lastId      Highest primary key already processed
     * @param   int                $limit       Maximum items in this batch
     * @param   string             $table       Source table name
     * @param   string             $context     Context string
     * @param   string             $schemaType  Schema type name
     * @param   callable           $builder     Maps a source row to a schema array
     *
     * @return  array{synced: int, skipped: int, lastId: int, exhausted: bool}
     *
     * @since   10.5.6
     */
    private static function syncSimpleBatch(
        DatabaseInterface $db,
        string $mode,
        int $lastId,
        int $limit,
        string $table,
        string $context,
        string $schemaType,
        callable $builder
    ): array {
        $synced  = 0;
        $skipped = 0;

        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName($table))
            ->where($db->quoteName('published') . ' = 1')
            ->where($db->quoteName('id') . ' > ' . $lastId)
            ->order($db->quoteName('id') . ' ASC');
        $db->setQuery($query, 0, $limit);
        $rows = $db->loadObjectList() ?: [];

        if ($rows === []) {
            return ['synced' => 0, 'skipped' => 0, 'lastId' => $lastId, 'exhausted' => true];
        }

        $ids      = array_map(static fn ($row) => (int) $row->id, $rows);
        $existing = self::prefetchSchemaRows($db, $context, $ids);

        foreach ($rows as $row) {
            $stored = $existing[(int) $row->id]->schema ?? null;

            if (!self::shouldSyncStored(self::isSchemaManuallyEdited($stored), $mode)) {
                $skipped++;
                continue;
            }

            self::upsertSchemaRow(
                $db,
                (int) $row->id,
                $context,
                $schemaType,
                $builder($row),
                (int) ($existing[(int) $row->id]->id ?? 0)
            );
            $synced++;
        }

        return [
            'synced'    => $synced,
            'skipped'   => $skipped,
            'lastId'    => (int) end($rows)->id,
            'exhausted' => \count($rows) < $limit,
        ];
    }

    /**
     * Compute a fingerprint hash of auto-generated schema data.
     *
     * The hash allows Smart Sync to detect whether schema was manually
     * edited: if stored schema (minus _autoHash) still hashes to the
     * stored _autoHash, the admin never touched it.
     *
     * @param   array  $schema  Schema data (without _autoHash key)
     *
     * @return  string  Short hash
     *
     * @since   10.3.0
     */
    public static function computeAutoHash(array $schema): string
    {
        unset($schema['_autoHash'], $schema['_customFields'], $schema['_fieldHashes'], $schema['_editedFields']);
        ksort($schema);

        return substr(md5(json_encode($schema, JSON_UNESCAPED_UNICODE)), 0, 12);
    }

    /**
     * Check if a stored schema row has been manually edited.
     *
     * Compares the stored _autoHash against the actual hash of the stored data.
     * If they match, the admin never edited the schema fields.
     *
     * @param   DatabaseInterface  $db       Database instance
     * @param   int                $itemId   Item ID
     * @param   string             $context  Context string
     *
     * @return  bool|null  True = manually edited, false = untouched, null = no row
     *
     * @since   10.3.0
     */
    private static function isManuallyEdited(DatabaseInterface $db, int $itemId, string $context): ?bool
    {
        $query = $db->createQuery()
            ->select($db->quoteName('schema'))
            ->from($db->quoteName('#__schemaorg'))
            ->where($db->quoteName('itemId') . ' = ' . $itemId)
            ->where($db->quoteName('context') . ' = ' . $db->quote($context));
        return self::isSchemaManuallyEdited($db->setQuery($query)->loadResult());
    }

    /**
     * Decide whether a stored `#__schemaorg` payload has been manually edited.
     *
     * Split out from `isManuallyEdited()` so the bulk sync path can reuse the
     * comparison against schema it already prefetched in one batched query.
     *
     * @param   string|null  $stored  Raw stored schema JSON, or null when no row exists
     *
     * @return  bool|null  True = manually edited, false = untouched, null = no row
     *
     * @since   10.5.6
     */
    private static function isSchemaManuallyEdited(?string $stored): ?bool
    {
        if ($stored === null) {
            return null;
        }

        try {
            $data = json_decode($stored, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return true;
        }

        $storedHash = $data['_autoHash'] ?? null;

        if ($storedHash === null) {
            // No hash means it was created before Smart Sync or manually
            return true;
        }

        // Must strip the same keys computeAutoHash() strips when it stamped
        // this row, or every row carrying a non-empty _customFields/_fieldHashes
        // (written by the schemaorg plugin's per-field Smart Sync) permanently
        // mismatches and Smart Sync can never touch it again.
        return $storedHash !== self::computeAutoHash($data);
    }

    /**
     * Build sermon schema from a DB row object (used by syncOne and bulk sync).
     *
     * @param   DatabaseInterface  $db   Database instance (for teacher/topic lookups)
     * @param   object             $msg  Message row from #__bsms_studies
     *
     * @return  array
     *
     * @since   10.3.0
     */
    private static function buildSermonSchemaFromRow(DatabaseInterface $db, object $msg, ?array $maps = null): array
    {
        // Single source of truth for the related-data lookups: the per-item
        // path prefetches a one-row map rather than running its own queries,
        // so a bulk chunk and a single item can never build different schema
        // (or different `computeAutoHash()` fingerprints) for the same row.
        $maps ??= self::prefetchSermonMaps($db, [$msg]);

        $schema = ['@type' => 'CreativeWork'];

        if (!empty($msg->studytitle)) {
            $schema['headline'] = $msg->studytitle;
        }

        if (!empty($msg->studyintro)) {
            $schema['description'] = self::cleanDescription($msg->studyintro);
        }

        if (!empty($msg->studydate) && $msg->studydate !== '0000-00-00 00:00:00') {
            $schema['datePublished'] = $msg->studydate;
        }

        if (!empty($msg->modified) && $msg->modified !== '0000-00-00 00:00:00') {
            $schema['dateModified'] = $msg->modified;
        }

        if (!empty($msg->image)) {
            $schema['image'] = $msg->image;
        }

        // Teacher names
        $names = $maps['teachers'][(int) $msg->id] ?? [];

        if (!empty($names)) {
            $schema['author'] = ['@type' => 'Person', 'name' => implode(', ', $names)];
        }

        // Publisher (organization)
        $orgName = self::getOrgName();

        if ($orgName !== '') {
            $schema['publisher'] = ['@type' => 'Organization', 'name' => $orgName];
        }

        // Custom fields: series, genre, location, topics
        $customFields = [];

        $seriesName = $maps['series'][(int) ($msg->series_id ?? 0)] ?? null;

        if ($seriesName) {
            $customFields[] = ['genericTitle' => 'isPartOf', 'genericValue' => $seriesName];
        }

        $msgType = $maps['messagetype'][(int) ($msg->messagetype ?? 0)] ?? null;

        if ($msgType) {
            $customFields[] = ['genericTitle' => 'genre', 'genericValue' => Text::_($msgType)];
        }

        $location = $maps['locations'][(int) ($msg->location_id ?? 0)] ?? null;

        if ($location) {
            $customFields[] = ['genericTitle' => 'locationCreated', 'genericValue' => $location];
        }

        // Topics
        $topics = $maps['topics'][(int) $msg->id] ?? [];

        if (!empty($topics)) {
            $translated     = array_map(static fn ($t) => Text::_($t), $topics);
            $customFields[] = ['genericTitle' => 'about', 'genericValue' => implode(', ', $translated)];
        }

        if (!empty($customFields)) {
            $schema['genericField'] = $customFields;
        }

        return $schema;
    }

    /**
     * Resolve every related-record lookup `buildSermonSchemaFromRow()` needs for a
     * set of message rows, using one query per related table instead of one query
     * per message per table.
     *
     * Both orderings below are pinned explicitly. `author` and the `about`
     * genericField are built by imploding these lists, so an unstable row order
     * would produce a different string — and therefore a different
     * `computeAutoHash()` — for unchanged data, making every Smart Sync run
     * rewrite every row.
     *
     * @param   DatabaseInterface  $db        Database instance
     * @param   object[]           $messages  Message rows from `#__bsms_studies`
     *
     * @return  array{teachers: array<int, string[]>, topics: array<int, string[]>, series: array<int, string>, messagetype: array<int, string>, locations: array<int, string>}
     *
     * @since   10.5.6
     */
    private static function prefetchSermonMaps(DatabaseInterface $db, array $messages): array
    {
        $maps = ['teachers' => [], 'topics' => [], 'series' => [], 'messagetype' => [], 'locations' => []];

        $studyIds = [];

        foreach ($messages as $msg) {
            $studyIds[] = (int) $msg->id;
        }

        $studyIds = array_values(array_unique(array_filter($studyIds)));

        if ($studyIds === []) {
            return $maps;
        }

        // Teacher names, grouped by study and kept in per-study ordering order.
        $query = $db->createQuery()
            ->select([$db->quoteName('st.study_id'), $db->quoteName('t.teachername')])
            ->from($db->quoteName('#__bsms_teachers', 't'))
            ->innerJoin(
                $db->quoteName('#__bsms_study_teachers', 'st') . ' ON '
                . $db->quoteName('st.teacher_id') . ' = ' . $db->quoteName('t.id')
            )
            ->whereIn($db->quoteName('st.study_id'), $studyIds)
            ->order(
                [
                    $db->quoteName('st.study_id') . ' ASC',
                    $db->quoteName('st.ordering') . ' ASC',
                    $db->quoteName('st.id') . ' ASC',
                ]
            );

        foreach ($db->setQuery($query)->loadObjectList() ?: [] as $row) {
            $maps['teachers'][(int) $row->study_id][] = $row->teachername;
        }

        // Topic names, grouped by study. `#__bsms_studytopics` has no ordering
        // column, so its auto-increment id stands in for insertion order.
        $query = $db->createQuery()
            ->select([$db->quoteName('st.study_id'), $db->quoteName('t.topic_text')])
            ->from($db->quoteName('#__bsms_topics', 't'))
            ->innerJoin(
                $db->quoteName('#__bsms_studytopics', 'st') . ' ON '
                . $db->quoteName('st.topic_id') . ' = ' . $db->quoteName('t.id')
            )
            ->whereIn($db->quoteName('st.study_id'), $studyIds)
            ->order(
                [
                    $db->quoteName('st.study_id') . ' ASC',
                    $db->quoteName('st.id') . ' ASC',
                ]
            );

        foreach ($db->setQuery($query)->loadObjectList() ?: [] as $row) {
            $maps['topics'][(int) $row->study_id][] = $row->topic_text;
        }

        // One lookup per single-valued foreign key, over the distinct ids in the set.
        $lookups = [
            'series'      => ['#__bsms_series', 'series_text', 'series_id'],
            'messagetype' => ['#__bsms_message_type', 'message_type', 'messagetype'],
            'locations'   => ['#__bsms_locations', 'location_text', 'location_id'],
        ];

        foreach ($lookups as $mapKey => [$table, $column, $property]) {
            $ids = [];

            foreach ($messages as $msg) {
                $ids[] = (int) ($msg->$property ?? 0);
            }

            $ids = array_values(array_unique(array_filter($ids)));

            if ($ids === []) {
                continue;
            }

            $query = $db->createQuery()
                ->select([$db->quoteName('id'), $db->quoteName($column)])
                ->from($db->quoteName($table))
                ->whereIn($db->quoteName('id'), $ids);

            foreach ($db->setQuery($query)->loadObjectList() ?: [] as $row) {
                $maps[$mapKey][(int) $row->id] = $row->$column;
            }
        }

        return $maps;
    }

    /**
     * Build teacher schema from a DB row object.
     *
     * @param   object  $teacher  Teacher row from #__bsms_teachers
     *
     * @return  array
     *
     * @since   10.3.0
     */
    private static function buildTeacherSchemaFromRow(object $teacher): array
    {
        $schema = ['@type' => 'Person'];

        if (!empty($teacher->teachername)) {
            $schema['name'] = $teacher->teachername;
        }

        if (!empty($teacher->title)) {
            $schema['jobTitle'] = $teacher->title;
        }

        if (!empty($teacher->short)) {
            $schema['description'] = self::cleanDescription($teacher->short);
        } elseif (!empty($teacher->information)) {
            $schema['description'] = self::cleanDescription($teacher->information);
        }

        if (!empty($teacher->teacher_image)) {
            $schema['image'] = $teacher->teacher_image;
        } elseif (!empty($teacher->teacher_thumbnail)) {
            $schema['image'] = $teacher->teacher_thumbnail;
        }

        if (!empty($teacher->website)) {
            $schema['url'] = $teacher->website;
        }

        // Social links
        $sameAs = [];

        if (!empty($teacher->social_links) && \is_string($teacher->social_links)) {
            try {
                $links = json_decode($teacher->social_links, true, 512, JSON_THROW_ON_ERROR);

                foreach ($links as $link) {
                    if (!empty($link['url']) && filter_var($link['url'], FILTER_VALIDATE_URL)) {
                        $sameAs[] = ['value' => $link['url']];
                    }
                }
            } catch (\Throwable) {
                // skip
            }
        }

        if (empty($sameAs)) {
            foreach (['facebooklink', 'twitterlink', 'bloglink', 'link1', 'link2', 'link3'] as $field) {
                if (!empty($teacher->$field) && filter_var($teacher->$field, FILTER_VALIDATE_URL)) {
                    $sameAs[] = ['value' => $teacher->$field];
                }
            }
        }

        if (!empty($sameAs)) {
            $schema['sameAs'] = $sameAs;
        }

        // worksFor: teacher org_name → admin setting → site name
        $orgName = !empty($teacher->org_name) ? $teacher->org_name : self::getOrgName();

        if ($orgName !== '') {
            $schema['worksFor'] = ['@type' => 'Organization', 'name' => $orgName];
        }

        return $schema;
    }

    /**
     * Build series schema from a DB row object.
     *
     * @param   object  $series  Series row from #__bsms_series
     *
     * @return  array
     *
     * @since   10.3.0
     */
    private static function buildSeriesSchemaFromRow(object $series): array
    {
        $schema = ['@type' => 'CreativeWorkSeries'];

        if (!empty($series->series_text)) {
            $schema['name'] = $series->series_text;
        }

        if (!empty($series->description)) {
            $schema['description'] = self::cleanDescription($series->description);
        }

        if (!empty($series->series_thumbnail)) {
            $schema['image'] = $series->series_thumbnail;
        }

        return $schema;
    }

    /**
     * Decide whether an item should be written, given its already-known edited state.
     *
     * @param   bool|null  $edited  Result of `isSchemaManuallyEdited()` (null = no row)
     * @param   string     $mode    Sync mode (new, smart, force)
     *
     * @return  bool  True if the item should be written
     *
     * @since   10.5.6
     */
    private static function shouldSyncStored(?bool $edited, string $mode): bool
    {
        if ($mode === self::SYNC_FORCE) {
            return true;
        }

        if ($edited === null) {
            // No existing row — always sync
            return true;
        }

        if ($mode === self::SYNC_NEW) {
            // Row exists — skip
            return false;
        }

        // Smart mode: sync only if not manually edited
        return !$edited;
    }

    /**
     * Insert or update a schema.org row with auto-hash fingerprint.
     *
     * @param   DatabaseInterface  $db          Database instance
     * @param   int                $itemId      Item ID
     * @param   string             $context     Context string
     * @param   string             $schemaType  Schema type name
     * @param   array              $schema      Schema data array (without _autoHash)
     * @param   int|null           $existingId  Known primary key of the existing row (0 when
     *                                          known to be absent); null looks it up
     *
     * @return  void
     *
     * @since   10.3.0
     */
    private static function upsertSchemaRow(
        DatabaseInterface $db,
        int $itemId,
        string $context,
        string $schemaType,
        array $schema,
        ?int $existingId = null
    ): void {
        // Stamp auto-hash before saving
        $schema['_autoHash'] = self::computeAutoHash($schema);

        if ($existingId === null) {
            $query = $db->createQuery()
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__schemaorg'))
                ->where($db->quoteName('itemId') . ' = ' . $itemId)
                ->where($db->quoteName('context') . ' = ' . $db->quote($context));
            $existingId = (int) $db->setQuery($query)->loadResult();
        }

        $json = json_encode($schema, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        // Written as an explicit query rather than insertObject()/updateObject():
        // those resolve the column list per call, so each one costs an extra
        // SHOW FULL COLUMNS round trip — doubling the query count of a bulk sync.
        if ($existingId > 0) {
            $query = $db->createQuery()
                ->update($db->quoteName('#__schemaorg'))
                ->set($db->quoteName('itemId') . ' = ' . $itemId)
                ->set($db->quoteName('context') . ' = ' . $db->quote($context))
                ->set($db->quoteName('schemaType') . ' = ' . $db->quote($schemaType))
                ->set($db->quoteName('schema') . ' = ' . $db->quote($json))
                ->where($db->quoteName('id') . ' = ' . $existingId);
        } else {
            $query = $db->createQuery()
                ->insert($db->quoteName('#__schemaorg'))
                ->columns(
                    [
                        $db->quoteName('itemId'),
                        $db->quoteName('context'),
                        $db->quoteName('schemaType'),
                        $db->quoteName('schema'),
                    ]
                )
                ->values(
                    implode(
                        ',',
                        [$itemId, $db->quote($context), $db->quote($schemaType), $db->quote($json)]
                    )
                );
        }

        $db->setQuery($query)->execute();
    }
}
