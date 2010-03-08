<?php
defined('_JEXEC') or die();
global $mainframe, $option;
jimport( 'joomla.application.component.model' );

$params = &JComponentHelper::getParams($option);
$default_order = $params->get('default_order');

class biblestudyModelstudieslist extends JModel
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
		//$params =& $mainframe->getPageParameters();
		$params 			=& $mainframe->getPageParameters();
		$templatemenuid = $params->get('templatemenuid');
		if (!$templatemenuid){$templatemenuid = 1;}
		JRequest::setVar( 'templatemenuid', $templatemenuid, 'get');
		//JRequest::setVar( 'templatemenuid', $params->get('templatemenuid'), 'get');
		
		$template = $this->getTemplate();
		$params = new JParameter($template[0]->params);
		
		//dump ($params, 'params: ');
		$config = JFactory::getConfig();
		// Get the pagination request variables
		//$limitstart = JRequest::getVar('limitstart', 0, '', 'int');
		//$this->setState('limit', $mainframe->getUserStateFromRequest('com_biblestudy.limit', 'limit', $config->getValue('config.list_limit'), 'int'));
		//$this->setState('limit', $mainframe->getUserStateFromRequest('com_biblestudy.limit', 'limit', $params->get('items'), 'int'));
		$this->setState('limit',$params->get('itemslimit'),'limit',$params->get('itemslimit'),'int');
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
		/*
		$query = 'SELECT #__bsms_studies.*, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername, #__bsms_teachers.title AS teachertitle,'
		. ' #__bsms_series.id AS sid, #__bsms_series.series_text, #__bsms_series.description AS sdescription, #__bsms_series.series_thumbnail, #__bsms_message_type.id AS mid,'
		. ' #__bsms_message_type.message_type AS message_type, #__bsms_books.bookname,'
		. ' #__bsms_topics.id AS tp_id, #__bsms_topics.topic_text, #__bsms_locations.id AS lid, #__bsms_locations.location_text'
		. ' FROM #__bsms_studies'
		. ' LEFT JOIN #__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)'
		. ' LEFT JOIN #__bsms_teachers ON (#__bsms_studies.teacher_id = #__bsms_teachers.id)'
		. ' LEFT JOIN #__bsms_series ON (#__bsms_studies.series_id = #__bsms_series.id)'
		. ' LEFT JOIN #__bsms_message_type ON (#__bsms_studies.messagetype = #__bsms_message_type.id)'
		. '	LEFT JOIN #__bsms_topics ON (#__bsms_studies.topics_id = #__bsms_topics.id)'
		. ' LEFT JOIN #__bsms_locations ON (#__bsms_studies.location_id = #__bsms_locations.id)'
		. $where
		. $orderby
		;
		*/
		
		$query = 'SELECT #__bsms_studies.*, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername,'
			  . ' #__bsms_series.id AS sid, #__bsms_series.series_text, #__bsms_series.description AS sdescription, '
			  . ' #__bsms_message_type.id AS mid,'
			  . ' #__bsms_message_type.message_type AS message_type, #__bsms_books.bookname,'
			  . ' #__bsms_locations.id AS lid, #__bsms_locations.location_text,'
			  . ' group_concat(#__bsms_topics.id separator ", ") AS tp_id, group_concat(#__bsms_topics.topic_text separator ", ") as topic_text'
			  . ' FROM #__bsms_studies'
			  . ' left join #__bsms_studytopics ON (#__bsms_studies.id = #__bsms_studytopics.study_id)'
			  . ' LEFT JOIN #__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)'
			  . ' LEFT JOIN #__bsms_teachers ON (#__bsms_studies.teacher_id = #__bsms_teachers.id)'
			  . ' LEFT JOIN #__bsms_series ON (#__bsms_studies.series_id = #__bsms_series.id)'
			  . ' LEFT JOIN #__bsms_message_type ON (#__bsms_studies.messagetype = #__bsms_message_type.id)'
			  . ' LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studytopics.topic_id)'
			  . ' LEFT JOIN #__bsms_locations ON (#__bsms_studies.location_id = #__bsms_locations.id)'
			  . $where
			  . ' GROUP BY #__bsms_studies.id'
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
	
	function getTeachers() {
		if (empty($this->_teachers)) {
			$query = 'SELECT id AS value, teachername AS text, published'
			. ' FROM #__bsms_teachers'
			. ' WHERE published = 1'
			. ' ORDER BY teachername ASC';
			$this->_teachers = $this->_getList($query);
		}
		return $this->_teachers;
	}

	/**
	 * @desc Returns teachers
	 * @return Array
	 */
	function getSeries() {
		if(empty($this->_series)) {
			$query = 'SELECT id AS value, series_text AS text, published'
			. ' FROM #__bsms_series'
			. ' WHERE published = 1'
			. ' ORDER BY series_text ASC';
			$this->_series = $this->_getList($query);
		}
		return $this->_series;
	}

	/**
	 * @desc Returns message types
	 * @return Array
	 */
	function getMessageTypes() {
		if (empty($this->_messageTypes)) {
			$query = 'SELECT id AS value, message_type AS text, published'
			. ' FROM #__bsms_message_type'
			. ' WHERE published = 1'
			. ' ORDER BY message_type ASC';
			$this->_messageTypes = $this->_getList($query);
		}
		return $this->_messageTypes;
	}

	/**
	 * @desc Returns years where studies exist
	 * @return Array
	 */
	function getStudyYears() {
		if (empty($this->_StudyYears)) {
			$query = " SELECT DISTINCT date_format(studydate, '%Y') AS value, date_format(studydate, '%Y') AS text "
			. ' FROM #__bsms_studies '
			. ' ORDER BY value DESC';
			$this->_StudyYears = $this->_getList($query);
		}
		return $this->_StudyYears;
	}

	/**
	 * @desc Returns the locations
	 * @return Array
	 */
	function getLocations() {
		if (empty($this->_Locations)) {
			$query = ' SELECT id AS value, location_text AS text, published FROM #__bsms_locations WHERE published = 1'
			. ' ORDER BY location_text ASC ';
			$this->_Locations = $this->_getList($query);
		}
		return $this->_Locations;
	}

	/**
	 * @desc Returns message types
	 * @return Array
	 */
	function getTopics() {
		if (empty($this->_Topics)) {
		    /*
			$query = 'SELECT DISTINCT #__bsms_studies.id, #__bsms_studies.topics_id AS value, #__bsms_topics.topic_text AS text, #__bsms_topics.published'
			. ' FROM #__bsms_studies'
			. ' LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studies.topics_id)'
			. ' WHERE #__bsms_topics.published = 1'
			. ' ORDER BY #__bsms_topics.topic_text ASC';
			*/
            $query = 'SELECT DISTINCT #__bsms_studytopics.topic_id as id, #__bsms_studytopics.topic_id AS value, #__bsms_topics.topic_text AS text'
            . ' FROM #__bsms_studytopics'
            . ' LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studytopics.topic_id)'
            . ' WHERE #__bsms_topics.published = 1'
            . ' ORDER BY #__bsms_topics.topic_text ASC';

			$this->_Topics = $this->_getList($query);
		} 
		return $this->_Topics;
	}

	/**
	 * @desc Returns Orders
	 * @return Array
	 */
	function getOrders() {
		if (empty($this->_Orders)) {
			$query = ' SELECT * FROM #__bsms_order '
			. ' ORDER BY id ';
			$this->_Orders = $this->_getList($query);
		}
		return $this->_Orders;
	}

