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
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Helper class for migrating template parameters during upgrades.
 *
 * This class provides a future-proof way to add new default parameters
 * to existing templates during component upgrades.
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class CwmtemplatemigrationHelper
{
    /**
     * Database driver
     *
     * @var DatabaseInterface
     * @since 10.1.0
     */
    protected DatabaseInterface $db;

    /**
     * Array of parameter migrations keyed by version.
     * Each migration defines parameters to add with their default values.
     *
     * Format:
     * [
     *     '10.1.0' => [
     *         'parameter_name' => 'default_value',
     *         'another_param' => '1',
     *     ],
     *     '10.2.0' => [
     *         'new_feature_enabled' => '0',
     *     ],
     * ]
     *
     * @var array
     * @since 10.1.0
     */
    protected array $migrations = [
        '10.1.0' => [
            'default_show_archived'      => '2',
            'default_show_archive_badge' => '1',
        ],
    ];

    /**
     * Array of parameter renames keyed by version.
     * Each rename defines old_name => new_name mappings.
     *
     * Format:
     * [
     *     '10.1.0' => [
     *         'old_param_name' => 'new_param_name',
     *     ],
     * ]
     *
     * @var array
     * @since 10.1.0
     */
    protected array $renames = [
        '10.1.0' => [
            // Fix search filter param names to match PHP code expectations
            'show_type_search'      => 'show_messagetype_search',
            'show_locations_search' => 'show_location_search',
        ],
    ];

    /**
     * Array of color field conversions keyed by version.
     * Each conversion lists parameter names that should have legacy 0x format converted to #.
     *
     * Format:
     * [
     *     '10.1.0' => [
     *         'backcolor',
     *         'frontcolor',
     *     ],
     * ]
     *
     * @var array
     * @since 10.1.0
     */
    protected array $colorConversions = [
        '10.1.0' => [
            // Template color params that may have legacy 0x format
            'backcolor',
            'frontcolor',
            'lightcolor',
            'screencolor',
            'popupbackground',
            'teacherdisplay_color',
            'seriesdisplay_color',
        ],
    ];

    /**
     * Constructor
     *
     * @since 10.1.0
     */
    public function __construct()
    {
        $this->db = Factory::getContainer()->get(DatabaseInterface::class);
    }

    /**
     * Run all template parameter migrations from a given version.
     *
     * @param   string  $fromVersion  The version to migrate from (migrations after this version will run)
     *
     * @return  int  Number of templates updated
     *
     * @since   10.2.0
     */
    public function migrateFromVersion(string $fromVersion = '0.0.0'): int
    {
        $updatedCount = 0;

        // First, run any parameter renames
        $renamesToRun = $this->getRenamesAfterVersion($fromVersion);

        if (!empty($renamesToRun)) {
            $paramsToRename = [];

            foreach ($renamesToRun as $version => $renames) {
                foreach ($renames as $oldName => $newName) {
                    $paramsToRename[$oldName] = $newName;
                }
                Log::add('Including template param renames for version ' . $version, Log::INFO, 'com_proclaim');
            }

            $updatedCount += $this->renameParamsInTemplates($paramsToRename);
        }

        // Run any color field conversions (0x format to # format)
        $colorConversionsToRun = $this->getColorConversionsAfterVersion($fromVersion);

        if (!empty($colorConversionsToRun)) {
            $colorFields = [];

            foreach ($colorConversionsToRun as $version => $fields) {
                foreach ($fields as $field) {
                    $colorFields[$field] = true;
                }
                Log::add('Including color field conversions for version ' . $version, Log::INFO, 'com_proclaim');
            }

            $colorFieldNames = array_keys($colorFields);
            $updatedCount   += $this->convertColorFieldsInTemplates($colorFieldNames);

            // Also convert admin table color fields
            $updatedCount += $this->convertColorFieldsInAdmin();
        }

        // Get all migrations that should run (versions greater than fromVersion)
        $migrationsToRun = $this->getMigrationsAfterVersion($fromVersion);

        if (empty($migrationsToRun) && empty($renamesToRun) && empty($colorConversionsToRun)) {
            Log::add('No template migrations to run from version ' . $fromVersion, Log::INFO, 'com_proclaim');
            return 0;
        }

        // Merge all parameters from applicable migrations
        $paramsToAdd = [];

        foreach ($migrationsToRun as $version => $params) {
            foreach ($params as $key => $value) {
                $paramsToAdd[$key] = $value;
            }
            Log::add('Including template migration for version ' . $version, Log::INFO, 'com_proclaim');
        }

        // Apply merged parameters to all templates
        if (!empty($paramsToAdd)) {
            $updatedCount += $this->applyParamsToTemplates($paramsToAdd);
        }

        Log::add('Template migration complete. Updated ' . $updatedCount . ' templates.', Log::INFO, 'com_proclaim');

        return $updatedCount;
    }

    /**
     * Run all template parameter migrations regardless of version.
     * Useful for ensuring all templates have all required parameters.
     *
     * @return  int  Number of templates updated
     *
     * @since   10.2.0
     */
    public function migrateAll(): int
    {
        return $this->migrateFromVersion('0.0.0');
    }

    /**
     * Add a custom migration at runtime (for testing or special cases).
     *
     * @param   string  $version  Version identifier for the migration
     * @param   array   $params   Array of parameter => default value pairs
     *
     * @return  self
     *
     * @since   10.2.0
     */
    public function addMigration(string $version, array $params): self
    {
        $this->migrations[$version] = $params;
        return $this;
    }

    /**
     * Get migrations that should run for versions after the specified version.
     *
     * @param   string  $fromVersion  Version to compare against
     *
     * @return  array  Array of migrations to run
     *
     * @since   10.2.0
     */
    protected function getMigrationsAfterVersion(string $fromVersion): array
    {
        $result = [];

        foreach ($this->migrations as $version => $params) {
            if (version_compare($version, $fromVersion, '>')) {
                $result[$version] = $params;
            }
        }

        // Sort by version
        uksort($result, 'version_compare');

        return $result;
    }

    /**
     * Get renames that should run for versions after the specified version.
     *
     * @param   string  $fromVersion  Version to compare against
     *
     * @return  array  Array of renames to run
     *
     * @since   10.2.1
     */
    protected function getRenamesAfterVersion(string $fromVersion): array
    {
        $result = [];

        foreach ($this->renames as $version => $renames) {
            if (version_compare($version, $fromVersion, '>')) {
                $result[$version] = $renames;
            }
        }

        // Sort by version
        uksort($result, 'version_compare');

        return $result;
    }

    /**
     * Get color conversions that should run for versions after the specified version.
     *
     * @param   string  $fromVersion  Version to compare against
     *
     * @return  array  Array of color field names to convert
     *
     * @since   10.2.1
     */
    protected function getColorConversionsAfterVersion(string $fromVersion): array
    {
        $result = [];

        foreach ($this->colorConversions as $version => $fields) {
            if (version_compare($version, $fromVersion, '>')) {
                $result[$version] = $fields;
            }
        }

        // Sort by version
        uksort($result, 'version_compare');

        return $result;
    }

    /**
     * Convert legacy 0x color format to # hex format in all templates.
     *
     * @param   array  $colorFields  Array of color field names to convert
     *
     * @return  int  Number of templates updated
     *
     * @since   10.2.1
     */
    protected function convertColorFieldsInTemplates(array $colorFields): int
    {
        $updatedCount = 0;

        // Get all templates
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName(['id', 'params', 'title']))
            ->from($this->db->quoteName('#__bsms_templates'));
        $this->db->setQuery($query);
        $templates = $this->db->loadObjectList();

        foreach ($templates as $template) {
            $updated  = false;
            $registry = new Registry();

            if (!empty($template->params)) {
                $registry->loadString($template->params);
            }

            // Convert each color field if it has legacy 0x format
            foreach ($colorFields as $fieldName) {
                $value = $registry->get($fieldName);

                if ($value !== null && preg_match('/^0x([0-9A-Fa-f]{6})$/i', $value, $matches)) {
                    $newValue = '#' . strtoupper($matches[1]);
                    $registry->set($fieldName, $newValue);
                    $updated = true;
                    Log::add(
                        'Converted color field "' . $fieldName . '" from "' . $value . '" to "' . $newValue
                        . '" in template "' . $template->title . '"',
                        Log::INFO,
                        'com_proclaim'
                    );
                }
            }

            // Save if any parameters were converted
            if ($updated) {
                $this->updateTemplateParams($template->id, $registry->toString());
                $updatedCount++;
            }
        }

        return $updatedCount;
    }

    /**
     * Convert legacy 0x color format to # hex format in admin table.
     *
     * @return  int  Number of admin records updated
     *
     * @since   10.2.1
     */
    protected function convertColorFieldsInAdmin(): int
    {
        $updatedCount = 0;

        // Admin color fields stored in params column
        $adminColorFields = [
            'download_button_color',
            'media_button_color',
        ];

        // Get admin record (usually just one row)
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName(['id', 'params']))
            ->from($this->db->quoteName('#__bsms_admin'));
        $this->db->setQuery($query);
        $adminRecords = $this->db->loadObjectList();

        foreach ($adminRecords as $admin) {
            $updated  = false;
            $registry = new Registry();

            if (!empty($admin->params)) {
                $registry->loadString($admin->params);
            }

            // Convert each color field if it has legacy 0x format
            foreach ($adminColorFields as $fieldName) {
                $value = $registry->get($fieldName);

                if ($value !== null && preg_match('/^0x([0-9A-Fa-f]{6})$/i', $value, $matches)) {
                    $newValue = '#' . strtoupper($matches[1]);
                    $registry->set($fieldName, $newValue);
                    $updated = true;
                    Log::add(
                        'Converted admin color field "' . $fieldName . '" from "' . $value . '" to "' . $newValue . '"',
                        Log::INFO,
                        'com_proclaim'
                    );
                }
            }

            // Save if any parameters were converted
            if ($updated) {
                $updateQuery = $this->db->getQuery(true)
                    ->update($this->db->quoteName('#__bsms_admin'))
                    ->set($this->db->quoteName('params') . ' = ' . $this->db->quote($registry->toString()))
                    ->where($this->db->quoteName('id') . ' = ' . (int) $admin->id);
                $this->db->setQuery($updateQuery);
                $this->db->execute();
                $updatedCount++;
            }
        }

        return $updatedCount;
    }

    /**
     * Rename parameters in all templates.
     *
     * @param   array  $renames  Array of old_name => new_name pairs
     *
     * @return  int  Number of templates updated
     *
     * @since   10.2.1
     */
    protected function renameParamsInTemplates(array $renames): int
    {
        $updatedCount = 0;

        // Get all templates
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName(['id', 'params', 'title']))
            ->from($this->db->quoteName('#__bsms_templates'));
        $this->db->setQuery($query);
        $templates = $this->db->loadObjectList();

        foreach ($templates as $template) {
            $updated  = false;
            $registry = new Registry();

            if (!empty($template->params)) {
                $registry->loadString($template->params);
            }

            // Rename each parameter if old name exists
            foreach ($renames as $oldName => $newName) {
                $oldValue = $registry->get($oldName);

                if ($oldValue !== null) {
                    // Copy value to new name
                    $registry->set($newName, $oldValue);
                    // Remove old name
                    $registry->remove($oldName);
                    $updated = true;
                    Log::add(
                        'Renamed parameter "' . $oldName . '" to "' . $newName . '" in template "' . $template->title . '"',
                        Log::INFO,
                        'com_proclaim'
                    );
                }
            }

            // Save if any parameters were renamed
            if ($updated) {
                $this->updateTemplateParams($template->id, $registry->toString());
                $updatedCount++;
            }
        }

        return $updatedCount;
    }

    /**
     * Apply parameters to all templates, only adding if not already set.
     *
     * @param   array  $paramsToAdd  Array of parameter => default value pairs
     *
     * @return  int  Number of templates updated
     *
     * @since   10.2.0
     */
    protected function applyParamsToTemplates(array $paramsToAdd): int
    {
        $updatedCount = 0;

        // Get all templates
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName(['id', 'params', 'title']))
            ->from($this->db->quoteName('#__bsms_templates'));
        $this->db->setQuery($query);
        $templates = $this->db->loadObjectList();

        foreach ($templates as $template) {
            $updated  = false;
            $registry = new Registry();

            if (!empty($template->params)) {
                $registry->loadString($template->params);
            }

            // Add each parameter if it doesn't exist
            foreach ($paramsToAdd as $param => $defaultValue) {
                if ($registry->get($param) === null) {
                    $registry->set($param, $defaultValue);
                    $updated = true;
                    Log::add(
                        'Added parameter "' . $param . '" with default "' . $defaultValue . '" to template "' . $template->title . '"',
                        Log::INFO,
                        'com_proclaim'
                    );
                }
            }

            // Save if any parameters were added
            if ($updated) {
                $this->updateTemplateParams($template->id, $registry->toString());
                $updatedCount++;
            }
        }

        return $updatedCount;
    }

    /**
     * Update a template's params in the database.
     *
     * @param   int     $templateId  Template ID
     * @param   string  $params      JSON encoded params
     *
     * @return  bool  True on success
     *
     * @since   10.2.0
     */
    protected function updateTemplateParams(int $templateId, string $params): bool
    {
        $query = $this->db->getQuery(true)
            ->update($this->db->quoteName('#__bsms_templates'))
            ->set($this->db->quoteName('params') . ' = ' . $this->db->quote($params))
            ->where($this->db->quoteName('id') . ' = ' . $templateId);
        $this->db->setQuery($query);

        return $this->db->execute();
    }

    /**
     * Get the list of all defined migrations.
     *
     * @return  array  Array of migrations
     *
     * @since   10.2.0
     */
    public function getMigrations(): array
    {
        return $this->migrations;
    }

    /**
     * Check if a specific parameter exists in any template.
     *
     * @param   string  $paramName  Parameter name to check
     *
     * @return  bool  True if parameter exists in at least one template
     *
     * @since   10.2.0
     */
    public function parameterExistsInTemplates(string $paramName): bool
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('params'))
            ->from($this->db->quoteName('#__bsms_templates'));
        $this->db->setQuery($query);
        $templates = $this->db->loadColumn();

        foreach ($templates as $params) {
            if (!empty($params)) {
                $registry = new Registry($params);

                if ($registry->get($paramName) !== null) {
                    return true;
                }
            }
        }

        return false;
    }
}
