<?php
/**
 books Tables for BibleStudy
 */

// no direct access
defined('_JEXEC') or die('Restricted access');



class Tablebooksedit extends JTable
{
	/**
	 * Primary Key
	 *
	 * @var int
	 */
	var $id = null;
	var $published = null;

	/**
	 * @var string
	 */
	var $bookname = null;
	var $booknumber = null;

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tablebooksedit(& $db) {
		parent::__construct('#__bsms_books', 'id', $db);
	}
}
?>
