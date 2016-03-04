<?php
/**
 * @package     BibleStudy
 * @subpackage  Search.BibleStudy
 * @copyright   2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        http://www.JoomlaBibleStudy.org
 *
 */
defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR . '/components/com_biblestudy/lib/defines.php';
/**
 * Plugin class for BibleStudy Search
 *
 * @package     BibleStudy
 * @subpackage  Search.BibleStudy
 * @since       7.0.2
 */
class PlgSearchBiblestudysearch extends JPlugin
{

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * Content Search Areas
	 *
	 * @return array An array of search areas
	 */
	public function onContentSearchAreas()
	{
		static $areas = array(
			'biblestudies' => 'JBS_PLG_SEARCH_BIBLESTUDYSEARCH'
		);

		return $areas;
	}

	/**
	 * Biblestudy Search method
	 *
	 * The sql must return the following fields that are used in a common display
	 * routine:
	 *
	 * @param   string  $text      Target search string
	 * @param   string  $phrase    mathcing option, exact|any|all
	 * @param   string  $ordering  ordering option, newest|oldest|popular|alpha|category
	 * @param   mixed   $areas     An array if the search it to be restricted to areas, null if search all
	 *
	 * @return array
	 */
	public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		$db        = JFactory::getDbo();
		$jinput    = JFactory::getApplication()->input;
		$user      = JFactory::getUser();
		$groups    = implode(',', $user->getAuthorisedViewLevels());
		$limit     = $this->params->def('search_limit');
		$sContent  = $this->params->get('search_content', 1);
		$sArchived = $this->params->get('search_archived', 1);
		$rows      = array();

