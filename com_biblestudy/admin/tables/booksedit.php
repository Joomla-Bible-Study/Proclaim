<?php
/**
 * @version $Id: booksedit.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

//No Direct Access
defined('_JEXEC') or die;



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
