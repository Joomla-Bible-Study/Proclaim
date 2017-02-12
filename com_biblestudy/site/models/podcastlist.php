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

/**
 * Model class for MessageList
 *
 * @package  BibleStudy.Site
 * @since    8.0.0
 */
class BiblestudyModelPodcastlist extends JModelList
{

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
				$query->select('m.id, m.params')
					->from('#__bsms_mediafiles as m')
					->where('m.published = ' . 1)
					->where('m.study_id = ' . $message->id);
				$this->_db->setQuery($query);
				$mediafiles = $this->_db->loadObjectList();

				foreach ($mediafiles as $mf => $med)
				{
					// Get the media files in one query
					if (isset($med->id))
					{
						$mediafiles[$mf] = $listing->getMediaFiles((array) $med->id);
					}
				}

				$messages[$m]->mediafiles = $mediafiles;
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
