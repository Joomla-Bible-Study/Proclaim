<?php

/**
 * @version $Id$
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

defined('_JEXEC') or die();
$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
jimport( 'joomla.application.component.model' );
include_once (JPATH_COMPONENT_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'translated.php');

$params = &JComponentHelper::getParams($option);
$default_order = $params->get('default_order');

class biblestudyModellandingpage extends JModel
{

	var $_total = null;
	var $_pagination = null;

	/**
	 * @desc From database
	 */
	var $_template;
	var $_admin;


	function __construct()
	{
		parent::__construct();
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
		$params =& $mainframe->getPageParameters();
		$t = JRequest::getInt('t','get');
		if (!$t){
			$t = 1;
		}
		jimport('joomla.html.parameter');
		$template = $this->getTemplate();

		// Convert parameter fields to objects.
		$registry = new JRegistry;
		$registry->loadJSON($template[0]->params);
		$params = $registry;

		$config = JFactory::getConfig();

		$this->setState('limit',$params->get('itemslimit'),'limit',$params->get('itemslimit'),'int');
		$this->setState('limitstart', JRequest::getVar('limitstart', 0, '', 'int'));

		// In case limit has been changed, adjust limitstart accordingly
		$this->setState('limitstart', ($this->getState('limit') != 0 ? (floor($this->getState('limitstart') / $this->getState('limit')) * $this->getState('limit')) : 0));
		// In case we are on more than page 1 of results and the total changes in one of the drop downs to a selection that has fewer in its total, we change limitstart
		if ($this->getTotal() < $this->getState('limitstart')) {
			$this->setState('limitstart', 0,'','int');
		}
	}

	function setSelect($string){

	}

	/**
	 * @desc Returns the query
	 * @return string The query to be used to retrieve the rows from the database
	 */
	function _buildQuery()
	{
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();
		$query = 'SELECT #__bsms_teachers.id AS tid, #__bsms_teachers.teachername, #__bsms_teachers.title AS teachertitle,'
		. ' #__bsms_series.id AS sid, #__bsms_series.series_text, #__bsms_series.description AS sdescription, #__bsms_series.series_thumbnail, #__bsms_message_type.id AS mid,'
		. ' #__bsms_message_type.message_type AS message_type, #__bsms_books.bookname,'
		. ' #__bsms_topics.id AS tp_id, #__bsms_topics.topic_text, #__bsms_topics.params AS topic_params,#__bsms_locations.id AS lid, #__bsms_locations.location_text'
		. ' FROM #__bsms_studies'
		. ' LEFT JOIN #__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)'
		. ' LEFT JOIN #__bsms_teachers ON (#__bsms_studies.teacher_id = #__bsms_teachers.id)'
		. ' LEFT JOIN #__bsms_series ON (#__bsms_studies.series_id = #__bsms_series.id)'
		. ' LEFT JOIN #__bsms_message_type ON (#__bsms_studies.messagetype = #__bsms_message_type.id)'
		. ' LEFT JOIN #__bsms_studytopics ON (#__bsms_studies.id = #__bsms_studytopics.study_id)'
		. ' LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studytopics.topic_id)'
		. ' LEFT JOIN #__bsms_locations ON (#__bsms_studies.location_id = #__bsms_locations.id)'
		. $where
		. $orderby
		;
		return $query;
	}

	/**
	 * @desc Returns teachers
	 * @return Array
	 */
	function getAdmin()
	{
		if (empty($this->_admin)) {
			$query = 'SELECT *'
			. ' FROM #__bsms_admin'
			. ' WHERE id = 1';
			$this->_admin = $this->_getList($query);
		}
		return $this->_admin;
	}


	function getTemplate() {
		if(empty($this->_template)) {
			$templateid = JRequest::getVar('t',1,'get', 'int');
			$query = 'SELECT *'
			. ' FROM #__bsms_templates'
			. ' WHERE published = 1 AND id = '.$templateid;
			$this->_template = $this->_getList($query);
		}
		return $this->_template;
	}

	/**
	 * Method to get the total number of studies items
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	/**
	 * Method to get a pagination object for the studies
	 *
	 * @access public
	 * @return integer
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_pagination;
	}

	function _buildContentWhere()
	{
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
		$params = &JComponentHelper::getParams($option);
		$default_order = $params->get('default_order');

		$where = array();
		$rightnow = date('Y-m-d H:i:s');
		$where[] = ' #__bsms_studies.published = 1';
		$where[] = " date_format(#__bsms_studies.studydate, '%Y-%m-%d %T') <= ".(int)$rightnow;

		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );

		return $where;
	}

	function _buildContentOrderBy()
	{
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');

		$filter_orders		= $mainframe->getUserStateFromRequest( $option.'filter_orders',		'filter_orders',		'DESC',	'word' );

		if ($filter_orders == 'ASC'){
			$orderby 	= ' ORDER BY studydate ASC ';
		} else {
			$orderby 	= ' ORDER BY studydate DESC ';
		}

		return $orderby;
	}
}