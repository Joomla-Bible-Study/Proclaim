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
     * @var string
     */
    var $podcast = null;
    var $series = null;
    var $study = null;
    var $teacher = null;
    var $media = null;
    var $params = null;
    var $download = null;
    var $main = null;
    var $showhide = null;
    var $drop_tables = null;

    public function bind($array, $ignore = '') {
        if (isset($array['params']) && is_array($array['params'])) {
            // Convert the params field to a string.
            $parameter = new JRegistry;
            $parameter->loadArray($array['params']);
            $array['params'] = (string) $parameter;
        }

        return parent::bind($array, $ignore);
    }

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
    function TableAdmin(& $db) {
        parent::__construct('#__bsms_admin', 'id', $db);
    }

    /**
     * Overload the store method for the Weblinks table.
     *
     * @param	boolean	Toggle whether null values should be updated.
     * @return	boolean	True on success, false on failure.
     * @since	1.6
     */
    public function store($updateNulls = false) {
        if (!$this->id) {
            return false;
        }
        return parent::store($updateNulls);
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
     * Get the parent asset id for the record
     *
     * @return      int
     * @since       1.6
     */
    protected function _getAssetParentId($table = null, $id = null) {
        $asset = JTable::getInstance('Asset');
        $asset->loadByName('com_biblestudy');
        return $asset->id;
    }

}