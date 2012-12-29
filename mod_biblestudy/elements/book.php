<?php

/**
 * Book Element
 *
 * @package     BibleStudy
 * @subpackage  Model.BibleStudy
 * @copyright   (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        http://www.JoomlaBibleStudy.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

/**
 * Book Element
 *
 * @package     BibleStudy
 * @subpackage  Model.BibleStudy
 * @since       7.0.0
 */
class JElementbook extends JElement
{

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
public var $_name = 'book';

	/**
	 * Element Function
	 *
	 * @param string $name
	 * @param string $value
	 * @param string $node
	 * @param string $control_name
	 *
	 * @return string
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		$db = JFactory::getDBO();

		$query = 'SELECT DISTINCT #__bsms_studies.booknumber, #__bsms_books.bookname, #__bsms_books.booknumber AS bnum, #__bsms_books.id AS bid' .
				' FROM #__bsms_studies' .
				' LEFT JOIN #__bsms_books ON (#__bsms_books.booknumber = #__bsms_studies.booknumber)' .
				' WHERE #__bsms_books.published = 1' .
				' ORDER BY #__bsms_books.booknumber ASC';
		$db->setQuery($query);
		$options = $db->loadObjectList();
		array_unshift($options, JHTML::_('select.option', '0', '- ' . JText::_('Select a Book') . ' -', 'booknumber', 'bookname'));

		return JHTML::_('select.genericlist', $options, '' . $control_name . '[' . $name . ']', 'class="inputbox"', 'booknumber', 'bookname', $value, $control_name . $name);
	}

}