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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseInterface;

/**
 * Playlists model class
 *
 * @package  Proclaim.Admin
 * @since    10.3.3
 */
class CwmplaylistsModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @throws  \Exception
     * @since   10.3.3
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id',
                'playlist.id',
                'title',
                'playlist.title',
                'published',
                'playlist.published',
                'sync_enabled',
                'playlist.sync_enabled',
                'server_id',
                'playlist.server_id',
                'last_sync',
                'playlist.last_sync',
                'ordering',
                'playlist.ordering',
                'access',
                'playlist.access',
                'access_level',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Method to get a store ID based on the model configuration state.
     *
     * @param   string  $id  A prefix for the store ID.
     *
     * @return  string  A store ID.
     *
     * @since   10.3.3
     */
    protected function getStoreId($id = ''): string
    {
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.server_id');

        return parent::getStoreId($id);
    }

    /**
     * Method to auto-populate the model state.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.3.3
     */
    protected function populateState($ordering = 'playlist.ordering', $direction = 'asc'): void
    {
        $app = Factory::getApplication();

        if ($layout = $app->getInput()->get('layout')) {
            $this->context .= '.' . $layout;
        }

        $params = ComponentHelper::getParams('com_proclaim');
        $this->setState('params', $params);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '');
        $this->setState('filter.search', $search);

        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '');
        $this->setState('filter.access', $access);

        $serverId = $this->getUserStateFromRequest($this->context . '.filter.server_id', 'filter_server_id', '');
        $this->setState('filter.server_id', $serverId);

        parent::populateState($ordering, $direction);
    }

    /**
     * Get List Query
     *
     * @return  \Joomla\Database\QueryInterface  A query object to retrieve the data set.
     *
     * @throws  \Exception
     * @since   10.3.3
     */
    protected function getListQuery(): mixed
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery();

        $query->select(
            $this->getState(
                'list.select',
                implode(', ', $db->quoteName([
                    'playlist.id',
                    'playlist.title',
                    'playlist.published',
                    'playlist.access',
                    'playlist.remote_playlist_id',
                    'playlist.server_id',
                    'playlist.series_id',
                    'playlist.sync_enabled',
                    'playlist.last_sync',
                    'playlist.ordering',
                    'playlist.checked_out',
                    'playlist.checked_out_time',
                ]))
            )
        );
        $query->from($db->quoteName('#__bsms_playlists', 'playlist'));

        // Join over the access view levels.
        $query->select($db->quoteName('ag.title', 'access_level'))
            ->join(
                'LEFT',
                $db->quoteName('#__viewlevels', 'ag') . ' ON ' . $db->quoteName('ag.id') . ' = ' . $db->quoteName('playlist.access')
            );

        // Join over the server for its display name.
        $query->select($db->quoteName('srv.server_name', 'server_name'))
            ->join(
                'LEFT',
                $db->quoteName('#__bsms_servers', 'srv') . ' ON ' . $db->quoteName('srv.id') . ' = ' . $db->quoteName('playlist.server_id')
            );

        // Join over the users for the checked out user.
        $query->select($db->quoteName('uc.name', 'editor'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('playlist.checked_out')
            );

        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            $query->where($db->quoteName('playlist.access') . ' = ' . (int) $access);
        }

        // Restrict non-admin users to their authorised view levels. Required for
        // security, not just tidiness: this model backs the REST API, where an
        // unauthenticated caller would otherwise receive playlists whose view
        // level they cannot see.
        $user = $this->getCurrentUser();

        if (!$user->authorise('core.admin')) {
            $query->whereIn($db->quoteName('playlist.access'), $user->getAuthorisedViewLevels());
        }

        // Filter by server.
        if ($serverId = $this->getState('filter.server_id')) {
            $query->where($db->quoteName('playlist.server_id') . ' = ' . (int) $serverId);
        }

        // Filter by search in title.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where($db->quoteName('playlist.id') . ' = ' . (int) substr($search, 3));
            } else {
                $search = $db->quote('%' . $db->escape($search, true) . '%');
                $query->where($db->quoteName('playlist.title') . ' LIKE ' . $search);
            }
        }

        // Filter by published state.
        $published = $this->getState('filter.published');

        if (is_numeric($published)) {
            $query->where($db->quoteName('playlist.published') . ' = ' . (int) $published);
        } elseif ($published === '') {
            $query->where('(' . $db->quoteName('playlist.published') . ' = 0 OR ' . $db->quoteName('playlist.published') . ' = 1)');
        }

        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', 'playlist.ordering');
        $orderDirn = $this->state->get('list.direction', 'asc');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
