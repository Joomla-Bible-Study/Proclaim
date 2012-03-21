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
require_once (JPATH_ADMINISTRATOR  .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_biblestudy' .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'biblestudy.php');
require_once (JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'upload.php');
jimport('joomla.application.component.view');

class BiblestudyViewMediafile extends JView {

	protected $form;
	protected $item;
	protected $state;
	protected $admin;

	function display($tpl = null) {
		$this->form = $this->get("Form");
		$this->item = $this->get("Item");
		$this->state = $this->get("State");
		$this->canDo = BibleStudyHelper::getActions($this->item->id, 'mediafile');
		//Load the Admin settings
		$this->loadHelper('params');
		$this->admin = BsmHelper::getAdmin();
                $host = JURI::base();
                $document = JFactory::getDocument();
                $document->addScript($host.'media/com_biblestudy/js/swfupload/swfupload.js');
                $document->addScript($host.'media/com_biblestudy/js/swfupload/swfupload.queue.js');
                $document->addScript($host.'media/com_biblestudy/js/swfupload/fileprogress.js');
                $document->addScript($host.'media/com_biblestudy/js/swfupload/handlers.js');
                $document->addScript(JURI::root() . 'administrator/components/com_biblestudy/views/mediafile/tmpl/submitbutton.js');
                $document->addStyleSheet($host.'media/com_biblestudy/js/swfupload/default.css');
                $swfUploadHeadJs = JBSUpload::uploadjs($host);
                //add the javascript to the head of the html document
                $document->addScriptDeclaration($swfUploadHeadJs);
		//Needed to load the article field type for the article selector
		JFormHelper::addFieldPath(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_content'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'fields'.DIRECTORY_SEPARATOR.'modal');

                $db = JFactory::getDBO();
                //get server for upload dropdown
                $query = 'SELECT id as value, server_name as text FROM #__bsms_servers WHERE published=1 ORDER BY server_name ASC';
                $db->setQuery($query);
                $db->query();
               // $servers = $db->loadObjectList();
                $server = array(
                array('value' => '', 'text' => JText::_('JBS_MED_SELECT_SERVER')),
                );
                $serverlist = array_merge( $server, $db->loadObjectList() );
                $idsel = "'SWFUpload_0'";
                $this->assignRef('upload_server', JHTML::_('select.genericList', $serverlist, 'upload_server', 'class="inputbox" onchange="showupload('.$idsel.')"'. '', 'value', 'text', '' ));

                //Get folders for upload dropdown
                $query = 'SELECT id as value, foldername as text FROM #__bsms_folders WHERE published=1 ORDER BY foldername ASC';
                $db->setQuery($query);
                $db->query();
               // $folders = $db->loadObjectList();
                $folder = array(
                array('value' => '', 'text' => JText::_('JBS_MED_SELECT_FOLDER')),
                );
                $folderlist = array_merge( $folder, $db->loadObjectList() );
                $idsel = "'SWFUpload_0'";
                $this->assignRef('upload_folder', JHTML::_('select.genericList', $folderlist, 'upload_folder', 'class="inputbox" onchange="showupload('.$idsel.')"'. '', 'value', 'text', '' ));

		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() {
                JRequest::setVar('hidemainmenu', true);
		$isNew = ($this->item->id < 1);
		$title = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
		JToolBarHelper::title(JText::_('JBS_CMN_MEDIA_FILES') . ': <small><small>[' . $title . ']</small></small>', 'mp3.png');

		if ($this->canDo->get('core.edit','com_biblestudy'))
		{
			JToolBarHelper::save('mediafile.save');
			JToolBarHelper::apply('mediafile.apply');
		}
		JToolBarHelper::cancel('mediafile.cancel', 'JTOOLBAR_CANCEL');
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