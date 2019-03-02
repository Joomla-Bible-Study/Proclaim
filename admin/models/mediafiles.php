<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * MediaFiles model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class BiblestudyModelMediafiles extends JModelList
{
	/**
	 * Model context string.
	 *
	 * @var        string
	 *
	 * @since 7.0
	 */
	public $context = 'com_biblestudy.mediafiles';

	/**
	 * Number of Deletions
	 *
	 * @var int
	 *
	 * @since 7.0
	 */
	private $deletes;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since 7.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'mediafile.id',
				'published', 'mediafile.published',
				'ordering', 'mediafile.ordering',
				'studytitle', 'study.studytitle',
				'createdate', 'mediafile.createdate',
				'language', 'mediafile.language'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Manually joins items and returns and nested object array
	 *
	 * @return mixed  Array  Media files array
	 *
	 * @since 9.0.0
	 * @throws Exception
	 */
	public function getItems()
	{
		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		try
		{
			// Needed for site view
			JModelLegacy::addIncludePath(BIBLESTUDY_PATH_ADMIN_MODELS);

			$serverModel = JModelLegacy::getInstance('Server', 'BibleStudyModel');

			$items = parent::getItems();

			if (!$items)
			{
				return false;
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

			foreach ($items as $item)
			{
				if (empty($item->serverType))
				{
					$item->serverType = 'legacy';
				}

				$item->serverConfig = $serverModel->getConfig($item->serverType);

				// Convert all JSON strings to Arrays
				$registry = new Registry;
				$registry->loadString($item->params);
				$item->params = $registry;

				$registry2 = new Registry;
				$registry2->loadString($item->metadata);
				$item->metadata = $registry2;
			}

			$this->cache[$store] = $items;
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return $this->cache[$store];
	}

	/**
	 * Get Deletes
	 *
	 * @return object
	 *
	 * @since 7.0
	 */
	public function getDeletes()
	{
		if (empty($this->deletes))
		{
			$query = 'SELECT allow_deletes'
				. ' FROM #__bsms_admin'
				. ' WHERE id = 1';
			$this->deletes = $this->_getList($query);
		}

		return $this->deletes;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   7.0
	 * @throws  Exception
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();

		// Adjust the context to support modal layouts.
		$input  = new JInput;
		$layout = $input->get('layout');

		if ($layout)
		{
			$this->context .= '.' . $layout;
		}

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
		$this->setState('filter.access', $access);

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$mediaYears = $this->getUserStateFromRequest($this->context . '.filter.mediaYears', 'filter_mediaYears');
		$this->setState('filter.mediaYears', $mediaYears);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		parent::populateState('mediafile.createdate', 'DESC');

		// Force a language
		$forcedLanguage = $app->input->get('forcedLanguage');

		if (!empty($forcedLanguage))
		{
			$this->setState('filter.language', $forcedLanguage);
			$this->setState('filter.forcedLanguage', $forcedLanguage);
		}
	}

	/**
	 * Get Stored ID
	 *
	 * @param   string  $id  An identifier string to generate the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since 7.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.study_id');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
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
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();

		$query->select(
			$this->getState(
				'list.select', 'mediafile.* ')
		);

		$query->from('#__bsms_mediafiles AS mediafile');

		// Join over the language
		$query->select('l.title AS language_title');
		$query->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = mediafile.language');

		// Join over the studies
		$query->select('study.studytitle AS studytitle');
		$query->join('LEFT', '#__bsms_studies AS study ON study.id = mediafile.study_id');

		// Join over servers
		$query->select('server.type as serverType');
		$query->join('LEFT', '#__bsms_servers as server ON server.id = mediafile.server_id');

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = mediafile.access');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor')
			->join('LEFT', '#__users AS uc ON uc.id=mediafile.checked_out');

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('mediafile.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(mediafile.published = 0 OR mediafile.published = 1)');
		}

		// Filter by access level.
		$access = $this->getState('filter.access');

		if ($access)
		{
			$query->where('mediafile.access = ' . (int) $access);
		}

		// Implement View Level Access
		if (!$user->authorise('core.admin'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('mediafile.access IN (' . $groups . ')');
		}

		// Filter by study title
		$study = $this->getState('filter.study_id');

		if (!empty($study))
		{
			$query->where('mediafile.study_id LIKE "%' . $study . '%"');
		}

		// Filter by media years
		$mediaYears = $this->getState('filter.mediaYears');

		if (!empty($mediaYears))
		{
			$query->where('YEAR(mediafile.createdate) = ' . (int) $mediaYears);
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('mediafile.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('study.studytitle LIKE ' . $search . ' OR study.alias LIKE ' . $search);
			}
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language'))
		{
			$query->where('mediafile.language = ' . $db->quote($language));
		}

		// Add the list ordering clause
		$orderCol  = $this->state->get('list.ordering', 'mediafile.createdate');
		$orderDirn = $this->state->get('list.direction', 'desc');

		// Sqlsrv change
		if ($orderCol == 'study_id')
		{
			$orderCol = 'mediafile.study_id';
		}

		if ($orderCol == 'ordering')
		{
			$orderCol = 'mediafile.study_id, mediafile.ordering';
		}

		if ($orderCol == 'published')
		{
			$orderCol = 'mediafile.published';
		}

		if ($orderCol == 'id')
		{
			$orderCol = 'mediafile.id';
		}

		if ($orderCol == 'mediafile.ordering')
		{
			$orderCol = 'mediafile.study_id ' . $orderDirn . ', mediafile.ordering';
		}

		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}
}
