<?php

/**
 * @version     $Id$
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/
//No Direct Access
defined('_JEXEC') or die();
require_once (JPATH_ADMINISTRATOR  .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_biblestudy' .DIRECTORY_SEPARATOR. 'lib' .DIRECTORY_SEPARATOR. 'biblestudy.defines.php');
require_once (JPATH_ADMINISTRATOR  .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_biblestudy' .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'biblestudy.php');
jimport('joomla.application.component.view');

class biblestudyViewmediafilesedit extends JView {

	protected $form;
	protected $item;
	protected $state;
	protected $admin;

	function display($tpl = null) {
		$this->form = $this->get("Form");
		$this->item = $this->get("Item");
		$this->state = $this->get("State");
		$this->canDo = BibleStudyHelper::getActions($this->item->id, 'mediafilesedit');
		//Load the Admin settings
		$this->loadHelper('params');
		$this->admin = BsmHelper::getAdmin();

		//Needed to load the article field type for the article selector
		JFormHelper::addFieldPath(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_content'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'fields'.DIRECTORY_SEPARATOR.'modal');

		$this->setLayout('form');

		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() {
		$isNew = ($this->item->id < 1);
		$title = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
		JToolBarHelper::title(JText::_('JBS_CMN_MEDIA_FILES') . ': <small><small>[' . $title . ']</small></small>', 'mp3.png');

		if ($this->canDo->get('core.edit','com_biblestudy'))
		{
			JToolBarHelper::save('mediafilesedit.save');
			JToolBarHelper::apply('mediafilesedit.apply');
		}
		JToolBarHelper::cancel('mediafilesedit.cancel', 'JTOOLBAR_CANCEL');
		if ($this->canDo->get('core.edit','com_biblestudy') && !$isNew)
		{
			JToolBarHelper::divider();
			JToolBarHelper::custom('resetDownloads', 'download.png', 'Reset Download Hits', 'JBS_MED_RESET_DOWNLOAD_HITS', false, false);
			JToolBarHelper::custom('resetPlays', 'play.png', 'Reset Plays', 'JBS_MED_RESET_PLAYS', false, false);
		}

		// Add an upload button and view a popup screen width 550 and height 400
		JToolBarHelper::divider();
		JToolBarHelper::media_manager();
		JToolBarHelper::divider();
		JToolBarHelper::help('biblestudy', true);
	}

}