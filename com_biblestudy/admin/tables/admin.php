<?php

/**
 * Locations Tables for BibleStudy
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

/**
 * Admin table class
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class TableAdmin extends JTable {

    /**
     * Primary Key
     *
     * @var int
     */
    var $id = null;

    /**
     * Podcast
     * @var string
     */
    var $podcast = null;

    /**
     * Series
     * @var string
     */
    var $series = null;

    /**
     * Study
     * @var string
     */
    var $study = null;

    /**
     * Teacher
     * @var string
     */
    var $teacher = null;

    /**
     * Media
     * @var string
     */
    var $media = null;

    /**
     * Params
     * @var string
     */
    var $params = null;

    /**
     * Download
     * @var string
     */
    var $download = null;

    /**
     * Main
     * @var string
     */
    var $main = null;

    /**
     * ShowHide
     * @var string
     */
    var $showhide = null;

    /**
     * Drop Tables
     * @var string
     */
    var $drop_tables = null;

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
            // Convert the params field to a string.
            $parameter = new JRegistry;
            $parameter->loadArray($array['params']);
            $array['params'] = (string) $parameter;
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Method to load a row from the database by primary key and bind the fields
     * to the JTable instance properties.
     *
     * @param   mixed    $pk
     * @param   boolean  $reset  True to reset the default values before loading the new row.
     *
     * @return  boolean  True if successful. False if row not found or on error (internal error state set in that case).
     *
     * @link    http://docs.joomla.org/JTable/load
     * @since   11.1
     */
    public function load($pk = null, $reset = true) {
        if (parent::load($pk, $reset)) {
            // Convert the params field to a registry.
            $params = new JRegistry;
            $params->loadJSON($this->params);
            $this->params = $params;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Constructor
     *
     * @param object Database connector object
     */
    public function TableAdmin(& $db) {
        parent::__construct('#__bsms_admin', 'id', $db);
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
        return 'com_biblestudy.admin.' . (int) $this->$k;
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return      string
     * @since       1.6
     */
    protected function _getAssetTitle() {
        $title = 'JBS Admin: - ' . $this->id;
        return $title;
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