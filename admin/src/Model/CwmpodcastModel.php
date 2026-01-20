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

use CWM\Component\Proclaim\Administrator\Table\CwmpodcastTable;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;

/**
 * Podcast model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmpodcastModel extends AdminModel
{
    /**
     * Protect prefix
     *
     * @var        string    The prefix to use with controller messages.
     * @since    1.6
     */
    protected $text_prefix = 'com_proclaim';

    /**
     * Allowed batch commands
     *
     * @var array
     * @since 10.0.0
     */
    protected $batch_commands = [
        'assetgroup_id' => 'batchAccess',
        'language_id'   => 'batchLanguage',
        'linkType'      => 'batchLinkType',
    ];

    /**
     * Get the form data
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form  A JForm object on success, false on failure
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getForm($data = [], $loadData = true): Form
    {
        // Get the form.
        return $this->loadForm('com_proclaim.podcast', 'podcast', ['control' => 'jform', 'load_data' => $loadData]);
    }

    /**
     * Method to check out a row for editing.
     *
     * @param   null  $pk  The numeric ID of the primary key.
     *
     * @return int|null False on failure or error, true otherwise.
     *
     * @since   11.1
     */
    public function checkout($pk = null): ?int
    {
        return (int)$pk;
    }

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
    public function getTable($name = 'Cwmpodcast', $prefix = '', $options = []): Table
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Method to get the data that should be injected into the form.
     *
     * @return  CMSObject|array   The default data is an empty array.
     *
     * @throws \Exception
     * @since   7.0
     */
    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_proclaim.edit.podcast.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param   Table  $table  A reference to a Table object.
     *
     * @return  void
     *
     * @throws \Exception
     * @since   10.0.0
     */
    protected function prepareTable($table): void
    {
        $date = Factory::getDate();
        $user = Factory::getApplication()->getIdentity();

        // Always ensure created date is set (handles empty string from form)
        if (empty($table->created) || $table->created === '') {
            $table->created = $date->toSql();
        }

        if (empty($table->id)) {
            // Set the values for a new record
            if (empty($table->created_by)) {
                $table->created_by = $user->get('id');
            }
        } else {
            // Set the values for existing records
            $table->modified    = $date->toSql();
            $table->modified_by = $user->get('id');
        }
    }

    /**
     * Batch link type changes for a group of podcasts.
     *
     * @param   string  $value     The new value matching a link type.
     * @param   array   $pks       An array of row IDs.
     * @param   array   $contexts  An array of item contexts.
     *
     * @return  bool  True if successful, false otherwise and internal error is set.
     *
     * @throws \Exception
     * @since   10.0.0
     */
    protected function batchLinkType($value, $pks, $contexts): bool
    {
        // Set the variables
        $user = Factory::getApplication()->getSession()->get('user');
        /** @var CwmpodcastTable $table */
        $table = $this->getTable();

        foreach ($pks as $pk) {
            if ($user->authorise('core.edit', $contexts[$pk])) {
                $table->reset();
                $table->load($pk);
                $table->linktype = (int)$value;

                if (!$table->store()) {
                    $this->setError($table->getError());

                    return false;
                }
            } else {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
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
