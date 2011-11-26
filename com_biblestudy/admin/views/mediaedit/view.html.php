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
jimport( 'joomla.application.component.view' );


class biblestudyViewmediaedit extends JView
{

	protected $form;
	protected $item;
	protected $state;
	protected $admin;
	protected $defaults;

	function display($tpl = null)
	{
		$this->form = $this->get("Form");
		$this->item = $this->get("Item");
		$this->state = $this->get("State");
		$this->setLayout('form');
		$directory = '/components/com_biblestudy/images';
		$this->assignRef('directory', $directory);
		 
		$this->canDo	= BibleStudyHelper::getActions($this->item->id, 'mediaedit' );
		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() {
		 
		$isNew = ($this->item->id < 1);
		$title = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
		JToolBarHelper::title(JText::_('JBS_CMN_MEDIAIMAGES') . ': <small><small>[' . $title . ']</small></small>', 'mediaimages.png');

		if ($this->canDo->get('core.edit','com_biblestudy'))
		{
			JToolBarHelper::save('mediaedit.save');
			JToolBarHelper::apply('mediaedit.apply');
		}
		JToolBarHelper::cancel('mediaedit.cancel', 'JTOOLBAR_CANCEL');

		JToolBarHelper::divider();
		JToolBarHelper::help('biblestudy', true);
	}
}