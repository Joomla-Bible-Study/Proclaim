<?php

/**
 * @version     $Id: teacherlist.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 */
//No Direct Access
defined('_JEXEC') or die();

jimport('joomla.application.component.modellist');

class biblestudyModelteacherlist extends JModelList {

	/**
	 * teacherlist data array
	 *
	 * @var array
	 */
	var $_data;
	var $_total = null;
	var $_pagination = null;
	var $allow_deletes = null;

	function __construct($config = array()) {
		if(empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
              'teacher.published',
              'teacher.ordering',
              'teacher.teachername'
			);
		}

		parent::__construct($config);
	}

	function getDeletes() {
		if (empty($this->_deletes)) {
			$query = 'SELECT allow_deletes'
			. ' FROM #__bsms_admin'
			. ' WHERE id = 1';
			$this->_deletes = $this->_getList($query);
		}
		return $this->_deletes;
	}

	/*
	 * @since   7.0
	*/

	protected function populateState() {
		$state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state');
		$this->setState('filter.state', $state);

		$published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		parent::populateState('teacher.teachername', 'ASC');
	}

	/*
	 * @since   7.0
	*/

	protected function getListQuery() {
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select(
		$this->getState(
                        'list.select',
                        'teacher.id, teacher.published, teacher.ordering, teacher.teachername'));
		$query->from('#__bsms_teachers AS teacher');

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('teacher.published = ' . (int) $published);
		}
		else if ($published === '') {
			$query->where('(teacher.published = 0 OR teacher.published = 1)');
		}

		//Add the list ordering clause
		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		$query->order($db->getEscaped($orderCol . ' ' . $orderDirn));
		return $query;
	}

}