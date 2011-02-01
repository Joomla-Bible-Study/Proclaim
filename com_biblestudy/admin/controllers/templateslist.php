<?php

/**
 * @version     $Id: templateslist.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

    jimport('joomla.application.component.controlleradmin');

    abstract class controllerClass extends JControllerAdmin {

    }

class BiblestudyControllertemplateslist extends controllerClass {

    function __construct() {
        parent::__construct();

        //register extra tasks
        $this->registerTask('add', 'edit');
    }

    function edit() {
        JRequest::setVar('view', 'templateedit');
        JRequest::setVar('layout', 'form');
        JRequest::setVar('hidemenu', 1);

        parent::display();
    }

    function save() {
        $model = $this->getModel('templateedit');
        $data = JRequest::get('post');
        if ($model->store($post)) {
            $msg = JText::_('JBS_TPL_TEMPLATE_SAVED');
        } else {
            $msg = JText::_('JBS_TPL_ERROR_SAVING_TEMPLATE');
        }

        // Check the table in so it can be edited.... we are done with it anyway
        $link = 'index.php?option=com_biblestudy&view=templateslist';
        $this->setRedirect($link, $msg);
    }

    function cancel() {
        $msg = JText::_('JBS_CMN_OPERATION_CANCELLED');
        $this->setRedirect('index.php?option=com_biblestudy&view=templateslist', $msg);
    }

    /**
     * Proxy for getModel
     *
     * @param <String> $name    The name of the model
     * @param <String> $prefix  The prefix for the PHP class name
     * @return JModel
     *
     * @since 7.0
     */
    public function &getModel($name = 'templateedit', $prefix = 'biblestudyModel') {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }

}
?>

