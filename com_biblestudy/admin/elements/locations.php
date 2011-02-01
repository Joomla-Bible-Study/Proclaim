<?php
/**
* @version		$Id: locations.php 1284 2011-01-04 07:57:59Z genu $
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

class JElementlocations extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'locations';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db = &JFactory::getDBO();

		$query = "SELECT l.id, l.location_text AS text"
		. "\n FROM #__bsms_locations AS l"
		. "\n WHERE l.published = 1"
		. "\n ORDER BY l.location_text ASC"
		;
/*$query = 'SELECT DISTINCT #__bsms_studies.location_id, #__bsms_locations.location_text AS text, #__bsms_locations.id AS id' .
				' FROM #__bsms_studies' .
				' LEFT JOIN #__bsms_locations ON (#__bsms_locations.id = #__bsms_studies.location_id)' .
				' WHERE #__bsms_locations.published = 1' .
				' ORDER BY #__bsms_locations.location_text ASC';*/
		$db->setQuery( $query );
		$options = $db->loadObjectList( );
		//dump ($options, 'options: ');
		array_unshift($options, JHTML::_('select.option', '0', '- '.JText::_('JBS_CMN_SELECT_LOCATION').' -', 'id', 'text'));
		return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'id', 'text', $value, $control_name.$name );
	}
}