		if (is_array($areas))
		{
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas())))
			{
				return array();
			}
		}

		$state = array();

		if ($sContent)
		{
			$state[] = 1;
		}
		if ($sArchived)
		{
			$state[] = 2;
		}

		$text = trim($text);

		if ($text == '')
		{
			return array();
		}

		switch ($phrase)
		{
			case 'exact':
				$text      = $db->Quote('%' . $db->escape($text) . '%', false);
				$wheres2   = array();
				$wheres2[] = 'a.studytext LIKE ' . $text;
				$wheres2[] = 'a.studyintro LIKE ' . $text;
				$wheres2[] = 'a.teachername LIKE ' . $text;
				$wheres2[] = 'a.bookname LIKE ' . $text;
				$wheres2[] = 'a.series_text LIKE ' . $text;
				$wheres2[] = 't.topic_text LIKE ' . $text;
				$where     = '(' . implode(') OR (', $wheres2) . ')';
				break;

			case 'all':
			case 'any':
			default:
				$words  = explode(' ', $text);
				$wheres = array();

				foreach ($words as $word)
				{
					$word      = $db->Quote('%' . $db->escape($word) . '%', false);
					$wheres2   = array();
					$wheres2[] = 'a.studytext LIKE ' . $word;
					$wheres2[] = 'a.studyintro LIKE ' . $word;
					$wheres2[] = '#__bsms_teachers.teachername LIKE ' . $word;
					$wheres2[] = '#__bsms_books.bookname LIKE ' . $word;
					$wheres2[] = '#__bsms_series.series_text LIKE ' . $word;
					$wheres2[] = 't.topic_text LIKE ' . $word;
					$wheres[]  = implode(' OR ', $wheres2);
				}
				$where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
				break;
		}

		switch ($ordering)
		{
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
		if (!empty($state))
		{
			// Load language files (english language file as fallback)
			$language = JFactory::getLanguage();
			$language->load('com_biblestudy', BIBLESTUDY_PATH_ADMIN, 'en-GB', true);
			$language->load('com_biblestudy', BIBLESTUDY_PATH_ADMIN, null, true);

			$query     = $db->getQuery(true);
			$set_title = $this->params->get('set_title');
			$template  = $jinput->getInt('t', '1');

			switch ($set_title)
			{
				case 0 :

					if ($this->params->get('show_description') > 0)
					{
						$query->select("#__bsms_books.bookname AS title, a.chapter_begin, CONCAT(a.studytitle,' - ',a.studyintro) AS text," .
							" a.studydate AS created, #__bsms_books.id AS bid, #__bsms_books.bookname, a.id AS sid, a.published AS spub," .
							" #__bsms_books.published AS bpub, #__bsms_series.id AS seriesid, #__bsms_series.series_text, " .
							"  #__bsms_teachers.id AS tid, #__bsms_teachers.teachername, a.id as id, 'Bible Studies' AS section," .
							" CONCAT('index.php?option=com_biblestudy&view=sermon&id=', a.id,'&t=" . $template . "') AS href," .
							" '2' AS browsernav"
						);
					}
					else
					{
						$query->select("#__bsms_books.bookname AS title, a.chapter_begin, a.studytitle AS text, a.studydate AS created," .
							" #__bsms_books.id AS bid, #__bsms_books.bookname, a.id AS sid, a.published AS spub, #__bsms_books.published AS bpub," .
							" #__bsms_series.id AS seriesid, #__bsms_series.series_text, " .
							" #__bsms_teachers.id AS tid, #__bsms_teachers.teachername, 'Bible Studies' AS section," .
							" CONCAT('index.php?option=com_biblestudy&view=sermon&id=', a.id,'&t=" . $template . "') AS href, '2' AS browsernav");
					}
					break;
				case 1 :

					if ($this->params->get('show_description') > 0)
					{
						$query->select("a.studytitle AS title, a.studyintro, #__bsms_books.bookname AS book, a.chapter_begin," .
							" a.studydate AS created, #__bsms_books.id AS bid, #__bsms_books.bookname, a.id AS sid, a.published AS spub," .
							" #__bsms_books.published AS bpub, #__bsms_series.id AS seriesid, #__bsms_series.series_text," .
							" #__bsms_teachers.id AS tid, #__bsms_teachers.teachername, a.id as id," .
							" 'Bible Studies' AS section, CONCAT('index.php?option=com_biblestudy&view=sermon&id=', a.id," .
							" '&t=" . $template . "') AS href, '2' AS browsernav");
					}
					else
					{
						$query->select("#__bsms_books.bookname AS book, a.chapter_begin, a.studytitle AS title, a.studydate AS created," .
							" #__bsms_books.id AS bid, #__bsms_books.bookname, a.id AS sid, a.published AS spub, #__bsms_books.published AS bpub," .
							"  #__bsms_series.id AS seriesid, #__bsms_series.series_text, " .
							" #__bsms_teachers.id AS tid, #__bsms_teachers.teachername, a.id as id, 'Bible Studies' AS section," .
							" CONCAT('index.php?option=com_biblestudy&view=sermon&id=', a.id,'&t=" . $template . "') AS href, '2' AS browsernav");
					}
					break;
			}
			$query->from(' #__bsms_studies as a');
			$query->select('st.topic_id');
			$query->join('LEFT', '#__bsms_studytopics AS st ON a.id = st.study_id');
			$query->select('t.id, t.topic_text as topics_text');
			$query->join('LEFT', '#__bsms_topics AS t ON t.id = st.topic_id');
			$query->join('LEFT', '#__bsms_books ON (#__bsms_books.booknumber = a.booknumber)');
			$query->join('LEFT', '#__bsms_series ON (#__bsms_series.id = a.series_id)');
			$query->join('LEFT', '#__bsms_teachers ON (#__bsms_teachers.id = a.teacher_id)');
			$query->where('(' . $where . ')' . ' AND a.published in (' . implode(',', $state) . ') AND a.access IN (' . $groups . ')');
			$query->order($order);

			$db->setQuery($query, 0, $limit);
			$rows = $db->loadObjectList();

			foreach ($rows AS $i => $row)
			{
				switch ($set_title)
				{
					case 0:
						$rows[$i]->title = JText::_($rows[$i]->title) . ' ' . $rows[$i]->chapter_begin;
						break;

					case 1:

						if ($this->params->get('show_description') > 0)
						{
							$rows[$i]->text = JText::_($rows[$i]->book) . ' ' . $rows[$i]->chapter_begin . ' | ' . $rows[$i]->studyintro;
						}
						else
						{
							$rows[$i]->text = JText::_($rows[$i]->book) . ' ' . $rows[$i]->chapter_begin;
						}
						break;
				}
			}
		}

		return $rows;
	}

}
