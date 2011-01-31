<?php

/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

    jimport('joomla.application.component.controllerform');

    abstract class controllerClass extends JControllerForm {

    }

class biblestudyControllerteacheredit extends controllerClass {

    protected $view_list = 'teacherlist';

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
        JRequest::setVar('view', 'teacheredit');
        JRequest::setVar('layout', 'form');
        JRequest::setVar('hidemainmenu', 1);

        parent::display();
    }

    /**
     * save a record (and redirect to main page)
     * @return void
     */
    function legacySave() {
        $model = $this->getModel('teacheredit');

        if ($model->store($post)) {
            $msg = JText::_('JBS_TCH_TEACHER_SAVED');
        } else {
            $msg = JText::_('JBS_TCH_ERROR_SAVING_TEACHER');
        }

        // Check the table in so it can be edited.... we are done with it anyway
        $link = 'index.php?option=com_biblestudy&view=teacherlist';
        $this->setRedirect($link, $msg);
    }

    /**
     * apply a record
     * @return void
     */
    function legacyApply() {
        $model = $this->getModel('teacheredit');
        $cid = JRequest::getVar('id', 1, 'post', 'int');
        if ($model->store($post)) {
            $msg = JText::_('JBS_TCH_TEACHER_SAVED');
        } else {
            $msg = JText::_('JBS_TCH_ERROR_SAVING_TEACHER');
        }

        // Check the table in so it can be edited.... we are done with it anyway
        $link = 'index.php?option=com_biblestudy&controller=teacheredit&task=edit&cid[]=' . $cid . '';
        $this->setRedirect($link, $msg);
    }

    /**
     * remove record(s)
     * @return void
     */
    function legacyRemove() {
        $model = $this->getModel('teacheredit');
        if (!$model->delete()) {
            $msg = JText::_('JBS_TCH_ERROR_DELETING_TEACHER');
        } else {
            $msg = JText::_('JBS_TCH_TEACHER_DELETED');
        }

        $this->setRedirect('index.php?option=com_biblestudy&view=teacherlist', $msg);
    }

    function legacyPublish() {
        $mainframe = & JFactory::getApplication();

        $cid = JRequest::getVar('cid', array(0), 'post', 'array');

        if (!is_array($cid) || count($cid) < 1) {
            JError::raiseError(500, JText::_('JBS_CMN_SELECT_ITEM_PUBLISH'));
        }

        $model = $this->getModel('teacheredit');
        if (!$model->publish($cid, 1)) {
            echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
        }

        $this->setRedirect('index.php?option=com_biblestudy&view=teacherlist');
    }

    function legacyUnpublish() {
        $mainframe = & JFactory::getApplication();

        $cid = JRequest::getVar('cid', array(0), 'post', 'array');

        if (!is_array($cid) || count($cid) < 1) {
            JError::raiseError(500, JText::_('JBS_CMN_SELECT_ITEM_UNPUBLISH'));
        }

        $model = $this->getModel('teacheredit');
        if (!$model->publish($cid, 0)) {
            echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
        }

        $this->setRedirect('index.php?option=com_biblestudy&view=teacherlist');
    }

    /**
     * cancel editing a record
     * @return void
     */
    function legacyCancel() {
        $msg = JText::_('JBS_CMN_OPERATION_CANCELLED');
        $this->setRedirect('index.php?option=com_biblestudy&view=teacherlist', $msg);
    }

}

?>