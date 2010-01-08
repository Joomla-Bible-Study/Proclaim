<?php
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * @desc Generic class to serve the models
 * @author Eugen
 */
class BSMModel extends JModel{
	var $association;
	var $data = null;
	var $id = false;
	var $table;

	var $filters = array();

	function __construct() {
		parent::__construct();

		global $mainframe;
		
		$this->setState('limitstart', $mainframe->getUserStateFromRequest('com_biblestudy&view='.$this->name.'.limitstart', 'limitstart', 0, 'int'));
		$this->setState('limit', $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int'));

		$cid = JRequest::getVar('cid', false, '', 'array');
		$this->id = $cid[0];
	}

	function getData($conditions = array(), $fields = array('*'), $order = null, $prefix = 'bsms_') {
		global $mainframe, $option;
		
		$SELECT = array();
		$JOIN = '';
		$WHERE = array();
		foreach($fields as $field) {
			array_push($SELECT, $this->table.'.'.$field);
		}
		
		//SELECT ASSOCIATIONS
		foreach($this->association as $table) {
			foreach($table['fields'] as $foreignField) {
				array_push($SELECT, $table['table'].'.'.$foreignField);
			}
			if(empty($table['prefix']))
				$table['prefix'] = 'bsms_';
			$JOIN .= ' LEFT JOIN #__'.$table['prefix'].$table['table'].' AS '.$table['table'].' ON ('.$table['table'].'.id = '.$this->table.'.'.$table['foreign_key'].') ';
		}
		
		//WHERE CONDITIONS
		if($this->id !== false) {
			$conditions['id'] = $this->id;
		}
		foreach($conditions as $field=>$value) {
			array_push($WHERE, $this->table.'.'.$field.' = '.'"'.$value.'"');
		}
		
		//ORDER AND SORTTING
		$ORDER = 'ORDER BY '.$mainframe->getUserStateFromRequest($option.'filter_order', 'filter_order', $this->order, 'cmd');
		$ORDER .= ' '.strtoupper($mainframe->getUserStateFromRequest($option.'filter_order_Dir', 'filter_order_Dir', 'ASC'));
		
		$query = 'SELECT '.implode($SELECT, ', ').' FROM #__'.$prefix.$this->table.' AS '.$this->table.$JOIN;
		
		if (count($WHERE) > 0)
			$query .= ' WHERE '.implode($WHERE, ' AND ');
		
		if (!$this->id) 
			$query .= ' '.$ORDER;
		echo $query;
		
		if($this->data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'))) {
			return $this->data;
		} else {
			$this->setError(get_class($this).':'.__FUNCTION__.':('.$this->table.'):: Query failed');
			return false;
		}
	}

	function save($data = null) {

	}


	function getPagination() {
		jimport('joomla.html.pagination');
		return new JPagination(50, $this->getState('limitstart'), $this->getState('limit'));
	}
}