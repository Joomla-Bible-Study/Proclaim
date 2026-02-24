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
    protected function populateState($ordering = 'study.studydate', $direction = 'desc'): void
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

        $formSubmited = $app->getInput()->post->get('form_submited');

        // Gets the value of a user state variable and sets it in the session
        $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
        $this->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');

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
     * Build an SQL query to load the list data
     *
     * @return  QueryInterface|string
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
                        'study.id',
                        'study.published',
                        'study.studydate',
                        'study.studytitle',
                        'study.ordering',
                        'study.hits',
                        'study.alias',
                        'study.language',
                        'study.access',
                        'study.publish_up',
                        'study.publish_down',
                        'study.params',
                        'study.checked_out',
                        'study.checked_out_time',
                    ]
                ))
            )
        );
        $query->from($db->quoteName('#__bsms_studies', 'study'));

        // Join over the language
        $query->select($db->quoteName('l.title', 'language_title'));
        $query->join(
            'LEFT',
            $db->quoteName('#__languages', 'l') . ' ON ' . $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('study.language')
        );

        // Join over Message Types
        $query->select($db->quoteName('messageType.message_type', 'messageType'));
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_message_type', 'messageType') . ' ON ' . $db->quoteName('messageType.id') . ' = ' . $db->quoteName('study.messagetype')
        );

        // Join over Teachers (via junction table, primary teacher only; falls back to legacy teacher_id)
        $query->select($db->quoteName('teacher.teachername', 'teachername'));
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_study_teachers', 'stj') . ' ON ' . $db->quoteName('stj.study_id') . ' = ' . $db->quoteName('study.id')
            . ' AND ' . $db->quoteName('stj.ordering') . ' = 0'
        );
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_teachers', 'teacher') . ' ON ' . $db->quoteName('teacher.id')
            . ' = COALESCE(' . $db->quoteName('stj.teacher_id') . ', ' . $db->quoteName('study.teacher_id') . ')'
        );

        // Join over Series
        $query->select($db->quoteName('series.series_text'));
        $query->select($db->quoteName('series.id', 'series_id'));
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_series', 'series') . ' ON ' . $db->quoteName('series.id') . ' = ' . $db->quoteName('study.series_id')
        );

        // Join over Location
        $query->select($db->quoteName('locations.location_text'));
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_locations', 'locations') . ' ON ' . $db->quoteName('locations.id') . ' = ' . $db->quoteName('study.location_id')
        );

        // Join over Plays/Downloads
        $query->select(
            'SUM(' . $db->quoteName('mediafile.plays') . ') AS ' . $db->quoteName('totalplays')
            . ', SUM(' . $db->quoteName('mediafile.downloads') . ') AS ' . $db->quoteName('totaldownloads')
            . ', ' . $db->quoteName('mediafile.study_id')
        );
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_mediafiles', 'mediafile') . ' ON ' . $db->quoteName('mediafile.study_id') . ' = ' . $db->quoteName('study.id')
        );
        $query->group($db->quoteName('study.id'));

        // Join over the users for the checked out user.
        $query->select($db->quoteName('uc.name', 'editor'))
            ->join('LEFT', $db->quoteName('#__users', 'uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('study.checked_out'));

        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            $query->where($db->quoteName('study.access') . ' = ' . (int) $access);
        }

        // Apply hybrid security filter: location-based + Joomla view-level access
        CwmlocationHelper::applySecurityFilter($query, 'study');

        // Filter by teacher
        $teacher = $this->getState('filter.teacher');

        if (is_numeric($teacher)) {
            $tSubquery = $db->getQuery(true)
                ->select('1')
                ->from($db->quoteName('#__bsms_study_teachers', 'stf'))
                ->where($db->quoteName('stf.study_id') . ' = ' . $db->quoteName('study.id'))
                ->where($db->quoteName('stf.teacher_id') . ' = ' . (int) $teacher);
            $query->where('EXISTS (' . $tSubquery . ')');
        }

        // Filter by series
        $series = $this->getState('filter.series');

        if (is_numeric($series)) {
            $query->where($db->quoteName('study.series_id') . ' = ' . (int) $series);
        }

        // Filter by message type
        $messageType = $this->getState('filter.messageType');

        if (is_numeric($messageType)) {
            $query->where($db->quoteName('study.messageType') . ' = ' . (int) $messageType);
        }

        // Filter by Year
        $year = $this->getState('filter.year');

        if (!empty($year)) {
            $query->where('YEAR(' . $db->quoteName('study.studydate') . ') = ' . (int) $year);
        }

        // Filter by published state
        $published = $this->getState('filter.published');

        if (is_numeric($published)) {
            $query->where($db->quoteName('study.published') . ' = ' . (int) $published);
        } elseif ($published === '') {
            $query->where('(' . $db->quoteName('study.published') . ' = 0 OR ' . $db->quoteName('study.published') . ' = 1 OR ' . $db->quoteName('study.published') . ' = 2)');
        }

        // Filter by search in title.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where($db->quoteName('study.id') . ' = ' . (int) substr($search, 3));
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
            $query->where($db->quoteName('study.location_id') . ' = ' . (int) $location);
        }

        // Add the list ordering clause
        $orderCol  = $this->state->get('list.ordering', 'study.studydate');
        $orderDirn = $this->state->get('list.direction', 'DESC');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
