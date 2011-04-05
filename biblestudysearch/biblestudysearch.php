<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $mainframe->registerEvent( 'onSearch', 'botSearchBiblestudies' );
$mainframe->registerEvent( 'onSearchAreas', 'botSearchBiblestudiesAreas' );
	static $areas = array(
		'biblestudies' => 'Bible Studies'
	);
		return $areas;
}

function &botSearchBiblestudies ($text, $phrase='', $ordering='', $areas=null)
{
if (!$text) {
	return array();
	}
if (is_array( $areas )) {
	if (!array_intersect( $areas, array_keys( botSearchBiblestudiesAreas() ) )) {
		return array();
	}
}
$db =& JFactory::getDBO();

//Let's get the Itemid
$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
include_once($path1.'helper.php');
$admin_params = getAdminsettings();
$item = getItemidLink($isplugin=0, $admin_params); 
if ($phrase == 'exact'){
	$where = "(LOWER(studytitle) LIKE '%$text%')
	OR (LOWER(studytext) LIKE '%$text%')" .
"	OR (LOWER(studyintro) LIKE '%$text%')" .
"   OR (LOWER(teachername) LIKE '%text%')" .
"   OR (LOWER(bookname) LIKE '%text%')" .
"   OR (LOWER(series_text) LIKE '%text%')" .
"   OR (LOWER(topic_text) LIKE '%text%')";
}
else
	{
	$words = explode( ' ', $text );
	$wheres = array();
	foreach ($words as $word) {
		$wheres[] = "(LOWER(studytitle) LIKE '%$word%')
		OR (LOWER(studytext) LIKE '%$word%')" .
		" OR (LOWER(studyintro) LIKE '%$word%')".
		"   OR (LOWER(teachername) LIKE '%text%')" .
		"   OR (LOWER(bookname) LIKE '%text%')" .
		"   OR (LOWER(series_text) LIKE '%text%')" .
		"   OR (LOWER(topic_text) LIKE '%text%')";
}
if ($phrase == 'all')
{
	$separator = "AND";
}
else
{
	$separator = "OR";
}
$where = '(' . implode( ") $separator (" , $wheres ) . ')';
}
$where .= ' AND #__bsms_studies.published = 1';
switch ($ordering) {
	case 'oldest':
		$order = 'studydate ASC';
		break;
	case 'alpha':
		$order = 'studytitle ASC';
		break;
	case 'newest':
	default:
		$order = 'studydate DESC';
		break;
}
$plugin =& JPluginHelper::getPlugin('search', 'biblestudysearch');
$pluginParams = new JParameter( $plugin->params );
$set_title = $pluginParams->get('set_title');
$template = JRequest::getInt('templatemenuid','1','get');
switch ($set_title)
	{
		case 0 :
		 
			if ($pluginParams->get('show_description') > 0){
			$query = "SELECT CONCAT(#__bsms_books.bookname,' ',#__bsms_studies.chapter_begin) AS title, CONCAT(#__bsms_studies.studytitle,' - ',#__bsms_studies.studyintro) AS text, #__bsms_studies.studydate AS created, #__bsms_books.id AS bid, #__bsms_books.bookname, #__bsms_studies.id AS sid, #__bsms_studies.published AS spub, #__bsms_books.published AS bpub, #__bsms_series.id AS seriesid, #__bsms_series.series_text, #__bsms_topics.id AS tid, #__bsms_topics.topic_text, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername, #__bsms_studies.id as id,"
					. "\n 'Bible Studies' AS section,"
					. "\n CONCAT('index.php?option=com_biblestudy&view=studydetails&id=', #__bsms_studies.id,'&templatemenuid=".$template."&Itemid=".$item."') AS href,"
					. "\n '2' AS browsernav"
					. "\n FROM #__bsms_studies"
					. "\n LEFT JOIN #__bsms_books ON (#__bsms_books.booknumber = #__bsms_studies.booknumber)"
					. "\n LEFT JOIN #__bsms_series ON (#__bsms_series.id = #__bsms_studies.series_id)"
					. "\n LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studies.topics_id)"
					. "\n LEFT JOIN #__bsms_teachers ON (#__bsms_teachers.id = #__bsms_studies.teacher_id)"
					. "\n WHERE $where"
					. "\n ORDER by $order";
			}
			else {
			$query = "SELECT CONCAT(#__bsms_books.bookname,' ',#__bsms_studies.chapter_begin) AS title, #__bsms_studies.studytitle AS text, #__bsms_studies.studydate AS created, #__bsms_books.id AS bid, #__bsms_books.bookname, #__bsms_studies.id AS sid, #__bsms_studies.published AS spub, #__bsms_books.published AS bpub, #__bsms_series.id AS seriesid, #__bsms_series.series_text, #__bsms_topics.id AS tid, #__bsms_topics.topic_text, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername, "
					. "\n 'Bible Studies' AS section,"
					. "\n CONCAT('index.php?option=com_biblestudy&view=studydetails&id=', #__bsms_studies.id,'&templatemenuid=".$template."&Itemid=".$item."') AS href,"
					. "\n '2' AS browsernav"
					. "\n FROM #__bsms_studies"
					. "\n LEFT JOIN #__bsms_books ON (#__bsms_books.booknumber = #__bsms_studies.booknumber)"
					. "\n LEFT JOIN #__bsms_series ON (#__bsms_series.id = #__bsms_studies.series_id)"
					. "\n LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studies.topics_id)"
					. "\n LEFT JOIN #__bsms_teachers ON (#__bsms_teachers.id = #__bsms_studies.teacher_id)"
					. "\n WHERE $where"
					. "\n ORDER by $order";
			}
			break;
		case 1 :
		
			if ($pluginParams->get('show_description') > 0){
			$query = "SELECT CONCAT(#__bsms_studies.studytitle,' - ',#__bsms_studies.studyintro) AS title, CONCAT(#__bsms_books.bookname,' ',#__bsms_studies.chapter_begin) AS text, #__bsms_studies.studydate AS created, #__bsms_books.id AS bid, #__bsms_books.bookname, #__bsms_studies.id AS sid, #__bsms_studies.published AS spub, #__bsms_books.published AS bpub, #__bsms_series.id AS seriesid, #__bsms_series.series_text, #__bsms_topics.id AS tid, #__bsms_topics.topic_text, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername, #__bsms_studies.id as id,"
					. "\n 'Bible Studies' AS section,"
					. "\n CONCAT('index.php?option=com_biblestudy&view=studydetails&id=', #__bsms_studies.id,'&templatemenuid=".$template."&Itemid=".$item."') AS href,"
					. "\n '2' AS browsernav"
					. "\n FROM #__bsms_studies"
					. "\n LEFT JOIN #__bsms_books ON (#__bsms_books.booknumber = #__bsms_studies.booknumber)"
					. "\n LEFT JOIN #__bsms_series ON (#__bsms_series.id = #__bsms_studies.series_id)"
					. "\n LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studies.topics_id)"
					. "\n LEFT JOIN #__bsms_teachers ON (#__bsms_teachers.id = #__bsms_studies.teacher_id)"
					. "\n WHERE $where"
					. "\n ORDER by $order";
			}
			else {
			$query = "SELECT CONCAT(#__bsms_books.bookname,' ',#__bsms_studies.chapter_begin) AS text, #__bsms_studies.studytitle AS title, #__bsms_studies.studydate AS created, #__bsms_books.id AS bid, #__bsms_books.bookname, #__bsms_studies.id AS sid, #__bsms_studies.published AS spub, #__bsms_books.published AS bpub, #__bsms_series.id AS seriesid, #__bsms_series.series_text, #__bsms_topics.id AS tid, #__bsms_topics.topic_text, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername, #__bsms_studies.id as id,"
					. "\n 'Bible Studies' AS section,"
					. "\n CONCAT('index.php?option=com_biblestudy&view=studydetails&id=', #__bsms_studies.id,'&templatemenuid=".$template."&Itemid=".$item."') AS href,"
					. "\n '2' AS browsernav"
					. "\n FROM #__bsms_studies"
					. "\n LEFT JOIN #__bsms_books ON (#__bsms_books.booknumber = #__bsms_studies.booknumber)"
					. "\n LEFT JOIN #__bsms_series ON (#__bsms_series.id = #__bsms_studies.series_id)"
					. "\n LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studies.topics_id)"
					. "\n LEFT JOIN #__bsms_teachers ON (#__bsms_teachers.id = #__bsms_studies.teacher_id)"
					. "\n WHERE $where"
					. "\n ORDER by $order";
			}
		break;
		}
				
		$limit = $pluginParams->get( 'search_limit', 50);
		$db->setQuery( $query, 0, $limit );
		$rows = $db->loadObjectList();
		
		return $rows;
}	