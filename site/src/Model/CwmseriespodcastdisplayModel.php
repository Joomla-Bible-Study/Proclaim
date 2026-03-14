<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;

/**
 * Model class for PodcastDisplay
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class CwmseriespodcastdisplayModel extends ItemModel
{
    /**
     * Model context string.
     *
     * @var  string
     *
     * @since 7.0
     */
    protected $context = 'com_proclaim.podcastdisplay';

    /**
     * Cached studies query (avoids rebuilding for getTotal).
     *
     * @var  QueryInterface|null
     *
     * @since 10.1.0
     */
    private ?QueryInterface $studiesQuery = null;

    /**
     * Method to get study data.
     *
     * @param   int  $pk  The ID of the study.
     *
     * @return    mixed    Menu item data object on success, false on failure.
     *
     * @throws \Exception
     *
     * @since 7.1.0
     * @todo  look at removing this, as it may not be used. bcc
     */
    public function getItem($pk = null): mixed
    {
        // Initialise variables.
        $pk = (!empty($pk)) ? $pk : (int) $this->getState('series.id');

        if ($pk > 0) {
            if (!isset($this->_item[$pk])) {
                $db    = $this->getDatabase();
                $query = $db->getQuery(true);
                $query->select(
                    $this->getState(
                        'item.select',
                        'se.*,CASE WHEN CHAR_LENGTH(se.alias) THEN CONCAT_WS(\':\', se.id, se.alias) ELSE se.id END AS slug'
                    )
                );
                $query->from($db->quoteName('#__bsms_series', 'se'));

                // Join over teachers
                $query->select(
                    $db->quoteName(
                        ['t.id', 't.teachername', 't.title', 't.teacher_thumbnail'],
                        ['tid', null, 'teachertitle', null]
                    )
                );
                $query->join('LEFT', $db->quoteName('#__bsms_teachers', 't') . ' ON ' . $db->quoteName('se.teacher') . ' = ' . $db->quoteName('t.id'));
                $query->where($db->quoteName('se.id') . ' = :id')
                    ->bind(':id', $pk, ParameterType::INTEGER);
                $db->setQuery($query);
                $data = $db->loadObject();

                if (empty($data)) {
                    Factory::getApplication()->enqueueMessage(Text::_('JBS_CMN_SERIES_NOT_FOUND'), 'message');

                    return false;
                }

                $this->_item[$pk] = $data;
            }

            return $this->_item[$pk];
        }

        return false;
    }

    /**
     * Get Studies Query
     *
     * @return QueryInterface
     *
     * @throws \Exception
     * @since 7.0
     */
    protected function getStudiesQuery(): QueryInterface
    {
        $app = Factory::getApplication();
        $sid = (int) $app->getUserState('sid');

        $params          = $app->getParams();
        $user            = $app->getIdentity();
        $groups          = $user->getAuthorisedViewLevels();
        $db              = $this->getDatabase();
        $query           = $db->getQuery(true);
        $template_params = Cwmparams::getTemplateparams();
        $t_params        = $template_params->params;
        $nullDate        = $db->getNullDate();

        $query->select(
            $this->getState(
                'list.select',
                implode(', ', $db->quoteName([
                    'study.id', 'study.published', 'study.studydate', 'study.studytitle',
                    'study.booknumber', 'study.chapter_begin', 'study.verse_begin',
                    'study.chapter_end', 'study.verse_end', 'study.hits', 'study.alias',
                    'study.studyintro', 'study.teacher_id', 'study.secondary_reference',
                    'study.booknumber2', 'study.location_id',
                ]))
            )
        );
        $quotedNullDate = $db->quote($nullDate);
        // Use studydate as fallback for modified
        $query->select(
            'CASE WHEN ' . $db->quoteName('study.modified') . ' = ' . $quotedNullDate
            . ' THEN ' . $db->quoteName('study.studydate') . ' ELSE ' . $db->quoteName('study.modified')
            . ' END AS ' . $db->quoteName('modified')
        );
        $query->select($db->quoteName('study.modified_by') . ', ' . $db->quoteName('study.user_name', 'modified_by_name'));
        // Use studydate as fallback for publish_up
        $query->select(
            'CASE WHEN ' . $db->quoteName('study.publish_up') . ' = ' . $quotedNullDate
            . ' THEN ' . $db->quoteName('study.studydate') . ' ELSE ' . $db->quoteName('study.publish_up')
            . ' END AS ' . $db->quoteName('publish_up')
        );
        $query->select(implode(', ', $db->quoteName([
            'study.publish_down', 'study.series_id', 'study.download_id',
            'study.thumbnailm', 'study.thumbhm', 'study.thumbwm',
            'study.access', 'study.user_name', 'study.user_id', 'study.studynumber',
            'study.chapter_begin2', 'study.chapter_end2', 'study.verse_end2', 'study.verse_begin2',
        ])));
        $query->select($query->length($db->quoteName('study.studytext')) . ' AS ' . $db->quoteName('readmore'));
        $query->select(
            'CASE WHEN CHAR_LENGTH(' . $db->quoteName('study.alias') . ') THEN CONCAT_WS('
            . $db->quote(':') . ', ' . $db->quoteName('study.id') . ', ' . $db->quoteName('study.alias')
            . ') ELSE ' . $db->quoteName('study.id') . ' END AS ' . $db->quoteName('slug')
        );
        $query->from($db->quoteName('#__bsms_studies', 'study'));

        // Join over Message Types
        $query->select($db->quoteName('messageType.message_type', 'message_type'));
        $query->join('LEFT', $db->quoteName('#__bsms_message_type', 'messageType') . ' ON ' . $db->quoteName('messageType.id') . ' = ' . $db->quoteName('study.messagetype'));

        // Join over Teachers
        $query->select(
            $db->quoteName('teacher.teachername', 'teachername') . ', ' .
            $db->quoteName('teacher.title', 'teachertitle') . ', ' .
            $db->quoteName('teacher.teacher_thumbnail', 'thumb')
        );
        $query->join('LEFT', $db->quoteName('#__bsms_study_teachers', 'st') . ' ON ' . $db->quoteName('st.study_id') . ' = ' . $db->quoteName('study.id') . ' AND ' . $db->quoteName('st.ordering') . ' = 0');
        $query->join('LEFT', $db->quoteName('#__bsms_teachers', 'teacher') . ' ON ' . $db->quoteName('teacher.id') . ' = COALESCE(' . $db->quoteName('st.teacher_id') . ', ' . $db->quoteName('study.teacher_id') . ')');

        // Join over Series
        $query->select(
            $db->quoteName(
                ['series.series_text', 'series.series_thumbnail', 'series.description', 'series.access'],
                [null, null, 'sdescription', 'series_access']
            )
        );
        $query->join('LEFT', $db->quoteName('#__bsms_series', 'series') . ' ON ' . $db->quoteName('series.id') . ' = ' . $db->quoteName('study.series_id'));

        // Join over Books
        $query->select($db->quoteName('book.bookname'));
        $query->join('LEFT', $db->quoteName('#__bsms_books', 'book') . ' ON ' . $db->quoteName('book.booknumber') . ' = ' . $db->quoteName('study.booknumber'));

        $query->select($db->quoteName('book2.bookname', 'bookname2'));
        $query->join('LEFT', $db->quoteName('#__bsms_books', 'book2') . ' ON ' . $db->quoteName('book2.booknumber') . ' = ' . $db->quoteName('study.booknumber2'));

        // Join over Locations
        $query->select($db->quoteName('locations.location_text'));
        $query->join('LEFT', $db->quoteName('#__bsms_locations', 'locations') . ' ON ' . $db->quoteName('study.location_id') . ' = ' . $db->quoteName('locations.id'));

        // Join over users
        $query->select($db->quoteName('users.name', 'submitted'));
        $query->join('LEFT', $db->quoteName('#__users', 'users') . ' ON ' . $db->quoteName('study.user_id') . ' = ' . $db->quoteName('users.id'));

        $query->group($db->quoteName('study.id'));

        // Filter only for authorized view
        $query->whereIn($db->quoteName('study.access'), $groups);
        $query->extendWhere(
            'AND',
            [
                $db->quoteName('series.access') . ' IN (' . implode(',', $groups) . ')',
                $db->quoteName('study.series_id') . ' <= 0',
                $db->quoteName('study.series_id') . ' IS NULL',
            ],
            'OR'
        );

        // Filter by published state based on show_archived parameter
        $showArchived = $params->get('show_archived', '');
        if ($showArchived === '' || $showArchived === null) {
            $showArchived = $t_params->get('sddefault_show_archived', '0');
        }
        switch ($showArchived) {
            case '1': // Archived only
                $query->whereIn($db->quoteName('study.published'), [2]);
                break;
            case '2': // Both published and archived
                $query->whereIn($db->quoteName('study.published'), [1, 2]);
                break;
            default: // Published only (backward compatible)
                $query->whereIn($db->quoteName('study.published'), [1]);
                break;
        }

        // Cascading series date window for non-admin users (like Joomla categories)
        $canEditState = $user->authorise('core.edit.state', 'com_proclaim');
        $canEdit      = $user->authorise('core.edit', 'com_proclaim');

        if (!$canEditState && !$canEdit) {
            $quotedNow = $db->quote((new Date())->toSql());
            $query->where(
                '(('
                . $db->quoteName('series.published') . ' = 1'
                . ' AND (' . $db->quoteName('series.publish_up') . ' = ' . $db->quote($nullDate) . ' OR ' . $db->quoteName('series.publish_up') . ' <= ' . $quotedNow . ')'
                . ' AND (' . $db->quoteName('series.publish_down') . ' = ' . $db->quote($nullDate) . ' OR ' . $db->quoteName('series.publish_down') . ' >= ' . $quotedNow . ')'
                . ') OR ' . $db->quoteName('study.series_id') . ' <= 0'
                . ' OR ' . $db->quoteName('study.series_id') . ' IS NULL)'
            );
        } else {
            $query->extendWhere(
                'AND',
                [
                    $db->quoteName('series.published') . ' = 1',
                    $db->quoteName('study.series_id') . ' <= 0',
                    $db->quoteName('study.series_id') . ' IS NULL',
                ],
                'OR'
            );
        }

        if ($sid > 0) {
            $query->where($db->quoteName('study.series_id') . ' = :sid')
                ->bind(':sid', $sid, ParameterType::INTEGER);
        }

        // Order by order filter
        $orderparam = $params->get('default_order');

        if (empty($orderparam)) {
            $orderparam = $t_params->get('series_detail_order', '1');
        }

        $order = ($orderparam == 2) ? 'ASC' : 'DESC';

        $query->order($db->quoteName('studydate') . ' ' . $order);

        return $query;
    }

    /**
     * Get Studies
     *
     * @return array
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getStudies(): array
    {
        $db    = $this->getDatabase();
        $query = $this->getStudiesQuery();

        // Cache the query so getTotal() can clone it
        $this->studiesQuery = $query;

        $template_params = Cwmparams::getTemplateparams();
        $t_params        = $template_params->params;

        // Fix pagination offset
        $offset = (int) $this->getState('list.offset');
        $limit  = (int) $t_params->get('series_detail_limit', 20);

        $db->setQuery($query, $offset, $limit);
        $studies = $db->loadObjectList();

        if (\count($studies) < 1) {
            return [];
        }

        // Batch-load media stats separately to avoid Cartesian product
        $studyIds   = array_column($studies, 'id');
        $mediaQuery = $db->getQuery(true)
            ->select([
                $db->quoteName('study_id'),
                'GROUP_CONCAT(DISTINCT ' . $db->quoteName('id') . ') AS ' . $db->quoteName('mids'),
                'SUM(' . $db->quoteName('plays') . ') AS ' . $db->quoteName('totalplays'),
                'SUM(' . $db->quoteName('downloads') . ') AS ' . $db->quoteName('totaldownloads'),
            ])
            ->from($db->quoteName('#__bsms_mediafiles'))
            ->whereIn($db->quoteName('study_id'), $studyIds)
            ->group($db->quoteName('study_id'));
        $db->setQuery($mediaQuery);
        $mediaStats = $db->loadObjectList('study_id');

        foreach ($studies as $study) {
            $stats                 = $mediaStats[$study->id] ?? null;
            $study->mids           = $stats->mids ?? null;
            $study->totalplays     = (int) ($stats->totalplays ?? 0);
            $study->totaldownloads = (int) ($stats->totaldownloads ?? 0);
            $study->study_id       = (int) $study->id;
        }

        return $studies;
    }

    /**
     * Get Total Studies
     *
     * @return int
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getTotal(): int
    {
        $db = $this->getDatabase();

        // Reuse the cached query from getStudies() if available, otherwise build fresh
        $query = $this->studiesQuery !== null
            ? clone $this->studiesQuery
            : $this->getStudiesQuery();

        $query->clear('select')->clear('order')->clear('limit')->clear('offset');
        $query->select('COUNT(DISTINCT ' . $db->quoteName('study.id') . ')');

        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return void
     *
     * @throws \Exception
     * @since    1.6
     */
    protected function populateState(): void
    {
        $app = Factory::getApplication();

        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);
        $template = Cwmparams::getTemplateparams();
        $admin    = Cwmparams::getAdmin();

        $template->params->merge($params);
        $template->params->merge($admin->params);
        $params = $template->params;

        // Load state from the request.
        $pk = $app->getInput()->get('id', '', 'int');

        if (empty($pk)) {
            $mseries_id = $params->get('mseries_id');
            if (!empty($mseries_id)) {
                if (\is_array($mseries_id)) {
                    $pk = (int)$mseries_id[0];
                } else {
                    $pk = (int)$mseries_id;
                }
            }
        }

        $this->setState('series.id', $pk);

        // Use getUserStateFromRequest to handle pagination state persistence
        $offset = $app->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0, 'int');
        $this->setState('list.offset', $offset);

        $t = $params->get('seriesid');

        if (!$t) {
            $t = $app->getInput()->get('t', 1, 'int');
        }

        $template->id = $t;

        $this->setState('template', $template);
        $this->setState('administrator', $admin);

        // Get show_archived parameter from menu, fall back to template default
        $showArchived = $params->get('show_archived', '');
        if ($showArchived === '' || $showArchived === null) {
            $showArchived = $params->get('sddefault_show_archived', '0');
        }
        $this->setState('filter.show_archived', $showArchived);

        $user = Factory::getApplication()->getIdentity();

        $canEditState = $user !== null && $user->authorise('core.edit.state', 'com_proclaim');
        $canEdit      = $user !== null && $user->authorise('core.edit', 'com_proclaim');

        if (!$canEditState && !$canEdit) {
            $this->setState('filter.published', 1);
            $this->setState('filter.archived', 2);
        }
    }
}
