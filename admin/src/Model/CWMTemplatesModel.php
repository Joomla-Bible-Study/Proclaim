<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Templates model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMTemplatesModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @throws \Exception
	 * @since   11.1
	 * @see     JController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'template.id',
				'published', 'template.published',
				'title', 'template.title'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Templates
	 *
	 * @var object
	 * @since    7.0.0
	 */
	private $templates;

	/**
	 * Get Templates
	 *
	 * @return object
	 *
	 * @since    7.0.0
	 */
	public function getTemplates()
	{
		if (empty($this->templates))
		{
			$query           = 'SELECT id as value, title as text FROM `#__bsms_templates` WHERE published = 1 ORDER BY id ASC';
			$this->templates = $this->_getList($query);
		}

		return $this->templates;
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
		$db    = Factory::getContainer()->get('DatabaseDriver');
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
	protected function populateState($ordering = 'template.title', $direction = 'ASC'): void
	{
		$app = Factory::getApplication();

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		$type = $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type');
		$this->setState('filter.type', $type);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Build and SQL query to load the list data
	 *
	 * @return  \Joomla\Database\QueryInterface
	 *
	 * @since   7.0
	 */
	protected function getListQuery()
	{
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select', 'template.id, template.published, template.title'
			)
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
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('template.title LIKE ' . $search);
			}
		}

		// Add the list ordering clause
		$orderCol  = $this->state->get('list.ordering', 'template.id');
		$orderDirn = $this->state->get('list.direction', 'ACS');
		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}
}
