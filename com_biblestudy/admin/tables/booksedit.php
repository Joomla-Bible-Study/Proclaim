<?php
/**
 * @version $Id: booksedit.php 1 $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

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
