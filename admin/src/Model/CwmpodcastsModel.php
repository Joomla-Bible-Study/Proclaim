<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

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
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id',
                'podcast.id',
                'filename',
                'podcast.filename',
                'published',
                'podcast.published',
                'ordering',
                'podcast.ordering',
                'language',
                'podcast.language'
            );
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
     * @since   7.0
     */
    protected function populateState($ordering = 'podcast.title', $direction = 'ASC')
    {
        $app = Factory::getApplication();

        $forcedLanguage = $app->input->get('forcedLanguage', '', 'cmd');

        // Adjust the context to support modal layouts.
        if ($layout = $app->input->get('layout')) {
            $this->context .= '.' . $layout;
        }

        // Adjust the context to support forced languages.
        if ($forcedLanguage) {
            $this->context .= '.' . $forcedLanguage;
        }

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
        $this->setState('filter.access', $access);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
        $this->setState('filter.language', $language);

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
    protected function getListQuery()
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $user  = Factory::getApplication()->getSession()->get('user');

        $query->select(
            $this->getState(
                'list.select',
                'podcast.id, podcast.published, podcast.title, podcast.description, podcast.filename, podcast.language, podcast.access'
            )
        );
        $query->from('#__bsms_podcast AS podcast');

        // Join over the language
        $query->select('l.title AS language_title');
        $query->join('LEFT', $db->qn('#__languages') . ' AS l ON l.lang_code = podcast.language');

        // Join over the asset groups.
        $query->select('ag.title AS access_level')
            ->join('LEFT', '#__viewlevels AS ag ON ag.id = podcast.access');

        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            $query->where('podcast.access = ' . (int)$access);
        }

        // Implement View Level Access
        if (!$user->authorise('core.cwmadmin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('podcast.access IN (' . $groups . ')');
        }

        // Filter by published state
        $published = $this->getState('filter.published');

        if (is_numeric($published)) {
            $query->where('podcast.published = ' . (int)$published);
        } elseif ($published === '') {
            $query->where('(podcast.published = 0 OR podcast.published = 1)');
        }

        // Filter on the language.
        if ($language = $this->getState('filter.language')) {
            $query->where('podcast.language = ' . $db->quote($language));
        }

        // Filter by search in filename or study title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('podcast.id = ' . (int)substr($search, 3));
            } else {
                $search = $db->quote('%' . $db->escape($search, true) . '%');
                $query->where('(podcast.title LIKE ' . $search . ' OR podcast.description LIKE ' . $search . ')');
            }
        }

        // Add the list ordering clause
        $orderCol  = $this->state->get('list.ordering', 'podcast.id');
        $orderDirn = $this->state->get('list.direction', 'desc');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
