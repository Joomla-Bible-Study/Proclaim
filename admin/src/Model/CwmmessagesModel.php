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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\QueryInterface;

/**
 * Message model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmmessagesModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @throws \Exception
     * @since   11.1
     * @see     Controller
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id',
                'study.id',
                'publish_up',
                'study.publish_up',
                'publish_down',
                'study.publish_down',
                'published',
                'study.published',
                'studydate',
                'study.studydate',
                'studytitle',
                'study.studytitle',
                'ordering',
                'study.ordering',
                'year',
                'teacher',
                'teachername',
                'teacher.teachername',
                'messagetype',
                'message_type',
                'messageType.message_type',
                'series',
                'series_text',
                'series.series_text',
                'study.series_id',
                'access',
                'series.access',
                'access_level',
                'location',
                'location.location_text',
                'language',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     *
     * @since   7.1.0
     */
    protected function getStoreId($id = ''): string
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.year');
        $id .= ':' . $this->getState('filter.teacher');
        $id .= ':' . $this->getState('filter.series');
        $id .= ':' . $this->getState('filter.messagetype');
        $id .= ':' . $this->getState('filter.location');
        $id .= ':' . serialize($this->getState('filter.access'));
        $id .= ':' . $this->getState('filter.language');
        $id .= ':' . $this->getState('filter.search');

        return parent::getStoreId($id);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return    void
     *
     * @throws \Exception
     * @since 7.1.0
     */
    protected function populateState($ordering = 'study.studydate', $direction = 'desc')
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

        // Load the parameters.
        $params = ComponentHelper::getParams('com_proclaim');
        $this->setState('params', $params);

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $teacher = $this->getUserStateFromRequest($this->context . '.filter.teacher', 'filter_teacher');
        $this->setState('filter.teacher', $teacher);

        $series = $this->getUserStateFromRequest($this->context . '.filter.series', 'filter_series');
        $this->setState('filter.series', $series);

        $messageType = $this->getUserStateFromRequest($this->context . '.filter.messagetype', 'filter_messagetype');
        $this->setState('filter.messagetype', $messageType);

        $year = $this->getUserStateFromRequest($this->context . '.filter.year', 'filter_year');
        $this->setState('filter.year', $year);

        $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
        $this->setState('filter.language', $language);

        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '', 'int');
        $this->setState('filter.access', $access);

        $location = $this->getUserStateFromRequest($this->context . 'filter.location', 'filter_location');
        $this->setState('filter.location', $location);

        $formSubmited = $app->input->post->get('form_submited');

        // Gets the value of a user state variable and sets it in the session
        $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
        $this->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');

        if ($formSubmited) {
            $access = $app->input->post->get('access');
            $this->setState('filter.access', $access);

            $authorId = $app->input->post->get('author_id');
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
     * Build an SQL query to load the list data
     *
     * @return  QueryInterface|string
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
                'study.id, study.published, study.studydate, study.studytitle, study.ordering, study.hits, study.alias' .
                ', study.language, study.access, study.publish_up, study.publish_down, study.params'
            )
        );
        $query->from('#__bsms_studies AS study');

        // Join over the language
        $query->select('l.title AS language_title');
        $query->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = study.language');

        // Join over Message Types
        $query->select('messageType.message_type AS messageType');
        $query->join('LEFT', '#__bsms_message_type AS messageType ON messageType.id = study.messagetype');

        // Join over Teachers
        $query->select('teacher.teachername AS teachername');
        $query->join('LEFT', '#__bsms_teachers AS teacher ON teacher.id = study.teacher_id');

        // Join over Series
        $query->select('series.series_text, series.id AS series_id');
        $query->join('LEFT', '#__bsms_series AS series ON series.id = study.series_id');

        // Join over Location
        $query->select('locations.location_text');
        $query->join('LEFT', '#__bsms_locations AS locations ON locations.id = study.location_id');

        // Join over Plays/Downloads
        $query->select(
            'SUM(mediafile.plays) AS totalplays, SUM(mediafile.downloads) as totaldownloads, mediafile.study_id'
        );
        $query->join('LEFT', '#__bsms_mediafiles AS mediafile ON mediafile.study_id = study.id');
        $query->group('study.id');

        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            $query->where('study.access = ' . (int)$access);
        }

        // Implement View Level Access
        if (!$user->authorise('core.cwmadmin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('study.access IN (' . $groups . ')');
        }

        // Filter by teacher
        $teacher = $this->getState('filter.teacher');

        if (is_numeric($teacher)) {
            $query->where('study.teacher_id = ' . (int)$teacher);
        }

        // Filter by series
        $series = $this->getState('filter.series');

        if (is_numeric($series)) {
            $query->where('study.series_id = ' . (int)$series);
        }

        // Filter by message type
        $messageType = $this->getState('filter.messageType');

        if (is_numeric($messageType)) {
            $query->where('study.messageType = ' . (int)$messageType);
        }

        // Filter by Year
        $year = $this->getState('filter.year');

        if (!empty($year)) {
            $query->where('YEAR(study.studydate) = ' . (int)$year);
        }

        // Filter by published state
        $published = $this->getState('filter.published');

        if (is_numeric($published)) {
            $query->where('study.published = ' . (int)$published);
        } elseif ($published === '') {
            $query->where('(study.published = 0 OR study.published = 1)');
        }

        // Filter by search in title.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('study.id = ' . (int)substr($search, 3));
            } else {
                $search = '%' . str_replace(' ', '%', trim($search)) . '%';
                $query->where(
                    '(' . $db->quoteName('study.studytitle') . ' LIKE :search1 OR ' . $db->quoteName('study.alias') . ' LIKE :search2)'
                )
                    ->bind([':search1', ':search2'], $search);
            }
        }

        // Filter by location
        $location = $this->getState('filter.location');

        if (is_numeric($location)) {
            $query->where('study.location_id = ' . (int)$location);
        }

        // Add the list ordering clause
        $orderCol  = $this->state->get('list.ordering', 'study.studydate');
        $orderDirn = $this->state->get('list.direction', 'DESC');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
