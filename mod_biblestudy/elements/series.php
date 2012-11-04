<?php
/**
 *
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();


class JElementseries extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'series';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db = &JFactory::getDBO();
		$language =& JFactory::getLanguage();
		$language->load('com_biblestudy');
		
		$query = 'SELECT DISTINCT #__bsms_studies.series_id, #__bsms_series.series_text, #__bsms_series.id AS sid' .
				' FROM #__bsms_studies' .
				' LEFT JOIN #__bsms_series ON (#__bsms_series.id = #__bsms_studies.series_id)' .
				' WHERE #__bsms_series.published = 1' .
				' ORDER BY #__bsms_series.series_text ASC';
		$db->setQuery($query);
		$options = $db->loadObjectList();
		array_unshift($options, JHTML::_('select.option', '0', '- '.JText::_('Select a Series').' -', 'series_id', 'series_text'));

		return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'series_id', 'series_text', $value, $control_name.$name );
	}
}