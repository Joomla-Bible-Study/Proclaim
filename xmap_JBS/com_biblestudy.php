<?php

/**
 * Xmap Plugin for BibleStudy
 *
 * @package     BibleStudy
 * @subpackage  Xmap.BibleStudy
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @description Xmap plugin for Bible Study Component
 *              adapted for Bible Study by TOm Fuller
 *
 * Plugin tested with Xmap 2.0 and Joomla Bible Study 7.0.1 on Joomla 1.7.0
 *
 */
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * Class for xmap biblestudy
 *
 * @package     BibleStudy
 * @subpackage  Xamp.BibleStudy
 * @since       7.0.4
 */
class Xmap_Com_Biblestudy
{

	/**
	 * Type
	 *
	 * @param   array  &$xmap    ?
	 * @param   array  &$parent  ?
	 *
	 * @return boolean
	 */
	public function isOfType(&$xmap, &$parent)
	{
		if (strpos($parent->link, 'option=com_biblestudy'))
		{
			return true;
		}

		return false;
	}

	/**
	 * Prepare Menu
	 *
	 * @param   array  $node    ?
	 * @param   array  $params  ?
	 *
	 * @return string
	 */
	public static function prepareMenuItem($node, $params)
	{
		$db         = JFactory::getDbo();
		$link_query = parse_url($node->link);

		if (!isset($link_query['query']))
		{
			return;
		}

		parse_str(html_entity_decode($link_query['query']), $link_vars);
		$view   = JArrayHelper::getValue($link_vars, 'view', '');
		$layout = JArrayHelper::getValue($link_vars, 'layout', '');
		$id     = JArrayHelper::getValue($link_vars, 'id', 0);
	}

