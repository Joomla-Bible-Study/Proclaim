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
use JAccessRules;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;

/**
 * Admin table class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmadminTable extends Table
{
    /**
     * Primary Key
     *
     * @var int
     * @since    7.0.0
     */
    public int $id = 0;

    /**
     * Drop Tables
     *
     * @var int
     * @since    7.0.0
     */
    public int $drop_tables = 0;

    /**
     * Params
     *
     * @var ?string
     * @since    7.0.0
     */
    public ?string $params = null;

    /**
     * Asset ID
     *
     * @var int
     * @since    7.0.0
     */
    public $asset_id = 0;

    /**
     * Access Level
     *
     * @var int
     * @since    7.0.0
     */
    public $access = 0;

    /**
     * Install State
     *
     * @var ?string
     * @since    7.0.0
     */
    public ?string $installstate = null;

    /**
     * Debug settings
     *
     * @var ?int
     * @since    7.0.0
     */
    public ?int $debug = null;

    /**
     * Constructor
     *
     * @param   DatabaseDriver  $db  Database connector object
     *
     * @since    7.0.0
     */
    public function __construct(&$db)
    {
        parent::__construct('#__bsms_admin', 'id', $db);
    }

    /**
     * Method to bind an associative array or object to the Table instance.This
     * method only binds properties that are publicly accessible and optionally
     * takes an array of properties to ignore when binding.
     *
     * @param   mixed  $array   An associative array or object to bind to the Table instance.
     * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
     *
     * @return  bool  True on success.
     *
     * @link    http://docs.joomla.org/Table/bind
     * @since   11.1
     */
    public function bind($array, $ignore = ''): bool
    {
        $params = [];

        // For Saving the page.
        if (isset($array['params']) && is_array($array['params'])) {
            $registry = new Registry();
            $registry->loadArray($array['params']);
            $array['params'] = (string)$registry;
        }

        // For loading the administrator page
        if (isset($array['params']) && is_string($array['params'])) {
            // Convert the params field to a string.
            $parameter = new Registry();
            $parameter->loadString($array['params']);
            $params = $parameter->toArray();
        }

        // If simple mode, check and rename some files to hide menus
        $views   = array();
        $views[] = 'landingpage';
        $views[] = 'podcastdisplay';
        $views[] = 'podcastlist';
        $views[] = 'seriesdisplay';
        $views[] = 'seriesdisplays';
        $views[] = 'sermon';
        $views[] = 'teacher';
        $views[] = 'teachers';

        if ($params['simple_mode'] === 1) {
            // Go through each folder and change content of default.xml to add the hidden value to the layout tag
            foreach ($views as $view) {
                $filecontents = file_get_contents(
                    JPATH_ROOT . DIRECTORY_SEPARATOR .
                    'components/com_proclaim/views/' . $view . '/tmpl/default.xml'
                );

                if (!substr_count($filecontents, '<layout hidden=\"true\" ')) {
                    $filecontents = str_replace('<layout ', '<layout hidden=\"true\" ', $filecontents);
                    file_put_contents(
                        JPATH_ROOT . DIRECTORY_SEPARATOR .
                        'components/com_proclaim/views/' . $view . '/tmpl/default.xml',
                        $filecontents
                    );
                }
            }
        }

        // Remove the hidden value from the layout tag
        if ($params['simple_mode'] === 0) {
            foreach ($views as $view) {
                $filecontents = file_get_contents(
                    JPATH_ROOT . DIRECTORY_SEPARATOR .
                    'components/com_proclaim/views/' . $view . '/tmpl/default.xml'
                );
                $filecontents = str_replace('hidden=\"true \" ', '', $filecontents);
                file_put_contents(
                    JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_proclaim/views/' . $view . '/tmpl/default.xml',
                    $filecontents
                );
            }
        }

        // Bind the rules.
        if (isset($array['rules']) && is_array($array['rules'])) {
            $rules = new JAccessRules($array['rules']);
            $this->setRules($rules);
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Method to store a row in the database from the Table instance properties.
     * If a primary key value is set the row with that primary key value will be
     * updated with the instance property values.  If no primary key value is set
     * a new row will be inserted into the database with the properties from the
     * Table instance.
     *
     * @param   bool  $updateNulls  True to update fields even if they are null.
     *
     * @return  bool  True on success.
     *
     * @link    https://docs.joomla.org/Table/store
     * @since   11.1
     */
    public function store($updateNulls = false): bool
    {
        if (!$this->_rules) {
            $this->setRules(
                '{"core.delete":[],"core.edit":[],"core.create":[],"core.edit.state":[],"core.edit.own":[]}'
            );
        }

        return parent::store($updateNulls);
    }

    /**
     * Method to compute the default name of the asset.
     * The default name is in the form `table_name.id`
     * where id is the value of the primary key of the table.
     *
     * @return  string
     *
     * @since   1.6
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;

        return 'com_proclaim.cwmadmin.' . (int)$this->$k;
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return  string
     *
     * @since   1.6
     */
    protected function _getAssetTitle()
    {
        return 'JBS Admin: ' . $this->id;
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
