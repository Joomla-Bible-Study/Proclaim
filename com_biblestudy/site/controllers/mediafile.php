<?php

/**
 * Controller MediaFile
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Controller class for MediaFile
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class biblestudyControllermediafile extends JControllerForm {
    /*
     * NOTE: This is needed to prevent Joomla 1.6's pluralization mechanisim from kicking in
     *
     * @todo    We should rename this controler to "mediafile" and the list view controller
     * to "mediafiles" so that the pluralization in 1.6 would work properly
     *
     * @since 7.0
     */

    /**
     * constructor (registers additional tasks to methods)
     * @return void
     */
    function __construct() {
        parent::__construct();
        // Register Extra tasks
        $this->registerTask('add', 'edit');
        $this->registerTask('upload', 'upload');
    }

    /**
     * Get Model
     * @param string $name
     * @param string $prefix
     * @return array
     */
    public function &getModel($name = 'mediafile', $prefix = 'biblestudyModel') {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }
/**
     * Method to edit an existing record.
     *
     * @param	string	$key	The name of the primary key of the URL variable.
     * @param	string	$urlVar	The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return	Boolean	True if access level check and checkout passes, false otherwise.
     * @since	1.6
     */
    public function edit($key = null, $urlVar = 'a_id') {
        $result = parent::edit($key, $urlVar);
        return $result;
    }
    /**
     * Link to Docman Category Items
     */
    public function docmanCategoryItems() {
        //hide errors and warnings
        error_reporting(0);
        $catId = JRequest::getVar('catId');

        $model = & $this->getModel('mediafilesedit');
        $items = & $model->getdocManCategoryItems($catId);
        echo $items;
    }

    /**
     * Link to Sections May need to be Removed.
     */
    public function articlesSectionCategories() {
        error_reporting(0);
        $secId = JRequest::getVar('secId');

        $model = & $this->getModel('mediafilesedit');
        $items = & $model->getArticlesSectionCategories($secId);
        echo $items;
    }

    /**
     * Link to Articals Category Items
     */
    public function articlesCategoryItems() {
        error_reporting(0);
        $catId = JRequest::getVar('catId');

        $model = & $this->getModel('mediafilesedit');
        $items = & $model->getCategoryItems($catId);
        echo $items;
    }

    /**
     * Link to VertueMart Items
     */
    public function virtueMartItems() {
        error_reporting(0);
        $catId = JRequest::getVar('catId');

        $model = & $this->getModel('mediafilesedit');
        $items = & $model->getVirtueMartItems($catId);
        echo $items;
    }

    /**
     * Reset Download count
     */
    public function resetDownloads() {
        $msg = null;
        $id = JRequest::getInt('id', 0, 'post');
        $db = JFactory::getDBO();
        $db->setQuery("UPDATE #__bsms_mediafiles SET downloads='0' WHERE id = " . $id);
        $reset = $db->query();
        if ($db->getErrorNum() > 0) {
            $error = $db->getErrorMsg();
            $msg = JText::_('JBS_CMN_ERROR_RESETTING_DOWNLOADS') . ' ' . $error;
            $this->setRedirect('index.php?option=com_biblestudy&view=mediafilesedit&controller=admin&layout=form&cid[]=' . $id, $msg);
        } else {
            $updated = $db->getAffectedRows();
            $msg = JText::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . JText::_('JBS_CMN_ROWS_RESET');
            $this->setRedirect('index.php?option=com_biblestudy&view=mediafilesedit&controller=studiesedit&layout=form&cid[]=' . $id, $msg);
        }
    }

    /**
     * Reset Play Count
     */
    public function resetPlays() {
        $msg = null;
        $id = JRequest::getInt('id', 0, 'post');
        $db = JFactory::getDBO();
        $db->setQuery("UPDATE #__bsms_mediafiles SET plays='0' WHERE id = " . $id);
        $reset = $db->query();
        if ($db->getErrorNum() > 0) {
            $error = $db->getErrorMsg();
            $msg = JText::_('JBS_CMN_ERROR_RESETTING_PLAYS') . ' ' . $error;
            $this->setRedirect('index.php?option=com_biblestudy&view=mediafilesedit&controller=admin&layout=form&cid[]=' . $id, $msg);
        } else {
            $updated = $db->getAffectedRows();
            $msg = JText::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . JText::_('JBS_CMN_ROWS_RESET');
            $this->setRedirect('index.php?option=com_biblestudy&view=mediafilesedit&controller=studiesedit&layout=form&cid[]=' . $id, $msg);
        }
    }

}
