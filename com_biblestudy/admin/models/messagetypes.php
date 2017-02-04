<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * MessageType model class
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyModelMessagetypes extends JModelList
{
	/**
	 * Number of Deletions
	 *
	 * @var int
	 * @since 7.0.0
	 */
	private $deletes;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since 7.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'messagetype.id',
				'published', 'messagetype.published',
				'mesage_type', 'messagetype.message_type,',
				'ordering', 'messagetype.ordering',
				'access', 'messagetype.access', 'access_level'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Get Deletes
	 *
	 * @return object
	 *
	 * @since 7.0.0
	 */
	public function getDeletes()
	{
		if (empty($this->deletes))
		{
			$query          = 'SELECT allowdeletes'
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
	 * @since   7.0.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Adjust the context to support modal layouts.
		$input  = new JInput;
		$layout = $input->get('layout');

		if ($layout)
		{
			$this->context .= '.' . $layout;
		}

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
		$this->setState('filter.access', $access);

		parent::populateState('messagetype.message_type', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   7.1.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.messagetype');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	/**
	 * Get List Query
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   7.0.0
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$user  = JFactory::getUser();

		$query->select(
			$this->getState(
				'list.select', 'messagetype.id, messagetype.published, messagetype.message_type, ' .
				'messagetype.ordering, messagetype.access, messagetype.alias')
		);

		$query->from('#__bsms_message_type AS messagetype');

		// Filter by published state
		$published = $this->getState('filter.published');

		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$query->where('messagetype.access = ' . (int) $access);
		}

		// Implement View Level Access
		if (!$user->authorise('core.admin'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('messagetype.access IN (' . $groups . ')');
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('messagetype.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('messagetype.message_type LIKE ' . $search);
			}
		}

		if (is_numeric($published))
		{
			$query->where('messagetype.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(messagetype.published = 0 OR messagetype.published = 1)');
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'messagetype.message_type');
		$orderDirn = $this->state->get('list.direction', 'acs');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}
}
