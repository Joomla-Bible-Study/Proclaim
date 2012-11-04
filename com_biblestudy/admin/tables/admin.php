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
	var $podcast = null;
	var $series = null;
	var $study = null;
	var $teacher = null;
	var $media = null;
	var $params = null;
	var $download = null;
	var $main = null;
	var $showhide = null;

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
