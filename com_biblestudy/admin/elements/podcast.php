<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class JElementpodcast extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'podcast';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db = &JFactory::getDBO();

		$query = "SELECT t.id, t.title AS text, t.published"
		. "\n FROM #__bsms_podcast AS t"
		. "\n WHERE t.published = 1"
		. "\n ORDER BY t.title ASC"
		;
		$db->setQuery( $query );
		$options = $db->loadObjectList( );
		array_unshift($options, JHTML::_('select.option', '0', '- '.JText::_('Select a Podcast').' -', 'id', 'text'));
		return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'id', 'text', $value, $control_name.$name );
	}
}