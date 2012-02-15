<?php

/**
 * @version $Id: view.html.php 1 $
 * @package BibleStudy SermonSpeaker Converter
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

defined('_JEXEC') or die();

jimport('joomla.application.component.view' );


class ss2jbsViewss2jbs extends JView {

	function display($tpl = null) {



		JToolBarHelper::title(   JText::_( 'Convert from Sermon Speaker to Joomla Bible Study' ) );

		//Check for version of Sermon Speaker 3.4 or higher
		$ssversion = $this->versionXML($component='sermonspeaker');
		if (!$ssversion){
			$ssversion = JText::_('No Sermon Speaker version found');
		}
		$jbsversion = $this->versionXML($component = 'jbs');
		if (!$ssversion){
			$jbsversion = JText::_('No Joomla Bible Study version found');
		}
		$this->assignRef('jbsversion', $jbsversion);
		$this->assignRef('ssversion',$ssversion);
		 
		 

		parent::display($tpl);
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

			case 'jbs':
				if ($data = JApplicationHelper::parseXMLInstallFile(JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_biblestudy'.DIRECTORY_SEPARATOR.'biblestudy.xml'))
				{
					return $data['version'];
				}
				else {return FALSE;
				}
				break;
		}
	}
}