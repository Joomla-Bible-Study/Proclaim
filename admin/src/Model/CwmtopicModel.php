<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;

/**
 * Topic Model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmtopicModel extends AdminModel
{
    /**
     * Get the form data
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed  A JForm object on success, false on failure
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_proclaim.topic', 'topic', array('control' => 'jform', 'load_data' => $loadData));

        if ($form === null) {
            return false;
        }

        // Modify the form based on access controls.
        if (!$this->canEditState((object)$data)) {
            // Disable fields for display.
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('published', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is a record you can edit.
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('published', 'filter', 'unset');
        }

        return $form;
    }

    /**
     * Method to check-out a row for editing.
     *
     * @param   integer  $pk  The numeric id of the primary key.
     *
     * @return  boolean  False on failure or error, true otherwise.
     *
     * @since   11.1
     */
    public function checkout($pk = null)
    {
        return $pk;
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
    public function getTable($name = 'Cwmtopic', $prefix = '', $options = array()): Table
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Load Form Data
     *
     * @return  array    The default data is an empty array.
     *
     * @throws \Exception
     * @since   7.0
     */
    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_proclaim.edit.topic.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Custom clean the cache of com_proclaim and proclaim modules
     *
     * @param   string   $group      The cache group
     * @param   integer  $client_id  The ID of the client
     *
     * @return  void
     *
     * @since    1.6
     */
    protected function cleanCache($group = null, $client_id = 0)
    {
        parent::cleanCache('com_proclaim');
        parent::cleanCache('mod_proclaim');
    }
}
