<?php
defined('_JEXEC') or die('Restriced Access');

/**
 * @desc This class in an abstraction layer for a lot of the functionality
 * that goes on inside the component.
 * @author Eugen
 *
 */
class bsm {
	var $model;
	var $relations;

	var $start;
	var $end;
	var $limit;


	/*
	 * @desc Reference the Joomla database object
	*/
	function __construct($model, $relations) {

		$this->model = $model;
		$this->relations = $relations;
	}

	/**
	 * @desc returns the data of a model. If model associations are set
	 * then they are included in the result.
	 * @return array
	 */
	function find($id = null) {

	}
}
?>