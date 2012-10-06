<?php defined('_JEXEC') or die('Restriced Access'); ?>
<?php
if(class_exists('modbiblestudyhelper')){return;}
		
class modBiblestudyHelper
{
	var $_template;
	var $_admin;
	function getLatest($params)
	{
		$items = $params->get('moduleitems', 1);
		
		$db =& JFactory::getDBO();
		$teacher = $params->get('teacher_id', 1);
		$topic = $params->get('topic_id', 1);
		$book = $params->get('booknumber', 101);
		$series = $params->get('series_id', 1);
		$locations = $params->get('locations');
		$condition = $params->get('condition', 'OR');
		$messagetype_menu = $params->get('messagetype');
		if($condition > 0){
			$condition = ' AND ';
			}
		else {
			$condition = ' OR ';
			}
		$where = array();
		
		$where[] = ' #__bsms_studies.published = 1';

		if ($teacher > 0) {
			$where[] = ' #__bsms_studies.teacher_id = '.(int) $teacher;
		}
		if ($book > 0) {
			$where[] = ' #__bsms_studies.booknumber = '.(int) $book;
		}
		if ($series > 0) {
			$where[] = ' #__bsms_studies.series_id = '.(int) $series;
		}
		if ($topic > 0) {
			$where[] = ' #__bsms_studies.topics_id = '.(int) $topic;
		}
		if ($locations > 0) {
			$where[] = ' #__bsms_studies.location_id = '.(int) $locations;
		}
		if ($messagetype_menu > 0) {
			$where[] = ' #__bsms_studies.messagetype = '.(int) $messagetype_menu;
		}
		$user =& JFactory::getUser();
		$level_user = $user->get('gid');
		$where[] = ' #__bsms_studies.show_level <= '.$level_user;	

		$where 		= ( count( $where ) ? ' WHERE '. implode( $condition, $where ) : '' );

$where2 = array();
		$continue = 0;
		if ($params->get('mult_teachers')) 
			{ 
				if (!$filter_teacher)
				{
					$continue = 1;
					$filters = explode(",", $params->get('mult_teachers'));
					foreach ($filters AS $filter)
						{
							$where2[] = '#__bsms_studies.teacher_id = '.(int)$filter;
						}
				}
			}
		
		if ($params->get('mult_locations')) 
			{ 
				if (!$filter_location)
				{
					$continue = 1;
					$filters = null;
					$filters = explode(",", $params->get('mult_locations'));
					foreach ($filters AS $filter)
						{
							$where2[] = '#__bsms_studies.location_id = '.(int)$filter;
						}
				}
			}
			
		if ($params->get('mult_books')) 
			{ 
				if (!$filter_book)
				{
					$continue = 1;
					$filters = null;
					$filters = explode(",", $params->get('mult_books'));
					foreach ($filters AS $filter)
						{
							$where2[] = '#__bsms_studies.booknumber = '.(int)$filter;
						}
				}
			}
		
		if ($params->get('mult_series')) 
			{ 
				if (!$filter_series)
				{
					$continue = 1;
					$filters = null;
					$filters = explode(",", $params->get('mult_series'));
					foreach ($filters AS $filter)
						{
							$where2[] = '#__bsms_studies.series_id = '.(int)$filter;
						}
				}
			}
			
		if ($params->get('mult_topics')) 
			{ 
				if (!$filter_topic) 
				{
					$continue = 1;
					$filters = null;
					$filters = explode(",", $params->get('mult_topics'));
					foreach ($filters AS $filter)
						{
							$where2[] = '#__bsms_studies.topics_id = '.(int)$filter;
						}
				}
			}
			
		if ($params->get('mult_messagetype')) 
			{ 
				if (!$filter_messagetype)
				{
					$continue = 1;
					$filters = null;
					$filters = explode(",", $params->get('mult_messagetype'));
					foreach ($filters AS $filter)
						{
							$where2[] = '#__bsms_studies.messagetype = '.(int)$filter;
						}
				}
			}
			
		$where2 		= ( count( $where2 ) ? ' '. implode( ' OR ', $where2 ) : '' );

		if ($continue > 0) {$where = $where.' AND ( '.$where2.')';}
		
		$query = 'SELECT #__bsms_studies.*, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername, #__bsms_teachers.title AS teachertitle,'
			. ' #__bsms_series.id AS sid, #__bsms_series.series_text, #__bsms_message_type.id AS mid,'
			. ' #__bsms_message_type.message_type AS message_type, #__bsms_books.bookname AS bname,'
			. ' #__bsms_topics.id AS tp_id, #__bsms_topics.topic_text, #__bsms_locations.id AS lid, #__bsms_locations.location_text'
			. ' FROM #__bsms_studies'
			. ' LEFT JOIN #__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)'
			. ' LEFT JOIN #__bsms_teachers ON (#__bsms_studies.teacher_id = #__bsms_teachers.id)'
			. ' LEFT JOIN #__bsms_series ON (#__bsms_studies.series_id = #__bsms_series.id)'
			. ' LEFT JOIN #__bsms_message_type ON (#__bsms_studies.messagetype = #__bsms_message_type.id)'
			. '	LEFT JOIN #__bsms_topics ON (#__bsms_studies.topics_id = #__bsms_topics.id)'
			. ' LEFT JOIN #__bsms_locations ON (#__bsms_studies.location_id = #__bsms_locations.id)'
			. $where 
			. ' ORDER BY #__bsms_studies.studydate DESC ';
		$db->setQuery( $query, 0, $items );
		$rows = $db->loadObjectList();
		//dump ($rows, 'rows: ');
		return $rows;
	}
	function _buildContentWhere()
	{
		
	
		//return $where;
		//return $this->where;
	}
	
	function getTemplate($params) {
		$db =& JFactory::getDBO();
		//if(empty($this->_template)) {
			$templateid = $params->get('modulemenuid', 1);
			//$templateid = JRequest::getVar('templatemenuid',1,'get', 'int');
			//dump ($templateid, 'templateid: ');
			$query = 'SELECT *'
			. ' FROM #__bsms_templates'
			. ' WHERE published = 1 AND id = '.$templateid;
			$db->setQuery($query);
			$template = $db->loadObjectList();
			//$this->_template = $this->_getList($query);
			//dump ($this->_template, 'this->_template');
		//}
		return $template;
	}	
	
	 function getAdmin()
	{
		//if (empty($this->_admin)) {
			$db =& JFactory::getDBO();
			$query = 'SELECT *'
			. ' FROM #__bsms_admin'
			. ' WHERE id = 1';
			$db->setQuery($query);
			$admin = $db->loadObjectList();
		//}
		return $admin;
	}
	
	function renderStudy(&$study, &$params)
	{
		require(JModuleHelper::getLayoutPath('mod_biblestudy', '_study'));
	}
}
	
?>