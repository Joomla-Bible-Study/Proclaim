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
 * Template codes model class
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 */
class CwmtemplatecodesModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @throws \Exception
     * @since 7.1
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id',
                'templatecode.id',
                'published',
                'templatecode.published',
                'type',
                'templatecode.type',
                'access',
                'templatecode.access',
            ];
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
     * @throws \Exception
     * @since   7.1
     */
    protected function populateState($ordering = 'templatecode.filename', $direction = 'ASC'): void
    {
        $app = Factory::getApplication();

        // Adjust the context to support modal layouts.
        if ($layout = $app->getInput()->get('layout')) {
            $this->context .= '.' . $layout;
        }

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $location = $this->getUserStateFromRequest($this->context . '.filter.location', 'filter_location');
        $this->setState('filter.location', $location);

        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * Get list query
     *
     * @return \Joomla\Database\QueryInterface
     *
     * @since 7.1
     */
    protected function getListQuery(): mixed
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $user  = $this->getCurrentUser();

        $query->select(
            $this->getState(
                'list.select',
                implode(', ', $db->quoteName([
                    'templatecode.id',
                    'templatecode.published',
                    'templatecode.filename',
                    'templatecode.templatecode',
                    'templatecode.type',
                    'templatecode.checked_out',
                    'templatecode.checked_out_time',
                ]))
            )
        );
        $query->from($db->quoteName('#__bsms_templatecode', 'templatecode'));

        // Join over the users for the checked out user.
        $query->select($db->quoteName('uc.name', 'editor'))
            ->join('LEFT', $db->quoteName('#__users', 'uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('templatecode.checked_out'));

        // Filter by search in filename or study title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where($db->quoteName('templatecode.id') . ' = ' . (int)substr($search, 3));
            } else {
                $search = $db->quote('%' . $db->escape($search, true) . '%');
                $query->where(
                    '(' . $db->quoteName('templatecode.filename') . ' LIKE ' . $search
                    . ' OR ' . $db->quoteName('templatecode.templatecode') . ' LIKE ' . $search . ')'
                );
            }
        }

        // Join location name for display (graceful — column may not exist on older installs)
        $columns = $db->getTableColumns('#__bsms_templatecode');

        if (isset($columns['location_id'])) {
            $query->select($db->quoteName('loc.location_text', 'location_text'))
                ->join('LEFT', $db->quoteName('#__bsms_locations', 'loc') . ' ON ' . $db->quoteName('loc.id') . ' = ' . $db->quoteName('templatecode.location_id'));
        }

        // Restrict non-admin users: location-based filter when enabled
        if (!$user->authorise('core.admin')) {
            if (CwmlocationHelper::isEnabled() && isset($columns['location_id'])) {
                $accessible = CwmlocationHelper::getUserLocations((int) $user->id);

                if (!empty($accessible)) {
                    $inClause = implode(',', array_map('intval', $accessible));
                    $query->where(
                        '(' . $db->quoteName('templatecode.location_id') . ' IS NULL'
                        . ' OR ' . $db->quoteName('templatecode.location_id') . ' IN (' . $inClause . '))'
                    );
                } else {
                    $query->where($db->quoteName('templatecode.location_id') . ' IS NULL');
                }
            }
        }

        // Filter by location (dropdown)
        if (isset($columns['location_id'])) {
            $location = $this->getState('filter.location');

            if (is_numeric($location)) {
                $locationVal = (int) $location;
                $query->where($db->quoteName('templatecode.location_id') . ' = :locationId')
                    ->bind(':locationId', $locationVal, \Joomla\Database\ParameterType::INTEGER);
            }
        }

        // Filter by published state
        $published = $this->getState('filter.published');

        if (is_numeric($published)) {
            $query->where($db->quoteName('templatecode.published') . ' = ' . (int)$published);
        } elseif ($published === '') {
            $query->where('(' . $db->quoteName('templatecode.published') . ' = 0 OR ' . $db->quoteName('templatecode.published') . ' = 1)');
        }

        // Add the list ordering clause
        $orderCol  = $this->state->get('list.ordering', 'templatecode.filename');
        $orderDirn = $this->state->get('list.direction', 'ASC');
        $query->order($db->quoteName($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
