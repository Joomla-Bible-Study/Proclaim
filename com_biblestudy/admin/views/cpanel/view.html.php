<?php

/**
 * @author Joomla Bible Study
 * @copyright 2010
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
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
