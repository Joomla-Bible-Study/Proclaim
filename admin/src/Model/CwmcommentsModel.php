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
 * Comments model class
 *
 * @since  7.0.0
 */
class CwmcommentsModel extends ListModel
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
                'comment.id',
                'published',
                'comment.published',
                'ordering',
                'comment.ordering',
                'studytitle',
                'study.studytitle',
                'bookname',
                'comment.bookname',
                'createdate',
                'comment.createdate',
                'full_name',
                'comment.full_name',
                'language',
                'comment.language',
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
     * @since 7.0
     */
    protected function populateState($ordering = 'comment.comment_date', $direction = 'desc'): void
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

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
        $this->setState('filter.language', $language);

        $location = $this->getUserStateFromRequest($this->context . '.filter.location', 'filter_location');
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
     * Get Stored ID
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
     * List Query
     *
     * @return  \Joomla\Database\QueryInterface   A JDatabaseQuery object to retrieve the data set.
     *
     * @since   7.0
     */
    protected function getListQuery(): mixed
    {
        // Create a new query object.
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Select the required fields from the table.
        $query = $db->getQuery(true);
        $query->select(
            $this->getState(
                'list.select',
                implode(', ', $db->quoteName(
                    [
                        'comment.id',
                        'comment.published',
                        'comment.user_id',
                        'comment.full_name',
                        'comment.user_email',
                        'comment.comment_date',
                        'comment.comment_text',
                        'comment.access',
                        'comment.language',
                        'comment.asset_id',
                        'comment.checked_out',
                        'comment.checked_out_time',
                    ]
                ))
            )
        );
        $query->from($db->quoteName('#__bsms_comments', 'comment'));

        // Join over the language
        $query->select($db->quoteName('l.title', 'language_title'));
        $query->select($db->quoteName('l.image', 'language_image'));
        $query->join(
            'LEFT',
            $db->quoteName('#__languages', 'l') . ' ON ' . $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('comment.language')
        );

        // Filter by published state
        $published = $this->getState('filter.published');

        if (is_numeric($published)) {
            $query->where($db->quoteName('comment.published') . ' = ' . (int) $published);
        } elseif ($published === '') {
            $query->where('(' . $db->quoteName('comment.published') . ' = 0 OR ' . $db->quoteName('comment.published') . ' = 1)');
        }

        // Filter by search in title.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where($db->quoteName('comment.id') . ' = ' . (int) substr($search, 3));
            } else {
                $search = $db->quote('%' . $db->escape($search, true) . '%');
                $query->where('(' . $db->quoteName('study.studytitle') . ' LIKE ' . $search . ' OR ' . $db->quoteName('book.bookname') . ' LIKE ' . $search . ')');
            }
        }

        // Join over Studies
        $query->select($db->quoteName('study.studytitle', 'studytitle'));
        $query->select($db->quoteName('study.chapter_begin'));
        $query->select($db->quoteName('study.studydate'));
        $query->select($db->quoteName('study.booknumber'));
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_studies', 'study') . ' ON ' . $db->quoteName('study.id') . ' = ' . $db->quoteName('comment.study_id')
        );

        // Join over books
        $query->select($db->quoteName('book.bookname', 'bookname'));
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_books', 'book') . ' ON ' . $db->quoteName('book.booknumber') . ' = ' . $db->quoteName('study.booknumber')
        );

        // Join over the asset groups.
        $query->select($db->quoteName('ag.title', 'access_level'));
        $query->join(
            'LEFT',
            $db->quoteName('#__viewlevels', 'ag') . ' ON ' . $db->quoteName('ag.id') . ' = ' . $db->quoteName('comment.access')
        );

        // Filter by access level (dropdown — was defined in XML but never applied to query)
        $access = $this->getState('filter.access');

        if (is_numeric($access)) {
            $query->where($db->quoteName('comment.access') . ' = ' . (int) $access);
        }

        // Restrict non-admin users to their authorised view levels
        $user = $this->getCurrentUser();

        if (!$user->authorise('core.admin')) {
            $query->whereIn($db->quoteName('comment.access'), $user->getAuthorisedViewLevels());

            // Location-based filtering via parent study
            $studyColumns = $db->getTableColumns('#__bsms_studies');

            if (CwmlocationHelper::isEnabled() && isset($studyColumns['location_id'])) {
                $accessible = CwmlocationHelper::getUserLocations((int) $user->id);

                if (!empty($accessible)) {
                    $inClause = implode(',', array_map('intval', $accessible));
                    $query->where(
                        '(' . $db->quoteName('study.location_id') . ' IS NULL'
                        . ' OR ' . $db->quoteName('study.location_id') . ' IN (' . $inClause . '))'
                    );
                } else {
                    $query->where($db->quoteName('study.location_id') . ' IS NULL');
                }
            }
        }

        // Filter by location (dropdown) via parent study
        $studyColumns = $studyColumns ?? $db->getTableColumns('#__bsms_studies');

        if (isset($studyColumns['location_id'])) {
            $location = $this->getState('filter.location');

            if (is_numeric($location)) {
                $locationVal = (int) $location;
                $query->where($db->quoteName('study.location_id') . ' = :locationId')
                    ->bind(':locationId', $locationVal, \Joomla\Database\ParameterType::INTEGER);
            }
        }

        // Join over the users for the checked out user.
        $query->select($db->quoteName('uc.name', 'editor'))
            ->join('LEFT', $db->quoteName('#__users', 'uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('comment.checked_out'));

        // Add the list ordering clause
        $orderCol  = $this->state->get('list.ordering', 'study.studytitle');
        $orderDirn = $this->state->get('list.direction', 'asc');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
