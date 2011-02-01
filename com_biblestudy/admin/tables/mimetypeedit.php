<?php


// no direct access
defined('_JEXEC') or die('Restricted access');



class Tablemimetypeedit extends JTable
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
	var $mimetype = null;
	var $mimetext = null;

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tablemimetypeedit(& $db) {
		parent::__construct('#__bsms_mimetype', 'id', $db);
	}
}
?>
