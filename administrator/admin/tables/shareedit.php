<?php


// no direct access
defined('_JEXEC') or die('Restricted access');



class Tableshareedit extends JTable
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
	var $name = null;
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
	function Tableshareedit(& $db) {
		parent::__construct('#__bsms_share', 'id', $db);
	}
}
?>
