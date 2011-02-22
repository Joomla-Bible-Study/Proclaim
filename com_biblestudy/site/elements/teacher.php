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


class JElementteacher extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'teacher';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db = &JFactory::getDBO();
		$language =& JFactory::getLanguage();
		$language->load('com_biblestudy');
		
		$query = 'SELECT DISTINCT #__bsms_studies.teacher_id, #__bsms_teachers.teachername, #__bsms_teachers.id AS tid' .
				' FROM #__bsms_studies' .
				' LEFT JOIN #__bsms_teachers ON (#__bsms_teachers.id = #__bsms_studies.teacher_id)' .
				' WHERE #__bsms_teachers.published = 1' .
				' ORDER BY #__bsms_teachers.teachername ASC';
		$db->setQuery($query);
		$options = $db->loadObjectList();
		array_unshift($options, JHTML::_('select.option', '0', JText::_('JBS_CMN_SELECT_TEACHER'), 'teacher_id', 'teachername'));

		return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'teacher_id', 'teachername', $value, $control_name.$name );
	}
}