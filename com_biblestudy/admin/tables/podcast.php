<?php

/**
 * Podcast Tables for BibleStudy
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

/**
 * Podcast table class
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class TablePodcast extends JTable {

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
     * Title
     * @var string
     */
    var $title = null;

    /**
     * Website Address
     * @var string
     */
    var $website = null;

    /**
     * Description
     * @var string
     */
    var $description = null;

    /**
     * Image
     * @var string
     */
    var $image = null;

    /**
     * Image Hight
     * @var int
     */
    var $imageh = null;

    /**
     * Image Width
     * @var int
     */
    var $imagew = null;

    /**
     * Auther
     * @var string
     */
    var $author = null;

    /**
     * Podcast Image
     * @var string
     */
    var $podcastimage = null;

    /**
     * Podcast Summary
     * @var string
     */
    var $podcastsummary = null;

    /**
     * Podcast Sub Title
     * @var string
     */
    var $podcastsubtitle = null;

    /**
     * Podcast Search Words
     * @var string
     */
    var $podcastsearch = null;

    /**
     * File Name
     * @var string
     */
    var $filename = null;

    /**
     * Language of Podcast
     * @var string
     */
    var $language = null;

    /**
     * Podcast name
     * @var string
     */
    var $podcastname = null;

    /**
     * Editor Name
     * @var string
     */
    var $editor_name = null;

    /**
     * Editor Email Address
     * @var string
     */
    var $editor_email = null;

    /**
     * Limit of the episodes in the podcast
     * @var string
     */
    var $podcastlimit = null;

    /**
     * Episode Title
     * @var string
     */
    var $episodetitle = null;

    /**
     * Custom
     * @var string
     */
    var $custom = null;

    /**
     * Deatils template ID
     * @var string
     */
    var $detailstemplateid = null;

    /**
     * Constructor
     *
     * @param object Database connector object
     */
    function Tablepodcast(& $db) {
        parent::__construct('#__bsms_podcast', 'id', $db);
    }

    /**
     * Method to bind an associative array or object to the JTable instance.This
     * method only binds properties that are publicly accessible and optionally
     * takes an array of properties to ignore when binding.
     *
     * @param   mixed  $array   An associative array or object to bind to the JTable instance.
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
        return 'com_biblestudy.podcast.' . (int) $this->$k;
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return      string
     * @since       1.6
     */
    protected function _getAssetTitle() {
        $title = 'JBS Podcast: ' . $this->title;
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