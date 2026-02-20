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

use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;
use CWM\Component\Proclaim\Administrator\Lib\CwmscriptureMigration;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\DatabaseModel;
use Joomla\Registry\Registry;

/**
 * Upgrade Helper for 9.x to 10.x in-place migration wizard.
 *
 * Detects a legacy 9.x schema in the current database and provides
 * step-by-step methods for an AJAX-driven upgrade wizard.
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class CwmupgradeHelper
{
    /**
     * Detect whether a 9.x schema exists in the current database.
     *
     * Checks for the presence of `#__bsms_version` or `#__bsms_schemaversion`
     * tables which only exist in Proclaim 9.x and earlier.
     *
     * @return  array{detected: bool, version: string, record_counts: array}
     *
     * @since   10.1.0
     */
    public static function detect9xSchema(): array
    {
        $db        = Factory::getContainer()->get('DatabaseDriver');
        $prefix    = $db->getPrefix();
        $tableList = $db->getTableList();

        $versionTable       = $prefix . 'bsms_version';
        $schemaVersionTable = $prefix . 'bsms_schemaversion';

        $hasVersionTable       = \in_array($versionTable, $tableList, true);
        $hasSchemaVersionTable = \in_array($schemaVersionTable, $tableList, true);

        if (!$hasVersionTable && !$hasSchemaVersionTable) {
            return ['detected' => false, 'version' => '', 'record_counts' => []];
        }

        // Try to read the version from the 9.x tables
        $version = '';

        if ($hasSchemaVersionTable) {
            try {
                $query = $db->getQuery(true)
                    ->select($db->quoteName('version'))
                    ->from($db->quoteName('#__bsms_schemaversion'))
                    ->order($db->quoteName('id') . ' DESC')
                    ->setLimit(1);
                $db->setQuery($query);
                $version = (string) $db->loadResult();
            } catch (\Exception $e) {
                // Table may exist but be empty
            }
        }

        if (empty($version) && $hasVersionTable) {
            try {
                $query = $db->getQuery(true)
                    ->select($db->quoteName('version'))
                    ->from($db->quoteName('#__bsms_version'))
                    ->order($db->quoteName('id') . ' DESC')
                    ->setLimit(1);
                $db->setQuery($query);
                $version = (string) $db->loadResult();
            } catch (\Exception $e) {
                // Table may exist but be empty
            }
        }

        return [
            'detected'      => true,
            'version'       => $version,
            'record_counts' => [],
        ];
    }

    /**
     * Check if the detected 9.x version meets the minimum requirement.
     *
     * @param   string  $version  The detected 9.x version string
     *
     * @return  bool  True if version is >= 9.2.0
     *
     * @since   10.1.0
     */
    public static function meetsMinimumVersion(string $version): bool
    {
        if (empty($version)) {
            return false;
        }

        return version_compare($version, '9.2.0', '>=');
    }

    /**
     * Get detailed record counts from all bsms tables.
     *
     * @return  array  Associative array of table name => record count
     *
     * @since   10.1.0
     */
    public static function get9xInfo(): array
    {
        $db     = Factory::getContainer()->get('DatabaseDriver');
        $tables = CwmdbHelper::getObjects();
        $counts = [];

        foreach ($tables as $table) {
            try {
                $query = $db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from($db->quoteName($table['name']));
                $db->setQuery($query);
                $count = (int) $db->loadResult();

                // Use short name for display (strip prefix)
                $shortName          = str_replace('#__bsms_', '', $table['name']);
                $counts[$shortName] = $count;
            } catch (\Exception $e) {
                // Skip tables that can't be queried
            }
        }

        return $counts;
    }

    /**
     * Convert INI-format params to JSON across all bsms tables.
     *
     * Legacy 9.x stored parameters in INI format. This method scans all
     * bsms tables for `params` columns and converts INI strings to JSON.
     *
     * @return  array{converted: int, skipped: int, errors: array}
     *
     * @since   10.1.0
     */
    public static function convertIniToJson(): array
    {
        $db        = Factory::getContainer()->get('DatabaseDriver');
        $tables    = CwmdbHelper::getObjects();
        $converted = 0;
        $skipped   = 0;
        $errors    = [];

        foreach ($tables as $table) {
            try {
                // Check if table has a params column
                $columns = $db->getTableColumns($table['name']);

                if (!isset($columns['params'])) {
                    continue;
                }

                // Load all rows with non-empty, non-JSON params
                $query = $db->getQuery(true)
                    ->select([$db->quoteName('id'), $db->quoteName('params')])
                    ->from($db->quoteName($table['name']))
                    ->where($db->quoteName('params') . ' IS NOT NULL')
                    ->where($db->quoteName('params') . ' != ' . $db->quote(''))
                    ->where($db->quoteName('params') . ' NOT LIKE ' . $db->quote('{%'));
                $db->setQuery($query);
                $rows = $db->loadObjectList();

                foreach ($rows as $row) {
                    $registry = new Registry($row->params);
                    $json     = $registry->toString('JSON');

                    if ($json === '{}' && !empty(trim($row->params))) {
                        $skipped++;

                        continue;
                    }

                    $update = $db->getQuery(true)
                        ->update($db->quoteName($table['name']))
                        ->set($db->quoteName('params') . ' = ' . $db->quote($json))
                        ->where($db->quoteName('id') . ' = ' . (int) $row->id);
                    $db->setQuery($update);
                    $db->execute();
                    $converted++;
                }
            } catch (\Exception $e) {
                $errors[] = $table['name'] . ': ' . $e->getMessage();
            }
        }

        return [
            'converted' => $converted,
            'skipped'   => $skipped,
            'errors'    => $errors,
        ];
    }

    /**
     * Reset the #__schemas version for com_proclaim to 0.0.0.
     *
     * This forces Joomla's DatabaseModel::fix() to re-run all SQL update
     * files, applying the 10.x schema changes to the 9.x tables.
     *
     * @return  bool  True on success
     *
     * @since   10.1.0
     */
    public static function resetSchemaVersion(): bool
    {
        $db  = Factory::getContainer()->get('DatabaseDriver');
        $cid = self::getExtensionId();

        if (!$cid) {
            return false;
        }

        // Delete current schema entry
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__schemas'))
            ->where($db->quoteName('extension_id') . ' = ' . $cid);
        $db->setQuery($query);
        $db->execute();

        // Insert baseline version so fix() runs all update files
        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__schemas'))
            ->columns([$db->quoteName('extension_id'), $db->quoteName('version_id')])
            ->values($cid . ', ' . $db->quote('0.0.0'));
        $db->setQuery($query);
        $db->execute();

        Log::add('Schema version reset to 0.0.0 for upgrade wizard', Log::INFO, 'com_proclaim');

        return true;
    }

    /**
     * Run Joomla's DatabaseModel::fix() to apply all SQL update files.
     *
     * @return  array{success: bool, message: string}
     *
     * @since   10.1.0
     */
    public static function runSchemaMigration(): array
    {
        $cid = self::getExtensionId();

        if (!$cid) {
            return ['success' => false, 'message' => 'Extension ID not found'];
        }

        try {
            $databaseModel = new DatabaseModel();
            $databaseModel->fix([$cid]);

            Log::add('Schema migration completed via upgrade wizard', Log::INFO, 'com_proclaim');

            return ['success' => true, 'message' => 'Schema migration completed'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Run the full data-fix pipeline.
     *
     * This is the same pipeline used by restore and install finish steps.
     *
     * @return  array{steps: array, errors: array}
     *
     * @since   10.1.0
     */
    public static function runDataFixes(): array
    {
        $steps  = [];
        $errors = [];

        // 1. Fix menus
        try {
            CwmmigrationHelper::fixMenus();
            $steps[] = ['name' => 'fixMenus', 'success' => true, 'detail' => ''];
        } catch (\Exception $e) {
            $errors[] = 'fixMenus: ' . $e->getMessage();
            $steps[]  = ['name' => 'fixMenus', 'success' => false, 'detail' => $e->getMessage()];
        }

        // 2. Fix empty access
        try {
            CwmmigrationHelper::fixemptyaccess();
            $steps[] = ['name' => 'fixEmptyAccess', 'success' => true, 'detail' => ''];
        } catch (\Exception $e) {
            $errors[] = 'fixEmptyAccess: ' . $e->getMessage();
            $steps[]  = ['name' => 'fixEmptyAccess', 'success' => false, 'detail' => $e->getMessage()];
        }

        // 3. Fix empty language
        try {
            CwmmigrationHelper::fixemptylanguage();
            $steps[] = ['name' => 'fixEmptyLanguage', 'success' => true, 'detail' => ''];
        } catch (\Exception $e) {
            $errors[] = 'fixEmptyLanguage: ' . $e->getMessage();
            $steps[]  = ['name' => 'fixEmptyLanguage', 'success' => false, 'detail' => $e->getMessage()];
        }

        // 4. Migrate deprecated players
        try {
            $count   = CwmmigrationHelper::migrateDeprecatedPlayers();
            $steps[] = ['name' => 'migrateDeprecatedPlayers', 'success' => true, 'detail' => $count . ' updated'];
        } catch (\Exception $e) {
            $errors[] = 'migrateDeprecatedPlayers: ' . $e->getMessage();
            $steps[]  = ['name' => 'migrateDeprecatedPlayers', 'success' => false, 'detail' => $e->getMessage()];
        }

        // 5. Template parameter migration
        try {
            $migration = new CwmtemplatemigrationHelper();
            $count     = $migration->migrateAll();
            $steps[]   = ['name' => 'templateMigration', 'success' => true, 'detail' => $count . ' templates updated'];
        } catch (\Exception $e) {
            $errors[] = 'templateMigration: ' . $e->getMessage();
            $steps[]  = ['name' => 'templateMigration', 'success' => false, 'detail' => $e->getMessage()];
        }

        // 6. Seed bible translations
        try {
            $count   = CwmmigrationHelper::seedBibleTranslations();
            $steps[] = ['name' => 'seedBibleTranslations', 'success' => true, 'detail' => $count . ' seeded'];
        } catch (\Exception $e) {
            $errors[] = 'seedBibleTranslations: ' . $e->getMessage();
            $steps[]  = ['name' => 'seedBibleTranslations', 'success' => false, 'detail' => $e->getMessage()];
        }

        // 7. Populate study_teachers junction table
        try {
            $count   = CwmmigrationHelper::populateStudyTeachers();
            $steps[] = ['name' => 'populateStudyTeachers', 'success' => true, 'detail' => $count . ' populated'];
        } catch (\Exception $e) {
            $errors[] = 'populateStudyTeachers: ' . $e->getMessage();
            $steps[]  = ['name' => 'populateStudyTeachers', 'success' => false, 'detail' => $e->getMessage()];
        }

        // 8. Fix teacher aliases and duplicates
        try {
            $count   = CwmmigrationHelper::fixTeacherAliases();
            $steps[] = ['name' => 'fixTeacherAliases', 'success' => true, 'detail' => $count . ' fixed'];
        } catch (\Exception $e) {
            $errors[] = 'fixTeacherAliases: ' . $e->getMessage();
            $steps[]  = ['name' => 'fixTeacherAliases', 'success' => false, 'detail' => $e->getMessage()];
        }

        // 9. Scripture junction migration
        try {
            $count   = CwmscriptureMigration::migrate();
            $steps[] = ['name' => 'scriptureMigration', 'success' => true, 'detail' => $count . ' migrated'];
        } catch (\Exception $e) {
            $errors[] = 'scriptureMigration: ' . $e->getMessage();
            $steps[]  = ['name' => 'scriptureMigration', 'success' => false, 'detail' => $e->getMessage()];
        }

        // 10. Fix legacy mediafile paths
        try {
            $count   = CwmmigrationHelper::fixMediafileLegacyPaths();
            $steps[] = ['name' => 'fixMediafilePaths', 'success' => true, 'detail' => $count . ' fixed'];
        } catch (\Exception $e) {
            $errors[] = 'fixMediafilePaths: ' . $e->getMessage();
            $steps[]  = ['name' => 'fixMediafilePaths', 'success' => false, 'detail' => $e->getMessage()];
        }

        // 11. Register guided tours
        try {
            $tourHelper = new CwmguidedtourHelper();
            $count      = $tourHelper->registerGuidedTours();
            $steps[]    = ['name' => 'registerGuidedTours', 'success' => true, 'detail' => $count . ' registered'];
        } catch (\Exception $e) {
            // Non-critical: guided tours may not be supported on all Joomla versions
            $steps[] = ['name' => 'registerGuidedTours', 'success' => true, 'detail' => 'skipped'];
        }

        return [
            'steps'  => $steps,
            'errors' => $errors,
        ];
    }

    /**
     * Rebuild ACL assets for all Proclaim tables.
     *
     * @return  array{success: bool, message: string}
     *
     * @since   10.1.0
     */
    public static function rebuildAssets(): array
    {
        try {
            $results = Cwmassets::build();

            foreach ($results->query as $key => $items) {
                foreach ($items as $item) {
                    Cwmassets::fixAssets($key, $item);
                }
            }

            Log::add('Asset rebuild completed via upgrade wizard', Log::INFO, 'com_proclaim');

            return ['success' => true, 'message' => 'Assets rebuilt'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Verify the upgrade was successful.
     *
     * Checks that key 10.x tables and columns exist and reports record counts.
     *
     * @return  array{success: bool, checks: array, record_counts: array}
     *
     * @since   10.1.0
     */
    public static function verify(): array
    {
        $db        = Factory::getContainer()->get('DatabaseDriver');
        $prefix    = $db->getPrefix();
        $tableList = $db->getTableList();
        $checks    = [];

        // Verify key 10.x tables exist
        $requiredTables = [
            '#__bsms_studies',
            '#__bsms_teachers',
            '#__bsms_series',
            '#__bsms_mediafiles',
            '#__bsms_templates',
            '#__bsms_admin',
            '#__bsms_bible_translations',
            '#__bsms_bible_verses',
            '#__bsms_study_teachers',
            '#__bsms_study_scriptures',
        ];

        $allTablesExist = true;

        foreach ($requiredTables as $table) {
            $realName = str_replace('#__', $prefix, $table);
            $exists   = \in_array($realName, $tableList, true);

            $checks[] = [
                'item'   => $table,
                'status' => $exists ? 'ok' : 'missing',
            ];

            if (!$exists) {
                $allTablesExist = false;
            }
        }

        // Check schema version
        $cid = self::getExtensionId();

        if ($cid) {
            $query = $db->getQuery(true)
                ->select($db->quoteName('version_id'))
                ->from($db->quoteName('#__schemas'))
                ->where($db->quoteName('extension_id') . ' = ' . $cid);
            $db->setQuery($query);
            $schemaVersion = (string) $db->loadResult();

            $checks[] = [
                'item'   => 'schema_version',
                'status' => $schemaVersion ?: 'unknown',
            ];
        }

        // Get final record counts
        $recordCounts = self::get9xInfo();

        return [
            'success'       => $allTablesExist,
            'checks'        => $checks,
            'record_counts' => $recordCounts,
        ];
    }

    /**
     * Drop legacy 9.x artifact tables that are no longer needed.
     *
     * @return  int  Number of tables dropped
     *
     * @since   10.1.0
     */
    public static function cleanup9xArtifacts(): int
    {
        $db        = Factory::getContainer()->get('DatabaseDriver');
        $prefix    = $db->getPrefix();
        $tableList = $db->getTableList();
        $dropped   = 0;

        $legacyTables = [
            '#__bsms_version',
            '#__bsms_schemaversion',
            '#__bsms_timeset',
        ];

        foreach ($legacyTables as $table) {
            $realName = str_replace('#__', $prefix, $table);

            if (\in_array($realName, $tableList, true)) {
                try {
                    $db->setQuery('DROP TABLE IF EXISTS ' . $db->quoteName($table));
                    $db->execute();
                    $dropped++;
                    Log::add('Dropped legacy table: ' . $table, Log::INFO, 'com_proclaim');
                } catch (\Exception $e) {
                    Log::add('Failed to drop ' . $table . ': ' . $e->getMessage(), Log::WARNING, 'com_proclaim');
                }
            }
        }

        return $dropped;
    }

    /**
     * Check whether the database schema is behind the shipped SQL update files.
     *
     * Compares the version_id recorded in #__schemas against the latest
     * update file in admin/sql/updates/mysql/.  Returns an array with
     * the current and expected versions, or null when everything is current.
     *
     * @return  array{current: string, expected: string}|null  Null if up-to-date
     *
     * @since   10.1.0
     */
    public static function isSchemaOutOfDate(): ?array
    {
        try {
            $db  = Factory::getContainer()->get('DatabaseDriver');
            $cid = self::getExtensionId();

            if (!$cid) {
                return null;
            }

            // Current schema version from Joomla's tracking table
            $query = $db->getQuery(true)
                ->select($db->quoteName('version_id'))
                ->from($db->quoteName('#__schemas'))
                ->where($db->quoteName('extension_id') . ' = ' . $cid);
            $db->setQuery($query);
            $currentVersion = (string) $db->loadResult();

            if (!$currentVersion) {
                return null;
            }

            // Find the latest shipped update file
            $updateDir = JPATH_ADMINISTRATOR . '/components/com_proclaim/sql/updates/mysql';

            if (!is_dir($updateDir)) {
                return null;
            }

            $files = glob($updateDir . '/*.sql');

            if (empty($files)) {
                return null;
            }

            // Extract version strings (e.g. "10.1.0-20260220" from filename)
            $versions = [];

            foreach ($files as $file) {
                $versions[] = basename($file, '.sql');
            }

            usort($versions, 'version_compare');
            $latestVersion = end($versions);

            // If the recorded schema is older than the newest file, it's out of date
            if (version_compare($currentVersion, $latestVersion, '<')) {
                return [
                    'current'  => $currentVersion,
                    'expected' => $latestVersion,
                ];
            }
        } catch (\Exception) {
            // Fail silently — this is a non-critical informational check
        }

        return null;
    }

    /**
     * Get the Proclaim extension ID from #__extensions.
     *
     * @return  int  Extension ID, or 0 if not found
     *
     * @since   10.1.0
     */
    private static function getExtensionId(): int
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_proclaim'));
        $db->setQuery($query);

        return (int) $db->loadResult();
    }
}
