<?php


// no direct access
defined('_JEXEC') or die('Restricted access');



class Tablemessagetypeedit extends JTable
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
	var $message_type = null;

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tablemessagetypeedit(& $db) {
		parent::__construct('#__bsms_message_type', 'id', $db);
	}
}
?>
