<?php

/**
 * Bible Study Templatecode table class
 * @since 7.1.0
 * @version $Id: style.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2012 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

class TableTemplatecode extends JTable {

    /**
     * Constructor
     *
     * @param object Database connector object
     */
    function __construct(&$db) {
        parent::__construct('#__bsms_templatecode', 'id', $db);
    }

    public function getItem($pk = null) {
        return parent::getItem($pk);
    }

    public function bind($array, $ignore = '') {

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
        return 'com_biblestudy.templatecode.' . (int) $this->$k;
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return      string
     * @since       1.6
     */
    protected function _getAssetTitle() {
        $title = 'JBS Templatecode ' . $this->filename;
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

    /**
     * Overriden JTable::store to set modified data and user id.
     *
     * @param	boolean	True to update fields even if they are null.
     * @return	boolean	True on success.
     * @since	1.6
     */
    public function store($updateNulls = false) {
        $table = JTable::getInstance('Templatecode', 'Table');
        if ($this->filename == 'main' || $this->filename == 'custom' || $this->filename == 'formheader' || $this->filename == 'formfooter') {
            $this->setError(JText::_('JBS_STYLE_RESTRICED_FILE_NAME'));
            return false;
        }

        //write the css file
        jimport('joomla.client.helper');
        jimport('joomla.filesystem.file');
        $templatetype = $this->type;
        $filename = 'default_' . $this->filename . '.php';
        switch ($templatetype) {
            case 1:
                //sermons
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'sermons' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . $filename;
                break;
            case 2:
                //sermon
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'sermon' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . $filename;
                break;
            case 3:
                //teachers
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'teachers' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . $filename;
                break;
            case 4:
                //teacher
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'teacher' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . $filename;
                break;
            case 5:
                //seriesdisplays
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'seriesdisplays' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . $filename;
                break;
            case 6:
                //seriesdisplay
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'seriesdisplay' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . $filename;
                break;
            case 7:
                //seriesdisplay
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'mod_biblestudy' . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . $filename;
                break;
        }
        JClientHelper::setCredentialsFromRequest('ftp');
        $ftp = JClientHelper::getCredentials('ftp');

        $filecontent = $this->templatecode;
        //Check to see if there is the required code in the file
        $requiredtext = "defined('_JEXEC') or die;";
        $required = substr_count($filecontent, $requiredtext);
        if (!$required) {
            $filecontent = $requiredtext . $filecotent;
        }
        if (!$return = JFile::write($file, $filecontent)) {
            $this->setError(JText::_('JBS_STYLE_FILENAME_NOT_UNIQUE'));
            return false;
        }


        return parent::store($updateNulls);
    }

    public function delete($pk = null) {
        jimport('joomla.client.helper');
        jimport('joomla.filesystem.file');
        JClientHelper::setCredentialsFromRequest('ftp');
        $ftp = JClientHelper::getCredentials('ftp');
        $filename = $this->filename . '.php';
        $templatetype = $this->type;
        switch ($templatetype) {
            case 1:
                //sermons
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'sermons' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . $filename;
                break;
            case 2:
                //sermon
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'sermon' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . $filename;
                break;
            case 3:
                //teachers
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'teachers' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . $filename;
                break;
            case 4:
                //teacher
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'teacher' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . $filename;
                break;
            case 5:
                //seriesdisplays
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'seriesdisplays' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . $filename;
                break;
            case 6:
                //seriesdisplay
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'seriesdisplay' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . $filename;
                break;
            case 7:
                //seriesdisplay
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'mod_biblestudy' . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . $filename;
                break;
        }

        if (!$delete = JFile::delete($file)) {
            $this->setError(JText::_('JBS_STYLE_FILENAME_NOT_DELETED'));
            return false;
        }


        return parent::delete($pk);
    }

}