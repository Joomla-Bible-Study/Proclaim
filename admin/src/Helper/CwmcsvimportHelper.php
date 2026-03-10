<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseInterface;

/**
 * CSV Import helper for bulk-importing messages from CSV files.
 *
 * @since  10.1.0
 */
class CwmcsvimportHelper
{
    /**
     * In-memory caches for entity lookups (name → ID).
     *
     * @var array<string, array<string, int>>
     * @since 10.1.0
     */
    private static array $cache = [
        'teacher'     => [],
        'series'      => [],
        'location'    => [],
        'messagetype' => [],
        'topic'       => [],
    ];

    /**
     * Track auto-created entities for the report.
     *
     * @var array<string, array>
     * @since 10.1.0
     */
    private static array $autoCreated = [];

    /**
     * Process a batch of CSV rows.
     *
     * @param   array  $rows      Array of associative arrays (column_key => value)
     * @param   array  $mappings  Column mapping (csv_column_index => field_name)
     * @param   array  $settings  Import settings (auto_create, default_published, duplicate_handling)
     *
     * @return  array  Result with 'imported', 'skipped', 'errors' counts and 'details'
     *
     * @since  10.1.0
     */
    public static function processBatch(array $rows, array $mappings, array $settings): array
    {
        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($rows as $index => $row) {
            try {
                $rowData = self::mapRow($row, $mappings);
                $result  = self::importRow($rowData, $settings);

                if ($result['status'] === 'imported') {
                    $imported++;
                } elseif ($result['status'] === 'skipped') {
                    $skipped++;
                } else {
                    $errors[] = [
                        'row'     => $result['row_number'] ?? $index,
                        'field'   => $result['field'] ?? '',
                        'message' => $result['message'] ?? Text::_('JBS_CSV_UNKNOWN_ERROR'),
                    ];
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'row'     => $index,
                    'field'   => '',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return [
            'imported'     => $imported,
            'skipped'      => $skipped,
            'errors'       => $errors,
            'auto_created' => self::getAutoCreatedReport(),
        ];
    }

    /**
     * Map a raw CSV row array to named fields using column mappings.
     *
     * @param   array  $row       Raw CSV row (indexed array)
     * @param   array  $mappings  Column index => field name
     *
     * @return  array  Associative array of field_name => value
     *
     * @since  10.1.0
     */
    private static function mapRow(array $row, array $mappings): array
    {
        $mapped = [];

        foreach ($mappings as $colIndex => $fieldName) {
            if ($fieldName !== '' && $fieldName !== 'ignore' && isset($row[$colIndex])) {
                $mapped[$fieldName] = trim($row[$colIndex]);
            }
        }

        return $mapped;
    }

    /**
     * Import a single mapped row into the database.
     *
     * @param   array  $rowData   Associative array of field values
     * @param   array  $settings  Import settings
     *
     * @return  array  Result with 'status', optionally 'field', 'message'
     *
     * @since  10.1.0
     */
    public static function importRow(array $rowData, array $settings): array
    {
        $autoCreate       = (bool) ($settings['auto_create'] ?? true);
        $defaultPublished = (int) ($settings['default_published'] ?? 1);
        $duplicateAction  = $settings['duplicate_handling'] ?? 'skip';

        // Validate required fields
        $title = trim($rowData['studytitle'] ?? '');

        if ($title === '') {
            return ['status' => 'error', 'field' => 'studytitle', 'message' => Text::_('JBS_CSV_TITLE_REQUIRED')];
        }

        // Parse date
        $studyDate = self::parseDate($rowData['studydate'] ?? null);

        if ($studyDate === null) {
            $studyDate = date('Y-m-d');
        }

        // Check duplicates
        if ($duplicateAction !== 'create') {
            $existingId = self::findDuplicate($title, $studyDate);

            if ($existingId > 0) {
                if ($duplicateAction === 'skip') {
                    return ['status' => 'skipped'];
                }

                // 'update' mode — update existing record
                return self::updateExistingRow($existingId, $rowData, $settings);
            }
        }

        $db   = Factory::getContainer()->get(DatabaseInterface::class);
        $app  = Factory::getApplication();
        $user = $app->getIdentity();

        // Resolve foreign keys
        $teacherIds = [];

        if (!empty($rowData['teacher'])) {
            $teacherIds = self::parseMultipleTeachers($rowData['teacher'], $autoCreate);
        }

        $seriesId      = self::resolveOrCreateSeries($rowData['series'] ?? '', $autoCreate);
        $locationId    = self::processLocation($rowData['location'] ?? '', $autoCreate, (int) ($user ? $user->id : 0));
        $messageTypeId = self::resolveOrCreateMessageType($rowData['messagetype'] ?? '', $autoCreate);
        $published     = self::parsePublished($rowData['published'] ?? null, $defaultPublished);
        $alias         = OutputFilter::stringURLSafe($title);

        // Build columns and values
        $columns = [
            'studytitle', 'alias', 'studydate', 'published', 'access', 'language',
            'hits', 'studyintro', 'studytext', 'studynumber',
            'series_id', 'location_id', 'messagetype', 'teacher_id',
            'thumbnailm', 'created_by_alias',
            'created_by', 'ordering',
        ];

        $primaryTeacherId = 0;

        if (!empty($teacherIds)) {
            $primaryTeacherId = $teacherIds[0];
        }

        $values = [
            $db->quote($title),
            $db->quote($alias),
            $db->quote($studyDate),
            $published,
            1,
            $db->quote($rowData['language'] ?? '*'),
            0,
            $db->quote($rowData['studyintro'] ?? ''),
            $db->quote($rowData['studytext'] ?? ''),
            $db->quote($rowData['studynumber'] ?? ''),
            (int) $seriesId ?: 'NULL',
            (int) $locationId ?: 'NULL',
            (int) $messageTypeId ?: 'NULL',
            (int) $primaryTeacherId ?: 'NULL',
            $db->quote($rowData['thumbnailm'] ?? ''),
            $db->quote($rowData['created_by_alias'] ?? ''),
            (int) ($user ? $user->id : 0),
            0,
        ];

        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__bsms_studies'))
            ->columns($db->quoteName($columns))
            ->values(implode(', ', $values));
        $db->setQuery($query);
        $db->execute();

        $studyId = (int) $db->insertid();

        // Save teachers to junction table
        if (!empty($teacherIds)) {
            $teacherEntries = array_map(
                static fn (int $id) => ['teacher_id' => $id],
                $teacherIds
            );
            CwmstudyteacherHelper::saveTeachers($studyId, $teacherEntries);
            CwmstudyteacherHelper::syncLegacyColumn($studyId, $teacherEntries);
        }

        // Save scriptures
        if (!empty($rowData['scripture'])) {
            $scriptures = self::parseScriptures($rowData['scripture']);

            if (!empty($scriptures)) {
                CwmscriptureHelper::saveScriptures($studyId, $scriptures);
                CwmscriptureHelper::syncLegacyColumns($studyId, $scriptures);
            }
        }

        // Save topics
        if (!empty($rowData['topics'])) {
            self::saveTopics($studyId, $rowData['topics'], $autoCreate);
        }

        return ['status' => 'imported'];
    }

    /**
     * Update an existing study row with CSV data.
     *
     * @param   int    $studyId   Existing study ID
     * @param   array  $rowData   Mapped row data
     * @param   array  $settings  Import settings
     *
     * @return  array  Result with 'status'
     *
     * @since  10.1.0
     */
    private static function updateExistingRow(int $studyId, array $rowData, array $settings): array
    {
        $autoCreate = (bool) ($settings['auto_create'] ?? true);
        $db         = Factory::getContainer()->get(DatabaseInterface::class);

        $fields = [];

        if (!empty($rowData['studyintro'])) {
            $fields[] = $db->quoteName('studyintro') . ' = ' . $db->quote($rowData['studyintro']);
        }

        if (!empty($rowData['studytext'])) {
            $fields[] = $db->quoteName('studytext') . ' = ' . $db->quote($rowData['studytext']);
        }

        if (!empty($rowData['studynumber'])) {
            $fields[] = $db->quoteName('studynumber') . ' = ' . $db->quote($rowData['studynumber']);
        }

        if (!empty($rowData['series'])) {
            $seriesId = self::resolveOrCreateSeries($rowData['series'], $autoCreate);

            if ($seriesId > 0) {
                $fields[] = $db->quoteName('series_id') . ' = ' . $seriesId;
            }
        }

        if (!empty($rowData['location'])) {
            $locationId = self::resolveOrCreateLocation($rowData['location'], $autoCreate);

            if ($locationId > 0) {
                $fields[] = $db->quoteName('location_id') . ' = ' . $locationId;
            }
        }

        if (!empty($rowData['messagetype'])) {
            $messageTypeId = self::resolveOrCreateMessageType($rowData['messagetype'], $autoCreate);

            if ($messageTypeId > 0) {
                $fields[] = $db->quoteName('messagetype') . ' = ' . $messageTypeId;
            }
        }

        if (!empty($fields)) {
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__bsms_studies'))
                ->set($fields)
                ->where($db->quoteName('id') . ' = ' . $studyId);
            $db->setQuery($query);
            $db->execute();
        }

        // Update teachers if provided
        if (!empty($rowData['teacher'])) {
            $teacherIds     = self::parseMultipleTeachers($rowData['teacher'], $autoCreate);
            $teacherEntries = array_map(
                static fn (int $id) => ['teacher_id' => $id],
                $teacherIds
            );
            CwmstudyteacherHelper::saveTeachers($studyId, $teacherEntries);
            CwmstudyteacherHelper::syncLegacyColumn($studyId, $teacherEntries);
        }

        // Update scriptures if provided
        if (!empty($rowData['scripture'])) {
            $scriptures = self::parseScriptures($rowData['scripture']);

            if (!empty($scriptures)) {
                CwmscriptureHelper::saveScriptures($studyId, $scriptures);
                CwmscriptureHelper::syncLegacyColumns($studyId, $scriptures);
            }
        }

        // Update topics if provided
        if (!empty($rowData['topics'])) {
            self::saveTopics($studyId, $rowData['topics'], $autoCreate);
        }

        return ['status' => 'imported'];
    }

    /**
     * Resolve or auto-create a teacher by name.
     *
     * @param   string  $name        Teacher name
     * @param   bool    $autoCreate  Whether to auto-create if not found
     *
     * @return  int  Teacher ID, or 0 if not found/created
     *
     * @since  10.1.0
     */
    public static function resolveOrCreateTeacher(string $name, bool $autoCreate): int
    {
        $name = trim($name);

        if ($name === '') {
            return 0;
        }

        $cacheKey = strtolower($name);

        if (isset(self::$cache['teacher'][$cacheKey])) {
            return self::$cache['teacher'][$cacheKey];
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Case-insensitive lookup
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__bsms_teachers'))
            ->where('LOWER(' . $db->quoteName('teachername') . ') = LOWER(' . $db->quote($name) . ')')
            ->setLimit(1);
        $db->setQuery($query);
        $id = (int) $db->loadResult();

        if ($id > 0) {
            self::$cache['teacher'][$cacheKey] = $id;

            return $id;
        }

        if (!$autoCreate) {
            return 0;
        }

        // Auto-create
        $alias = OutputFilter::stringURLSafe($name);

        $insert = $db->getQuery(true)
            ->insert($db->quoteName('#__bsms_teachers'))
            ->columns($db->quoteName(['teachername', 'alias', 'published']))
            ->values($db->quote($name) . ', ' . $db->quote($alias) . ', 1');
        $db->setQuery($insert);
        $db->execute();

        $id = (int) $db->insertid();

        self::$cache['teacher'][$cacheKey] = $id;
        self::$autoCreated[]               = [
            'type' => 'teacher',
            'name' => $name,
            'id'   => $id,
            'url'  => 'index.php?option=com_proclaim&task=cwmteacher.edit&id=' . $id,
        ];

        return $id;
    }

    /**
     * Resolve or auto-create a series by name.
     *
     * @param   string  $name        Series name
     * @param   bool    $autoCreate  Whether to auto-create if not found
     *
     * @return  int  Series ID, or 0 if not found/created
     *
     * @since  10.1.0
     */
    public static function resolveOrCreateSeries(string $name, bool $autoCreate): int
    {
        $name = trim($name);

        if ($name === '') {
            return 0;
        }

        $cacheKey = strtolower($name);

        if (isset(self::$cache['series'][$cacheKey])) {
            return self::$cache['series'][$cacheKey];
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__bsms_series'))
            ->where('LOWER(' . $db->quoteName('series_text') . ') = LOWER(' . $db->quote($name) . ')')
            ->setLimit(1);
        $db->setQuery($query);
        $id = (int) $db->loadResult();

        if ($id > 0) {
            self::$cache['series'][$cacheKey] = $id;

            return $id;
        }

        if (!$autoCreate) {
            return 0;
        }

        $alias = OutputFilter::stringURLSafe($name);

        $insert = $db->getQuery(true)
            ->insert($db->quoteName('#__bsms_series'))
            ->columns($db->quoteName(['series_text', 'alias', 'published']))
            ->values($db->quote($name) . ', ' . $db->quote($alias) . ', 1');
        $db->setQuery($insert);
        $db->execute();

        $id = (int) $db->insertid();

        self::$cache['series'][$cacheKey] = $id;
        self::$autoCreated[]              = [
            'type' => 'series',
            'name' => $name,
            'id'   => $id,
            'url'  => 'index.php?option=com_proclaim&task=cwmserie.edit&id=' . $id,
        ];

        return $id;
    }

    /**
     * Resolve or auto-create a location by name.
     *
     * @param   string  $name        Location name
     * @param   bool    $autoCreate  Whether to auto-create if not found
     *
     * @return  int  Location ID, or 0 if not found/created
     *
     * @since  10.1.0
     */
    public static function resolveOrCreateLocation(string $name, bool $autoCreate): int
    {
        $name = trim($name);

        if ($name === '') {
            return 0;
        }

        $cacheKey = strtolower($name);

        if (isset(self::$cache['location'][$cacheKey])) {
            return self::$cache['location'][$cacheKey];
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__bsms_locations'))
            ->where('LOWER(' . $db->quoteName('location_text') . ') = LOWER(' . $db->quote($name) . ')')
            ->setLimit(1);
        $db->setQuery($query);
        $id = (int) $db->loadResult();

        if ($id > 0) {
            self::$cache['location'][$cacheKey] = $id;

            return $id;
        }

        if (!$autoCreate) {
            return 0;
        }

        $alias = OutputFilter::stringURLSafe($name);

        $insert = $db->getQuery(true)
            ->insert($db->quoteName('#__bsms_locations'))
            ->columns($db->quoteName(['location_text', 'alias', 'published']))
            ->values($db->quote($name) . ', ' . $db->quote($alias) . ', 1');
        $db->setQuery($insert);
        $db->execute();

        $id = (int) $db->insertid();

        self::$cache['location'][$cacheKey] = $id;
        self::$autoCreated[]                = [
            'type' => 'location',
            'name' => $name,
            'id'   => $id,
            'url'  => 'index.php?option=com_proclaim&task=cwmlocation.edit&id=' . $id,
        ];

        return $id;
    }

    /**
     * Resolve or auto-create a message type by name.
     *
     * @param   string  $name        Message type name
     * @param   bool    $autoCreate  Whether to auto-create if not found
     *
     * @return  int  Message type ID, or 0 if not found/created
     *
     * @since  10.1.0
     */
    public static function resolveOrCreateMessageType(string $name, bool $autoCreate): int
    {
        $name = trim($name);

        if ($name === '') {
            return 0;
        }

        $cacheKey = strtolower($name);

        if (isset(self::$cache['messagetype'][$cacheKey])) {
            return self::$cache['messagetype'][$cacheKey];
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__bsms_message_type'))
            ->where('LOWER(' . $db->quoteName('message_type') . ') = LOWER(' . $db->quote($name) . ')')
            ->setLimit(1);
        $db->setQuery($query);
        $id = (int) $db->loadResult();

        if ($id > 0) {
            self::$cache['messagetype'][$cacheKey] = $id;

            return $id;
        }

        if (!$autoCreate) {
            return 0;
        }

        $alias = OutputFilter::stringURLSafe($name);

        $insert = $db->getQuery(true)
            ->insert($db->quoteName('#__bsms_message_type'))
            ->columns($db->quoteName(['message_type', 'alias', 'published']))
            ->values($db->quote($name) . ', ' . $db->quote($alias) . ', 1');
        $db->setQuery($insert);
        $db->execute();

        $id = (int) $db->insertid();

        self::$cache['messagetype'][$cacheKey] = $id;
        self::$autoCreated[]                   = [
            'type' => 'messagetype',
            'name' => $name,
            'id'   => $id,
            'url'  => 'index.php?option=com_proclaim&task=cwmmessagetype.edit&id=' . $id,
        ];

        return $id;
    }

    /**
     * Resolve or auto-create a topic by name.
     *
     * @param   string  $name        Topic name
     * @param   bool    $autoCreate  Whether to auto-create if not found
     *
     * @return  int  Topic ID, or 0 if not found/created
     *
     * @since  10.1.0
     */
    public static function resolveOrCreateTopic(string $name, bool $autoCreate): int
    {
        $name = trim($name);

        if ($name === '') {
            return 0;
        }

        $cacheKey = strtolower($name);

        if (isset(self::$cache['topic'][$cacheKey])) {
            return self::$cache['topic'][$cacheKey];
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__bsms_topics'))
            ->where('LOWER(' . $db->quoteName('topic_text') . ') = LOWER(' . $db->quote($name) . ')')
            ->setLimit(1);
        $db->setQuery($query);
        $id = (int) $db->loadResult();

        if ($id > 0) {
            self::$cache['topic'][$cacheKey] = $id;

            return $id;
        }

        if (!$autoCreate) {
            return 0;
        }

        $alias = OutputFilter::stringURLSafe($name);

        $insert = $db->getQuery(true)
            ->insert($db->quoteName('#__bsms_topics'))
            ->columns($db->quoteName(['topic_text', 'alias', 'published']))
            ->values($db->quote($name) . ', ' . $db->quote($alias) . ', 1');
        $db->setQuery($insert);
        $db->execute();

        $id = (int) $db->insertid();

        self::$cache['topic'][$cacheKey] = $id;
        self::$autoCreated[]             = [
            'type' => 'topic',
            'name' => $name,
            'id'   => $id,
            'url'  => 'index.php?option=com_proclaim&task=cwmtopic.edit&id=' . $id,
        ];

        return $id;
    }

    /**
     * Parse semicolon-separated scripture references.
     *
     * @param   string  $text  Scripture text (e.g. "Luke 7:36-38; Genesis 1:1")
     *
     * @return  ScriptureReference[]
     *
     * @since  10.1.0
     */
    public static function parseScriptures(string $text): array
    {
        $text = trim($text);

        if ($text === '') {
            return [];
        }

        $parts      = preg_split('/\s*;\s*/', $text);
        $scriptures = [];

        foreach ($parts as $part) {
            $part = trim($part);

            if ($part === '') {
                continue;
            }

            $ref = CwmscriptureHelper::parseReference($part);

            if ($ref !== null) {
                $scriptures[] = $ref;
            }
        }

        return $scriptures;
    }

    /**
     * Parse semicolon-separated teacher names and resolve/create each.
     *
     * @param   string  $text        Teacher text (e.g. "Pastor Smith; Rev. Jones")
     * @param   bool    $autoCreate  Whether to auto-create
     *
     * @return  int[]  Array of teacher IDs
     *
     * @since  10.1.0
     */
    public static function parseMultipleTeachers(string $text, bool $autoCreate): array
    {
        $text = trim($text);

        if ($text === '') {
            return [];
        }

        $parts = preg_split('/\s*;\s*/', $text);
        $ids   = [];

        foreach ($parts as $part) {
            $part = trim($part);

            if ($part === '') {
                continue;
            }

            $id = self::resolveOrCreateTeacher($part, $autoCreate);

            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    /**
     * Save comma-separated topics for a study.
     *
     * @param   int     $studyId     Study primary key
     * @param   string  $topicsText  Comma-separated topic names
     * @param   bool    $autoCreate  Whether to auto-create topics
     *
     * @return  void
     *
     * @since  10.1.0
     */
    public static function saveTopics(int $studyId, string $topicsText, bool $autoCreate): void
    {
        $topicsText = trim($topicsText);

        if ($topicsText === '') {
            return;
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $parts = preg_split('/\s*,\s*/', $topicsText);

        foreach ($parts as $part) {
            $part = trim($part);

            if ($part === '') {
                continue;
            }

            $topicId = self::resolveOrCreateTopic($part, $autoCreate);

            if ($topicId <= 0) {
                continue;
            }

            // Check if mapping already exists
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__bsms_studytopics'))
                ->where($db->quoteName('study_id') . ' = ' . $studyId)
                ->where($db->quoteName('topic_id') . ' = ' . $topicId);
            $db->setQuery($query);

            if ((int) $db->loadResult() > 0) {
                continue;
            }

            $insert = $db->getQuery(true)
                ->insert($db->quoteName('#__bsms_studytopics'))
                ->columns($db->quoteName(['study_id', 'topic_id']))
                ->values($studyId . ', ' . $topicId);
            $db->setQuery($insert);
            $db->execute();
        }
    }

    /**
     * Find a duplicate study by title and date (case-insensitive).
     *
     * @param   string  $title  Study title
     * @param   string  $date   Study date (Y-m-d format)
     *
     * @return  int  Study ID if found, 0 otherwise
     *
     * @since  10.1.0
     */
    public static function findDuplicate(string $title, string $date): int
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__bsms_studies'))
            ->where('LOWER(' . $db->quoteName('studytitle') . ') = LOWER(' . $db->quote($title) . ')')
            ->where($db->quoteName('studydate') . ' = ' . $db->quote($date))
            ->setLimit(1);
        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    /**
     * Parse a date string in various common formats.
     *
     * @param   ?string  $input  Date string
     *
     * @return  ?string  Date in Y-m-d format, or null if unparseable
     *
     * @since  10.1.0
     */
    public static function parseDate(?string $input): ?string
    {
        if ($input === null || trim($input) === '') {
            return null;
        }

        $input = trim($input);

        // Try common formats
        $formats = [
            'Y-m-d',       // 2024-01-15
            'm/d/Y',       // 01/15/2024
            'm-d-Y',       // 01-15-2024
            'd/m/Y',       // 15/01/2024
            'M j, Y',      // Jan 15, 2024
            'M j Y',       // Jan 15 2024
            'F j, Y',      // January 15, 2024
            'F j Y',       // January 15 2024
            'j M Y',       // 15 Jan 2024
            'j F Y',       // 15 January 2024
            'd M Y',       // 15 Jan 2024
            'Y/m/d',       // 2024/01/15
            'Y-m-d H:i:s', // 2024-01-15 10:30:00
        ];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $input);

            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }

        // Fallback to strtotime
        $timestamp = strtotime($input);

        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return null;
    }

    /**
     * Parse published state from various text representations.
     *
     * @param   ?string  $input    Published text
     * @param   int      $default  Default value
     *
     * @return  int  Published state (0=unpublished, 1=published, 2=archived, -2=trashed)
     *
     * @since  10.1.0
     */
    public static function parsePublished(?string $input, int $default): int
    {
        if ($input === null || trim($input) === '') {
            return $default;
        }

        $input = strtolower(trim($input));

        $publishedMap = [
            '1'           => 1,
            'yes'         => 1,
            'true'        => 1,
            'published'   => 1,
            'active'      => 1,
            '0'           => 0,
            'no'          => 0,
            'false'       => 0,
            'unpublished' => 0,
            'inactive'    => 0,
            'draft'       => 0,
            '2'           => 2,
            'archived'    => 2,
            '-2'          => -2,
            'trashed'     => -2,
        ];

        return $publishedMap[$input] ?? $default;
    }

    /**
     * Generate a CSV template with headers and an example row.
     *
     * @return  string  CSV content
     *
     * @since  10.1.0
     */
    public static function generateTemplate(): string
    {
        $headers = [
            'Title',
            'Date',
            'Teacher',
            'Series',
            'Location',
            'Type',
            'Scripture',
            'Topics',
            'Introduction',
            'Body',
            'Number',
            'Published',
        ];

        $example = [
            'The Good Samaritan',
            '2024-01-15',
            'Pastor John Smith',
            'Parables of Jesus',
            'Main Sanctuary',
            'Sermon',
            'Luke 10:25-37; Luke 10:38-42',
            'Parables, Compassion, Love',
            'A study of the parable of the Good Samaritan',
            '',
            '1',
            'Published',
        ];

        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers);
        fputcsv($output, $example);
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Get the report of auto-created entities.
     *
     * @return  array  Array of auto-created entities with type, name, id, url
     *
     * @since  10.1.0
     */
    public static function getAutoCreatedReport(): array
    {
        return self::$autoCreated;
    }

    /**
     * Look up a location by name without auto-creating.
     *
     * Returns the location ID if found in the database, or 0 if not found.
     * Does not write to the database and does not require any special
     * permission — the caller is responsible for access validation.
     *
     * @param   string  $name  Location name to search for (case-insensitive).
     *
     * @return  int  Location ID, or 0 if not found.
     *
     * @since  10.1.0
     */
    public static function findLocationByName(string $name): int
    {
        $name = trim($name);

        if ($name === '') {
            return 0;
        }

        $cacheKey = strtolower($name);

        if (isset(self::$cache['location'][$cacheKey])) {
            return self::$cache['location'][$cacheKey];
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__bsms_locations'))
            ->where('LOWER(' . $db->quoteName('location_text') . ') = LOWER(' . $db->quote($name) . ')')
            ->setLimit(1);
        $db->setQuery($query);

        $id = (int) $db->loadResult();

        if ($id > 0) {
            self::$cache['location'][$cacheKey] = $id;
        }

        return $id;
    }

    /**
     * Resolve a location for CSV import, enforcing the current user's access.
     *
     * When the location system is enabled and the user is not a super admin:
     *   - If the resolved/created location is not in the user's accessible list,
     *     the location is silently dropped (returns 0) to avoid cross-campus leaks.
     *
     * Falls back to resolveOrCreateLocation() for the actual lookup/creation logic.
     *
     * @param   string  $name        Location name from the CSV file.
     * @param   bool    $autoCreate  Whether to auto-create if not found.
     * @param   int     $userId      Joomla user ID (0 = current user).
     *
     * @return  int  Location ID, or 0 if not found, not permitted, or empty name.
     *
     * @since  10.1.0
     */
    public static function processLocation(string $name, bool $autoCreate, int $userId = 0): int
    {
        $locationId = self::resolveOrCreateLocation($name, $autoCreate);

        if ($locationId <= 0) {
            return 0;
        }

        // No access check needed when a location system is disabled
        if (!CwmlocationHelper::isEnabled()) {
            return $locationId;
        }

        $app    = Factory::getApplication();
        /** @var User $user */
        $user   = $userId > 0
            ? Factory::getContainer()->get('user.factory')->loadUserById($userId)
            : $app->getIdentity();

        // Super admins may assign any location
        if ($user->authorise('core.admin')) {
            return $locationId;
        }

        $accessible = CwmlocationHelper::getUserLocations((int) $user->id);

        // Empty accessible list = super admin already handled above,
        // non-empty = must contain the target location
        if (!empty($accessible) && !\in_array($locationId, $accessible, true)) {
            return 0;
        }

        return $locationId;
    }

    /**
     * Reset the import state (caches and auto-created tracking).
     *
     * Call this at the start of a new import session.
     *
     * @return  void
     *
     * @since  10.1.0
     */
    public static function resetState(): void
    {
        self::$cache = [
            'teacher'     => [],
            'series'      => [],
            'location'    => [],
            'messagetype' => [],
            'topic'       => [],
        ];
        self::$autoCreated = [];
    }
}
