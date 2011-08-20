<?php
/**
 * @version		$Id: locations.php 8591 2007-08-27 21:09:32Z Tom Fuller $
 * @package		mod_biblestudy
 * @copyright            2010-2011
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
	var	$_name = 'location_text';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db = &JFactory::getDBO();

		$query = "SELECT l.id, l.location_text AS text"
		. "\n FROM #__bsms_locations AS l"
		. "\n WHERE l.published = 1"
		. "\n ORDER BY l.location_text ASC"
		;
		$db->setQuery( $query );
		$options = $db->loadObjectList( );
		array_unshift($options, JHTML::_('select.option', '0', '- '.JText::_('Select a Location').' -', 'id', 'text'));
		return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'id', 'text', $value, $control_name.$name );
	}
}