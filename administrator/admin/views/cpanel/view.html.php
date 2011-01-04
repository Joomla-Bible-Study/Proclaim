<?php

/**
 * @author Joomla Bible Study
 * @copyright 2010
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class biblestudyViewcpanel extends JView
{
	
	function display($tpl = null)
	{ 	
	   //Version check
		include_once(JPATH_ADMINISTRATOR.'/components/com_biblestudy/helpers/version.php');
		$versioncheck = latestVersion();
	//	dump ($versioncheck);
		$this->assignRef('versioncheck', $versioncheck);
        
        
        parent::display($tpl);
	}
}
?>
