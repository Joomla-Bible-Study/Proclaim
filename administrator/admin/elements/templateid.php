<?php
/**
* com_biblestudy element for parameters
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class JElementtemplateid extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'templateid';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db = &JFactory::getDBO();


		$query = "SELECT t.id, CONCAT(t.id,' - ',t.title) AS text"
		. "\n FROM #__bsms_templates AS t"
		. "\n ORDER BY t.title ASC"
		;
		$db->setQuery( $query );
		$options = $db->loadObjectList( );
		array_unshift($options, JHTML::_('select.option', '0', '- '.JText::_('Select a Menu Item').' -', 'id', 'text'));
		return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'id', 'text', $value, $control_name.$name );
	}
}
