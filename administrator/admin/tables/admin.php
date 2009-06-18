<?php
/**
Locations Tables for BibleStudy
 */

// no direct access
defined('_JEXEC') or die('Restricted access');



class Tableadmin extends JTable
{
	/**
	 * Primary Key
	 *
	 * @var int
	 */
	var $id = null;
	//var $published = null;

	/**
	 * @var string
	 */
	var $compat_mode = null;
	var $allow_deletes = null;
	var $drop_tables = null;
	var $admin_store = null;

	

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tableadmin(& $db) {
		parent::__construct('#__bsms_admin', 'id', $db);
	}
}
?>
