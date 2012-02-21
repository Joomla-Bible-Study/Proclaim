<?php

/**
 * @version $Id: view.html.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.defines.php');
jimport('joomla.application.component.view');
jimport('joomla.application.component.helper');
jimport('joomla.i18n.help');

require_once (BIBLESTUDY_PATH_ADMIN_LIB . DIRECTORY_SEPARATOR . 'biblestudy.stats.class.php');

class biblestudyViewadmin extends JView {

    protected $form;
    protected $item;
    protected $state;

    function display($tpl = null) {

        $this->form = $this->get("Form");
        $this->item = $this->get("Item");
        $this->state = $this->get("State");

        $this->setLayout('form');
        $this->addToolbar();

        $this->loadHelper('params');
        $config = JFactory::getConfig();
        $tmp_dest = $config->getValue('config.tmp_path');
        $this->assignRef('tmp_dest', $tmp_dest);

        $stats = new jbStats();
        $playerstats = $stats->players();
        $this->assignRef('playerstats', $playerstats);
        $this->assets = JRequest::getVar('checkassets', null, 'get', 'array');
        $popups = $stats->popups();
        $this->assignRef('popups', $popups);

        //get the list of backupfiles
        $backedupfiles = array();
        jimport('joomla.filesystem.folder');
        $path = JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'database';
        if (JFolder::exists($path))
        {
            if (!$files = JFolder::files($path, '.sql')){$this->lists['backedupfiles']= JText::_('JBS_CMN_NO_FILES_TO_DISPLAY');}
            else
            {
                asort($files, SORT_STRING);
                $filelist = array();
                foreach ($files as $i => $value) {
                    $filelisttemp = array('value' => $value, 'text' => $value);
                    $filelist[] = $filelisttemp;
                }
        
                $types[] = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_DB'));
                $types = array_merge($types, $filelist);
                $this->lists['backedupfiles'] = JHTML::_('select.genericlist', $types, 'backuprestore', 'class="inputbox" size="1" ', 'value', 'text', '');
    
            }
        }
        else
        {
            $this->lists['backedupfiles']= JText::_('JBS_CMN_NO_FILES_TO_DISPLAY');
        }
        //Check for presenc of Sermon Speaker
        $ssversion = $this->versionXML($component='sermonspeaker');
		if (!$ssversion){
			$this->ss = JText::_('JBS_ADM_NO_SERMON_SPEAKER_FOUND');
		}
        else
        {
            $this->ss = '<a href="index.php?option=com_biblestudy&view=admin&id=1&task=admin.convertSermonSpeaker">'.JText::_('JBS_ADM_CONVERT_SERMON_SPEAKER').'</a>';
        }
        $piversion = $this->versionXML($component='preachit');
        if (!$piversion){
			$this->pi = JText::_('JBS_ADM_NO_PREACHIT_FOUND');
		}
        else
        {
            $this->pi = '<a href="index.php?option=com_biblestudy&view=admin&id=1&task=admin.convertPreachIt">'.JText::_('JBS_ADM_CONVERT_PREACH_IT').'</a>';
        }
        
        parent::display($tpl);
    }

    protected function addToolbar() {
        JRequest::setVar('hidemainmenu', false);

        JToolBarHelper::title(JText::_('JBS_CMN_ADMINISTRATION'), 'administration');
        JToolBarHelper::preferences('com_biblestudy', '600', '800', 'JBS_ADM_PERMISSIONS');
        JToolBarHelper::divider();
        JToolBarHelper::save('admin.save');
        JToolBarHelper::apply('admin.apply');
        JToolBarHelper::cancel('admin.cancel', 'JTOOLBAR_CLOSE');
        JToolBarHelper::divider();
        JToolBarHelper::custom('admin.resetHits', 'reset.png', 'Reset All Hits', 'JBS_ADM_RESET_ALL_HITS', false, false);
        JToolBarHelper::custom('admin.resetDownloads', 'download.png', 'Reset All Download Hits', 'JBS_ADM_RESET_ALL_DOWNLOAD_HITS', false, false);
        JToolBarHelper::custom('admin.resetPlays', 'play.png', 'Reset All Plays', 'JBS_ADM_RESET_ALL_PLAYS', false, false);
        JToolBarHelper::divider();
        JToolBarHelper::help('biblestudy', true);
    }

function versionXML($component)
	{
		switch ($component)
		{
			case 'sermonspeaker':
				if ($data = JApplicationHelper::parseXMLInstallFile(JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_sermonspeaker'.DIRECTORY_SEPARATOR.'sermonspeaker.xml'))
				{
					return $data['version'];
				}
				else {return FALSE;
				}
				break;

			case 'preachit':
				if ($data = JApplicationHelper::parseXMLInstallFile(JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_preachit'.DIRECTORY_SEPARATOR.'preachit.xml'))
				{
					return $data['version'];
				}
				else {return FALSE;
				}
				break;
		}
	}
}