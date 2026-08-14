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
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\QueryInterface;
use Joomla\Filesystem\Folder;

/**
 * Servers model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmserversModel extends ListModel
{
    /**
     * A reverse lookup of the Endpoint ID to the Endpoint name
     *
     * @var     array
     * @since   9.0.0
     */
    protected array $rlu_id = [];

    /**
     * A reverse lookup of the Endpoint type to Endpoint name
     *
     * @var     array
     * @since    9.0.0
     */
    protected array $rlu_type = [];

    /**
     * Constructor.
     *
     * Declares the columns the list may be ordered and filtered by. Without this
     * whitelist ListModel::populateState() rejects every value the column headers
     * submit, so clicking a header reloaded the page without reordering it.
     *
     * Dotted names must match the query alias in getListQuery() -- `server`.
     *
     * @param   array                     $config   An optional associative array of configuration settings.
     * @param   MVCFactoryInterface|null  $factory  The factory.
     *
     * @throws \Exception
     * @since  10.5.6
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id',
                'server.id',
                'server_name',
                'server.server_name',
                'published',
                'server.published',
                'access',
                'server.access',
                'access_level',
                'type',
                'server.type',
                'location',
                'server.location_id',
            ];
        }

        parent::__construct($config, $factory);
    }

    /**
     * Method to get the reverse lookup of the server_id to server_name
     *
     * @return  array
     *
     * @since   9.0.0
     */
    public function getIdToNameReverseLookup(): array
    {
        if (empty($this->rlu_id)) {
            $_rlu = [];

            foreach ($this->getItems() as $server) {
                $_rlu[$server->id] = [
                    'name' => $server->server_name,
                    'type' => $server->type,
                ];
            }

            $this->rlu_id = $_rlu;
        }

        return $this->rlu_id;
    }

    /**
     * Method to get the reverse lookup of the Endpoint type to Endpoint name
     *
     * @return  array   Array of reverse lookup
     *
     * @since   9.0.0
     */
    public function getTypeReverseLookup(): array
    {
        if (empty($this->rlu_type)) {
            $this->getServerOptions();
        }

        return $this->rlu_type;
    }

    /**
     * Get a list of available endpoints
     *
     * @return  array|bool   Array of available endpoints options grouped by type or false if there aren't any
     *
     * @since   9.0.0
     */
    public function getServerOptions(): bool|array
    {
        $options = [];

        // Path to endpoints
        $path = JPATH_ADMINISTRATOR . '/components/com_proclaim/src/Addons/Servers';

        if (file_exists($path)) {
            $servers = Folder::folders($path);
        } else {
            return false;
        }

        $i = 0;

        foreach ($servers as $server) {
            $file = $path . '/' . $server . '/' . strtolower($server) . '.xml';

            if (is_file($file) && $xml = simplexml_load_string(file_get_contents($file))) {
                // Create the reverse lookup for Endpoint type to Endpoint name
                $this->rlu_type[strtolower($server)] = (string)$xml->name;

                // Legacy addon is kept for existing servers but hidden from the type picker
                if (strtolower($server) === 'legacy') {
                    unset($xml);

                    continue;
                }

                $o              = new \stdClass();
                $o->id          = $i;
                $o->type        = (string)$xml['type'];
                $o->name        = (string)$server;
                $o->image_url   = Uri::base(
                ) . 'components/com_proclaim/src/Addons/Servers/' . $server . '/' . strtolower($server) . '.png';
                $o->title       = (string)$xml->name;
                $o->description = (string)$xml->description;
                $o->path        = $path . '/' . $server . '/';

                $options[$i++] = $o;
                unset($xml);
            }
        }

        return $options;
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
     * @throws \Exception
     * @since   7.0.0
     */
    protected function populateState($ordering = 'server.server_name', $direction = 'DESC'): void
    {
        $app = Factory::getApplication();

        // Adjust the context to support modal layouts.
        if ($layout = $app->getInput()->get('layout')) {
            $this->context .= '.' . $layout;
        }

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $location = $this->getUserStateFromRequest($this->context . '.filter.location', 'filter_location', '');
        $this->setState('filter.location', $location);

        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * Build a store id that distinguishes one filter combination from another.
     *
     * ⚠️ Without this the parent keys the result cache on pagination alone, so
     * two different filter values in one request return the same rows.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string
     *
     * @since   10.5.8
     */
    protected function getStoreId($id = ''): string
    {
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.location');
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.type');

        return parent::getStoreId($id);
    }

    /**
     * Method to get a JDatabaseQuery object for retrieving the data set from a database.
     *
     * @return  QueryInterface|string   A JDatabaseQuery object to retrieve the data set.
     *
     * @throws  \Exception
     * @since   7.0.0
     */
    protected function getListQuery(): QueryInterface|string
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery();
        $user  = $this->getCurrentUser();

        $query->select($this->getState('list.select', 'server.id, server.published, server.server_name, server.type, server.location_id, server.checked_out, server.checked_out_time'));
        $query->from($db->quoteName('#__bsms_servers', 'server'));

        // Join over the users for the checked out user.
        $query->select($db->quoteName('uc.name', 'editor'))
            ->join('LEFT', $db->quoteName('#__users', 'uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('server.checked_out'));

        // Join location name for display
        $query->select($db->quoteName('loc.location_text', 'location_text'))
            ->join('LEFT', $db->quoteName('#__bsms_locations', 'loc') . ' ON ' . $db->quoteName('loc.id') . ' = ' . $db->quoteName('server.location_id'));

        // Filter by published state
        $published = $this->getState('filter.published');

        if (Factory::getApplication()->getInput()->get('layout') === 'modal' && $published === '') {
            $published = 1;
        }

        if (is_numeric($published)) {
            $publishedValue = (int) $published;
            $query->where($db->quoteName('server.published') . ' = :published')
                ->bind(':published', $publishedValue, \Joomla\Database\ParameterType::INTEGER);
        } elseif ($published === '') {
            $query->where($db->quoteName('server.published') . ' IN (0, 1)');
        }

        // Restrict non-admin users: use hybrid location filter when location system is enabled,
        // otherwise fall back to standard Joomla access-level filtering
        if (!$user->authorise('core.admin')) {
            if (CwmlocationHelper::isEnabled()) {
                // Shared server pattern: NULL = visible to all, specific ID = campus-restricted
                $accessible = CwmlocationHelper::getUserLocations((int) $user->id);

                if (!empty($accessible)) {
                    // Show shared (NULL) servers + servers belonging to user's accessible locations
                    $inClause = implode(',', array_map('intval', $accessible));
                    $query->where(
                        '(' . $db->quoteName('server.location_id') . ' IS NULL'
                        . ' OR ' . $db->quoteName('server.location_id') . ' IN (' . $inClause . '))'
                    );
                } else {
                    // No campus access — only shared (NULL) servers
                    $query->where($db->quoteName('server.location_id') . ' IS NULL');
                }
            } else {
                // Location system disabled — standard Joomla access-level filter
                $query->whereIn($db->quoteName('server.access'), $user->getAuthorisedViewLevels());
            }
        }

        // Filter by location
        $location = $this->getState('filter.location');

        if (is_numeric($location)) {
            $locationVal = (int) $location;
            $query->where($db->quoteName('server.location_id') . ' = :locationId')
                ->bind(':locationId', $locationVal, \Joomla\Database\ParameterType::INTEGER);
        }

        // Search and type are set only by the API (?filter[search]=,
        // ?filter[type]=); the admin screen has no field for either, so
        // getState returns null there and both stay inert.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where($db->quoteName('server.id') . ' = ' . (int) substr($search, 3));
            } else {
                $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';
                $query->where($db->quoteName('server.server_name') . ' LIKE :search')
                    ->bind(':search', $like, \Joomla\Database\ParameterType::STRING);
            }
        }

        // ⚠️ server.type is CHAR(255), not an id. Bound as a string — casting
        // it to int, as every other filter in this file does, would match
        // every row whose type does not begin with a digit against 0.
        $type = $this->getState('filter.type');

        if (\is_string($type) && $type !== '') {
            $query->where($db->quoteName('server.type') . ' = :type')
                ->bind(':type', $type, \Joomla\Database\ParameterType::STRING);
        }

        // Add the list ordering clause with whitelist validation
        $orderCol  = $this->state->get('list.ordering', 'server.server_name');
        $orderDirn = $this->state->get('list.direction', 'DESC');

        // Validate ordering column against whitelist
        $allowedColumns = ['server.id', 'server.server_name', 'server.published', 'server.type'];
        if (!\in_array($orderCol, $allowedColumns, true)) {
            $orderCol = 'server.server_name';
        }

        // Validate direction
        $orderDirn = strtoupper($orderDirn) === 'ASC' ? 'ASC' : 'DESC';

        $query->order($db->quoteName($orderCol) . ' ' . $orderDirn);

        return $query;
    }
}
