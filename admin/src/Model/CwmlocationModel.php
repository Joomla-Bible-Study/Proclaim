<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\Input\Input;

/**
 * Location model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmlocationModel extends AdminModel
{
    /**
     * Method to store a record
     *
     * @access    public
     * @return    bool    True on success
     *
     * @throws Exception
     * @since     7.0
     */
    public function store()
    {
        $row   = $this->getTable();
        $input = new Input();
        $data  = $input->get('post');

        // Bind the form fields to the table
        if (!$row->bind($data)) {
            $this->setError($this->_db->getErrorMsg());

            return false;
        }

        // Make sure the record is valid
        if (!$row->check()) {
            $this->setError($this->_db->getErrorMsg());

            return false;
        }

        // Store the web link table to the database
        if (!$row->store()) {
            $this->setError($this->_db->getErrorMsg());

            return false;
        }

        return true;
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
     * @throws  Exception
     * @since   3.0
     */
    public function getTable($name = 'Cwmlocation', $prefix = '', $options = array()): Table
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
     * @since  7.0
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            'com_proclaim.location',
            'location',
            array('control' => 'jform', 'load_data' => $loadData)
        );

        return $form ?? false;
    }

    /**
     * Method to check-out a row for editing.
     *
     * @param   ?int  $pk  The numeric id of the primary key.
     *
     * @return  bool  False on failure or error, true otherwise.
     *
     * @since   11.1
     */
	public function checkout($pk = null): bool
	{
		return true;
	}

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  array    The default data is an empty array.
     *
     * @throws Exception
     * @since   7.0
     */
    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_proclaim.edit.location.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
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
    protected function cleanCache($group = null, int $client_id = 0)
    {
        parent::cleanCache('com_proclaim');
        parent::cleanCache('mod_proclaim');
    }
}
