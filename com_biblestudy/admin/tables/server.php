<?php

/**
 * Servers Tables for BibleStudy
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
/**
 * Table class for Server
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class TableServer extends JTable {

    /**
     * Primary Key
     *
     * @var int
     */
    var $id = null;

    /**
     * Published
     * @var int
     */
    var $published = 1;

    /**
     * Server Name
     * @var string
     */
    var $server_name = null;

    /**
     * Server Path
     * @var string
     */
    var $server_path = null;

    /**
     * Server Type
     * @var string
     */
    var $server_type = null;

    /**
     * Ftp User Name
     * @var string
     */
    var $ftp_username = null;

    /**
     * FTP Password
     * @var string
     */
    var $ftp_password = null;

    /**
     * Rules
     * @var string
     */
    var $rules = null;

    /**
     * Asset ID
     * @var int
     */
    var $assset_id = null;

    /**
     * Constructor
     *
     * @param object Database connector object
     */
    public function Tableserver(& $db) {
        parent::__construct('#__bsms_servers', 'id', $db);
    }

    /**
     * Method to bind an associative array or object to the JTable instance.This
     * method only binds properties that are publicly accessible and optionally
     * takes an array of properties to ignore when binding.
     *
     * @param   mixed  $array     An associative array or object to bind to the JTable instance.
     * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
     *
     * @return  boolean  True on success.
     *
     * @link    http://docs.joomla.org/JTable/bind
     * @since   11.1
     */
    public function bind($array, $ignore = '') {
        if (isset($array['params']) && is_array($array['params'])) {
            $registry = new JRegistry();
            $registry->loadArray($array['params']);
            $array['params'] = (string) $registry;
        }


        // Bind the rules.
        if (isset($array['rules']) && is_array($array['rules'])) {
            $rules = new JRules($array['rules']);
            $this->setRules($rules);
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Method to compute the default name of the asset.
     * The default name is in the form `table_name.id`
     * where id is the value of the primary key of the table.
     *
     * @return      string
     * @since       1.6
     */
    protected function _getAssetName() {
        $k = $this->_tbl_key;
        return 'com_biblestudy.server.' . (int) $this->$k;
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return      string
     * @since       1.6
     */
    protected function _getAssetTitle() {
        return 'JBS Server: ' . $this->server_name;
    }

    /**
     * Method to get the parent asset under which to register this one.
     * By default, all assets are registered to the ROOT node with ID 1.
     * The extended class can define a table and id to lookup.  If the
     * asset does not exist it will be created.
     *
     * @param   JTable   $table  A JTable object for the asset parent.
     * @param   integer  $id     Id to look up
     *
     * @return  integer
     *
     * @since   11.1
     */
    protected function _getAssetParentId($table = null, $id = null) {
        $asset = JTable::getInstance('Asset');
        $asset->loadByName('com_biblestudy');
        return $asset->id;
    }

}