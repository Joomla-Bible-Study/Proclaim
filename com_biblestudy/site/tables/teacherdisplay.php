<?php


// no direct access
defined('_JEXEC') or die('Restricted access');



class Tableteacherdisplay extends JTable
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
	var $teachername = null;
	var $title = null;
	var $phone = null;
	var $email = null;
	var $website = null;
	var $information = null;
	var $image = null;
	var $imageh = null;
	var $imagew = null;
	var $thumb = null;
	var $thumbh = null;
	var $thumbw = null;

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tableteacherdisplay(& $db) {
		parent::__construct('#__bsms_teachers', 'id', $db);
	}
}
