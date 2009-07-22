<?php
defined('_JEXEC') or die();
global $mainframe, $option;
jimport( 'joomla.application.component.model' );

$params = &JComponentHelper::getParams($option);
$default_order = $params->get('default_order');

class biblestudyModelserieslist extends JModel
{

	var $_total = null;
	var $_pagination = null;
	
	/**
	 * @desc From database
	 */
	var $_data;
	var $_teachers;
	var $_series;
	var $_messageTypes;
	var $_studyYears;
	var $_locations;
	var $_topics;
	var $_orders;
	var $_select;
	var $_books;
	var $_template;
	var $_admin;
	

	function __construct()
	{
		parent::__construct();
		global $mainframe, $option;
		$params 			=& $mainframe->getPageParameters();
		$templatemenuid = $params->get('templatemenuid');
		if (!$templatemenuid){$templatemenuid = 1;}
		JRequest::setVar( 'templatemenuid', $templatemenuid, 'get');
		
		$template = $this->getTemplate();
		$params = new JParameter($template[0]->params);
		
		$config = JFactory::getConfig();
		$this->setState('limit',$params->get('serieslimit'),'limit',$params->get('serieslimit'),'int');
		$this->setState('limitstart', JRequest::getVar('limitstart', 0, '', 'int'));

		// In case limit has been changed, adjust limitstart accordingly
		$this->setState('limitstart', ($this->getState('limit') != 0 ? (floor($this->getState('limitstart') / $this->getState('limit')) * $this->getState('limit')) : 0));
		// In case we are on more than page 1 of results and the total changes in one of the drop downs to a selection that has fewer in its total, we change limitstart
		if ($this->getTotal() < $this->getState('limitstart')) {$this->setState('limitstart', 0,'','int');}
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
		$query = 'SELECT se.*, t.id AS tid, t.teachername, t.title AS teachertitle, t.thumb, t.thumbh, t.thumbw, t.teacher_thumbnail'
		. ' FROM #__bsms_series AS se'
/*				$query = 'SELECT #__bsms_studies.*, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername, #__bsms_teachers.title AS teachertitle,'
		. ' #__bsms_series.id AS sid, #__bsms_series.series_text, #__bsms_series.description AS sdescription, #__bsms_series.series_thumbnail, #__bsms_message_type.id AS mid,'
		. ' #__bsms_message_type.message_type AS message_type, #__bsms_books.bookname,'
		. ' #__bsms_topics.id AS tp_id, #__bsms_topics.topic_text, #__bsms_locations.id AS lid, #__bsms_locations.location_text'
		. ' FROM #__bsms_series'
		. ' LEFT JOIN #__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)' */
		. ' LEFT JOIN #__bsms_teachers AS t ON (se.teacher = t.id)'
//		. ' LEFT JOIN #__bsms_series ON (#__bsms_studies.series_id = #__bsms_series.id)'
//		. ' LEFT JOIN #__bsms_message_type ON (#__bsms_studies.messagetype = #__bsms_message_type.id)'
//		. '	LEFT JOIN #__bsms_topics ON (#__bsms_studies.topics_id = #__bsms_topics.id)'
//		. ' LEFT JOIN #__bsms_locations ON (#__bsms_studies.location_id = #__bsms_locations.id)'
		//. //$where
		//. $orderby
		; //dump ($query, 'query: ');
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
	
	
	function getOrders() {
		if (empty($this->_Orders)) {
			$query = ' SELECT * FROM #__bsms_order '
			. ' ORDER BY id ';
			$this->_Orders = $this->_getList($query);
		}
		return $this->_Orders;
	}



function getTemplate() {
		if(empty($this->_template)) {
			$templateid = JRequest::getVar('templatemenuid',1,'get', 'int');
			//dump ($templateid, 'templateid: ');
			$query = 'SELECT *'
			. ' FROM #__bsms_templates'
			. ' WHERE published = 1 AND id = '.$templateid;
			$this->_template = $this->_getList($query);
			//dump ($this->_template, 'this->_template');
		}
		return $this->_template;
	}
		
	function getData()
	{
		global $mainframe;
		//$params =& $mainframe->getPageParameters();
		//dump($data, 'Data from Model');
		// Lets load the data if it doesn't already exist
		if (empty( $this->_data ))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList( $query, $this->getState('limitstart'), $this->getState('limit') );
			//$this->_data = $this->_getList( $query, $this->getState('limitstart'), $params->get('itemslimit') );
		}

		return $this->_data;
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
			//dump ($this->getState('limitstart'), 'limitstart: ');
			
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
		global $mainframe, $option;
		$params = &JComponentHelper::getParams($option);
		$default_order = $params->get('default_order');
		$filter_series		= $mainframe->getUserStateFromRequest( $option.'filter_series',		'filter_series',		0,				'int' );
		$filter_orders		= $mainframe->getUserStateFromRequest( $option.'filter_orders',		'filter_orders',		'DESC',				'word' );


		$where = array();
		//$rightnow = date('Y-m-d H:i:s');
		$where[] = ' #__bsms_series.published = 1';
		//$where[] = " date_format(#__bsms_studies.studydate, '%Y-%m-%d %T') <= ".(int)$rightnow;

		if ($filter_series > 0) {
			$where[] = ' #__bsms_series.id = '.(int) $filter_series;
		}
		
		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );

		$where2 = array();
		$continue = 0;
		
		if ($params->get('mult_series')) 
			{ 
				if (!$filter_series)
				{
					$continue = 1;
					$filters = null;
					$filters = explode(",", $params->get('mult_series'));
					//dump ($filters, 'filters: ');
					foreach ($filters AS $filter)
						{
							$where2[] = '#__bsms_studies.series_id = '.(int)$filter;
							//dump ($where2, 'where2: ');
						}
					if ($params->get('series_id')) {$where2[] = '#__bsms_studies.series_id = '.$params->get('series_id');}
				}
			}
			
			
		$where2 		= ( count( $where2 ) ? ' '. implode( ' OR ', $where2 ) : '' );

		if ($continue > 0) {$where = $where.' AND ( '.$where2.')';}
		//dump ($where, 'where: ');
		return $where;
	}
	
	
	
	function _buildContentOrderBy()
	{
		global $mainframe, $option;

		$filter_orders		= $mainframe->getUserStateFromRequest( $option.'filter_orders',		'filter_orders',		'ASC',	'word' );

		if ($filter_orders == 'ASC'){
			$orderby 	= ' ORDER BY series_text ASC ';
		} else {
			$orderby 	= ' ORDER BY series_text DESC ';
		}

		return $orderby;
	}
}
?>