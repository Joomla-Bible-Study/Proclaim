<?php

/**
 * @version $Id$
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
require_once (JPATH_ADMINISTRATOR  .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_biblestudy' .DIRECTORY_SEPARATOR. 'lib' .DIRECTORY_SEPARATOR. 'biblestudy.defines.php');
jimport( 'joomla.application.component.view' );

class biblestudyViewcpanel extends JView
{

	function display($tpl = null)
	{
		 
		//Version check
		include_once(JPATH_ADMINISTRATOR.'/components/com_biblestudy/helpers/version.php');
		$versioncheck = latestVersion();
		$this->assignRef('versioncheck', $versioncheck);

		$db = JFactory::getDBO();
		//We see if one of the records matches the parent_id, if not, we need to reset them
		$query = "SELECT id FROM #__assets WHERE name = 'com_biblestudy'";
		$db->setQuery($query);
		$this->jbs_asset_id = $db->loadResult();

		// Check to see if assets have been fixed and if they match the parent_id
		$query = 'SELECT t.asset_id, a.parent_id FROM #__bsms_studies AS t LEFT JOIN #__assets AS a ON (t.asset_id = a.id) WHERE t.id = 1';
		$db->setQuery($query);
		$asset = $db->loadObject();
		$this->joomla_asset_id = $asset->parent_id;



		parent::display($tpl);
	}
}
