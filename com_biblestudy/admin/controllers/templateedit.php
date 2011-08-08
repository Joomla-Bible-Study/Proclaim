<?php

/**
 * @version     $Id: templateedit.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/
//No Direct Access
defined('_JEXEC') or die();

    jimport('joomla.application.component.controllerform');

    abstract class controllerClass extends JControllerForm {

    }

class biblestudyControllertemplateedit extends controllerClass {

    protected $view_list = 'templateslist';

    function __construct() {
        parent::__construct();

        //register extra tasks
    }

  
    function copy() {
        $cid = JRequest::getVar('cid', array(), 'post', 'array');
        JArrayHelper::toInteger($cid);

        $model = & $this->getModel('templateedit');

        if ($model->copy($cid)) {
            $msg = JText::_('JBS_TPL_TEMPLATE_COPIED');
        } else {
            $msg = $model->getError();
        }
        $this->setRedirect('index.php?option=com_biblestudy&view=templateslist', $msg);
    }

  
    function makeDefault() {
        $mainframe = & JFactory::getApplication();
        $cid = JRequest::getVar('cid', array(0), 'post', 'array');

        if (!is_array($cid) || count($cid) < 1) {
            JError::raiseError(500, JText::_('JBS_CMN_SELECT_ITEM_UNPUBLISH'));
        }

        $model = $this->getModel('templateedit');
        if (!$model->makeDefault($cid, 0)) {
            echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
        }

        $this->setRedirect('index.php?option=com_biblestudy&view=templateslist');
    }
  
}