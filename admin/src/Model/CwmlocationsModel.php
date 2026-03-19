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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseInterface;

/**
 * Locations model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmlocationsModel extends ListModel
{
    /**
     * Number of Deletes
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
     * @throws \Exception
     * @since 7.0.0
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id',
                'location.id',
                'published',
                'location.published',
                'mesage_type',
                'location.message_type,',
                'ordering',
                'location.ordering',
                'access',
                'location.access',
                'access_level',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Get Deletes
     *
     * @return int|object[]
     *
     * @since 7.0
     */
    public function getDeletes(): array
    {
        if (empty($this->deletes)) {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true);
            $query->select($db->quoteName('allow_deletes'))
                ->from($db->quoteName('#__bsms_admin'))
                ->where($db->quoteName('id') . ' = 1');
            $this->deletes = $this->_getList($query);
        }

        return $this->deletes;
    }

    /**
     * Method to get a store ID based on the model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  A prefix for the store ID.
     *
     * @return  string  A store ID.
     *
     * @since   7.1.0
     */
    protected function getStoreId($id = ''): string
    {
        // Compile the store ID.
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.search');

        return parent::getStoreId($id);
    }

    /**
     * Method to auto-populate the model state.
     *
     * This method should only be called once per instantiation and is designed
     * to be called on the first call to the getState() method unless the model
     * The configuration flag to ignore the request is set.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   7.0
     */
    protected function populateState($ordering = 'location.id', $direction = 'desc'): void
    {
        $app = Factory::getApplication();

        $forcedLanguage = $app->getInput()->get('forcedLanguage', '', 'cmd');

        // Adjust the context to support modal layouts.
        if ($layout = $app->getInput()->get('layout')) {
            $this->context .= '.' . $layout;
        }

        // Adjust the context to support forced languages.
        if ($forcedLanguage) {
            $this->context .= '.' . $forcedLanguage;
        }

        // Load the parameters.
        $params = ComponentHelper::getParams('com_proclaim');
        $this->setState('params', $params);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '');
        $this->setState('filter.search', $search);

        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '');
        $this->setState('filter.access', $access);

        $formSubmited = $app->getInput()->post->get('form_submited');

        // Gets the value of a user state variable and sets it in the session
        $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '');
        $this->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id', '');

        if ($formSubmited) {
            $access = $app->getInput()->post->get('access');
            $this->setState('filter.access', $access);

            $authorId = $app->getInput()->post->get('author_id');
            $this->setState('filter.author_id', $authorId);
        }

        // List state information.
        parent::populateState($ordering, $direction);

        if (!empty($forcedLanguage)) {
            $this->setState('filter.language', $forcedLanguage);
            $this->setState('filter.forcedLanguage', $forcedLanguage);
        }
    }

    /**
     * Override getItems to attach usage counts to each location.
     *
     * @return  array|false
     *
     * @since   10.3.0
     */
    public function getItems(): array|false
    {
        $items = parent::getItems();

        if ($items === false) {
            return false;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Tables that reference location_id
        $tables = [
            'messages'      => '#__bsms_studies',
            'series'        => '#__bsms_series',
            'podcasts'      => '#__bsms_podcast',
            'servers'       => '#__bsms_servers',
            'templates'     => '#__bsms_templates',
            'templatecodes' => '#__bsms_templatecode',
        ];

        // Collect all location IDs from current page
        $ids = array_map(fn ($item) => (int) $item->id, $items);

        if (empty($ids)) {
            return $items;
        }

        // Batch-load counts for all locations on this page
        $counts = [];

        foreach ($tables as $key => $table) {
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('location_id'),
                    'COUNT(*) AS ' . $db->quoteName('cnt'),
                ])
                ->from($db->quoteName($table))
                ->whereIn($db->quoteName('location_id'), $ids)
                ->group($db->quoteName('location_id'));
            $db->setQuery($query);
            $rows = $db->loadObjectList('location_id');

            foreach ($ids as $id) {
                $counts[$id][$key] = isset($rows[$id]) ? (int) $rows[$id]->cnt : 0;
            }
        }

        // Attach counts to items
        foreach ($items as $item) {
            $item->usage = $counts[(int) $item->id] ?? [
                'messages'      => 0,
                'series'        => 0,
                'podcasts'      => 0,
                'servers'       => 0,
                'templates'     => 0,
                'templatecodes' => 0,
            ];
            $item->usage_total = array_sum($item->usage);
        }

        return $items;
    }

    /**
     * Get List Query
     *
     * @return  \Joomla\Database\QueryInterface   A JDatabaseQuery object to retrieve the data set.
     *
     * @throws \Exception
     * @since   12.2
     */
    protected function getListQuery(): mixed
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select(
            $this->getState(
                'list.select',
                implode(', ', $db->quoteName(['location.id', 'location.published', 'location.access', 'location.location_text', 'location.checked_out', 'location.checked_out_time']))
            )
        );
        $query->from($db->quoteName('#__bsms_locations', 'location'));

        // Join over the asset groups.
        $query->select($db->quoteName('ag.title', 'access_level'));
        $query->join(
            'LEFT',
            $db->quoteName('#__viewlevels', 'ag') . ' ON ' . $db->quoteName('ag.id') . ' = ' . $db->quoteName('location.access')
        );

        // Join over the users for the checked out user.
        $query->select($db->quoteName('uc.name', 'editor'))
            ->join('LEFT', $db->quoteName('#__users', 'uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('location.checked_out'));

        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            $query->where($db->quoteName('location.access') . ' = ' . (int) $access);
        }

        // Apply location-based visibility filter (multi-campus support).
        // Super admins get [] from getUserLocations() — no filter added (see all).
        $visibleLocations = CwmlocationHelper::getUserLocations();

        if (!empty($visibleLocations)) {
            $query->whereIn($db->quoteName('location.id'), $visibleLocations);
        }

        // Filter by search in title.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where($db->quoteName('location.id') . ' = ' . (int) substr($search, 3));
            } else {
                $search = $db->quote('%' . $db->escape($search, true) . '%');
                $query->where($db->quoteName('location.location_text') . ' LIKE ' . $search);
            }
        }

        // Filter by published state
        $published = $this->getState('filter.published');

        if (is_numeric($published)) {
            $query->where($db->quoteName('location.published') . ' = ' . (int) $published);
        } elseif ($published === '') {
            $query->where('(' . $db->quoteName('location.published') . ' = 0 OR ' . $db->quoteName('location.published') . ' = 1)');
        }

        // Add the list ordering clause
        $orderCol  = $this->state->get('list.ordering', 'location.id');
        $orderDirn = $this->state->get('list.direction', 'desc');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
