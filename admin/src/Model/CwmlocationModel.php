<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmlocationHelper;
use CWM\Component\Proclaim\Administrator\Table\CwmlocationTable;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;

/**
 * Location model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmlocationModel extends AdminModel
{
    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $name     The table name. Optional.
     * @param   string  $prefix   The class prefix. Optional.
     * @param   array   $options  Configuration array for model. Optional.
     *
     * @return  Table  A Table object
     *
     * @throws  \Exception
     * @since   3.0
     */
    public function getTable($name = 'Cwmlocation', $prefix = '', $options = []): Table
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Get the form data
     *
     * @param   array  $data      Data for the form.
     * @param   bool   $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  false|Form  A JForm object on success, false on failure
     *
     * @throws \Exception
     * @since  7.0
     */
    public function getForm($data = [], $loadData = true): mixed
    {
        // Get the form.
        $form = $this->loadForm(
            'com_proclaim.location',
            'location',
            ['control' => 'jform', 'load_data' => $loadData]
        );

        return $form ?? false;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  array    The default data is an empty array.
     *
     * @throws \Exception
     * @since   7.0
     */
    protected function loadFormData(): mixed
    {
        $data = Factory::getApplication()->getUserState('com_proclaim.edit.location.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param   CwmlocationTable  $table  A reference to a Table object.
     *
     * @return  void
     *
     * @throws \Exception
     * @since   10.0.0
     */
    protected function prepareTable($table): void
    {
        $date = new Date();
        $user = Factory::getApplication()->getIdentity();

        // Always ensure created date is set (handles empty string from form)
        if (empty($table->created) || $table->created === '') {
            $table->created = $date->toSql();
        }

        if (empty($table->id)) {
            // Set the values for a new record
            if (empty($table->created_by)) {
                $table->created_by = $user->id;
            }
        } else {
            // Set the values for existing records
            $table->modified    = $date->toSql();
            $table->modified_by = $user->id;
        }
    }

    /**
     * Block trashing a location that still has content assigned.
     *
     * The trash action (publish state -2) is the precursor to permanent
     * deletion. Prevent trashing when content references this location so
     * admins are guided to merge/reassign first.
     *
     * @param   array  &$pks   Primary key IDs.
     * @param   int    $value  The publish state value.
     *
     * @return  bool
     *
     * @since   10.3.0
     */
    public function publish(&$pks, $value = 1): bool
    {
        // Only intercept trashing (value -2)
        if ((int) $value === -2) {
            foreach ($pks as $pk) {
                $this->assertLocationUnused((int) $pk);
            }
        }

        return parent::publish($pks, $value);
    }

    /**
     * Delete one or more location records after verifying they are not in use.
     *
     * Blocks deletion when:
     *   - Content is assigned to the location across any of the 6 entity tables, OR
     *   - The location is referenced in the group-to-location mapping config.
     *
     * @param   array  $pks  Primary key IDs to delete.
     *
     * @return  bool  True if all requested records were deleted; false otherwise.
     *
     * @since   10.1.0
     */
    public function delete(&$pks): bool
    {
        foreach ($pks as $pk) {
            $this->assertLocationUnused((int) $pk);
        }

        return parent::delete($pks);
    }

    /**
     * Throw if a location has content assigned or is in the group mapping.
     *
     * @param   int  $locationId  The location ID to check.
     *
     * @return  void
     *
     * @throws  \RuntimeException  If the location is in use.
     *
     * @since   10.3.0
     */
    private function assertLocationUnused(int $locationId): void
    {
        $usage = $this->getLocationUsage($locationId);
        $total = $usage['messages'] + $usage['series'] + $usage['podcasts']
            + $usage['servers'] + $usage['templates'] + $usage['templatecodes'];

        if ($total > 0) {
            throw new \RuntimeException(
                Text::sprintf('JBS_ERROR_LOCATION_IN_USE', $this->formatUsageSummary($usage))
            );
        }

        if ($this->isInGroupMapping($locationId)) {
            throw new \RuntimeException(Text::_('JBS_ERROR_LOCATION_IN_GROUP_MAPPING'));
        }
    }

    /**
     * Build a human-readable summary of non-zero usage counts.
     *
     * Example output: "1 message, 3 podcasts"
     *
     * @param   array  $usage  Usage counts keyed by entity type.
     *
     * @return  string
     *
     * @since   10.3.0
     */
    private function formatUsageSummary(array $usage): string
    {
        $labels = [
            'messages'      => ['message', 'messages'],
            'series'        => ['series', 'series'],
            'podcasts'      => ['podcast', 'podcasts'],
            'servers'       => ['server', 'servers'],
            'templates'     => ['template', 'templates'],
            'templatecodes' => ['template code', 'template codes'],
        ];

        $parts = [];

        foreach ($labels as $key => [$singular, $plural]) {
            if (($usage[$key] ?? 0) > 0) {
                $parts[] = $usage[$key] . ' ' . ($usage[$key] === 1 ? $singular : $plural);
            }
        }

        return implode(', ', $parts);
    }

    /**
     * Return content counts for a location, used for deletion safety checks.
     *
     * Results are cached per request to avoid repeated queries when checking
     * multiple locations in a batch delete.
     *
     * @param   int  $locationId  The location ID to check.
     *
     * @return  array{messages: int, series: int, podcasts: int, servers: int, templates: int, templatecodes: int}
     *
     * @since   10.1.0
     */
    public function getLocationUsage(int $locationId): array
    {
        static $cache = [];

        if (isset($cache[$locationId])) {
            return $cache[$locationId];
        }

        // Messages — always available via CwmlocationHelper
        $usage = CwmlocationHelper::getLocationUsage($locationId);

        // Series
        $usage['series'] = $this->countByLocationId('#__bsms_series', $locationId);

        // Podcasts
        $usage['podcasts'] = $this->countByLocationId('#__bsms_podcast', $locationId);

        // Servers
        $usage['servers'] = $this->countByLocationId('#__bsms_servers', $locationId);

        // Templates
        $usage['templates'] = $this->countByLocationId('#__bsms_templates', $locationId);

        // Template codes
        $usage['templatecodes'] = $this->countByLocationId('#__bsms_templatecode', $locationId);

        $cache[$locationId] = $usage;

        return $usage;
    }

    /**
     * Merge a source location into a target location.
     *
     * Reassigns all content referencing the source location to the target,
     * updates group mapping config if needed, then deletes the source.
     *
     * @param   int  $sourceId  The location to merge from (will be deleted).
     * @param   int  $targetId  The location to merge into (receives content).
     *
     * @return  int  The number of records reassigned.
     *
     * @throws  \RuntimeException  On validation failure.
     *
     * @since   10.3.0
     */
    public function merge(int $sourceId, int $targetId): int
    {
        if ($sourceId === $targetId) {
            throw new \RuntimeException(Text::_('JBS_LOC_MERGE_SAME_ERROR'));
        }

        // Validate both locations exist
        $sourceTable = $this->getTable();
        $targetTable = $this->getTable();

        if (!$sourceTable->load($sourceId) || !$sourceTable->id) {
            throw new \RuntimeException(Text::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $sourceId));
        }

        if (!$targetTable->load($targetId) || !$targetTable->id) {
            throw new \RuntimeException(Text::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $targetId));
        }

        // Target must be published
        if ((int) $targetTable->published !== 1) {
            throw new \RuntimeException(Text::_('JBS_LOC_MERGE_TARGET_UNPUBLISHED'));
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Reassign location_id across all 6 entity tables
        $tables = [
            '#__bsms_studies',
            '#__bsms_series',
            '#__bsms_podcast',
            '#__bsms_servers',
            '#__bsms_templates',
            '#__bsms_templatecode',
        ];

        $totalReassigned = 0;

        foreach ($tables as $table) {
            $query = $db->getQuery(true)
                ->update($db->quoteName($table))
                ->set($db->quoteName('location_id') . ' = ' . $targetId)
                ->where($db->quoteName('location_id') . ' = ' . $sourceId);
            $db->setQuery($query);
            $db->execute();
            $totalReassigned += $db->getAffectedRows();
        }

        // Update group mapping config if source is referenced
        $this->updateGroupMapping($sourceId, $targetId);

        // Delete the source location — bypass safety check via parent since content is moved
        $pks = [$sourceId];

        if (!parent::delete($pks)) {
            throw new \RuntimeException(Text::sprintf('JLIB_APPLICATION_ERROR_DELETE_FAILED', $sourceId));
        }

        $this->cleanCache();

        return $totalReassigned;
    }

    /**
     * Update the group-to-location mapping config, replacing source with target.
     *
     * If the source location is mapped to user groups, those mappings are
     * transferred to the target. If the target already has mappings, the
     * group lists are merged (deduplicated).
     *
     * @param   int  $sourceId  The location being removed.
     * @param   int  $targetId  The location receiving the mappings.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    private function updateGroupMapping(int $sourceId, int $targetId): void
    {
        $params = ComponentHelper::getParams('com_proclaim');
        $raw    = $params->get('location_group_mapping', '{}');

        if (\is_string($raw)) {
            try {
                $mapping = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                return;
            }
        } else {
            $mapping = (array) $raw;
        }

        if (!\is_array($mapping) || !isset($mapping[(string) $sourceId])) {
            return;
        }

        $sourceGroups = (array) $mapping[(string) $sourceId];
        $targetGroups = (array) ($mapping[(string) $targetId] ?? []);

        // Merge and deduplicate
        $merged = array_values(array_unique(array_merge($targetGroups, $sourceGroups)));

        $mapping[(string) $targetId] = $merged;
        unset($mapping[(string) $sourceId]);

        // Save back to component params
        $params->set('location_group_mapping', json_encode($mapping, JSON_THROW_ON_ERROR));

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('params') . ' = ' . $db->quote($params->toString()))
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_proclaim'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'));
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Count records in a table by location_id, returning 0 if the column does
     * not exist (graceful degradation for tables not yet in the location system).
     *
     * @param   string  $table      Database table name (with # prefix).
     * @param   int     $locationId Location ID to count.
     *
     * @return  int
     *
     * @since   10.1.0
     */
    private function countByLocationId(string $table, int $locationId): int
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName($table))
            ->where($db->quoteName('location_id') . ' = ' . $locationId);
        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    /**
     * Check whether a location ID appears in the component group-mapping config.
     *
     * @param   int  $locationId  Location ID to look up.
     *
     * @return  bool
     *
     * @since   10.1.0
     */
    private function isInGroupMapping(int $locationId): bool
    {
        $raw = ComponentHelper::getParams('com_proclaim')->get('location_group_mapping', '{}');

        if (\is_string($raw)) {
            try {
                $mapping = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                return false;
            }
        } else {
            $mapping = $raw;
        }

        if (!\is_array($mapping)) {
            return false;
        }

        return \array_key_exists((string) $locationId, $mapping);
    }

    /**
     * Custom clean the cache of com_proclaim and proclaim modules
     *
     * @param   string  $group      The cache group
     * @param   int     $client_id  The ID of the client
     *
     * @return  void
     *
     * @since    1.6
     */
    protected function cleanCache($group = null, int $client_id = 0): void
    {
        parent::cleanCache('com_proclaim');
        parent::cleanCache('mod_proclaim');
    }
}
