<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Table;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;
use Joomla\CMS\Access\Rule;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Table\Table;

/**
 * TemplateCode table class
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 */
class CwmtemplatecodeTable extends Table
{
    /**
     * File Name
     *
     * @var string
     *
     * @since 9.0.0
     */
    public $filename;

    /**
     * Type
     *
     * @var string
     *
     * @since 9.0.0
     */
    public $type;

    /**
     * Template Code
     *
     * @var string
     *
     * @since 9.0.0
     */
    public $templatecode;

    /**
     * Constructor
     *
     * @param     $db  \JDatabaseDriver connector object
     *
     * @since 9.0.0
     */
    public function __construct(&$db)
    {
        parent::__construct('#__bsms_templatecode', 'id', $db);
    }

    /**
     * Method to bind an associative array or object to the Table instance.This
     * method only binds properties that are publicly accessible and optionally
     * takes an array of properties to ignore when binding.
     *
     * @param   mixed  $array   An associative array or object to bind to the Table instance.
     * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
     *
     * @return  boolean  True on success.
     *
     * @link    http://docs.joomla.org/Table/bind
     * @since   11.1
     */
    public function bind($array, $ignore = '')
    {
        // Bind the rules.
        if (isset($array['rules']) && is_array($array['rules'])) {
            $rules = new Rule($array['rules']);
            $this->setRules($rules);
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Overriden Table::store to set modified data and user id.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return    boolean    True on success.
     *
     * @since    1.6
     */
    public function store($updateNulls = false)
    {
        if (
            $this->filename === 'main'
            || $this->filename === 'simple'
            || $this->filename === 'custom'
            || $this->filename === 'formheader'
            || $this->filename === 'formfooter'
        ) {
            Factory::getApplication()->enqueueMessage('JBS_STYLE_RESTRICED_FILE_NAME', 'error');

            return false;
        }

        // Write the file
        jimport('joomla.client.helper');
        jimport('joomla.filesystem.file');
        $templatetype = $this->type;
        $filename     = 'default_' . $this->filename . '.php';
        $file         = null;

        switch ($templatetype) {
            case 1:
                // Sermons
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_proclaim/tmpl/Cwmsermons' . DIRECTORY_SEPARATOR . $filename;
                break;
            case 2:
                // Sermon
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_proclaim/tmpl/Cwmsermon' . DIRECTORY_SEPARATOR . $filename;
                break;
            case 3:
                // Teachers
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_proclaim/tmpl/Cwmteachers' . DIRECTORY_SEPARATOR . $filename;
                break;
            case 4:
                // Teacher
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_proclaim/tmpl/Cwmteacher' . DIRECTORY_SEPARATOR . $filename;
                break;
            case 5:
                // Seriesdisplays
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_proclaim/tmpl/Cwmseriesdisplays' . DIRECTORY_SEPARATOR . $filename;
                break;
            case 6:
                // Seriesdisplay
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_proclaim/tmpl/Cwmseriesdisplay' . DIRECTORY_SEPARATOR . $filename;
                break;
            case 7:
                // Module's Display
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'modules/mod_proclaim/tmpl' . DIRECTORY_SEPARATOR . $filename;
                break;
        }

        $filecontent = $this->templatecode;

        // Check to see if there is the required code in the file
        $requiredtext = "defined('_JEXEC') or die;";
        $required     = substr_count($filecontent, $requiredtext);

        if (!$required) {
            $filecontent = $requiredtext . $filecontent;
        }

        if (!$return = File::write($file, $filecontent)) {
            Factory::getApplication()->enqueueMessage('JBS_STYLE_FILENAME_NOT_UNIQUE', 'error');

            return false;
        }

        if (!$this->_rules) {
            $this->setRules(
                '{"core.delete":[],"core.edit":[],"core.create":[],"core.edit.state":[],"core.edit.own":[]}'
            );
        }

        return parent::store($updateNulls);
    }

    /**
     * Method to delete a row from the database table by primary key value.
     *
     * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
     *
     * @return  boolean  True on success.
     *
     * @link    http://docs.joomla.org/Table/delete
     * @since   11.1
     */
    public function delete($pk = null)
    {
        $filename     = 'default_' . $this->filename . '.php';
        $templatetype = $this->type;
        $file         = null;

        switch ($templatetype) {
            case 1:
                // Sermons
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_proclaim/tmpl/Cwmsermons' . DIRECTORY_SEPARATOR . $filename;
                break;
            case 2:
                // Sermon
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_proclaim/tmpl/Cwmsermon' . DIRECTORY_SEPARATOR . $filename;
                break;
            case 3:
                // Teachers
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_proclaim/tmpl/Cwmteachers' . DIRECTORY_SEPARATOR . $filename;
                break;
            case 4:
                // Teacher
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_proclaim/tmpl/Cwmteacher' . DIRECTORY_SEPARATOR . $filename;
                break;
            case 5:
                // Seriesdisplays
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_proclaim/tmpl/Cwmseriesdisplays' . DIRECTORY_SEPARATOR . $filename;
                break;
            case 6:
                // Seriesdisplay
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_proclaim/tmpl/Cwmseriesdisplay' . DIRECTORY_SEPARATOR . $filename;
                break;
            case 7:
                // Module's Display
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'modules/mod_proclaim/tmpl' . DIRECTORY_SEPARATOR . $filename;
                break;
        }

        if (File::exists($file)) {
            if (!File::delete($file)) {
                Factory::getApplication()->enqueueMessage('JBS_STYLE_FILENAME_NOT_DELETED', 'error');

                return false;
            }
        }

        return parent::delete($pk);
    }

    /**
     * Method to compute the default name of the asset.
     * The default name is in the form `table_name.id`
     * where id is the value of the primary key of the table.
     *
     * @return  string
     *
     * @since       1.6
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;

        return 'com_proclaim.templatecode.' . (int)$this->$k;
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return      string
     *
     * @since       1.6
     */
    protected function _getAssetTitle()
    {
        return 'JBS Templatecode ' . $this->filename;
    }

    /**
     * Method to get the parent asset under which to register this one.
     * By default, all assets are registered to the ROOT node with ID 1.
     * The extended class can define a table and id to lookup.  If the
     * asset does not exist it will be created.
     *
     * @param   ?Table  $table  A Table object for the asset parent.
     * @param   null    $id     Id to look up
     *
     * @return  int
     *
     * @since   11.1
     */
    protected function _getAssetParentId(?Table $table = null, $id = null): int
    {
        // Get Proclaim Root ID
        return Cwmassets::parentId();
    }
}
