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


class JElementbook extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'book';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db = &JFactory::getDBO();
		$language =& JFactory::getLanguage();
		$language->load('com_biblestudy');
		
		$query = 'SELECT DISTINCT #__bsms_studies.booknumber, #__bsms_books.bookname, #__bsms_books.booknumber AS bnum, #__bsms_books.id AS bid' .
				' FROM #__bsms_studies' .
				' LEFT JOIN #__bsms_books ON (#__bsms_books.booknumber = #__bsms_studies.booknumber)' .
				' WHERE #__bsms_books.published = 1' .
				' ORDER BY #__bsms_books.booknumber ASC';
		$db->setQuery($query);
		// santon 2010-12-03: database query gets books as JBS_BBK_xxx phrases. How to include JText here???
		$options = $db->loadObjectList();
		//dump ($options, 'options: ');
		array_unshift($options, JHTML::_('select.option', '0', '- '.JText::_('JBS_CMN_SELECT_BOOK').' -', 'booknumber', 'bookname'));

		return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'booknumber', 'bookname', $value, $control_name.$name );
	}
}