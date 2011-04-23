<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php 
class plgSearchBiblestudy extends JPlugin
{

    /**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
     * based on plg_weblinks
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
	//	$this->loadLanguage();
	}

	/**
	 * @return array An array of search areas
	 */
	function onContentSearchAreas() {
		static $areas = array(
			'biblestudy' => 'PLG_SEARCH_BIBLESTUDY'
			);
			return $areas;
	}

/**
	 * Biblestudy Search method
	 *
	 * The sql must return the following fields that are used in a common display
	 * routine: 
	 * @param string Target search string
	 * @param string mathcing option, exact|any|all
	 * @param string ordering option, newest|oldest|popular|alpha|category
	 * @param mixed An array if the search it to be restricted to areas, null if search all
	 */
	function onContentSearch($text, $phrase='', $ordering='', $areas=null)
	{
		$db		= JFactory::getDbo();
		$app	= JFactory::getApplication();
		$user	= JFactory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());

        
		$searchText = $text;

		if (is_array($areas)) {
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas()))) {
				return array();
			}
		}
    
    $state = array();
		if ($sContent) {
			$state[]=1;
		}
		if ($sArchived) {
			$state[]=2;
		}
        
    $text = trim($text);
		if ($text == '') {
			return array();
		}
		$section	= JText::_('PLG_SEARCH_BIBLESTUDY');

		$wheres	= array();
		switch ($phrase)
		{
			case 'exact':
				$text		= $db->Quote('%'.$db->getEscaped($text, true).'%', false);
				$wheres2	= array();
				$wheres2[]	= 'a.url LIKE '.$text;
				$wheres2[]	= 'a.description LIKE '.$text;
				$wheres2[]	= 'a.title LIKE '.$text;
				$where		= '(' . implode(') OR (', $wheres2) . ')';
				break;

			case 'all':
			case 'any':
			default:
				$words	= explode(' ', $text);
				$wheres = array();
				foreach ($words as $word)
				{
					$word		= $db->Quote('%'.$db->getEscaped($word, true).'%', false);
					$wheres2	= array();
					$wheres2[]	= 'a.studytext LIKE '.$word;
					$wheres2[]	= 'a.studyintro LIKE '.$word;
					$wheres2[]	= 'a.teachername LIKE '.$word;
                    $wheres2[]	= 'a.bookname LIKE '.$word;
                    $wheres2[]	= 'a.series_text LIKE '.$word;
                    $wheres2[]	= 'a.topic_text LIKE '.$word;
					$wheres[]	= implode(' OR ', $wheres2);
				}
				$where	= '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
				break;
		}
        
        switch ($ordering) {
	case 'oldest':
		$order = 'a.studydate ASC';
		break;
	case 'alpha':
		$order = 'a.studytitle ASC';
		break;
	case 'newest':
	default:
		$order = 'a.studydate DESC';
		break;
   }
   if (!empty($state)) {
			$query	= $db->getQuery(true);
            $set_title = $this->params->get('set_title');
            $template = JRequest::getInt('t','1','get');
switch ($set_title)
	{
		case 0 :
		 
			if ($this->params->get('show_description') > 0){
			$query->select( "SELECT CONCAT(#__bsms_books.bookname,' ',#__bsms_studies.chapter_begin) AS title, CONCAT(#__bsms_studies.studytitle,' - ',#__bsms_studies.studyintro) AS text, #__bsms_studies.studydate AS created, #__bsms_books.id AS bid, #__bsms_books.bookname, #__bsms_studies.id AS sid, #__bsms_studies.published AS spub, #__bsms_books.published AS bpub, #__bsms_series.id AS seriesid, #__bsms_series.series_text, #__bsms_topics.id AS tid, #__bsms_topics.topic_text, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername, #__bsms_studies.id as id, 'Bible Studies' AS section, CONCAT('index.php?option=com_biblestudy&view=studydetails&id=', #__bsms_studies.id,'&t=".$template."') AS href, '2' AS browsernav");
					
			}
			else {
			$query = "SELECT CONCAT(#__bsms_books.bookname,' ',#__bsms_studies.chapter_begin) AS title, #__bsms_studies.studytitle AS text, #__bsms_studies.studydate AS created, #__bsms_books.id AS bid, #__bsms_books.bookname, #__bsms_studies.id AS sid, #__bsms_studies.published AS spub, #__bsms_books.published AS bpub, #__bsms_series.id AS seriesid, #__bsms_series.series_text, #__bsms_topics.id AS tid, #__bsms_topics.topic_text, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername, 'Bible Studies' AS section, CONCAT('index.php?option=com_biblestudy&view=studydetails&id=', #__bsms_studies.id,'&t=".$template."') AS href, '2' AS browsernav";
			}
			break;
		case 1 :
		
			if ($pluginParams->get('show_description') > 0){
			$query = "SELECT CONCAT(#__bsms_studies.studytitle,' - ',#__bsms_studies.studyintro) AS title, CONCAT(#__bsms_books.bookname,' ',#__bsms_studies.chapter_begin) AS text, #__bsms_studies.studydate AS created, #__bsms_books.id AS bid, #__bsms_books.bookname, #__bsms_studies.id AS sid, #__bsms_studies.published AS spub, #__bsms_books.published AS bpub, #__bsms_series.id AS seriesid, #__bsms_series.series_text, #__bsms_topics.id AS tid, #__bsms_topics.topic_text, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername, #__bsms_studies.id as id, 'Bible Studies' AS section, CONCAT('index.php?option=com_biblestudy&view=studydetails&id=', #__bsms_studies.id,'&t=".$template."') AS href, '2' AS browsernav";
			}
			else {
			$query = "SELECT CONCAT(#__bsms_books.bookname,' ',#__bsms_studies.chapter_begin) AS text, #__bsms_studies.studytitle AS title, #__bsms_studies.studydate AS created, #__bsms_books.id AS bid, #__bsms_books.bookname, #__bsms_studies.id AS sid, #__bsms_studies.published AS spub, #__bsms_books.published AS bpub, #__bsms_series.id AS seriesid, #__bsms_series.series_text, #__bsms_topics.id AS tid, #__bsms_topics.topic_text, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername, #__bsms_studies.id as id, 'Bible Studies' AS section, CONCAT('index.php?option=com_biblestudy&view=studydetails&id=', #__bsms_studies.id,'&t=".$template."') AS href, '2' AS browsernav";
			}
		break;
		}
    $query->from(' #__bsms_studies as a');
    $query->join('LEFT','#__bsms_books ON (#__bsms_books.booknumber = #__bsms_studies.booknumber)');
    $query->join('LEFT','#__bsms_series ON (#__bsms_series.id = #__bsms_studies.series_id)');
    $query->join('LEFT','#__bsms_topics ON (#__bsms_topics.id = #__bsms_studies.topics_id)');
    $query->join('LEFT','#__bsms_teachers ON (#__bsms_teachers.id = #__bsms_studies.teacher_id)');
    $query->where('('.$where.')' . ' AND a.state in ('.implode(',',$state).') AND  a.published=1 AND  a.access IN ('.$groups.')');
    $query->order($order);
    
    $db->setQuery($query, 0, $limit);
	$rows = $db->loadObjectList();
    return $rows;
    }
}

/*
?>
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

$set_title = $this->params->get('set_title');
$template = JRequest::getInt('t','1','get');
switch ($set_title)
	{
		case 0 :
		 
			if ($this->params->get('show_description') > 0){
			$query = "SELECT CONCAT(#__bsms_books.bookname,' ',#__bsms_studies.chapter_begin) AS title, CONCAT(#__bsms_studies.studytitle,' - ',#__bsms_studies.studyintro) AS text, #__bsms_studies.studydate AS created, #__bsms_books.id AS bid, #__bsms_books.bookname, #__bsms_studies.id AS sid, #__bsms_studies.published AS spub, #__bsms_books.published AS bpub, #__bsms_series.id AS seriesid, #__bsms_series.series_text, #__bsms_topics.id AS tid, #__bsms_topics.topic_text, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername, #__bsms_studies.id as id,"
					. "\n 'Bible Studies' AS section,"
					. "\n CONCAT('index.php?option=com_biblestudy&view=studydetails&id=', #__bsms_studies.id,'&t=".$template."&Itemid=".$item."') AS href,"
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
*/	
} // end of class