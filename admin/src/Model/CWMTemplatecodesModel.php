<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// No Direct Access
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseQuery;
use Joomla\Input\Input;

defined('_JEXEC') or die;

/**
 * Template codes model class
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 */
class CWMTemplatecodesModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since 7.1
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'templatecode.id',
				'published', 'templatecode.published',
				'type', 'templatecode.type',
				'access', 'templatecode.access',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Populate State
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   7.1
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Adjust the context to support modal layouts.
		$input  = new Input;
		$layout = $input->get('layout');

		if ($layout)
		{
			$this->context .= '.' . $layout;
		}

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		parent::populateState('templatecode.filename', 'ASC');
	}

	/**
	 * Get list query
	 *
	 * @return \Joomla\Database\QueryInterface
	 *
	 * @since 7.1
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select', 'templatecode.id, templatecode.published, templatecode.filename, templatecode.templatecode, templatecode.type'
			)
		);
		$query->from('`#__bsms_templatecode` AS templatecode');

		// Filter by search in filename or study title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('podcast.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('(templatecode.filename LIKE ' . $search . ' OR templatecode.templatecode LIKE ' . $search . ')');
			}
		}

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('templatecode.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(templatecode.published = 0 OR templatecode.published = 1)');
		}

		return $query;
	}
}
