<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmlocationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseInterface;

/**
 * Templates model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmtemplatesModel extends ListModel
{
    /**
     * Templates
     *
     * @var object
     * @since    7.0.0
     */
    private $templates;

    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @throws \Exception
     * @since   11.1
     * @see     ListModel
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id',
                'template.id',
                'published',
                'template.published',
                'title',
                'template.title',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Get Templates
     *
     * @return object
     *
     * @since    7.0.0
     */
    public function getTemplates(): array
    {
        if (empty($this->templates)) {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true);
            $query->select($db->quoteName('id', 'value'))
                ->select($db->quoteName('title', 'text'))
                ->from($db->quoteName('#__bsms_templates'))
                ->where($db->quoteName('published') . ' = 1')
                ->order($db->quoteName('id') . ' ASC');
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
    public function getTypes(): array
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select($db->quoteName('template.type', 'text'));
        $query->from($db->quoteName('#__bsms_templates', 'template'));
        $query->group($db->quoteName('template.type'));
        $query->order($db->quoteName('template.type'));

        $db->setQuery($query);

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
     * @throws \Exception
     * @since   7.0
     */
    protected function populateState($ordering = 'template.title', $direction = 'ASC'): void
    {
        $app = Factory::getApplication();

        // Adjust the context to support modal layouts.
        if ($layout = $app->getInput()->get('layout')) {
            $this->context .= '.' . $layout;
        }

        $type = $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type', '');
        $this->setState('filter.type', $type);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '');
        $this->setState('filter.search', $search);

        $location = $this->getUserStateFromRequest($this->context . '.filter.location', 'filter_location', '');
        $this->setState('filter.location', $location);

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
    protected function getListQuery(): mixed
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $user  = $this->getCurrentUser();

        $query->select(
            $this->getState(
                'list.select',
                implode(', ', $db->quoteName(['template.id', 'template.published', 'template.title', 'template.checked_out', 'template.checked_out_time']))
            )
        );
        $query->from($db->quoteName('#__bsms_templates', 'template'));

        // Join over the users for the checked out user.
        $query->select($db->quoteName('uc.name', 'editor'))
            ->join('LEFT', $db->quoteName('#__users', 'uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('template.checked_out'));

        // Filter by published state
        $published = $this->getState('filter.published');

        if (is_numeric($published)) {
            $query->where($db->quoteName('template.published') . ' = ' . (int) $published);
        } elseif ($published === '') {
            $query->where('(' . $db->quoteName('template.published') . ' = 0 OR ' . $db->quoteName('template.published') . ' = 1)');
        }

        // Filter by search in filename or study title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where($db->quoteName('template.id') . ' = ' . (int) substr($search, 3));
            } else {
                $search = $db->quote('%' . $db->escape($search, true) . '%');
                $query->where($db->quoteName('template.title') . ' LIKE ' . $search);
            }
        }

        // Join location name for display
        $query->select($db->quoteName('loc.location_text', 'location_text'))
            ->join('LEFT', $db->quoteName('#__bsms_locations', 'loc') . ' ON ' . $db->quoteName('loc.id') . ' = ' . $db->quoteName('template.location_id'));

        // Restrict non-admin users: hybrid location + access-level filter
        if (!$user->authorise('core.admin')) {
            if (CwmlocationHelper::isEnabled()) {
                $accessible = CwmlocationHelper::getUserLocations((int) $user->id);

                if (!empty($accessible)) {
                    $inClause = implode(',', array_map('intval', $accessible));
                    $query->where(
                        '(' . $db->quoteName('template.location_id') . ' IS NULL'
                        . ' OR ' . $db->quoteName('template.location_id') . ' IN (' . $inClause . '))'
                    );
                } else {
                    $query->where($db->quoteName('template.location_id') . ' IS NULL');
                }
            } else {
                $query->whereIn($db->quoteName('template.access'), $user->getAuthorisedViewLevels());
            }
        }

        // Filter by location (dropdown)
        $location = $this->getState('filter.location');

        if (is_numeric($location)) {
            $locationVal = (int) $location;
            $query->where($db->quoteName('template.location_id') . ' = :locationId')
                ->bind(':locationId', $locationVal, \Joomla\Database\ParameterType::INTEGER);
        }

        // Add the list ordering clause
        $orderCol  = $this->state->get('list.ordering', 'template.id');
        $orderDirn = $this->state->get('list.direction', 'ACS');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
