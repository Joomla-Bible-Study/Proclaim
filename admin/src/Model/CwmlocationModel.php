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
     * Delete one or more location records after verifying they are not in use.
     *
     * Blocks deletion when:
     *   - Messages, series, or podcasts are assigned to the location, OR
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
            $pk    = (int) $pk;
            $usage = $this->getLocationUsage($pk);

            // Block if content is assigned to this location
            if ($usage['messages'] > 0 || $usage['series'] > 0 || $usage['podcasts'] > 0) {
                $this->setError(
                    Text::sprintf(
                        'JBS_ERROR_LOCATION_IN_USE',
                        $usage['messages'],
                        $usage['series'],
                        $usage['podcasts']
                    )
                );

                return false;
            }

            // Block if this location is referenced in the group mapping config
            if ($this->isInGroupMapping($pk)) {
                $this->setError(Text::_('JBS_ERROR_LOCATION_IN_GROUP_MAPPING'));

                return false;
            }
        }

        return parent::delete($pks);
    }

    /**
     * Return content counts for a location, used for deletion safety checks.
     *
     * Results are cached per request to avoid repeated queries when checking
     * multiple locations in a batch delete.
     *
     * @param   int  $locationId  The location ID to check.
     *
     * @return  array{messages: int, series: int, podcasts: int}
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

        // Series — count if #__bsms_series has a location_id column
        $usage['series'] = $this->countByLocationId('#__bsms_series', $locationId);

        // Podcasts — count if #__bsms_podcast has a location_id column
        $usage['podcasts'] = $this->countByLocationId('#__bsms_podcast', $locationId);

        $cache[$locationId] = $usage;

        return $usage;
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
        $db      = Factory::getContainer()->get('DatabaseDriver');
        $columns = $db->getTableColumns($table);

        if (!isset($columns['location_id'])) {
            return 0;
        }

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
            $mapping = json_decode($raw, true);
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
