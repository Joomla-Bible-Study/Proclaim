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

class JElementstudydetails extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'studydetails';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db = &JFactory::getDBO();

		$query = "SELECT #__bsms_studies.id, #__bsms_books.id AS bid, #__bsms_studies.booknumber AS bnumber, #__bsms_books.bookname, CONCAT(#__bsms_studies.id,' - ', #__bsms_books.bookname,':',#__bsms_studies.chapter_begin,' - ',DATE_FORMAT(#__bsms_studies.studydate,'%Y-%m-%d')) AS text"
		. "\n FROM #__bsms_studies"
		. "\n LEFT JOIN #__bsms_books ON (#__bsms_books.booknumber = #__bsms_studies.booknumber)"
		. "\n WHERE #__bsms_studies.published = 1"
		. "\n ORDER BY #__bsms_studies.studydate DESC"
		;
		$db->setQuery( $query );
		$options = $db->loadObjectList( );
		array_unshift($options, JHTML::_('select.option', '0', '- '.JText::_('Select a Study to Link To').' -', 'id', 'text'));
		return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'id', 'text', $value, $control_name.$name );
	}
}