function getBooks() {
		if (empty($this->_Books)) {
			$query = 'SELECT id, booknumber AS value, bookname AS text, published'
  . ' FROM #__bsms_books'
  . ' WHERE published = 1'
  . ' ORDER BY booknumber';
			$this->_Books = $this->_getList($query);
		}
		return $this->_Books;
	}
/**
	 * @desc Returns the Template to display the list
	 * @return Array
	 */
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
		$filter_topic		= $mainframe->getUserStateFromRequest( $option.'filter_topic',		'filter_topic',		0,		'int' );
		$filter_book		= $mainframe->getUserStateFromRequest( $option.'filter_book',		'filter_book',		0,		'int' );
		$filter_chapter		= $mainframe->getUserStateFromRequest( $option.'filter_chapter',	'filter_chapter',	0,		'int' );
		$filter_teacher		= $mainframe->getUserStateFromRequest( $option.'filter_teacher',	'filter_teacher',	0,		'int' );
		$filter_series		= $mainframe->getUserStateFromRequest( $option.'filter_series',		'filter_series',	0,		'int' );
		$filter_messagetype	= $mainframe->getUserStateFromRequest( $option.'filter_messagetype','filter_messagetype',	0,	'int' );
		$filter_year		= $mainframe->getUserStateFromRequest( $option.'filter_year',		'filter_year',		0,		'int' );
		$filter_location	= $mainframe->getUserStateFromRequest( $option.'filter_location', 	'filter_location', 	0, 		'int');
		$teacher_menu = $params->get('teacher_id', -1);
		$topic_menu = $params->get('topic_id', 1);
		$book_menu = $params->get('booknumber', 101);
		$series_menu = $params->get('series_id', 1);
		$messagetype_menu = $params->get('messagetype', 1);
		$location_menu = $params->get('locations', 1);
		$chapter_menu = $params->get('chapter', 1);
		$filter_orders		= $mainframe->getUserStateFromRequest( $option.'filter_orders',		'filter_orders',		'DESC',				'word' );

		$where = array();
		$rightnow = date('Y-m-d H:i:s');
		$where[] = ' #__bsms_studies.published = 1';
		$where[] = " date_format(#__bsms_studies.studydate, '%Y-%m-%d %T') <= ".(int)$rightnow;

		if ($filter_topic > 0) {
			$where[] = ' #__bsms_studytopics.topic_id = '.(int) $filter_topic;
		}
		if ($filter_location > 0) {
			$where[] = ' #__bsms_studies.location_id = '.(int) $filter_location;
		}
		if ($filter_book > 0) {
			$chb = JRequest::getInt('minChpt','','post');
			$che = JRequest::getInt('maxChpt','','post');
			if ($chb && $che)
			{
				$where[] = ' (#__bsms_studies.booknumber = '.(int) $filter_book.'AND (#__bsms_studies.chapter_begin >='.$chb.' AND #__bsms_studies.chapter_end <=.'$che.'))';
			}
			else
			{
				$where[] = ' #__bsms_studies.booknumber = '.(int) $filter_book;
			}
		}
		if ($filter_chapter > 0) {
			$where[] = ' #__bsms_studies.chapter_begin = '.(int) $filter_chapter;
		}
		if ($filter_teacher > 0) {
			$where[] = ' #__bsms_studies.teacher_id = '.(int) $filter_teacher;
		}
		if ($filter_series > 0) {
			$where[] = ' #__bsms_studies.series_id = '.(int) $filter_series;
		}
		if ($filter_messagetype > 0) {
			$where[] = ' #__bsms_studies.messagetype = '.(int) $filter_messagetype;
		}
		if ($filter_year > 0) {
			$where[] = " date_format(#__bsms_studies.studydate, '%Y')= ".(int) $filter_year;
		}
		
		//Added for user level control
		$user =& JFactory::getUser();
		$level_user = $user->get('gid');
		//$level_user = $user->usertype;
		//dump ($level_user, 'Level_user: ');
		$where[] = ' #__bsms_studies.show_level <= '.$level_user;

		//End for user level control

		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );

		$where2 = array();
		$continue = 0;
		if ($params->get('teacher_id')&& !$filter_teacher) 
			{ 
				
					$filters = $params->get('teacher_id'); //dump ($filters, 'filters: ');
					switch ($filters)
					{
						case is_array($filters) :
							foreach ($filters AS $filter)
								{
									if ($filter == -1)
										{
											//$continue = 0;
											break;
										}
									{
										$continue = 1;
										$where2[] = '#__bsms_studies.teacher_id = '.(int)$filter; //dump ($where2, 'where2: ');
									}
								}
							break;
							
						case -1:
							//$continue = 0;
						break;
						
						default:
							$continue = 1;
							$where2[] = '#__bsms_studies.teacher_id = '.(int)$filters;
							break;
					}
				}
		if ($params->get('locations')&& !$filter_location) 
			{ 
				
					$filters = $params->get('locations'); //dump ($filters, 'filters: ');
					switch ($filters)
					{
						case is_array($filters) :
							foreach ($filters AS $filter)
								{
									if ($filter == -1)
										{
											//$continue = 0;
											break;
										}
									{
										$continue = 1;
										$where2[] = '#__bsms_studies.location_id = '.(int)$filter; //dump ($where2, 'where2: ');
									}
								}
							break;
							
						case -1:
							//$continue = 0;
						break;
						
						default:
							$continue = 1;
							$where2[] = '#__bsms_studies.location_id = '.(int)$filters;
							break;
					}
				} 	
		if ($params->get('booknumber')&& !$filter_book) 
			{ 
				
					$filters = $params->get('booknumber'); //dump ($filters, 'filters: ');
					switch ($filters)
					{
						case is_array($filters) :
							foreach ($filters AS $filter)
								{
									if ($filter == -1)
										{
											//$continue = 0;
											break;
										}
									{
										$continue = 1;
										$where2[] = '#__bsms_studies.booknumber = '.(int)$filter; //dump ($where2, 'where2: ');
									}
								}
							break;
							
						case -1:
							//$continue = 0;
						break;
						
						default:
							$continue = 1;
							$where2[] = '#__bsms_studies.booknumber = '.(int)$filters;
							break;
					}
				}
		if ($params->get('series_id')&& !$filter_series) 
			{ 
				
					$filters = $params->get('series_id'); //dump ($filters, 'filters: ');
					switch ($filters)
					{
						case is_array($filters) :
							foreach ($filters AS $filter)
								{
									if ($filter == -1)
										{
											//$continue = 0;
											break;
										}
									{
										$continue = 1;
										$where2[] = '#__bsms_studies.series_id = '.(int)$filter; //dump ($where2, 'where2: ');
									}
								}
							break;
							
						case -1:
							//$continue = 0;
						break;
						
						default:
							$continue = 1;
							$where2[] = '#__bsms_studies.series_id = '.(int)$filters;
							break;
					}
				}
		if ($params->get('topic_id')&& !$filter_topic) 
			{ 
				
					$filters = $params->get('topic_id'); //dump ($filters, 'filters: ');
					switch ($filters)
					{
						case is_array($filters) :
							foreach ($filters AS $filter)
								{
									if ($filter == -1)
										{
											//$continue = 0;
											break;
										}
									{
										$continue = 1;
										$where2[] = '#__bsms_studytopics.topic_id = '.(int)$filter; //dump ($where2, 'where2: ');
										$where2[] = '#__bsms_studies.topics_id = '.(int)$filter; 
									}
								}
							break;
							
						case -1:
							//$continue = 0;
						break;
						
						default:
							$continue = 1;
							$where2[] = '#__bsms_studytopics.topic_id = '.(int)$filters;
							$where2[] = '#__bsms_studies.topics_id = '.(int)$filters;
							break;
					}
				}
		if ($params->get('messagetype')&& !$filter_messagetype) 
			{ 
				
					$filters = $params->get('messagetype'); //dump ($filters, 'filters: ');
					switch ($filters)
					{
						case is_array($filters) :
							foreach ($filters AS $filter)
								{
									if ($filter == -1)
										{
											//$continue = 0;
											break;
										}
									{
										$continue = 1;
										$where2[] = '#__bsms_studies.messagetype = '.(int)$filter; //dump ($where2, 'where2: ');
									}
								}
							break;
							
						case -1:
							//$continue = 0;
						break;
						
						default:
							$continue = 1;
							$where2[] = '#__bsms_studies.messagetype = '.(int)$filters;
							break;
					}
				}
					
		$where2 		= ( count( $where2 ) ? ' '. implode( ' OR ', $where2 ) : '' );
//dump ($where2, 'where2: ');
//dump ($continue, 'continue: ');
		if ($continue > 0) {$where = $where.' AND ( '.$where2.')';}
		//dump ($where, 'where: ');
		return $where;
	}
	
	
	
	function _buildContentOrderBy()
	{
		global $mainframe, $option;
		$params = &JComponentHelper::getParams($option);
		
		$filter_orders		= $mainframe->getUserStateFromRequest( $option.'filter_orders',		'filter_orders',		'word' );

		if ($filter_orders)
			{
				if ($filter_orders == 'ASC'){
					$orderby 	= ' ORDER BY studydate ASC ';
				} else {
					$orderby 	= ' ORDER BY studydate DESC ';
				}
			}
		if (!$filter_orders)
		{	
			if ($params->get('default_order'))
				{
					$orderby 	= ' ORDER BY studydate ASC ';
				} else {
					$orderby 	= ' ORDER BY studydate DESC ';
				}
		}
		return $orderby;
	}
}
?>