<?php
defined('_JEXEC') or die();
$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
jimport( 'joomla.application.component.model' );

$params = &JComponentHelper::getParams($option);
$default_order = $params->get('default_order');
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

class biblestudyModelstudieslist extends modelClass {

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
		$config['table_path'] = JPATH_COMPONENT.DS.'tables';    // use site tables
		parent::__construct($config);
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
		$params 			=& $mainframe->getPageParameters();
        
		$t = $params->get('t');
		if (!$t){$t = 1;}
		JRequest::setVar( 't', $t, 'get');
		
		$template = $this->getTemplate();
        jimport('joomla.html.parameter');
		$params = new JParameter($template[0]->params);
        
		$this->_params = $params;
		$config = JFactory::getConfig();
		// Get the pagination request variables
		
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
		
		
		$query = 'SELECT #__bsms_studies.*, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername,'
			  . ' #__bsms_series.id AS sid, #__bsms_series.series_text, #__bsms_series.description AS sdescription, '
			  . ' #__bsms_message_type.id AS mid,'
			  . ' #__bsms_message_type.message_type AS message_type, #__bsms_books.bookname,'
			  . ' #__bsms_locations.id AS lid, #__bsms_locations.location_text,'
			  . ' group_concat(#__bsms_topics.id separator ", ") AS tp_id, group_concat(#__bsms_topics.topic_text separator ", ") as topic_text, sum(#__bsms_mediafiles.plays) AS totalplays, sum(#__bsms_mediafiles.downloads) AS totaldownloads, #__bsms_mediafiles.study_id'
			  . ' FROM #__bsms_studies'
			  . ' left join #__bsms_studytopics ON (#__bsms_studies.id = #__bsms_studytopics.study_id)'
			  . ' LEFT JOIN #__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)'
			  . ' LEFT JOIN #__bsms_teachers ON (#__bsms_studies.teacher_id = #__bsms_teachers.id)'
			  . ' LEFT JOIN #__bsms_series ON (#__bsms_studies.series_id = #__bsms_series.id)'
			  . ' LEFT JOIN #__bsms_message_type ON (#__bsms_studies.messagetype = #__bsms_message_type.id)'
			  . ' LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studytopics.topic_id)'
			  . ' LEFT JOIN #__bsms_locations ON (#__bsms_studies.location_id = #__bsms_locations.id)'
              . ' LEFT JOIN #__bsms_mediafiles ON (#__bsms_studies.id = #__bsms_mediafiles.study_id)'
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
		  //get parameters
          $booklist = $this->_params->get('booklist'); 
          if ($booklist == 1)
          {
            $query = 'SELECT DISTINCT s.booknumber AS value, s.published AS spublished, b.id, b.booknumber AS bbooknumber, b.bookname AS text FROM #__bsms_studies AS s LEFT JOIN #__bsms_books AS b ON ( b.booknumber = s.booknumber ) WHERE s.published =1 AND b.id IS NOT NULL ORDER BY bbooknumber';
          }
        else
        {
      		$query = 'SELECT id, booknumber AS value, bookname AS text, published'
            . ' FROM #__bsms_books'
            . ' WHERE published = 1'
            . ' ORDER BY booknumber';
        }	
  
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
			$templateid = JRequest::getVar('t',1,'get', 'int');
			$query = 'SELECT *'
			. ' FROM #__bsms_templates'
			. ' WHERE published = 1 AND id = '.$templateid;
			$this->_template = $this->_getList($query);
		}
		return $this->_template;
	}

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
    		
	function getData()
	{
		$mainframe =& JFactory::getApplication();
		// Lets load the data if it doesn't already exist
		if (empty( $this->_data ))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList( $query, $this->getState('limitstart'), $this->getState('limit') );
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
		$params = &JComponentHelper::getParams($option); //dump ($params, 'params: ');
        $params = $this->_params;
       	
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
			$chb = JRequest::getInt('minChapt','','post');
			$che = JRequest::getInt('maxChapt','','post');
			if ($chb && $che)
			{
				$where[] = ' (#__bsms_studies.booknumber = '.(int) $filter_book.' AND ((#__bsms_studies.chapter_begin <='.$che.' AND #__bsms_studies.chapter_end >= '.$chb.')))';
			}
			elseif ($chb) {
				$where[] = ' (#__bsms_studies.booknumber = '.(int) $filter_book.' AND ((#__bsms_studies.chapter_end >= '.$chb.')))';
			}
			elseif ($che) {
				$where[] = ' (#__bsms_studies.booknumber = '.(int) $filter_book.' AND ((#__bsms_studies.chapter_begin <='.$che.')))';
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
		
        //Check for authorization to view the study
        $user	= JFactory::getUser();
        $groups	= implode(',', $user->getAuthorisedViewLevels());

		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
        
        //These params are the filters set by the menu item, not in the JBS template
        $menubooks = null;
        $menuteacher = null;
        $menulocations = null;
        $menubooks = null;
        $menumessagetype = null;
        $menutopics = null;
        $menuseries = null;
        $menuitemid = JRequest::getInt( 'Itemid' );
          if ($menuitemid)
          {
            
            $menu = JSite::getMenu();
            $menuparams = $menu->getParams( $menuitemid );
            $menuteacher = $menuparams->get('mteacher_id');
            $menulocations = $menuparams->get('mlocations');
            $menubooks = $menuparams->get('mbooknumber');
            $menuseries = $menuparams->get('mseries_id');
            $menutopics = $menuparams->get('mtopic_id');
            $menumessagetype = $menuparams->get('mmessagetype');;
          }

        
        $where2 = array();
		$continue = 0;
		if (($params->get('teacher_id') || $menuteacher ) && !$filter_teacher) 
			{ 
				if ($menuteacher) {$filters = $menuteacher;}
					else {$filters = $params->get('teacher_id');}
					switch ($filters)
					{
						case is_array($filters) :
							foreach ($filters AS $filter)
								{
									if ($filter == -1)
										{
											break;
										}
									{
										$continue = 1;
										$where2[] = '#__bsms_studies.teacher_id = '.(int)$filter;
									}
								}
							break;
							
						case -1:
						break;
						
						default:
							$continue = 1;
							$where2[] = '#__bsms_studies.teacher_id = '.(int)$filters;
							break;
					}
				}
		if (($params->get('locations') || $menulocations)&& !$filter_location) 
			{ 
				if ($menulocations){$filters = $menulocations;}
					else {$filters = $params->get('locations');}
					switch ($filters)
					{
						case is_array($filters) :
							foreach ($filters AS $filter)
								{
									if ($filter == -1)
										{
											break;
										}
									{
										$continue = 1;
										$where2[] = '#__bsms_studies.location_id = '.(int)$filter;
									}
								}
							break;
							
						case -1:
						break;
						
						default:
							$continue = 1;
							$where2[] = '#__bsms_studies.location_id = '.(int)$filters;
							break;
					}
				} 	
		if (($params->get('booknumber') || $menubooks) && !$filter_book) 
			{ 
				if ($menubooks){$filters = $menubooks;}
					else {$filters = $params->get('booknumber');}
					switch ($filters)
					{
						case is_array($filters) :
							foreach ($filters AS $filter)
								{
									if ($filter == -1)
										{
											break;
										}
									{
										$continue = 1;
										$where2[] = '#__bsms_studies.booknumber = '.(int)$filter;
									}
								}
							break;
							
						case -1:
						break;
						
						default:
							$continue = 1;
							$where2[] = '#__bsms_studies.booknumber = '.(int)$filters;
							break;
					}
				}
		if (($params->get('series_id') || $menuseries) && !$filter_series) 
			{ 
				if ($menuseries) {$filters = $menuseries;}
					else {$filters = $params->get('series_id');}
					switch ($filters)
					{
						case is_array($filters) :
							foreach ($filters AS $filter)
								{
									if ($filter == -1)
										{
											break;
										}
									{
										$continue = 1;
										$where2[] = '#__bsms_studies.series_id = '.(int)$filter;
									}
								}
							break;
							
						case -1:
						break;
						
						default:
							$continue = 1;
							$where2[] = '#__bsms_studies.series_id = '.(int)$filters;
							break;
					}
				}
		if (($params->get('topic_id') || $menutopics) && !$filter_topic) 
			{ 
				if ($menutopics) {$filters = $menutopics;}
					else {$filters = $params->get('topic_id');}
					switch ($filters)
					{
						case is_array($filters) :
							foreach ($filters AS $filter)
								{
									if ($filter == -1)
										{
											break;
										}
									{
										$continue = 1;
										$where2[] = '#__bsms_studytopics.topic_id = '.(int)$filter;
										$where2[] = '#__bsms_studies.topics_id = '.(int)$filter; 
									}
								}
							break;
							
						case -1:
						break;
						
						default:
							$continue = 1;
							$where2[] = '#__bsms_studytopics.topic_id = '.(int)$filters;
							$where2[] = '#__bsms_studies.topics_id = '.(int)$filters;
							break;
					}
				}
		if (($params->get('messagetype') || $menumessagetype) && !$filter_messagetype) 
			{ 
				if ($menumessagetype){$filters = $menumessagetype;}
					else {$filters = $params->get('messagetype');}
					switch ($filters)
					{
						case is_array($filters) :
							foreach ($filters AS $filter)
								{
									if ($filter == -1)
										{
											break;
										}
									{
										$continue = 1;
										$where2[] = '#__bsms_studies.messagetype = '.(int)$filter;
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
		if ($continue > 0) {$where = $where.' AND ( '.$where2.')';}
		return $where;
	}
	
	
	
	function _buildContentOrderBy()
	{
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
		$params = &JComponentHelper::getParams($option);
		$filter_orders = '';

      if (!empty($_POST))
        {
            $filter_orders = $_POST['filter_orders'];
            if ($filter_orders)
            {
                $orderby 	= ' ORDER BY studydate '.$filter_orders.' '; 
            }
            else
            {
                $orderby = ' ORDER BY studydate '.$this->_params->get('default_order', 'DESC').' ';
            }
        }
      else
          {
            $orderby = ' ORDER BY studydate '.$this->_params->get('default_order', 'DESC').' ';
          } 
	return $orderby;
	}
    
     protected function getListQuery()
        {
                // Create a new query object.         
                $db = JFactory::getDBO();
                $query = $db->getQuery(true);
                // Select some fields
                $query = $this->_buildQuery();
                return $query;
        }
}