<?php


// no direct access
defined('_JEXEC') or die('Restricted access');



class Tableteacheredit extends JTable
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
	var $thumbw = null;
	var $thumbh = null;
	var $short = null;
	var $ordering = null;
	var $catid = null;
	var $list_show = null;
	var $teacher_thumbnail = null;
	var $teacher_image = null;
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tableteacheredit(& $db) {
		parent::__construct('#__bsms_teachers', 'id', $db);
	}
}
?>
