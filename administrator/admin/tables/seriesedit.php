<?php
/**
 * Bible Study Series table class
 * 
 * 
 */

// no direct access
defined('_JEXEC') or die('Restricted access');



class Tableseriesedit extends JTable
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
	var $series_text = null;
	
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tableseriesedit(& $db) {
		parent::__construct('#__bsms_series', 'id', $db);
	}
}
?>
