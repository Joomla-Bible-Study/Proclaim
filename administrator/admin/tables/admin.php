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
	var $params = null;

	function bind($array, $ignore = '')
{ 
        if (key_exists( 'params', $array ) && is_array( $array['params'] ))
        {
                $registry = new JRegistry();
                $registry->loadArray($array['params']);
                $array['params'] = $registry->toString();
        }
        return parent::bind($array, $ignore);
}

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
