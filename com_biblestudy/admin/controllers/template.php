<?php

/**
 * @version     $Id: template.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

abstract class controllerClass extends JControllerForm {

}

class BiblestudyControllerTemplate extends controllerClass {

	protected $view_list = 'templates';

	function __construct() {
		parent::__construct();

		//register extra tasks
	}


	function copy() {
		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		$model = & $this->getModel('template');

		if ($model->copy($cid)) {
			$msg = JText::_('JBS_TPL_TEMPLATE_COPIED');
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect('index.php?option=com_biblestudy&view=templates', $msg);
	}


	function makeDefault() {
		$mainframe = & JFactory::getApplication();
		$cid = JRequest::getVar('cid', array(0), 'post', 'array');

		if (!is_array($cid) || count($cid) < 1) {
			JError::raiseError(500, JText::_('JBS_CMN_SELECT_ITEM_UNPUBLISH'));
		}

		$model = $this->getModel('template');
		if (!$model->makeDefault($cid, 0)) {
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect('index.php?option=com_biblestudy&view=templates');
	}

}