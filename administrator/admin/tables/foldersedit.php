<?php
/**
 * Bible Study Series table class
 * 
 * 
 */

// no direct access
defined('_JEXEC') or die('Restricted access');



class Tablefoldersedit extends JTable
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
	var $foldername = null;
	var $folderpath = null;
	
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tablefoldersedit(& $db) {
		parent::__construct('#__bsms_folders', 'id', $db);
	}
}
?>
