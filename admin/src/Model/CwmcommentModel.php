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

use CWM\Component\Proclaim\Administrator\Table\CwmcommentTable;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;

/**
 * Comment model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmcommentModel extends AdminModel
{
    /**
     * The type alias for this content type (for example, 'com_proclaim.cwmcomment').
     *
     * @var      string
     * @since    3.2
     */
    public $typeAlias = 'com_proclaim.comment';
    /**
     * @var    string  The prefix to use with controller messages.
     * @since  1.6
     */
    protected $text_prefix = 'com_proclaim';

    /**
     * Overrides the AdminModel save routine to save the topics(tags)
     *
     * @param   array  $data  The form data.
     *
     * @return  bool  True on success, False on error.
     *
     * @since 7.0.1
     */
    public function save($data): bool
    {
        if (parent::save($data)) {
            return true;
        }

        return false;
    }

    /**
     * Method to check-out a row for editing.
     *
     * @param   int  $pk  The numeric id of the primary key.
     *
     * @return  bool  False on failure or error, true otherwise.
     *
     * @since   11.1
     */
    public function checkout($pk = null): mixed
    {
        return $pk;
    }

    /**
     * Get the form data
     *
     * @param   array    $data      Data for the form.
     * @param   bool  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed  A JForm object on success, false on failure
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getForm($data = [], $loadData = true): mixed
    {
        // Get the form.
        $form = $this->loadForm('com_proclaim.comment', 'comment', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        $jinput = Factory::getApplication()->getInput();

        // The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
        if ($jinput->get('a_id')) {
            $id = $jinput->get('a_id', 0);
        } else {
            // The back end uses id so we use that the rest of the time and set it to 0 by default.
            $id = $jinput->get('id', 0);
        }

        $user = Factory::getApplication()->getIdentity();

        // Check for existing article.
        // Modify the form based on Edit State access controls.
        if (
            ($id !== 0 && (!$user->authorise('core.edit.state', 'com_proclaim.comment.' . (int)$id)))
            || ($id === 0 && !$user->authorise('core.edit.state', 'com_proclaim'))
        ) {
            // Disable fields for display.
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('state', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is an article you can edit.
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('state', 'filter', 'unset');
        }

        return $form;
    }

    /**
     * Batch copy items to a new category or current.
     *
     * @param   int    $value     The new category.
     * @param   array  $pks       An array of row IDs.
     * @param   array  $contexts  An array of item contexts.
     *
     * @return  mixed  An array of new IDs on success, bool false on failure.
     *
     * @throws \Exception
     * @since    11.1
     */
    protected function batchCopy($value, $pks, $contexts): array|bool
    {
        $categoryId = (int)'';
        $newIds     = [];

        /** @type CwmcommentTable $table */
        $table = $this->getTable();
        $i     = 0;

        // Check that the user has create permission for the component
        $extension = Factory::getApplication()->getInput()->get('option', '');
        $user = Factory::getApplication()->getIdentity();

        if (!$user->authorise('core.create', $extension)) {
            $this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'));

            return false;
        }

        // Parent exists so we let's proceed
        while (!empty($pks)) {
            // Pop the first ID off the stack
            $pk = array_shift($pks);

            $table->reset();

            // Check that the row actually exists
            if (!$table->load($pk)) {
                if ($error = $table->getError()) {
                    // Fatal error
                    $this->setError($error);

                    return false;
                }

                // Not fatal error
                $this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                continue;
            }

            // Alter the title & alias
            $data = $this->generateNewTitle($categoryId, '', '');

            // Reset the ID because we are making a copy
            $table->id = 0;

            // Check the row.
            if (!$table->check()) {
                $this->setError($table->getError());

                return false;
            }

            // Store the row.
            if (!$table->store()) {
                $this->setError($table->getError());

                return false;
            }

            // Get the new item ID
            $newId = $table->get('id');

            // Add the new ID to the array
            $newIds[$i] = $newId;
            $i++;
        }

        // Clean the cache
        $this->cleanCache();

        return $newIds;
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
    public function getTable($name = 'Cwmcomment', $prefix = '', $options = []): Table
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Clean the cache
     *
     * @param   string  $group      The cache group
     * @param   int     $client_id  The ID of the client
     *
     * @return  void
     *
     * @since    1.6
     */
    protected function cleanCache($group = null, $client_id = 0): void
    {
        parent::cleanCache('com_proclaim');
        parent::cleanCache('mod_proclaim');
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return    bool    True if allowed to delete the record. Defaults to the permission set in the component.
     *
     * @throws \Exception
     * @since    1.6
     */
    protected function canDelete($record): bool
    {
        if (!empty($record->id)) {
            if ($record->published != -2) {
                return false;
            }

            return Factory::getApplication()->getIdentity()->authorise(
                'core.delete',
                'com_proclaim.comment.' . (int)$record->id
            );
        }

        return false;
    }

    /**
     * Method to test whether a record can have its state edited.
     *
     * @param   object  $record  A record object.
     *
     * @return  bool  True if allowed to change the state of the record. Defaults to the permission for the component.
     *
     * @throws \Exception
     * @since    1.6
     */
    protected function canEditState($record): bool
    {
        $user = Factory::getApplication()->getIdentity();

        // Check for existing article.
        if (!empty($record->id)) {
            return $user->authorise('core.edit.state', 'com_proclaim.comment.' . (int)$record->id);
        }

        // Default to component settings if serie known.
        return parent::canEditState($record);
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param   CwmcommentTable  $table  A reference to a JTable object.
     *
     * @return  void
     *
     * @throws \Exception
     * @since    1.6
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
                $table->created_by = $user->get('id');
            }
        } else {
            // Set the values for existing records
            $table->modified    = $date->toSql();
            $table->modified_by = $user->get('id');
        }
    }

    /**
     * Load Form Data
     *
     * @return object
     *
     * @throws \Exception
     * @since   7.0
     */
    protected function loadFormData(): object
    {
        $data = Factory::getApplication()->getUserState('com_proclaim.edit.comment.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }
}