	/**
	 * Get Tree
	 *
	 * @param   object  $xmap     ?
	 * @param   object  $parent   ?
	 * @param   array   &$params  ?
	 *
	 * @return mixed
	 */
	public function &getTree($xmap, $parent, &$params)
	{
		@set_time_limit(300);
		$db  = JFactory::getDBO();
		$app = JFactory::getApplication();

		$list = array();

		$link_query = parse_url($parent->link);

		if (!isset($link_query['query']))
		{
			return false;
		}

		$lang = JFactory::getLanguage();
		$lang->load('com_biblestudy', JPATH_ADMINISTRATOR);
		$lang->load('plg_xmap_com_biblestudy', JPATH_ADMINISTRATOR);

		parse_str(html_entity_decode($link_query['query']), $link_vars);
		$view             = JArrayHelper::getValue($link_vars, 'view', '');
		$id               = intval(JArrayHelper::getValue($link_vars, 'id', ''));
		$t                = $params['t'];
		$order            = $params['order'];
		$displaytype      = $params['displaytype'];
		$node             = new stdClass;
		$node->expandible = true;

		if (!$order)
		{
			$order = 'desc';
		}
		$limit = $params['limit'];

		if ($limit > 0)
		{
			// $limit = 'LIMIT ' . $limit;
		}
		else
		{
			$limit = '';
		}
		$showmedia = $params['showmedia'];

		// 1=year 2=book 3=teacher 4=location
		switch ($displaytype)
		{
			case 1:
				$query = $db->getQuery(true);
				$query->select('distinct year(studydate) as theYear')->from('#__bsms_studies')->where('published = 1')->order('year(studydate) ' . $order);
				$db->setQuery($query);
				$results          = $db->loadObjectList();
				$node->expandable = true;

				foreach ($results AS $result)
				{
					$node             = new stdclass;
					$node->id         = $parent->id;
					$node->uid        = $parent->uid . 'a' . $result->theYear;
					$node->name       = $result->theYear;
					$node->parent     = 1;
					$node->browsNav   = 1; /* Open new window */
					$node->ordering   = 2;
					$node->priority   = $parent->priority;
					$node->changefreq = $parent->changefreq;
					$node->type       = 'component';
					$node->menutype   = 'mainmenu';
					$node->link       = 'index.php?option=com_biblestudy&amp;view=sermons&amp;filter_year=' . $result->theYear . '&amp;t=' . $t;
					$xmap->printNode($node);
					$xmap->changeLevel(1);
					$query = $db->getQuery(true);
					$query->select('id, studytitle, alias, studydate, studyintro')->from('#__bsms_studies')->where('year(studydate) = ' . $result->theYear);
					$db->setQuery($query);
					$studies = $db->loadObjectList();

					foreach ($studies AS $study)
					{
						self::showStudies($study, $xmap, $t, $limit, $order, $params, $parent);
					}
					$xmap->changeLevel(-1);
				}
				break;

			case 2:
				$query = $db->getQuery(true);
				$query->select('distinct s.booknumber, b.bookname, b.booknumber as bnumber, s.studyintro')
					->from('#__bsms_studies as s')
					->leftJoin('#__bsms_books as b on (s.booknumber = b.booknumber)')
					->where('s.published = 1')->order('s.booknumber asc');
				$db->setQuery($query);
				$results          = $db->loadObjectList();
				$node->expandable = true;

				foreach ($results AS $result)
				{
					$field            = 'booknumber';
					$record           = $result->booknumber;
					$node             = new stdclass;
					$node->id         = $parent->id;
					$node->uid        = $parent->uid . 'a' . JText::_($result->bookname);
					$node->name       = JText::_($result->bookname);
					$node->parent     = 1;
					$node->browsNav   = 1; /* Open new window */
					$node->ordering   = 2;
					$node->priority   = $parent->priority;
					$node->changefreq = $parent->changefreq;
					$node->type       = 'component';
					$node->menutype   = 'mainmenu';
					$node->link       = 'index.php?option=com_biblestudy&amp;view=sermons&amp;filter_book=' . $result->booknumber . '&amp;t=' . $t;
					$xmap->printNode($node);
					$xmap->changeLevel(1);
					self::showYears($result, $xmap, $t, $limit, $order, $params, $field, $record, $parent);

					$xmap->changeLevel(-1);
				}
				break;

			case 3:
				$query = $db->getQuery(true);
				$query->select('id, teachername')->from('#__bsms_teachers')->where('published = 1')->order('ordering asc');
				$db->setQuery($query);
				$results          = $db->loadObjectList();
				$node->expandable = true;

				foreach ($results AS $result)
				{
					$field            = 'teacher_id';
					$record           = $result->id;
					$node             = new stdclass;
					$node->id         = $parent->teachername;
					$node->uid        = $parent->uid . 'a' . $result->teachername;
					$node->name       = $result->teachername;
					$node->parent     = 1;
					$node->browsNav   = 1; /* Open new window */
					$node->ordering   = 2;
					$node->priority   = $parent->priority;
					$node->changefreq = $parent->changefreq;
					$node->type       = 'component';
					$node->menutype   = 'mainmenu';
					$node->link       = 'index.php?option=com_biblestudy&amp;view=sermons&amp;filter_teacher=' . $result->id . '&amp;t=' . $t;
					$xmap->printNode($node);
					$xmap->changeLevel(1);
					self::showYears($result, $xmap, $t, $limit, $order, $params, $field, $record, $parent);

					$xmap->changeLevel(-1);
				}
				break;

			case 4:
				$query = $db->getQuery(true);
				$query->select('id, location_text')->from('#__bsms_locations')->where('published = 1')->order('location_text asc');
				$db->setQuery($query);
				$results          = $db->loadObjectList();
				$node->expandable = true;

				foreach ($results AS $result)
				{
					$field            = 'location_id';
					$record           = $result->id;
					$node             = new stdclass;
					$node->id         = $parent->location_text;
					$node->uid        = $parent->uid . 'a' . $result->location_text;
					$node->name       = $result->location_text;
					$node->parent     = 1;
					$node->browsNav   = 1; /* Open new window */
					$node->ordering   = 2;
					$node->priority   = $parent->priority;
					$node->changefreq = $parent->changefreq;
					$node->type       = 'component';
					$node->menutype   = 'mainmenu';
					$node->link       = 'index.php?option=com_biblestudy&amp;view=sermons&amp;filter_location =' . $result->id . '&amp;t=' . $t;
					$xmap->printNode($node);
					$xmap->changeLevel(1);
					self::showYears($result, $xmap, $t, $limit, $order, $params, $field, $record, $parent);

					$xmap->changeLevel(-1);
				}
				break;
		}

		return $list;
	}

