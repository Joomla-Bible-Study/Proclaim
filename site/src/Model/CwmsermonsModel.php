<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Model;

use CWM\Component\Proclaim\Administrator\Helper\CwmlocationHelper;
use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Administrator\Helper\CwmscriptureHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmstudyteacherHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Model class for Sermons
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class CwmsermonsModel extends ListModel
{
    /**
     * @var   Input
     *
     * @since 7.0.0
     */
    public mixed $input;

    /** @var string Needed for context for Populate State
     * @since 9.0.14
     */
    public $context = 'com_proclaim.sermons.list';

    /**
     * Whether the current request originates from a landing page.
     *
     * @var   int
     * @since 10.1.0
     */
    public int $landing = 0;

    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @throws \Exception
     * @since   11.1
     * @see     ListModel
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id',
                'study.id',
                'published',
                'study.published',
                'studydate',
                'study.studydate',
                'studytitle',
                'study.studytitle',
                'ordering',
                'study.ordering',
                'bookname',
                'book.bookname',
                'teachername',
                'teacher.teachername',
                'message_type',
                'messageType.message_type',
                'series_text',
                'series.series_text',
                'seriesid',
                'study.series_id',
                'hits',
                'study.hits',
                'access',
                'series.access',
                'access_level',
                'location',
                'location.location_text',
                'bookname2',
                'book.bookname2',
                'language',
                'study.language',
                'book',
                'teacher',
                'series',
                'search',
                'messagetype',
                'year',
                'topic',
            ];
        }

        parent::__construct($config);
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
     * @throws  \Exception
     * @since   11.1
     */
    protected function populateState($ordering = null, $direction = null): void
    {
        $app = Factory::getApplication();

        $forcedLanguage = $app->getInput()->get('forcedLanguage', '', 'cmd');

        // Load the parameters — wrap in try/catch so a bad menu item
        // (e.g. corrupted XML, missing columns) doesn't take down the page.
        try {
            $params = $app->getParams();
        } catch (\Exception $e) {
            $params = new Registry();
        }

        try {
            $template = Cwmparams::getTemplateparams();
            $admin    = Cwmparams::getAdmin();
        } catch (\Exception $e) {
            $template         = new \stdClass();
            $template->id     = 1;
            $template->params = new Registry();
            $admin            = new \stdClass();
            $admin->params    = new Registry();
        }

        $template->params->merge($params);
        $template->params->merge($admin->params);
        $params = $template->params;
        $this->setState('params', $params);
        $t = $params->get('sermonsid');

        if (!$t) {
            $t = $app->getInput()->get('t', 1, 'int');
        }

        $landing       = 0;
        $this->landing = 0;
        $landingcheck  = $app->getInput()->get('sendingview');

        if ($landingcheck === 'landing') {
            $landing       = 1;
            $this->landing = 1;
            $this->setState('sendingview', '');
        } else {
            // Clear both model state AND session so getUserStateFromRequest()
            // doesn't resurrect old filter selections from the session.
            $filterKeys = [
                'filter.book', 'filter.teacher', 'filter.series',
                'filter.messageType', 'filter.year', 'filter.topic',
                'filter.location', 'filter.landingbook', 'filter.landingteacher',
                'filter.landingseries', 'filter.landingmessageType',
                'filter.landingyear', 'filter.landingtopic', 'filter.landinglocation',
            ];

            foreach ($filterKeys as $key) {
                $this->setState($key, 0);
                $app->setUserState($this->context . '.' . $key, 0);
            }

            $this->landing = 0;
        }

        $template->id = $t;
        $this->setState('template', $template);
        $this->setState('administrator', $admin);

        $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
        $this->setState('filter.language', $language);

        $book = $this->getUserStateFromRequest($this->context . '.filter.book', 'filter_book');

        if ($landing === 1 && $book !== 0) {
            $book = $this->getUserStateFromRequest($this->context . '.filter.landingbook', 'filter_book_landing');
        }

        $this->setState('filter.book', $book);
        $this->setState('filter.landingbook', $book);

        $teacher = $this->getUserStateFromRequest($this->context . '.filter.teacher', 'filter_teacher');

        if ($landing === 1 && $teacher !== 0) {
            $teacher = $this->getUserStateFromRequest(
                $this->context . '.filter.landingteacher',
                'filter_teacher_landing'
            );
        }

        $this->setState('filter.teacher', $teacher);
        $this->setState('filter.landingteacher', $teacher);

        $series = $this->getUserStateFromRequest($this->context . '.filter.series', 'filter_series');

        if ($landing === 1 && $series !== 0) {
            $series = $this->getUserStateFromRequest($this->context . '.filter.landingseries', 'filter_series_landing');
        }

        $this->setState('filter.series', $series);
        $this->setState('filter.landingseries', $series);

        $messageType = $this->getUserStateFromRequest($this->context . '.filter.messageType', 'filter_messagetype');

        if ($landing === 1 && $messageType !== 0) {
            $messageType = $this->getUserStateFromRequest(
                $this->context . '.filter.landingmessagetype',
                'filter_messagetype_landing'
            );
        }

        $this->setState('filter.messageType', $messageType);
        $this->setState('filter.landingmessagetype', $messageType);

        $year = $this->getUserStateFromRequest($this->context . '.filter.year', 'filter_year');

        if ($landing === 1 && $year !== 0) {
            $year = $this->getUserStateFromRequest($this->context . '.filter.landingyear', 'filter_year_landing');
        }

        $this->setState('filter.year', $year);
        $this->setState('filter.landingyear', $year);

        $topic = $this->getUserStateFromRequest($this->context . '.filter.topic', 'filter_topic');

        if ($landing === 1 && $topic !== 0) {
            $topic = $this->getUserStateFromRequest($this->context . '.filter.landingtopic', 'filter_topic_landing');
        }

        $this->setState('filter.topic', $topic);
        $this->setState('filter.landingtopic', $topic);

        $location = $this->getUserStateFromRequest($this->context . '.filter.location', 'filter_location');

        if ($landing === 1 && $location !== 0) {
            $location = $this->getUserStateFromRequest(
                $this->context . '.filter.landinglocation',
                'filter_location_landing'
            );
        }

        $orderCol = $app->getInput()->get('filter_order');

        if (!empty($orderCol) && !\in_array($orderCol, $this->filter_fields, true)) {
            $orderCol = 'study.studydate';
        }

        $this->setState('list.ordering', $orderCol);

        // From landing page filter passing
        $listOrder = $app->getInput()->get('filter_order_Dir');

        if (!empty($listOrder) && !\in_array(strtoupper($listOrder), ['ASC', 'DESC', ''])) {
            $direction = 'DESC';
        }

        $this->setState('list.direction', $direction);

        $this->setState('filter.location', $location);
        $this->setState('filter.landinglocation', $location);

        // Get show_archived parameter from menu, fall back to template default
        $showArchived = $params->get('show_archived', '');
        if ($showArchived === '' || $showArchived === null) {
            $showArchived = $params->get('default_show_archived', '0');
        }
        $this->setState('filter.show_archived', $showArchived);

        parent::populateState($ordering, $direction);

        // Force a language
        if (!empty($forcedLanguage)) {
            $this->setState('filter.language', $forcedLanguage);
            $this->setState('filter.forcedLanguage', $forcedLanguage);
        }
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
     * @since    1.6
     */
    protected function getStoreId($id = ''): string
    {
        // Compile the store id.
        $id .= ':' . serialize($this->getState('filter.published'));
        $id .= ':' . $this->getState('filter.year');
        $id .= ':' . $this->getState('filter.book');
        $id .= ':' . $this->getState('filter.teacher');
        $id .= ':' . $this->getState('filter.series');
        $id .= ':' . $this->getState('filter.messagetype');
        $id .= ':' . $this->getState('filter.location');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.language');
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.show_archived');

        return parent::getStoreId($id);
    }

    /**
     * Configure model state from module params without touching session/request.
     *
     * This allows mod_proclaim to delegate its query to this model by mapping
     * module parameter names to the m-prefixed keys that getListQuery() reads.
     *
     * @param   Registry  $mergedParams  The fully-merged module params (admin + template + module)
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function setModuleState(Registry $mergedParams): void
    {
        // Map module param names → m-prefixed keys the filter logic expects
        $map = [
            'teacher_id'  => 'mteacher_id',
            'series_id'   => 'mseries_id',
            'booknumber'  => 'mbooknumber',
            'topic_id'    => 'mtopic_id',
            'locations'   => 'mlocations',
            'messagetype' => 'mmessagetype',
            'year'        => 'years',
        ];

        foreach ($map as $moduleKey => $paramKey) {
            $value = $mergedParams->get($moduleKey);

            if ($value !== null) {
                // Ensure arrays stay arrays, scalars become arrays
                if (!\is_array($value)) {
                    $value = [$value];
                }

                $mergedParams->set($paramKey, $value);
            }
        }

        // Location filter mode: all | specific | user
        $locationFilter = $mergedParams->get('location_filter', 'all');

        if ($locationFilter === 'specific') {
            $specificLocation = (int) $mergedParams->get('specific_location', -1);

            if ($specificLocation > 0) {
                $mergedParams->set('mlocations', [$specificLocation]);
            }
        } elseif ($locationFilter === 'user' && CwmlocationHelper::isEnabled()) {
            $accessible = CwmlocationHelper::getUserLocations();

            if (!empty($accessible)) {
                $mergedParams->set('mlocations', $accessible);
            }
        }

        $this->setState('params', $mergedParams);

        // Ordering
        $this->setState('list.ordering', 'study.studydate');
        $orderParam = $mergedParams->get('order', '1');
        $this->setState('list.direction', ($orderParam === '2') ? 'ASC' : 'DESC');

        // Pagination
        $this->setState('list.start', 0);
        $this->setState('list.limit', (int) $mergedParams->get('moduleitems', 5));

        // Language filter
        $language = $mergedParams->get('language', '*');

        if ($language !== '*') {
            $this->setState('filter.language', $language);
        }

        // Modules always show published only (no archived)
        $this->setState('filter.show_archived', '0');
    }

    /**
     * Build an SQL query to load the list data
     *
     * @return  QueryInterface|string
     *
     * @throws  \Exception
     * @since   7.0
     */
    protected function getListQuery(): QueryInterface|string
    {
        $user     = $this->getCurrentUser();
        $groups   = $user->getAuthorisedViewLevels();
        $db       = $this->getDatabase();
        $query    = parent::getListQuery();
        $nullDate = $db->quote($db->getNullDate());
        $query->select(
            $this->getState(
                'list.select',
                implode(', ', $db->quoteName([
                    'study.id', 'study.published', 'study.studydate', 'study.studytitle',
                    'study.booknumber', 'study.chapter_begin', 'study.verse_begin',
                    'study.chapter_end', 'study.verse_end', 'study.hits', 'study.alias',
                    'study.studyintro', 'study.teacher_id', 'study.secondary_reference',
                    'study.booknumber2', 'study.location_id', 'study.studytext', 'study.params',
                    'study.bible_version', 'study.bible_version2',
                ]))
            )
        );
        // Use studydate as fallback for modified
        $query->select(
            'CASE WHEN ' . $db->quoteName('study.modified') . ' = ' . $nullDate
            . ' THEN ' . $db->quoteName('study.studydate') . ' ELSE ' . $db->quoteName('study.modified')
            . ' END AS ' . $db->quoteName('modified')
        );
        $query->select($db->quoteName('study.modified_by') . ', ' . $db->quoteName('uam.name', 'modified_by_name'));
        // Use studydate as fallback for publish_up
        $query->select(
            'CASE WHEN ' . $db->quoteName('study.publish_up') . ' = ' . $nullDate
            . ' THEN ' . $db->quoteName('study.studydate') . ' ELSE ' . $db->quoteName('study.publish_up')
            . ' END AS ' . $db->quoteName('publish_up')
        );
        $query->select(implode(', ', $db->quoteName([
            'study.publish_down', 'study.series_id', 'study.download_id',
            'study.thumbnailm', 'study.thumbhm', 'study.thumbwm',
            'study.access', 'study.user_name', 'study.user_id', 'study.studynumber',
            'study.chapter_begin2', 'study.chapter_end2', 'study.verse_end2', 'study.verse_begin2',
        ])));
        // Select the original image path (avoids deriving from thumbnailm)
        $query->select($db->quoteName('study.image', 'study_image'));
        $query->select($query->length($db->quoteName('study.studytext')) . ' AS ' . $db->quoteName('readmore'));
        $query->select(
            'CASE WHEN CHAR_LENGTH(' . $db->quoteName('study.alias') . ') THEN CONCAT_WS('
            . $db->quote(':') . ', ' . $db->quoteName('study.id') . ', ' . $db->quoteName('study.alias')
            . ') ELSE ' . $db->quoteName('study.id') . ' END AS ' . $db->quoteName('slug')
        );
        $query->from($db->quoteName('#__bsms_studies', 'study'));

        // Join over Message Types
        $query->select($db->quoteName('messageType.message_type', 'message_type'));
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_message_type', 'messageType') . ' ON '
            . $db->quoteName('messageType.id') . ' = ' . $db->quoteName('study.messagetype')
        );

        // Join over Teachers (primary teacher via junction table, ordering=0)
        $query->select(
            $db->quoteName('teacher.teachername', 'teachername') . ', '
            . $db->quoteName('teacher.title', 'title') . ', '
            . $db->quoteName('teacher.teacher_thumbnail', 'thumb')
        );
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_study_teachers', 'stj') . ' ON '
            . $db->quoteName('stj.study_id') . ' = ' . $db->quoteName('study.id')
            . ' AND ' . $db->quoteName('stj.ordering') . ' = 0'
        );
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_teachers', 'teacher') . ' ON '
            . $db->quoteName('teacher.id') . ' = COALESCE(' . $db->quoteName('stj.teacher_id') . ', ' . $db->quoteName('study.teacher_id') . ')'
        );

        // Join over Series
        $query->select(
            $db->quoteName('series.series_text') . ', ' . $db->quoteName('series.series_thumbnail') . ', '
            . $db->quoteName('series.description', 'sdescription') . ', '
            . $db->quoteName('series.access', 'series_access')
        );
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_series', 'series') . ' ON '
            . $db->quoteName('series.id') . ' = ' . $db->quoteName('study.series_id')
        );

        // Join over Books
        $query->select($db->quoteName('book.bookname'));
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_books', 'book') . ' ON '
            . $db->quoteName('book.booknumber') . ' = ' . $db->quoteName('study.booknumber')
        );

        $query->select($db->quoteName('book2.bookname', 'bookname2'));
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_books', 'book2') . ' ON '
            . $db->quoteName('book2.booknumber') . ' = ' . $db->quoteName('study.booknumber2')
        );

        // NOTE: Mediafile aggregation (mids, totalplays, totaldownloads) is
        // batch-loaded in getItems() to avoid a Cartesian product with topics
        // that inflates SUM(plays) and SUM(downloads).

        // Join over Locations
        $query->select($db->quoteName('locations.location_text'));
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_locations', 'locations') . ' ON '
            . $db->quoteName('study.location_id') . ' = ' . $db->quoteName('locations.id')
        );

        // Join over topics
        $query->select('GROUP_CONCAT(DISTINCT ' . $db->quoteName('st.topic_id') . ')');
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_studytopics', 'st') . ' ON '
            . $db->quoteName('study.id') . ' = ' . $db->quoteName('st.study_id')
        );
        $query->select(
            'GROUP_CONCAT(DISTINCT ' . $db->quoteName('t.id') . '), '
            . 'GROUP_CONCAT(DISTINCT ' . $db->quoteName('t.topic_text') . ') AS ' . $db->quoteName('topics_text') . ', '
            . 'GROUP_CONCAT(DISTINCT ' . $db->quoteName('t.params') . ')'
        );
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_topics', 't') . ' ON '
            . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.topic_id')
        );

        // Join over the users for the author and modified_by names.
        $query->select(
            'CASE WHEN ' . $db->quoteName('study.user_name') . ' > ' . $db->quote(' ')
            . ' THEN ' . $db->quoteName('study.user_name') . ' ELSE ' . $db->quoteName('users.name') . ' END AS '
            . $db->quoteName('submitted')
        )
            ->select($db->quoteName('users.email', 'author_email'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'users') . ' ON '
                . $db->quoteName('study.user_id') . ' = ' . $db->quoteName('users.id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'uam') . ' ON '
                . $db->quoteName('uam.id') . ' = ' . $db->quoteName('study.modified_by')
            );

        $query->group($db->quoteName('study.id'));

        // Filter only for authorized view
        $query->whereIn($db->quoteName('study.access'), $groups);
        $query->extendWhere(
            'AND',
            [
                $db->quoteName('series.access') . ' IN (' . implode(',', $groups) . ')',
                $db->quoteName('study.series_id') . ' <= 0',
            ],
            'OR'
        );

        // Filter by published state based on show_archived parameter
        $showArchived = $this->getState('filter.show_archived', '0');
        switch ($showArchived) {
            case '1': // Archived only
                $query->where($db->quoteName('study.published') . ' = 2');
                break;
            case '2': // Both published and archived
                $query->where($db->quoteName('study.published') . ' IN (1, 2)');
                break;
            default: // Published only (backward compatible)
                $query->where($db->quoteName('study.published') . ' = 1');
                break;
        }
        // Define now date for publish filter
        $nowDate = $db->quote((new Date())->toSql());

        // Series must be published AND within its date window (like Joomla categories).
        // Admin users with edit.state or edit bypass date filtering.
        $canEditState = $user->authorise('core.edit.state', 'com_proclaim');
        $canEdit      = $user->authorise('core.edit', 'com_proclaim');

        if (!$canEditState && !$canEdit) {
            // Non-admin: enforce series published + date window, or no series assigned
            $query->where(
                '(('
                . $db->quoteName('series.published') . ' = 1'
                . ' AND (' . $db->quoteName('series.publish_up') . ' = ' . $nullDate . ' OR ' . $db->quoteName('series.publish_up') . ' <= ' . $nowDate . ')'
                . ' AND (' . $db->quoteName('series.publish_down') . ' = ' . $nullDate . ' OR ' . $db->quoteName('series.publish_down') . ' >= ' . $nowDate . ')'
                . ') OR ' . $db->quoteName('study.series_id') . ' <= 0)'
            );
        } else {
            // Admin: only check series published state
            $query->where('(' . $db->quoteName('series.published') . ' = 1 OR ' . $db->quoteName('study.series_id') . ' <= 0)');
        }

        // Filter by start and end dates for messages.
        if (!$canEditState && !$canEdit) {
            $query->where('(' . $db->quoteName('study.publish_up') . ' = ' . $nullDate . ' OR ' . $db->quoteName('study.publish_up') . ' <= ' . $nowDate . ')')
                ->where('(' . $db->quoteName('study.publish_down') . ' = ' . $nullDate . ' OR ' . $db->quoteName('study.publish_down') . ' >= ' . $nowDate . ')');
        }

        // Begin the filters for menu items
        /** @var Registry $params */
        $params = $this->getState('params');

        // Teacher — junction table EXISTS subquery for multi-teacher support
        $filterTeacher = $this->getState('filter.teacher');
        $filterTeacher = (int) (\is_array($filterTeacher) ? reset($filterTeacher) : $filterTeacher);

        foreach (['mteacher_id', 'lteacher_id'] as $paramKey) {
            $this->addTeacherFilter($query, $db, $params->get($paramKey), $filterTeacher);
        }

        // Book — chapter-range special handling
        $filterBook = (int) $this->getState('filter.book');

        foreach (['mbooknumber', 'lbooknumber'] as $paramKey) {
            $this->addBookFilter($query, $db, $params->get($paramKey), $filterBook);
        }

        // Standard column filters (location, series, topic, messagetype)
        $standardFilters = [
            ['study.location_id', ['mlocations', 'llocations'],     'filter.location'],
            ['study.series_id',   ['mseries_id', 'lseries_id'],    'filter.series'],
            ['st.topic_id',       ['mtopic_id', 'ltopic_id'],      'filter.topic'],
            ['study.messagetype', ['mmessagetype', 'lmessagetype'], 'filter.messagetype'],
        ];

        foreach ($standardFilters as [$column, $paramKeys, $stateKey]) {
            $filterVal = (int) $this->getState($stateKey);

            foreach ($paramKeys as $pk) {
                $this->addParamFilter($query, $db, $column, $params->get($pk), $filterVal);
            }
        }

        // Year — expression-based (not a column reference, so skip quoteName)
        $yearFilter = (int) $this->getState('filter.year');

        foreach (['years', 'lyears'] as $paramKey) {
            $this->addParamFilter(
                $query,
                $db,
                'YEAR(study.studydate)',
                $params->get($paramKey),
                $yearFilter,
                true,
            );
        }

        if ($this->getState('filter.language')) {
            $query->whereIn($db->quoteName('study.language'), [$this->getState('filter.language')], ParameterType::STRING);
        } elseif (Multilanguage::isEnabled()) {
            $query->whereIn($db->quoteName('study.language'), [Factory::getApplication()->getLanguage()->getTag(), '*'], ParameterType::STRING);
        }

        // Adding in search strings
        // Filter: like / search
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            $like = $db->quote('%' . trim($search) . '%');
            $query->where(
                '(' . $db->quoteName('study.studytitle') . ' LIKE ' . $like
                . ' OR ' . $db->quoteName('study.studytext') . ' LIKE ' . $like
                . ' OR ' . $db->quoteName('study.studyintro') . ' LIKE ' . $like
                . ' OR ' . $db->quoteName('series.series_text') . ' LIKE ' . $like
                . ' OR ' . $db->quoteName('series.description') . ' LIKE ' . $like
                . ' OR ' . $db->quoteName('t.topic_text') . ' LIKE ' . $like
                . ')'
            );
        }

        // Add the list ordering clause.
        $orderCol  = $this->getState('list.fullordering');
        $orderDirn = '';

        if (empty($orderCol) || $orderCol === " ") {
            $orderCol = $this->getState('list.ordering', 'study.studydate');
            $this->setState('list.direction', $params->get('default_order'));

            // Set order by menu if set. The New Default is blank as of 9.2.5
            if ($params->get('order') === '2') {
                $this->setState('list.direction', 'ASC');
            } elseif ($params->get('order') === '1') {
                $this->setState('list.direction', 'DESC');
            }

            $orderDirn = $this->getState('list.direction', 'DESC');
        }

        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }

    /**
     * Override getItems to batch-load related data from separate tables.
     *
     * Mediafile stats, scriptures are loaded in focused batch queries rather
     * than via LEFT JOINs in the main query. This avoids Cartesian products
     * (e.g. mediafiles x topics inflating SUM aggregates) and keeps each
     * query simple and independently cacheable.
     *
     * @return  array
     *
     * @since  10.1.0
     */
    #[\Override]
    public function getItems(): array
    {
        // Safety net for large GROUP_CONCAT result sets
        $db = $this->getDatabase();
        $db->setQuery('SET SQL_BIG_SELECTS=1');
        $db->execute();

        $items = parent::getItems();

        if (empty($items) || !\is_array($items)) {
            return [];
        }

        // Collect study IDs for batch loading
        $studyIds = [];

        foreach ($items as $item) {
            if (!empty($item->id)) {
                $studyIds[] = (int) $item->id;
            }
        }

        if (empty($studyIds)) {
            return $items;
        }

        // Batch-load mediafile aggregation (mids, totalplays, totaldownloads)
        $mediaStats = $this->batchLoadMediaStats($studyIds);

        // Batch-load all scripture references
        $scriptureMap = CwmscriptureHelper::getScripturesForStudies($studyIds);

        // Batch-load all teachers (for teachers-list element)
        $teacherMap = CwmstudyteacherHelper::getTeachersForStudies($studyIds);

        foreach ($items as $item) {
            $sid = (int) $item->id;

            // Media stats
            $stats                = $mediaStats[$sid] ?? null;
            $item->mids           = $stats->mids ?? null;
            $item->totalplays     = (int) ($stats->totalplays ?? 0);
            $item->totaldownloads = (int) ($stats->totaldownloads ?? 0);
            $item->study_id       = $sid;

            // Scriptures
            $item->scriptures = $scriptureMap[$sid] ?? [];

            // Teachers
            $item->teachers = $teacherMap[$sid] ?? [];
        }

        return $items;
    }

    /**
     * Batch-load mediafile statistics for a set of studies.
     *
     * Runs a single GROUP BY query against #__bsms_mediafiles instead of
     * joining mediafiles into the main query (which caused Cartesian products
     * with topics, inflating SUM(plays) and SUM(downloads)).
     *
     * @param   int[]  $studyIds  Study primary keys
     *
     * @return  array<int, \stdClass>  Keyed by study_id, each with mids/totalplays/totaldownloads
     *
     * @since   10.1.0
     */
    private function batchLoadMediaStats(array $studyIds): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select([
            $db->quoteName('study_id'),
            'GROUP_CONCAT(DISTINCT ' . $db->quoteName('id') . ') AS ' . $db->quoteName('mids'),
            'SUM(' . $db->quoteName('plays') . ') AS ' . $db->quoteName('totalplays'),
            'SUM(' . $db->quoteName('downloads') . ') AS ' . $db->quoteName('totaldownloads'),
        ])
            ->from($db->quoteName('#__bsms_mediafiles'));

        // Include archived media when show_archived is enabled
        $showArchived = $this->getState('filter.show_archived', '0');

        if ($showArchived === '1' || $showArchived === '2') {
            $query->where($db->quoteName('published') . ' IN (1, 2)');
        } else {
            $query->where($db->quoteName('published') . ' = 1');
        }

        $query->whereIn($db->quoteName('study_id'), $studyIds)
            ->group($db->quoteName('study_id'));

        $db->setQuery($query);

        return $db->loadObjectList('study_id') ?: [];
    }

    /**
     * Apply a standard column filter using the 3-branch param/user/both pattern.
     *
     * Handles: param only → restrict to param values; both param + user filter →
     * restrict to param values AND require user pick; user filter only → exact match.
     *
     * @param   QueryInterface  $query         Active query builder
     * @param   object          $db            Database driver
     * @param   string          $column        Column name (e.g. 'study.location_id') or SQL expression
     * @param   ?array          $paramValues   Values from menu/template params (null if unset)
     * @param   int             $filterValue   User-selected filter value (0 = none)
     * @param   bool            $isExpression  True if $column is a SQL expression (skip quoteName)
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private function addParamFilter(
        QueryInterface $query,
        object $db,
        string $column,
        ?array $paramValues,
        int $filterValue,
        bool $isExpression = false,
    ): void {
        $colRef   = $isExpression ? $column : $db->quoteName($column);
        $hasParam = $paramValues !== null && ($paramValues[0] ?? '-1') !== '-1';

        if ($hasParam && $filterValue < 1) {
            $intValues = array_map('intval', $paramValues);

            if (\count($intValues) > 1) {
                $query->where($colRef . ' IN (' . implode(',', $intValues) . ')');
            } else {
                $query->where($colRef . ' = ' . $intValues[0]);
            }
        } elseif ($hasParam && $filterValue >= 1) {
            $intValues = array_map('intval', $paramValues);
            $query->where($colRef . ' IN (' . implode(',', $intValues) . ')');
            $query->where($colRef . ' = ' . $filterValue);
        } elseif ($filterValue >= 1) {
            $query->where($colRef . ' = ' . $filterValue);
        }
    }

    /**
     * Apply teacher filter using EXISTS subquery against the junction table.
     *
     * Uses `#__bsms_study_teachers` instead of the legacy `study.teacher_id`
     * column, supporting the multi-teacher-per-sermon data model.
     *
     * @param   QueryInterface  $query        Active query builder
     * @param   object          $db           Database driver
     * @param   ?array          $paramValues  Teacher IDs from menu/template params
     * @param   int             $filterValue  User-selected teacher ID (0 = none)
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private function addTeacherFilter(
        QueryInterface $query,
        object $db,
        ?array $paramValues,
        int $filterValue,
    ): void {
        $hasParam = $paramValues !== null && ($paramValues[0] ?? '-1') !== '-1';

        if ($hasParam && $filterValue < 1) {
            $intValues = array_map('intval', $paramValues);
            $sub       = $db->getQuery(true)
                ->select('1')
                ->from($db->quoteName('#__bsms_study_teachers', 'stf'))
                ->where($db->quoteName('stf.study_id') . ' = ' . $db->quoteName('study.id'))
                ->whereIn($db->quoteName('stf.teacher_id'), $intValues);
            $query->where('EXISTS (' . $sub . ')');
        } elseif ($hasParam && $filterValue >= 1) {
            $intValues = array_map('intval', $paramValues);
            $sub       = $db->getQuery(true)
                ->select('1')
                ->from($db->quoteName('#__bsms_study_teachers', 'stf'))
                ->where($db->quoteName('stf.study_id') . ' = ' . $db->quoteName('study.id'))
                ->whereIn($db->quoteName('stf.teacher_id'), $intValues);
            $query->where('EXISTS (' . $sub . ')');

            $sub2 = $db->getQuery(true)
                ->select('1')
                ->from($db->quoteName('#__bsms_study_teachers', 'stf2'))
                ->where($db->quoteName('stf2.study_id') . ' = ' . $db->quoteName('study.id'))
                ->where($db->quoteName('stf2.teacher_id') . ' = ' . $filterValue);
            $query->where('EXISTS (' . $sub2 . ')');
        } elseif ($filterValue >= 1) {
            $sub = $db->getQuery(true)
                ->select('1')
                ->from($db->quoteName('#__bsms_study_teachers', 'stf'))
                ->where($db->quoteName('stf.study_id') . ' = ' . $db->quoteName('study.id'))
                ->where($db->quoteName('stf.teacher_id') . ' = ' . $filterValue);
            $query->where('EXISTS (' . $sub . ')');
        }
    }

    /**
     * Apply book number filter with chapter-range support.
     *
     * Multi-value params use a simple IN clause. Single-value params and
     * user-selected filters use chapter-range logic with booknumber2 fallback.
     *
     * @param   QueryInterface  $query        Active query builder
     * @param   object          $db           Database driver
     * @param   ?array          $paramValues  Book numbers from menu/template params
     * @param   int             $filterValue  User-selected book number (0 = none)
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private function addBookFilter(
        QueryInterface $query,
        object $db,
        ?array $paramValues,
        int $filterValue,
    ): void {
        $hasParam = $paramValues !== null && ($paramValues[0] ?? '-1') !== '-1';
        $col      = $db->quoteName('study.booknumber');

        if ($hasParam && $filterValue < 1) {
            $intValues = array_map('intval', $paramValues);

            if (\count($intValues) > 1) {
                $query->where($col . ' IN (' . implode(',', $intValues) . ')');
            } else {
                $this->addBookChapterWhere($query, $db, $intValues[0]);
            }
        } elseif ($hasParam && $filterValue >= 1) {
            $intValues = array_map('intval', $paramValues);

            if (\count($intValues) > 1) {
                $query->where($col . ' IN (' . implode(',', $intValues) . ')');
            } else {
                $this->addBookChapterWhere($query, $db, $intValues[0]);
            }

            $this->addBookChapterWhere($query, $db, $filterValue);
        } elseif ($filterValue >= 1) {
            $this->addBookChapterWhere($query, $db, $filterValue);
        }
    }

    /**
     * Add WHERE clause for a single book number with optional chapter range.
     *
     * Also checks booknumber2 (secondary scripture reference) as a fallback.
     * Chapter range bounds come from the request input (minChapt, maxChapt).
     *
     * @param   QueryInterface  $query  Active query builder
     * @param   object          $db     Database driver
     * @param   int             $book   Book number to filter on
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private function addBookChapterWhere(QueryInterface $query, object $db, int $book): void
    {
        $appInput = Factory::getApplication()->getInput();
        $chb      = $appInput->get('minChapt', 0, 'int');
        $che      = $appInput->get('maxChapt', 0, 'int');

        $bn  = $db->quoteName('study.booknumber');
        $bn2 = $db->quoteName('study.booknumber2');
        $cb  = $db->quoteName('study.chapter_begin');
        $ce  = $db->quoteName('study.chapter_end');

        if ($chb && $che) {
            $query->where(
                '(' . $bn . ' = ' . $book
                . ' AND ' . $cb . ' >= ' . $chb
                . ' AND ' . $ce . ' <= ' . $che
                . ') OR ' . $bn2 . ' = ' . $book
            );
        } elseif ($chb) {
            $query->where(
                '(' . $bn . ' = ' . $book
                . ' AND ' . $cb . ' >= ' . $chb
                . ') OR ' . $bn2 . ' = ' . $book
            );
        } elseif ($che) {
            $query->where(
                '(' . $bn . ' = ' . $book
                . ' AND ' . $ce . ' <= ' . $che
                . ') OR ' . $bn2 . ' = ' . $book
            );
        } else {
            $query->where(
                '(' . $bn . ' = ' . $book
                . ' OR ' . $bn2 . ' = ' . $book . ')'
            );
        }
    }
}
