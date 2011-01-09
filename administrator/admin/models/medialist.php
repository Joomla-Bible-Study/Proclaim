<?php

/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();


//Joomla 1.6 <-> 1.5 Branch
try {
    jimport('joomla.application.component.modellist');

    abstract class modelClass extends JModelList {

    }

} catch (Exception $e) {
    jimport('joomla.application.component.model');

    abstract class modelClass extends JModel {

    }

}
 
class biblestudyModelmedialist extends modelClass
{
	/**
	 *
	 * @var array
	 */
	var $_data;
	var $_admin;
function getAdmin()
	{
		if (empty($this->_admin)) {
			$query = 'SELECT params'
			. ' FROM #__bsms_admin'
			. ' WHERE id = 1';
			$this->_admin = $this->_getList($query);
		}
		return $this->_admin;
	}
	/**
	 * Returns the query
	 * @return string The query to be used to retrieve the rows from the database
	 */
	function _buildQuery()
	{
		$query = ' SELECT * '
			. ' FROM #__bsms_media '
		;

		return $query;
	}

	/**
	 * Retrieves the data
	 * @return array Array of objects containing the data from the database
	 */
	function getData()
	{
		// Lets load the data if it doesn't already exist
		if (empty( $this->_data ))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList( $query );
		}

		return $this->_data;
	}

}
?>