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
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;

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
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $filename = null;

    /**
     * Type
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $type = null;

    /**
     * Template Code
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $templatecode = null;

    /**
     * Constructor
     *
     * @param     $db  DatabaseInterface connector object
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
     * @param   array|object  $src     An associative array or object to bind to the Table instance.
     * @param   array|string  $ignore  An optional array or space separated list of properties to ignore while binding.
     *
     * @return  bool  True on success.
     *
     * @link    http://docs.joomla.org/Table/bind
     * @since   11.1
     */
    public function bind($src, $ignore = ''): bool
    {
        // Bind the rules.
        if (isset($src['rules']) && is_array($src['rules'])) {
            $rules = new Rule($src['rules']);
            $this->setRules($rules);
        }

        return parent::bind($src, $ignore);
    }

    /**
     * Overridden Table::store to set modified data and user id.
     *
     * @param   bool  $updateNulls  True to update fields even if they are null.
     *
     * @return  bool  True on success.
     *
     * @throws \Exception
     * @since    1.6
     */
    public function store($updateNulls = false): bool
    {
        if (
            $this->filename === 'main' ||
            $this->filename === 'simple' ||
            $this->filename === 'custom' ||
            $this->filename === 'formheader' ||
            $this->filename === 'formfooter'
        ) {
            Factory::getApplication()->enqueueMessage('JBS_STYLE_RESTRICTED_FILE_NAME', 'error');

            return false;
        }

        // Write the file
        $templateType = $this->type;
        $filename     = 'default_' . $this->filename . '.php';

        switch ($templateType) {
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
            default:
                $file = null;
                break;
        }

        $templateCodeContent = $this->templatecode;

        // Check to see if there is the required code in the file
        $templateCheckString = "defined('_JEXEC') or die;";
        $required            = substr_count($templateCodeContent, $templateCheckString);

        if (!$required) {
            $templateCodeContent = $templateCheckString . $templateCodeContent;
        }

        if (!File::write($file, $templateCodeContent)) {
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
     * @return  bool  True on success.
     *
     * @throws \Exception
     * @since   11.1
     * @link    http://docs.joomla.org/Table/delete
     */
    public function delete($pk = null): bool
    {
        $filename     = 'default_' . $this->filename . '.php';
        $templateType = $this->type;

        switch ($templateType) {
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
            default:
                $file = null;
                break;
        }

        if (file_exists($file) && !File::delete($file)) {
            Factory::getApplication()->enqueueMessage('JBS_STYLE_FILENAME_NOT_DELETED', 'error');

            return false;
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
        // Get to Proclaim Root ID
        return Cwmassets::parentId();
    }
}
