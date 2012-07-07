<?php

/**
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
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
     *
     * @param type $name
     * @param type $prefix
     * @return type
     */
    public function &getModel($name = 'mediafile', $prefix = 'biblestudyModel') {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }

    /**
     * Link to Docman Category Items
     */
    function docmanCategoryItems() {
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
    function articlesSectionCategories() {
        error_reporting(0);
        $secId = JRequest::getVar('secId');

        $model = & $this->getModel('mediafilesedit');
        $items = & $model->getArticlesSectionCategories($secId);
        echo $items;
    }

    /**
     * Link to Articals Category Items
     */
    function articlesCategoryItems() {
        error_reporting(0);
        $catId = JRequest::getVar('catId');

        $model = & $this->getModel('mediafilesedit');
        $items = & $model->getCategoryItems($catId);
        echo $items;
    }

    /**
     * Link to VertueMart Items
     */
    function virtueMartItems() {
        error_reporting(0);
        $catId = JRequest::getVar('catId');

        $model = & $this->getModel('mediafilesedit');
        $items = & $model->getVirtueMartItems($catId);
        echo $items;
    }

    /**
     * Reset Download count
     */
    function resetDownloads() {
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
    function resetPlays() {
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
