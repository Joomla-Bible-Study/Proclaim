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
 * Podcasts model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmpodcastsModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @throws \Exception
     * @since 7.0
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id',
                'podcast.id',
                'filename',
                'podcast.filename',
                'published',
                'podcast.published',
                'ordering',
                'podcast.ordering',
                'language',
                'podcast.language',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Method to get a store id based on the model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  An identifier string to generate the store id.
     *
     * @return  string  A store id.
     *
     * @since 7.0
     */
    protected function getStoreId($id = ''): string
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . serialize($this->getState('filter.access'));
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.language');
        $id .= ':' . $this->getState('filter.location');

        return parent::getStoreId($id);
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
     * @since   7.0
     */
    protected function populateState($ordering = 'podcast.title', $direction = 'ASC'): void
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

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '');
        $this->setState('filter.search', $search);

        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '');
        $this->setState('filter.access', $access);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
        $this->setState('filter.language', $language);

        $location = $this->getUserStateFromRequest($this->context . '.filter.location', 'filter_location', '');
        $this->setState('filter.location', $location);

        parent::populateState($ordering, $direction);

        if (!empty($forcedLanguage)) {
            $this->setState('filter.language', $forcedLanguage);
            $this->setState('filter.forcedLanguage', $forcedLanguage);
        }
    }

    /**
     * Method to get a JDatabaseQuery object for retrieving the data set from a database.
     *
     * @return  \Joomla\Database\QueryInterface   A JDatabaseQuery object to retrieve the data set.
     *
     * @throws \Exception
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
                implode(', ', $db->quoteName(
                    [
                        'podcast.id',
                        'podcast.published',
                        'podcast.title',
                        'podcast.description',
                        'podcast.filename',
                        'podcast.language',
                        'podcast.access',
                        'podcast.checked_out',
                        'podcast.checked_out_time',
                    ]
                ))
            )
        );
        $query->from($db->quoteName('#__bsms_podcast', 'podcast'));

        // Join over the language
        $query->select($db->quoteName('l.title', 'language_title'));
        $query->join(
            'LEFT',
            $db->quoteName('#__languages', 'l') . ' ON ' . $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('podcast.language')
        );

        // Join over the asset groups.
        $query->select($db->quoteName('ag.title', 'access_level'))
            ->join(
                'LEFT',
                $db->quoteName('#__viewlevels', 'ag') . ' ON ' . $db->quoteName('ag.id') . ' = ' . $db->quoteName('podcast.access')
            );

        // Join over the users for the checked out user.
        $query->select($db->quoteName('uc.name', 'editor'))
            ->join('LEFT', $db->quoteName('#__users', 'uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('podcast.checked_out'));

        // Join location name for display (graceful — column may not exist on older installs)
        $columns = $db->getTableColumns('#__bsms_podcast');

        if (isset($columns['location_id'])) {
            $query->select($db->quoteName('loc.location_text', 'location_text'))
                ->join('LEFT', $db->quoteName('#__bsms_locations', 'loc') . ' ON ' . $db->quoteName('loc.id') . ' = ' . $db->quoteName('podcast.location_id'));
        }

        // Filter by access level (dropdown).
        if ($access = $this->getState('filter.access')) {
            $query->where($db->quoteName('podcast.access') . ' = ' . (int) $access);
        }

        // Restrict non-admin users: hybrid location + access-level filter
        if (!$user->authorise('core.admin')) {
            if (CwmlocationHelper::isEnabled() && isset($columns['location_id'])) {
                $accessible = CwmlocationHelper::getUserLocations((int) $user->id);

                if (!empty($accessible)) {
                    $inClause = implode(',', array_map('intval', $accessible));
                    $query->where(
                        '(' . $db->quoteName('podcast.location_id') . ' IS NULL'
                        . ' OR ' . $db->quoteName('podcast.location_id') . ' IN (' . $inClause . '))'
                    );
                } else {
                    $query->where($db->quoteName('podcast.location_id') . ' IS NULL');
                }
            } else {
                $query->whereIn($db->quoteName('podcast.access'), $user->getAuthorisedViewLevels());
            }
        }

        // Filter by location (dropdown)
        if (isset($columns['location_id'])) {
            $location = $this->getState('filter.location');

            if (is_numeric($location)) {
                $locationVal = (int) $location;
                $query->where($db->quoteName('podcast.location_id') . ' = :locationId')
                    ->bind(':locationId', $locationVal, \Joomla\Database\ParameterType::INTEGER);
            }
        }

        // Filter by published state
        $published = $this->getState('filter.published');

        if (is_numeric($published)) {
            $query->where($db->quoteName('podcast.published') . ' = ' . (int) $published);
        } elseif ($published === '') {
            $query->where('(' . $db->quoteName('podcast.published') . ' = 0 OR ' . $db->quoteName('podcast.published') . ' = 1)');
        }

        // Filter on the language.
        if ($language = $this->getState('filter.language')) {
            $query->where($db->quoteName('podcast.language') . ' = ' . $db->quote($language));
        }

        // Filter by search in filename or study title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where($db->quoteName('podcast.id') . ' = ' . (int) substr($search, 3));
            } else {
                $search = $db->quote('%' . $db->escape($search, true) . '%');
                $query->where('(' . $db->quoteName('podcast.title') . ' LIKE ' . $search . ' OR ' . $db->quoteName('podcast.description') . ' LIKE ' . $search . ')');
            }
        }

        // Add the list ordering clause
        $orderCol  = $this->state->get('list.ordering', 'podcast.id');
        $orderDirn = $this->state->get('list.direction', 'desc');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
