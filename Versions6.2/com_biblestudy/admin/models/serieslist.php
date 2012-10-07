<?php

// Serieslist Model for Bible Study Component


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );



class biblestudyModelserieslist extends JModel
{
	/**
	 *
	 * @var array
	 */
	var $_data;
	var $allow_deletes = null;

	/**
	 * Returns the query
	 * @return string The query to be used to retrieve the rows from the database
	 */
	function _buildQuery()
	{
		$query = ' SELECT * '
		. ' FROM #__bsms_series '
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
	function getDeletes()
	{
		if (empty($this->_deletes)) {
			$query = 'SELECT allow_deletes'
			. ' FROM #__bsms_admin'
			. ' WHERE id = 1';
			$this->_deletes = $this->_getList($query);
		}
		return $this->_deletes;
	}
}
?>