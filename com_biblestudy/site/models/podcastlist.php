<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Base this model on the backend version.
JLoader::register('BiblestudyModelMessages', JPATH_ADMINISTRATOR . '/components/com_biblestudy/models/messages.php');
use \Joomla\Registry\Registry;

/**
 * Model class for MessageList
 *
 * @package  BibleStudy.Site
 * @since    8.0.0
 */
class BiblestudyModelPodcastlist extends JModelList
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return    void
	 *
	 * @since    1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		/** @type JApplicationSite $app */
		$app = JFactory::getApplication();

		$return = $app->input->get('return', null, 'base64');
		$this->setState('return_page', base64_decode($return));

		// Load the parameters.
		$params   = $app->getParams();
		$this->setState('params', $params);
		$template = JBSMParams::getTemplateparams();
		$admin    = JBSMParams::getAdmin();

		$template->params->merge($params);
		$template->params->merge($admin->params);
		$params = $template->params;

		$t = $params->get('messageid');

		if (!$t)
		{
			$input = new JInput;
			$t     = $input->get('t', 1, 'int');
		}

		$template->id = $t;

		$this->setState('template', $template);
		$this->setState('admin', $admin);

		// Adjust the context to support modal layouts.
		$input  = $app->input;
		$layout = $input->get('layout');

		if ($layout)
		{
			$this->context .= '.' . $layout;
		}

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$studytitle = $this->getUserStateFromRequest($this->context . '.filter.studytitle', 'filter_studytitle');
		$this->setState('filter.studytitle', $studytitle);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$book = $this->getUserStateFromRequest($this->context . '.filter.book', 'filter_book');
		$this->setState('filter.book', $book);

		$teacher = $this->getUserStateFromRequest($this->context . '.filter.teacher', 'filter_teacher');
		$this->setState('filter.teacher', $teacher);

		$series = $this->getUserStateFromRequest($this->context . '.filter.series', 'filter_series');
		$this->setState('filter.series', $series);

		$messageType = $this->getUserStateFromRequest($this->context . '.filter.messagetype', 'filter_messagetype');
		$this->setState('filter.messagetype', $messageType);

		$year = $this->getUserStateFromRequest($this->context . '.filter.year', 'filter_year');
		$this->setState('filter.year', $year);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
		$this->setState('filter.access', $access);

		$location = $this->getUserStateFromRequest($this->context . 'filter.location', 'filter_location');
		$this->setState('filter.location', $location);

		// Force a language
		$forcedLanguage = $app->input->get('forcedLanguage');

		if (!empty($forcedLanguage))
		{
			$this->setState('filter.language', $forcedLanguage);
			$this->setState('filter.forcedLanguage', $forcedLanguage);
		}

		parent::populateState('study.studydate', 'DESC');
	}

	/**
	 * Build an SQL query to load the list data
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   7.0
	 */
	protected function getListQuery()
	{
		$user            = JFactory::getUser();
		$query           = parent::getListQuery();

		$query->select(
			$this->getState(
				'list.select', '*')
		);
		$query->from('#__bsms_series');

		return $query;
	}

	/**
	 * Method to get a list of sermons.
	 * Overridden to add a check for access levels.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   9.0.0
	 */
	public function getItems()
	{
		$user   = JFactory::getUser();
		$groups = $user->getAuthorisedViewLevels();

		$query = $this->_db->getQuery(true);
		$query->select(
			$this->getState(
				'list.select', '*')
		);

		$query->from('#__bsms_series');
		$this->_db->setQuery($query);

		$items   = $this->_db->loadObjectList();
		$listing = new JBSMListing;

		foreach ($items as $t => $item)
		{
			// Check the access level. Remove articles the user shouldn't see
			if (!in_array($items[$t]->access, $groups))
			{
				unset($items[$t]);
			}

			$query = $this->_db->getQuery(true);
			$query->select('s.*');
			$query->from('#__bsms_studies as s');
			$query->where('s.id = 577');
			$query->where('s.published = ' . 1);
			$query->where('s.series_id = ' . (int) $item->id);
			$query->order('s.studydate DESC');
			$this->_db->setQuery($query);

			$messages = $this->_db->loadObjectList();

			foreach ($messages as $m => $message)
			{
				// Check the access level. Remove articles the user shouldn't see
				if (!in_array($messages[$m]->access, $groups))
				{
					unset($messages[$m]);
				}

				$query = $this->_db->getQuery(true);
				$query->select('#__bsms_mediafiles.*, #__bsms_servers.id AS ssid, #__bsms_servers.params AS sparams, #__bsms_servers.media AS smedia,'
					. ' s.studytitle, s.studydate, s.studyintro, s.media_hours, s.media_minutes, s.media_seconds, s.teacher_id,'
					. ' s.booknumber, s.chapter_begin, s.chapter_end, s.verse_begin, s.verse_end, t.teachername, t.id as tid, s.id as sid, s.studyintro');
				$query->from('#__bsms_mediafiles');
				$query->leftJoin('#__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server_id)');
				$query->leftJoin('#__bsms_studies AS s ON (s.id = #__bsms_mediafiles.study_id)');
				$query->leftJoin('#__bsms_teachers AS t ON (t.id = s.teacher_id)');

				$query->where('#__bsms_mediafiles.study_id = ' . (int) $message->id);
				$query->where('#__bsms_mediafiles.published = 1');
				$query->where('#__bsms_mediafiles.language in (' . $this->_db->quote(JFactory::getLanguage()->getTag()) . ',' . $this->_db->quote('*') . ')');
				$query->order('ordering ASC');
				$this->_db->setQuery($query);
				$mediafiles = $this->_db->loadObjectList();

				foreach ($mediafiles as $mf => $med)
				{
					$reg = new Registry;
					$reg->loadString($med->params);
					$filename   = $reg->get('filename', '');
					$extension  = substr($filename, strrpos($filename, '.') + 1);

					// Get the media files in one query
					if ($extension !== 'mp3')
					{
						unset($mediafiles[$mf]);
					}

					if (isset($mediafiles[$mf]))
					{
							$reg = new Registry;
							$reg->loadString($mediafiles[$mf]->params);
							$mediafiles[$mf]->params = $reg;
					}
				}

				if (!empty($mediafiles))
				{
					$messages[$m]->mediafiles = $mediafiles;
				}
				else
				{
					unset($messages[$m]);
				}
			}

			$items[$t]->messages = $messages;
		}

		if (JFactory::getApplication()->isSite())
		{
			$user   = JFactory::getUser();
			$groups = $user->getAuthorisedViewLevels();

			for ($x = 0, $count = count($items); $x < $count; $x++)
			{
				// Check the access level. Remove articles the user shouldn't see
				if (!in_array($items[$x]->access, $groups))
				{
					unset($items[$x]);
				}
			}
		}

		return $items;
	}
}
