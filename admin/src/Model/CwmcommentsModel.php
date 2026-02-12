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

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

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
        $db = Factory::getContainer()->get('DatabaseDriver');

        // Select the required fields from the table.
        $query = $db->getQuery(true);
        $query->select(
            $this->getState(
                'list.select',
                implode(', ', $db->qn(
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
        $query->from($db->qn('#__bsms_comments', 'comment'));

        // Join over the language
        $query->select($db->qn('l.title', 'language_title'));
        $query->select($db->qn('l.image', 'language_image'));
        $query->join(
            'LEFT',
            $db->qn('#__languages', 'l') . ' ON ' . $db->qn('l.lang_code') . ' = ' . $db->qn('comment.language')
        );

        // Filter by published state
        $published = $this->getState('filter.published');

        if (is_numeric($published)) {
            $query->where($db->qn('comment.published') . ' = ' . (int) $published);
        } elseif ($published === '') {
            $query->where('(' . $db->qn('comment.published') . ' = 0 OR ' . $db->qn('comment.published') . ' = 1)');
        }

        // Filter by search in title.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where($db->qn('comment.id') . ' = ' . (int) substr($search, 3));
            } else {
                $search = $db->quote('%' . $db->escape($search, true) . '%');
                $query->where('(' . $db->qn('study.studytitle') . ' LIKE ' . $search . ' OR ' . $db->qn('book.bookname') . ' LIKE ' . $search . ')');
            }
        }

        // Join over Studies
        $query->select($db->qn('study.studytitle', 'studytitle'));
        $query->select($db->qn('study.chapter_begin'));
        $query->select($db->qn('study.studydate'));
        $query->select($db->qn('study.booknumber'));
        $query->join(
            'LEFT',
            $db->qn('#__bsms_studies', 'study') . ' ON ' . $db->qn('study.id') . ' = ' . $db->qn('comment.study_id')
        );

        // Join over books
        $query->select($db->qn('book.bookname', 'bookname'));
        $query->join(
            'LEFT',
            $db->qn('#__bsms_books', 'book') . ' ON ' . $db->qn('book.booknumber') . ' = ' . $db->qn('study.booknumber')
        );

        // Join over the asset groups.
        $query->select($db->qn('ag.title', 'access_level'));
        $query->join(
            'LEFT',
            $db->qn('#__viewlevels', 'ag') . ' ON ' . $db->qn('ag.id') . ' = ' . $db->qn('comment.access')
        );

        // Join over the users for the checked out user.
        $query->select($db->qn('uc.name', 'editor'))
            ->join('LEFT', $db->qn('#__users', 'uc') . ' ON ' . $db->qn('uc.id') . ' = ' . $db->qn('comment.checked_out'));

        // Add the list ordering clause
        $orderCol  = $this->state->get('list.ordering', 'study.studytitle');
        $orderDirn = $this->state->get('list.direction', 'asc');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
