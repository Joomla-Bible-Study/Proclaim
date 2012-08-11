<?php

/**
 * Comment JTable
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

/**
 * Table class for Comment
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class TableComment extends JTable {

    /**
     * Primary Key
     *
     * @var int
     */
    var $id = null;

    /**
     * Study ID
     * @var string
     */
    var $study_id = null;

    /**
     * User ID
     * @var string
     */
    var $user_id = null;

    /**
     * Comment Date
     * @var string
     */
    var $comment_date = null;

    /**
     * Full Name
     * @var string
     */
    var $full_name = null;

    /**
     * Published
     * @var string
     */
    var $published = 1;

    /**
     * Comment Text
     * @var string
     */
    var $comment_text = null;

    /**
     * User Email
     * @var string
     */
    var $user_email = null;

    /**
     * Constructor
     *
     * @param object Database connector object
     */
    function TableComment(& $db) {
        parent::__construct('#__bsms_comments', 'id', $db);
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
        return 'com_biblestudy.comment.' . (int) $this->$k;
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return      string
     * @since       1.6
     */
    protected function _getAssetTitle() {
        $title = 'JBS Comment - id/date: ' . $this->user_id . ' - ' . $this->comment_date;
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
     * @since       1.6
     */
    protected function _getAssetParentId($table = null, $id = null) {
        $asset = JTable::getInstance('Asset');
        $asset->loadByName('com_biblestudy');
        return $asset->id;
    }

}