	/**
	 * Show Media Files
	 *
	 * @param   int     $id      ?
	 * @param   object  $xmap    ?
	 * @param   string  $limit   ?
	 * @param   string  $order   ?
	 * @param   array   $params  ?
	 * @param   object  $parent  ?
	 *
	 * @return void
	 */
	public function showMediaFiles($id, $xmap, $limit, $order, $params, $parent)
	{
		$t         = $params['t'];
		$showmedia = $params['showmedia'];

		if ($showmedia == 1)
		{
			$xmap->changeLevel(1);
			$db    = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('m.id, m.server, m.path, m.filename, m.published, m.player,'
				. ' sr.id AS srid, sr.server_path,'
				. ' f.id AS fid, f.folderpath, mime.id as mimeid, mime.mimetext')
				->from('#__bsms_mediafiles AS m')
				->leftJoin('#__bsms_servers AS sr ON (sr.id = m.server)')
				->leftJoin('#__bsms_folders AS f ON (f.id = m.path)')
				->leftJoin('#__bsms_mimetype as mime on (mime.id = m.mime_type)')
				->where('m.published = 1')->where('m.study_id = ' . $id)->order('createdate ' . $order);
			$db->setQuery($query, 0, $limit);
			$medias = $db->loadObjectList();

			foreach ($medias AS $media)
			{
				$node      = new stdclass;
				$node->id  = $parent->id;
				$node->uid = $parent->uid . 'a' . $media->id;

				$node->parent     = 1;
				$node->browsNav   = 1; /* Open new window */
				$node->ordering   = 2;
				$node->priority   = $parent->priority;
				$node->changefreq = $parent->changefreq;

				if ($media->filename)
				{
					$node->name = $media->filename;

					if ($params['filelink'] == 1)
					{
						$node->link = $media->folderpath . $media->filename;
					}
					else
					{
						$node->link = 'index.php?option=com_biblestudy&amp;player=1&amp;view=popup&amp;mediaid=' . $media->id . '&amp;t=' . $t;
					}
				}
				if (!$media->filename)
				{
					$node->link = 'index.php?option=com_biblestudy&amp;player=1&amp;view=popup&amp;mediaid=' . $media->id . '&amp;t=' . $t;

					if ($media->mimetext)
					{
						$node->name = $media->mimetext;
					}
					else
					{
						$node->name = $params['nofilename'];
					}
				}

				$xmap->printNode($node);
			}
			$xmap->changeLevel(-1);
		}
	}

	/**
	 * Show Studies
	 *
	 * @param   object  $study   ?
	 * @param   object  $xmap    ?
	 * @param   int     $t       ?
	 * @param   string  $limit   ?
	 * @param   string  $order   ?
	 * @param   array   $params  ?
	 * @param   object  $parent  ?
	 *
	 * @return void
	 */
	public function showStudies($study, $xmap, $t, $limit, $order, $params, $parent)
	{
		$node             = new stdclass;
		$node->id         = $parent->id;
		$node->uid        = $parent->uid . 'a' . $study->id;
		$study->name      = $study->alias ? ($study->alias) : str_replace(' ', '-', htmlspecialchars_decode($study->studytitle, ENT_QUOTES));
		$node->name       = $study->name;
		$node->parent     = 1;
		$node->browsNav   = 1; /* Open new window */
		$node->ordering   = 2;
		$node->priority   = $parent->priority;
		$node->changefreq = $parent->changefreq;
		$node->type       = 'component';
		$node->menutype   = 'mainmenu';
		$node->link       = 'index.php?option=com_biblestudy&amp;view=sermon&amp;id=' . $study->id . '&amp;t=' . $t;

		if ($params['description'] == 1)
		{
			$node->name .= ' - ' . $study->studyintro;
		}
		$xmap->printNode($node);
		self::showMediaFiles($study->id, $xmap, $limit, $order, $params, $parent);
	}

	/**
	 * Show Years
	 *
	 * @param   array   $result  ?
	 * @param   object  $xmap    ?
	 * @param   int     $t       ?
	 * @param   string  $limit   ?
	 * @param   string  $order   ?
	 * @param   array   $params  ?
	 * @param   int     $field   ?
	 * @param   int     $record  ?
	 * @param   object  $parent  ?
	 *
	 * @return void
	 */
	public function showYears($result, $xmap, $t, $limit, $order, $params, $field, $record, $parent)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('distinct year(studydate) as theYear')->from('#__bsms_studies')->where('published = 1')->where($field . ' = ' . $record)
			->order('theYear ' . $order);
		$db->setQuery($query);
		$years = $db->loadObjectList();

		foreach ($years as $year)
		{
			$node             = new stdclass;
			$node->id         = $parent->id;
			$node->uid        = $parent->uid . 'a' . $year->theYear;
			$node->name       = $year->theYear;
			$node->parent     = 1;
			$node->browsNav   = 1; /* Open new window */
			$node->ordering   = 2;
			$node->priority   = $parent->priority;
			$node->changefreq = $parent->changefreq;
			$node->type       = 'component';
			$node->menutype   = 'mainmenu';
			$node->link       = 'index.php?option=com_biblestudy&amp;view=sermons&amp;filter_year=' . $year->theYear . '&amp;t=' . $t;
			$xmap->printNode($node);
			$xmap->changeLevel(1);
			$query = $db->getQuery(true);
			$query->select('id, studytitle, studydate')->from('#__bsms_studies')->where('year(studydate) = ' . $year->theYear)
				->where($field . ' = ' . $record);
			$db->setQuery($query);
			$studies = $db->loadObjectList();

			foreach ($studies AS $study)
			{
				self::showStudies($study, $xmap, $t, $limit, $order, $params, $parent);
			}
			$xmap->changeLevel(-1);
		}
	}

}
