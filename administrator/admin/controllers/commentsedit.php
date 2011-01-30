<?php

/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();


//Joomla 1.6 <-> 1.5 Branch
try {
    jimport('joomla.application.component.controllerform');

    abstract class controllerClass extends JControllerForm {

    }

} catch (Exception $e) {
    jimport('joomla.application.component.controller');

    abstract class controllerClass extends JController {

    }

}

class biblestudyControllercommentsedit extends controllerClass {

    protected $view_list = 'commentslist';
    /**
     * constructor (registers additional tasks to methods)
     * @return void
     */
    function __construct() {
        parent::__construct();

        // Register Extra tasks
        $this->registerTask('add', 'edit');
    }

    /**
     * display the edit form
     * @return void
     */
    function legacyEdit() {
        JRequest::setVar('view', 'commentsedit');
        JRequest::setVar('layout', 'form');
        JRequest::setVar('hidemainmenu', 1);

        parent::display();
    }

    /**
     * save a record (and redirect to main page)
     * @return void
     */
    function legacySave() {
        $model = $this->getModel('commentsedit');

        if ($model->store($post)) {
            $msg = JText::_('JBS_CMT_COMMENT_SAVED');
        } else {
            $msg = JText::_('JBS_CMT_ERROR_SAVING_COMMENT');
        }

        // Check the table in so it can be edited.... we are done with it anyway
        $link = 'index.php?option=com_biblestudy&view=commentslist';
        $this->setRedirect($link, $msg);
    }

    /**
     * remove record(s)
     * @return void
     */
    function legacyRemove() {
        $model = $this->getModel('commentsedit');
        if (!$model->delete()) {
            $msg = JText::_('JBS_CMT_ERROR_DELETING_ITEM');
        } else {
            $msg = JText::_('JBS_CMT_ITEMS_DELETED');
        }

        $this->setRedirect('index.php?option=com_biblestudy&view=commentslist', $msg);
    }

    function legacyPublish() {
        $mainframe = & JFactory::getApplication();

        $cid = JRequest::getVar('cid', array(0), 'post', 'array');

        if (!is_array($cid) || count($cid) < 1) {
            JError::raiseError(500, JText::_('JBS_CMN_SELECT_ITEM_PUBLISH'));
        }

        $model = $this->getModel('commentsedit');
        if (!$model->publish($cid, 1)) {
            echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
        }

        $this->setRedirect('index.php?option=com_biblestudy&view=commentslist');
    }

    function legacyUnpublish() {
        $mainframe = & JFactory::getApplication();

        $cid = JRequest::getVar('cid', array(0), 'post', 'array');

        if (!is_array($cid) || count($cid) < 1) {
            JError::raiseError(500, JText::_('JBS_CMN_SELECT_ITEM_UNPUBLISH'));
        }

        $model = $this->getModel('commentsedit');
        if (!$model->publish($cid, 0)) {
            echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
        }

        $this->setRedirect('index.php?option=com_biblestudy&view=commentslist');
    }

    /**
     * cancel editing a record
     * @return void
     */
    function legacyCancel() {
        $msg = JText::_('JBS_CMN_OPERATION_CANCELLED');
        $this->setRedirect('index.php?option=com_biblestudy&view=commentslist', $msg);
    }

}

?>