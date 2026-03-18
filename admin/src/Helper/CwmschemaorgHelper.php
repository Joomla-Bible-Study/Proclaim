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
        if (!empty($item->studydate)) {
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

            if (!empty($item->studydate)) {
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

            if (!empty($study->studydate)) {
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
    public static function hasJoomlaSchema(int $itemId, string $context): bool
    {
        if ($itemId <= 0) {
            return false;
        }

        try {
            $db    = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__schemaorg'))
                ->where($db->quoteName('itemId') . ' = ' . $itemId)
                ->where($db->quoteName('context') . ' = ' . $db->quote($context));
            $db->setQuery($query);

            return (int) $db->loadResult() > 0;
        } catch (\Throwable) {
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
            $json        = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | $prettyPrint);
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
        return rtrim(Uri::root(), '/') . '/' . ltrim(Route::_($route), '/');
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
        $links  = [];
        $fields = ['facebooklink', 'twitterlink', 'bloglink', 'website', 'link1', 'link2'];

        foreach ($fields as $field) {
            $value = $item->$field ?? '';

            if (\is_string($value) && $value !== '' && filter_var($value, FILTER_VALIDATE_URL)) {
                $links[] = $value;
            }
        }

        return $links;
    }

    /**
     * Bulk-sync schema.org data for all published messages, teachers, and series.
     *
     * Inserts or updates rows in Joomla's #__schemaorg table with auto-generated
     * structured data from item fields.
     *
     * @param   bool  $force  If true, overwrite existing schema entries
     *
     * @return  array{messages: int, teachers: int, series: int}  Count of synced items per type
     *
     * @since   10.3.0
     */
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

    public static function syncAll(string $mode = self::SYNC_SMART): array
    {
        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $counts  = ['messages' => 0, 'teachers' => 0, 'series' => 0, 'skipped' => 0];

        $result = self::syncMessages($db, $mode);
        $counts['messages'] = $result['synced'];
        $counts['skipped'] += $result['skipped'];

        $result = self::syncTeachers($db, $mode);
        $counts['teachers'] = $result['synced'];
        $counts['skipped'] += $result['skipped'];

        $result = self::syncSeries($db, $mode);
        $counts['series'] = $result['synced'];
        $counts['skipped'] += $result['skipped'];

        return $counts;
    }

    /**
     * Sync schema.org data for all published messages.
     *
     * @param   DatabaseInterface  $db    Database instance
     * @param   string             $mode  Sync mode
     *
     * @return  array{synced: int, skipped: int}
     *
     * @since   10.3.0
     */
    private static function syncMessages(DatabaseInterface $db, string $mode): array
    {
        $context = 'com_proclaim.cwmmessage';
        $synced  = 0;
        $skipped = 0;

        $query = $db->getQuery(true)
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
            ->where($db->quoteName('m.published') . ' = 1');
        $db->setQuery($query);
        $messages = $db->loadObjectList() ?: [];

        foreach ($messages as $msg) {
            if (!self::shouldSync($db, (int) $msg->id, $context, $mode)) {
                $skipped++;
                continue;
            }

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
            $tQuery = $db->getQuery(true)
                ->select($db->quoteName('t.teachername'))
                ->from($db->quoteName('#__bsms_teachers', 't'))
                ->innerJoin(
                    $db->quoteName('#__bsms_study_teachers', 'st') . ' ON '
                    . $db->quoteName('st.teacher_id') . ' = ' . $db->quoteName('t.id')
                )
                ->where($db->quoteName('st.study_id') . ' = ' . (int) $msg->id)
                ->order($db->quoteName('st.ordering') . ' ASC');
            $names = $db->setQuery($tQuery)->loadColumn() ?: [];

            if (!empty($names)) {
                $schema['author'] = ['@type' => 'Person', 'name' => implode(', ', $names)];
            }

            // Custom fields: series, topics, genre, location
            $customFields = [];

            if (!empty($msg->series_id) && (int) $msg->series_id > 0) {
                $sQuery = $db->getQuery(true)
                    ->select($db->quoteName('series_text'))
                    ->from($db->quoteName('#__bsms_series'))
                    ->where($db->quoteName('id') . ' = ' . (int) $msg->series_id);
                $seriesName = $db->setQuery($sQuery)->loadResult();

                if ($seriesName) {
                    $customFields[] = ['genericTitle' => 'isPartOf', 'genericValue' => $seriesName];
                }
            }

            if (!empty($msg->messagetype) && (int) $msg->messagetype > 0) {
                $mtQuery = $db->getQuery(true)
                    ->select($db->quoteName('message_type'))
                    ->from($db->quoteName('#__bsms_message_type'))
                    ->where($db->quoteName('id') . ' = ' . (int) $msg->messagetype);
                $msgType = $db->setQuery($mtQuery)->loadResult();

                if ($msgType) {
                    $customFields[] = ['genericTitle' => 'genre', 'genericValue' => Text::_($msgType)];
                }
            }

            if (!empty($msg->location_id) && (int) $msg->location_id > 0) {
                $lQuery = $db->getQuery(true)
                    ->select($db->quoteName('location_text'))
                    ->from($db->quoteName('#__bsms_locations'))
                    ->where($db->quoteName('id') . ' = ' . (int) $msg->location_id);
                $location = $db->setQuery($lQuery)->loadResult();

                if ($location) {
                    $customFields[] = ['genericTitle' => 'locationCreated', 'genericValue' => $location];
                }
            }

            // Topics
            $topQuery = $db->getQuery(true)
                ->select($db->quoteName('t.topic_text'))
                ->from($db->quoteName('#__bsms_topics', 't'))
                ->innerJoin(
                    $db->quoteName('#__bsms_studytopics', 'st') . ' ON '
                    . $db->quoteName('st.topic_id') . ' = ' . $db->quoteName('t.id')
                )
                ->where($db->quoteName('st.study_id') . ' = ' . (int) $msg->id);
            $topics = $db->setQuery($topQuery)->loadColumn() ?: [];

            if (!empty($topics)) {
                $translated     = array_map(static fn ($t) => Text::_($t), $topics);
                $customFields[] = ['genericTitle' => 'about', 'genericValue' => implode(', ', $translated)];
            }

            if (!empty($customFields)) {
                $schema['genericField'] = $customFields;
            }

            self::upsertSchemaRow($db, (int) $msg->id, $context, 'Sermon', $schema);
            $synced++;
        }

        return ['synced' => $synced, 'skipped' => $skipped];
    }

    /**
     * Sync schema.org data for all published teachers.
     *
     * @param   DatabaseInterface  $db    Database instance
     * @param   string             $mode  Sync mode
     *
     * @return  array{synced: int, skipped: int}
     *
     * @since   10.3.0
     */
    private static function syncTeachers(DatabaseInterface $db, string $mode): array
    {
        $context = 'com_proclaim.teacher';
        $synced  = 0;
        $skipped = 0;

        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__bsms_teachers'))
            ->where($db->quoteName('published') . ' = 1');
        $db->setQuery($query);
        $teachers = $db->loadObjectList() ?: [];

        foreach ($teachers as $teacher) {
            if (!self::shouldSync($db, (int) $teacher->id, $context, $mode)) {
                $skipped++;
                continue;
            }

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

            // Social links → sameAs
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
                    // Malformed JSON
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

            self::upsertSchemaRow($db, (int) $teacher->id, $context, 'Teacher', $schema);
            $synced++;
        }

        return ['synced' => $synced, 'skipped' => $skipped];
    }

    /**
     * Sync schema.org data for all published series.
     *
     * @param   DatabaseInterface  $db    Database instance
     * @param   string             $mode  Sync mode
     *
     * @return  array{synced: int, skipped: int}
     *
     * @since   10.3.0
     */
    private static function syncSeries(DatabaseInterface $db, string $mode): array
    {
        $context = 'com_proclaim.serie';
        $synced  = 0;
        $skipped = 0;

        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__bsms_series'))
            ->where($db->quoteName('published') . ' = 1');
        $db->setQuery($query);
        $allSeries = $db->loadObjectList() ?: [];

        foreach ($allSeries as $series) {
            if (!self::shouldSync($db, (int) $series->id, $context, $mode)) {
                $skipped++;
                continue;
            }

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

            self::upsertSchemaRow($db, (int) $series->id, $context, 'Series', $schema);
            $synced++;
        }

        return ['synced' => $synced, 'skipped' => $skipped];
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
        unset($schema['_autoHash']);
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
        $query = $db->getQuery(true)
            ->select($db->quoteName('schema'))
            ->from($db->quoteName('#__schemaorg'))
            ->where($db->quoteName('itemId') . ' = ' . $itemId)
            ->where($db->quoteName('context') . ' = ' . $db->quote($context));
        $stored = $db->setQuery($query)->loadResult();

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

        unset($data['_autoHash']);
        ksort($data);

        return $storedHash !== substr(md5(json_encode($data, JSON_UNESCAPED_UNICODE)), 0, 12);
    }

    /**
     * Determine whether an item should be synced based on mode.
     *
     * @param   DatabaseInterface  $db       Database instance
     * @param   int                $itemId   Item ID
     * @param   string             $context  Context string
     * @param   string             $mode     Sync mode (new, smart, force)
     *
     * @return  bool  True if the item should be written
     *
     * @since   10.3.0
     */
    private static function shouldSync(DatabaseInterface $db, int $itemId, string $context, string $mode): bool
    {
        if ($mode === self::SYNC_FORCE) {
            return true;
        }

        $edited = self::isManuallyEdited($db, $itemId, $context);

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
        array $schema
    ): void {
        // Stamp auto-hash before saving
        $schema['_autoHash'] = self::computeAutoHash($schema);

        // Check for existing row
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__schemaorg'))
            ->where($db->quoteName('itemId') . ' = ' . $itemId)
            ->where($db->quoteName('context') . ' = ' . $db->quote($context));
        $existingId = (int) $db->setQuery($query)->loadResult();

        $entry             = new \stdClass();
        $entry->itemId     = $itemId;
        $entry->context    = $context;
        $entry->schemaType = $schemaType;
        $entry->schema     = json_encode($schema, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        if ($existingId > 0) {
            $entry->id = $existingId;
            $db->updateObject('#__schemaorg', $entry, 'id');
        } else {
            $db->insertObject('#__schemaorg', $entry, 'id');
        }
    }
}
