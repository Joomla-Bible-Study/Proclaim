<?php

/**
 * @version     $Id: view.html.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ADMINISTRATOR  .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_biblestudy' .DIRECTORY_SEPARATOR. 'lib' .DIRECTORY_SEPARATOR. 'biblestudy.defines.php');
jimport('joomla.application.component.view');

class BiblestudyViewTeacher extends JView {

	protected $form;
	protected $item;
	protected $state;
	protected $admin;

	function display($tpl = null) {
		$this->form = $this->get("Form");
		$this->item = $this->get("Item");
		$this->state = $this->get("State");
		$this->canDo	= BibleStudyHelper::getActions($this->item->id, 'teacher');
		//Load the Admin settings
		$this->loadHelper('params');
		$this->admin = BsmHelper::getAdmin();

		$this->setLayout("form");
		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() {
                JRequest::setVar('hidemainmenu', true);
		$isNew = ($this->item->id < 1);
		$title = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
		JToolBarHelper::title(JText::_('JBS_CMN_TEACHERS') . ': <small><small>[' . $title . ']</small></small>', 'teachers.png');

		if ($this->canDo->get('core.edit','com_biblestudy'))
		{
			JToolBarHelper::save('teacher.save');
			JToolBarHelper::apply('teacher.apply');
		}
		JToolBarHelper::cancel('teacher.cancel', 'JTOOLBAR_CANCEL');

		JToolBarHelper::divider();
		JToolBarHelper::help('biblestudy', true);
	}

}