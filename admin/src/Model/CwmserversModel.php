<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Uri\Uri;

/**
 * Servers model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmserversModel extends ListModel
{
    /**
     * A reverse lookup of the Endpoint id to Endpoint name
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
     * Method to get the reverse lookup of the server_id to server_name
     *
     * @return  array
     *
     * @since   9.0.0
     */
    public function getIdToNameReverseLookup()
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
    public function getTypeReverseLookup()
    {
        if (empty($this->rlu_type)) {
            $this->getServerOptions();
        }

        return $this->rlu_type;
    }

    /**
     * Get a list of available endpoints
     *
     * @return  array|boolean   Array of available endpoints options grouped by type or false if there aren't any
     *
     * @since   9.0.0
     */
    public function getServerOptions()
    {
        $options = [];

        // Path to endpoints
        $path = JPATH_ADMINISTRATOR . '/components/com_proclaim/src/Addons/Servers';

        if (Folder::exists($path)) {
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
        if ($layout = $app->input->get('layout')) {
            $this->context .= '.' . $layout;
        }

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * Method to get a JDatabaseQuery object for retrieving the data set from a database.
     *
     * @return  \Joomla\Database\QueryInterface   A JDatabaseQuery object to retrieve the data set.
     *
     * @throws  \Exception
     * @since   7.0.0
     */
    protected function getListQuery()
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $user  = Factory::getApplication()->getSession()->get('user');

        $query->select($this->getState('list.select', 'server.id, server.published, server.server_name, server.type'));
        $query->from('#__bsms_servers AS server');

        // Filter by published state
        $published = $this->getState('filter.published');

        if (Factory::getApplication()->input->get('layout') === 'modal' && $published === '') {
            $published = 1;
        }

        if (is_numeric($published)) {
            $query->where('server.published = ' . (int)$published);
        } elseif ($published === '') {
            $query->where('(server.published = 0 OR server.published = 1)');
        }

        // Implement View Level Access
        if (!$user->authorise('core.cwmadmin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('server.access IN (' . $groups . ')');
        }

        // Add the list ordering clause
        $orderCol  = $this->state->get('list.ordering', 'server.server_name');
        $orderDirn = $this->state->get('list.direction', 'DESC');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
