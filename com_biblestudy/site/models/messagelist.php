<?php

/**
 * Message Model
 *
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link    http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

// Base this model on the backend version.
require_once JPATH_ADMINISTRATOR . '/components/com_biblestudy/models/messages.php';

/**
 * Model class for Messages
 *
 * @package BibleStudy.Site
 * @since   7.0.0
 */
class BiblestudyModelMessagelist extends BiblestudyModelMessages
{

	/**
	 * Get Plays
	 *
	 * @param int $id
	 *
	 * @return string
	 */
	function getPlays($id)
	{
		$query  = ' SELECT SUM(plays) AS totalPlays FROM #__bsms_mediafiles WHERE study_id = ' . $id . ' GROUP BY study_id';
		$result = $this->_getList($query);
		if (!$result) {
			$result = '0';

			return $result;
		}

		return $result[0]->totalPlays;
	}

	/**
	 * Method to get a pagination object for the studies
	 *
	 * @access public
	 * @return integer
	 */
	function legacyGetPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_pagination;
	}

	/**
	 * Build Content Where
	 *
	 * @return string
	 */
	function _buildContentWhere()
	{
		$mainframe = JFactory::getApplication();
		$input     = new JInput;
		$option    = $input->get('option', '', 'cmd');

		$filter_book        = $mainframe->getUserStateFromRequest($option . 'filter_book', 'filter_book', 0, 'int');
		$filter_teacher     = $mainframe->getUserStateFromRequest($option . 'filter_teacher', 'filter_teacher', 0, 'int');
		$filter_series      = $mainframe->getUserStateFromRequest($option . 'filter_series', 'filter_series', 0, 'int');
		$filter_messagetype = $mainframe->getUserStateFromRequest($option . 'filter_messagetype', 'filter_messagetype', 0, 'int');
		$filter_year        = $mainframe->getUserStateFromRequest($option . 'filter_year', 'filter_year', 0, 'int');
		$filter_orders      = $mainframe->getUserStateFromRequest($option . 'filter_orders', 'filter_orders', 'DESC', 'word');


		$where = array();

		if ($filter_book > 0) {
			$where[] = ' #__bsms_studies.booknumber = ' . (int) $filter_book;
		}
		if ($filter_teacher > 0) {
			$where[] = ' #__bsms_studies.teacher_id = ' . (int) $filter_teacher;
		}
		if ($filter_series > 0) {
			$where[] = ' #__bsms_studies.series_id = ' . (int) $filter_series;
		}
		if ($filter_messagetype > 0) {
			$where[] = ' #__bsms_studies.messagetype = ' . (int) $filter_messagetype;
		}
		if ($filter_year > 0) {
			$where[] = " YEAR(#__bsms_studies.studydate)= " . (int) $filter_year;
		}

		$where = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');

		return $where;
	}

	/**
	 * Build Content Order By
	 *
	 * @return string
	 */
	function _buildContentOrderBy()
	{
		$mainframe = JFactory::getApplication();
		$input     = new JInput;
		$option    = $input->get('option', '', 'cmd');

		$orders           = array(
			'id',
			'published',
			'studydate',
			'messagetype',
			'teacher_id',
			'studytitle',
			'series_id'
		);
		$filter_order     = $mainframe->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'ordering', 'cmd');
		$filter_order_Dir = strtoupper($mainframe->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'ASC'));
		if ($filter_order_Dir != 'ASC' && $filter_order_Dir != 'DESC') {
			$filter_order_Dir = 'ASC';
		}
		if (!in_array($filter_order, $orders)) {
			$filter_order = 'studydate';
		}

		$orderby = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir . ' , id';

		return $orderby;
	}

}