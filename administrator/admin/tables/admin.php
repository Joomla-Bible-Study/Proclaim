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

	 public function bind($array, $ignore = '') 
        {
                if (isset($array['params']) && is_array($array['params'])) 
                {
                        // Convert the params field to a string.
                        $parameter = new JRegistry;
                        $parameter->loadArray($array['params']);
                        $array['params'] = (string)$parameter;
                }
                return parent::bind($array, $ignore);
        }

     public function load($pk = null, $reset = true) 
        {
                if (parent::load($pk, $reset)) 
                {
                        // Convert the params field to a registry.
                        $params = new JRegistry;
                        $params->loadJSON($this->params);
                        $this->params = $params;
                        return true;
                }
                else
                {
                        return false;
                }
        }
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tableadmin(& $db) {
		parent::__construct('#__bsms_admin', 'id', $db);
	}
    
    	/**
	 * Overload the store method for the Weblinks table.
	 *
	 * @param	boolean	Toggle whether null values should be updated.
	 * @return	boolean	True on success, false on failure.
	 * @since	1.6
	 */
	public function store($updateNulls = false)
	{

		// Attempt to store the user data.
		return parent::store($updateNulls);
	}


}
?>
