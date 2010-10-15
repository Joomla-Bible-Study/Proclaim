<?php
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * @desc Generic class to serve the models
 * @author Eugen,
 */
class BSMModel extends JModel{
	var $association;
	var $data = null;
	var $id = false;
	var $name;
	var $prefix = 'bsms_';
	var $table;
	var $total;

	function __construct() {
		parent::__construct();

		$mainframe =& JFactory::getApplication();
		
		$this->setState('limitstart', $mainframe->getUserStateFromRequest('com_biblestudy&view='.$this->name.'.limitstart', 'limitstart', 0, 'int'));
		$this->setState('limit', $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int'));

		$cid = JRequest::getVar('cid', false, '', 'array');
		$this->id = $cid[0];
	}

	function getData($fields = array('*'), $conditions = array(), $order = null) {
		$mainframe =& JFactory::getApplication();, $option;
		
		$SELECT = array();
		$JOIN = '';
		$WHERE = array();
		
		//Include the ID in the fields if its not already
		if(!in_array('id', $fields) && !in_array('*', $fields))
			array_unshift($fields, 'id');
			
		foreach($fields as $field) {
			array_push($SELECT, $this->table.'.'.$field);
		}
		
		//Handle Associations
		foreach($this->association as $table) {
			if(!in_array('id', $table['fields']) && !in_array('*', $table['fields']))
				array_unshift($table['fields'], 'id');

			foreach($table['fields'] as $foreignField) {
				array_push($SELECT, $table['table'].'.'.$foreignField.' AS "'.$table['alias'].'.'.$foreignField.'"');
			}

			if(!key_exists('prefix', $table)) {
				$table['prefix'] = 'bsms_';
			}

			$JOIN .= ' LEFT JOIN #__'.$table['prefix'].$table['table'].' AS '.$table['table'].' ON ('.$table['table'].'.id = '.$this->table.'.'.$table['foreign_key'].') ';
			unset($table['prefix']);
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
		
		$query = 'SELECT '.implode($SELECT, ', ').' FROM #__'.$this->prefix.$this->table.' AS '.$this->table.$JOIN;
		
		if (count($WHERE) > 0)
			$query .= ' WHERE '.implode($WHERE, ' AND ');
		
		if (!$this->id) 
			$query .= ' '.$ORDER;
			
		echo $query.'<hr>';
		$this->_db->setQuery($query, $this->getState('limitstart'), $this->getState('limit'));
		$result = $this->_db->loadAssocList();
		$this->total = $this->_db->getAffectedRows();

		//Format the results into an array
		$i = 0;
		foreach($result as $data) {
			$this->data[$i][$this->name] = $data;
			foreach($this->association as $foreignTable) {
				$this->data[$i][$foreignTable['alias']]['id'] = $this->data[$i][$this->name][$foreignTable['alias'].'.id'];
				foreach($foreignTable['fields'] as $foreignField) {
					$this->data[$i][$foreignTable['alias']][$foreignField] = $this->data[$i][$this->name][$foreignTable['alias'].'.'.$foreignField];	
					unset($this->data[$i][$this->name][$foreignTable['alias'].'.'.$foreignField], $this->data[$i][$this->name][$foreignTable['alias'].'.id']);
				}
			}
			$i++;
		}
		//dump($this->data);
		return $this->data;
	}

	function getFilter($fields = array(), $order) {
		$query = 'SELECT DISTINCT '.$fields['value'].' AS value, '.$fields['text'].' AS text FROM #__'.$this->prefix.$this->table.' ORDER BY value '.$order;
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
	
	function save($data = null) {

	}


	function getPagination() {
		jimport('joomla.html.pagination');
		return new JPagination(50, $this->getState('limitstart'), $this->getState('limit'));
	}
}