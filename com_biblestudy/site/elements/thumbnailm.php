<?php
/**
* @version		$Id: contact.php 8591 2007-08-27 21:09:32Z hackwar $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class JElementthumbnailm extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'thumbnailm';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db = &JFactory::getDBO();
		//This is the method to retrieve a list of files in a folder
		jimport('joomla.filesystem.folder');
		
		$query = 'SELECT params'
			. ' FROM #__bsms_admin'
			. ' WHERE id = 1';
			
		//$query = "SELECT l.id, l.location_text AS text"
		//. "\n FROM #__bsms_locations AS l"
		//. "\n WHERE l.published = 1"
		//. "\n ORDER BY l.location_text ASC"
		//;
/*$query = 'SELECT DISTINCT #__bsms_studies.location_id, #__bsms_locations.location_text AS text, #__bsms_locations.id AS id' .
				' FROM #__bsms_studies' .
				' LEFT JOIN #__bsms_locations ON (#__bsms_locations.id = #__bsms_studies.location_id)' .
				' WHERE #__bsms_locations.published = 1' .
				' ORDER BY #__bsms_locations.location_text ASC';*/
		$db->setQuery( $query );
		$admin = $db->loadObjectList( );
		$paramsdata = $admin[0]->params;
		//dump ($admin, 'params: ');
		$paramsdefs = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_biblestudy'.DS.'models'.DS.'admin.xml';
		$admin_params = new JParameter($paramsdata, $paramsdefs);
		$itemselected = $admin_params->get('thumbnail_study');
		
		$folder = JPATH_SITE.'/images/'.$admin_params->get('study_images', '/images/stories');
		//dump ($itemselected, 'control_name: ');
		$folder = JFolder::makeSafe($folder);
		$folders = JFolder::files($folder, '.', true);
		//$folderfinal = new stdClass;
		//$folderfinal2 = new stdClass;
		foreach($folders as $key=>$value)
		{
			$folderfinal1 = array('id'=>$key, 'value'=>$value);
			$folderfinal2[] = $folderfinal1;
			
		}
		//dump ($folderfinal2, 'folders: ');
		array_unshift($folderfinal2, JHTML::_('select.option', '0', JText::_('JBS_CMN_SELECT_IMAGE'), 'value', 'value'));
		//dump ($folderfinal2, 'control_name: ');
		return JHTML::_('select.genericlist',  $folderfinal2, ''.$control_name.'['.$name.']', 'class="inputbox"', 'value', 'value', $control_name.$name );
		//return	= JHTML::_('list.images',  'thumbnailm', $studiesedit->thumbnailm, $javascript, $directory, "bmp|gif|jpg|png|swf"  );
		//return JHTML::_('select.genericlist',  $folderfinal2, ''.$control_name.'['.$name.']', 'class="inputbox"', 'value', 'value', $value, $itemselected );
		
	}
}