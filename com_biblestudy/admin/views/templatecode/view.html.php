<?php

/**
 * @version $Id: view.html.php 2025 2011-08-28 04:08:06Z genu $
 * @since 7.1.0
 * @desc View for Style edit
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ADMINISTRATOR  .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_biblestudy' .DIRECTORY_SEPARATOR. 'lib' .DIRECTORY_SEPARATOR. 'biblestudy.defines.php');
require_once (JPATH_ADMINISTRATOR  .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_biblestudy' .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'biblestudy.php');
jimport('joomla.application.component.view');

class biblestudyViewTemplatecode extends JView {

	protected $form;
	protected $item;
	protected $state;
	protected $defaults;

	function display($tpl = null) {
            $link = JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_biblestudy'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'templatecodehelp.html';
		$this->form = $this->get("Form");
		$item = $this->get("Item");
                if ($item->id == 0)
                {
                    jimport('joomla.client.helper');
                    jimport('joomla.filesystem.file');
                    JClientHelper::setCredentialsFromRequest('ftp');
                    $ftp = JClientHelper::getCredentials('ftp');
                    $file = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'defaulttemplatecode.php';
                    $this->defaultcode = JFile::read($file);
                    
                }
                $this->item = $item;
		$this->state = $this->get("State");
		$this->canDo	= BibleStudyHelper::getActions($this->item->id, 'templatecode');
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
                JRequest::setVar('hidemainmenu', true);
		$isNew = ($this->item->id < 1);
		$title = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
		JToolBarHelper::title(JText::_('JBS_CMN_TEMPLATECODE') . ': <small><small>[' . $title . ']</small></small>', 'templates.png');

                if ($this->canDo->get('core.create'))
                {
                    JToolbarHelper::save2new('templatecode.save2new');
                }
		if ($this->canDo->get('core.edit','com_biblestudy'))
		{
			JToolBarHelper::save('templatecode.save');
			JToolBarHelper::apply('templatecode.apply');
                        JToolBarHelper::save2copy('templatecode.save2copy');
		}
		JToolBarHelper::cancel('templatecode.cancel', 'JTOOLBAR_CANCEL');

		JToolBarHelper::divider();
		JToolBarHelper::help('templatecodehelp', true);
                JToolBarHelper::divider();
                
                
               
	}

}