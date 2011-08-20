<?php

/**
 * @version $Id: view.html.php 1 $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/
//No Direct Access
defined('_JEXEC') or die();
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'helpers' .DS. 'biblestudy.php');
jimport('joomla.application.component.view');

class biblestudyViewLocationsedit extends JView {

	protected $form;
	protected $item;
	protected $state;
	protected $defaults;

	function display($tpl = null) {
		$this->form = $this->get("Form");
		$this->item = $this->get("Item");
		$this->state = $this->get("State");
		$this->canDo	= BibleStudyHelper::getActions($this->item->id, 'locationsedit');
		if (!JFactory::getUser()->authorize('core.manage','com_biblestudy'))
		{
			JError::raiseError(404,JText::_('JBS_CMN_NOT_AUTHORIZED'));
			return false;
		}
		$this->setLayout("form");
		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() {
		$isNew = ($this->item->id < 1);
		$title = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
		JToolBarHelper::title(JText::_('JBS_CMN_LOCATIONS') . ': <small><small>[' . $title . ']</small></small>', 'locations.png');

		if ($this->canDo->get('core.edit','com_biblestudy'))
		{
			JToolBarHelper::save('locationsedit.save');
			JToolBarHelper::apply('locationsedit.apply');
		}
		JToolBarHelper::cancel('locationsedit.cancel', 'JTOOLBAR_CANCEL');

		JToolBarHelper::divider();
		JToolBarHelper::help('biblestudy', true);
	}

}