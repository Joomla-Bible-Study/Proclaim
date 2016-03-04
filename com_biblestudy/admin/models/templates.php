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

/**
 * Templates model class
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyModelTemplates extends JModelList
{

	/**
	 * Templates
	 *
	 * @var object
	 */
	private $_templates;

	/**
	 * Get Templates
	 *
	 * @return object
	 */
	public function getTemplates()
	{
		if (empty($this->_templates))
		{
			$query            = 'SELECT id as value, title as text FROM #__bsms_templates WHERE published = 1 ORDER BY id ASC';
			$this->_templates = $this->_getList($query);
		}

		return $this->_templates;
	}

	/**
	 * Gets a list of templates types for the filter dropdown
	 *
	 * @return  array  Array of objects
	 *
	 * @since   7.0
	 */
	public function getTypes()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('template.type AS text');
		$query->from('#__bsms_templates AS template');
		$query->group('template.type');
		$query->order('template.type');

		$db->setQuery($query->__toString());

		return $db->loadObjectList();
	}

	/**
	 * Populate State
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   7.0
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

		$type = $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type');
		$this->setState('filter.type', $type);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		parent::populateState('template.title', 'ASC');
	}

	/**
	 * Build and SQL query to load the list data
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   7.0
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select', 'template.id, template.published, template.title')
		);
		$query->from('#__bsms_templates AS template');

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('template.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(template.published = 0 OR template.published = 1)');
		}

		// Filter by search in filename or study title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('template.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('(template.title LIKE ' . $search . ' OR template.params LIKE ' . $search . ')');
			}
		}

		// Add the list ordering clause
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

}
