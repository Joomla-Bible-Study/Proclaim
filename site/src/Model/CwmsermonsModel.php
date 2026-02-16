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

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Administrator\Helper\CwmscriptureHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmstudyteacherHelper;
use CWM\Component\Proclaim\Administrator\Helper\Cwmtranslated;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
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
     * Cached filter query results to avoid duplicate queries per request.
     *
     * @var array<string, array>
     * @since 10.1.0
     */
    private array $filterCache = [];

    /**
     * Clear the in-memory filter dropdown cache.
     *
     * Useful after data changes (e.g. saving a study) when filter dropdowns
     * (teachers, years, series, books) may have changed.
     *
     * @param   string|null  $key  Specific cache key to clear, or null to clear all
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function resetFilterCache(?string $key = null): void
    {
        if ($key !== null) {
            unset($this->filterCache[$key]);
        } else {
            $this->filterCache = [];
        }
    }

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
                'series',
                'search',
                'messagetype',
                'series',
                'topic',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Translate item entries: books, topics
     *
     * @param   array  $items  Books
     *
     * @return array
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getTranslated($items = []): array
    {
        foreach ($items as $item) {
            $item->bookname   = Text::_($item->bookname);
            $item->topic_text = Cwmtranslated::getTopicItemTranslated($item);
            $item->bookname2  = Text::_($item->bookname2);
            $item->topic_text = Cwmtranslated::getTopicItemTranslated($item);
        }

        return $items;
    }

    /**
     * Get Downloads
     *
     * @param   int  $id  ID of Download
     *
     * @return string
     *
     * @since 7.0
     * @todo  Need to see if we can use this out of a helper to reduce code.
     */
    public function getDownloads(int $id): string
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $query->select('SUM(' . $db->quoteName('downloads') . ') AS totalDownloads')
            ->from($db->quoteName('#__bsms_mediafiles'))
            ->where($db->quoteName('study_id') . ' = :studyId')
            ->bind(':studyId', $id, ParameterType::INTEGER)
            ->group($db->quoteName('study_id'));
        $result = $this->_getList($query);

        if (!$result) {
            return '0';
        }

        return $result[0]->totalDownloads;
    }

    /**
     * Creates and executes a new query that retrieves the medifile information from the mediafiles table.
     * It then adds to the dataObject the mediafiles associated with the sermon.
     *
     * @return array|null
     *
     * @since 7.0
     */
    public function getFiles(): ?array
    {
        $mediaFiles = null;
        $db         = $this->getDatabase();

        foreach ($this->_data as $sermon) {
            $sermon_id = (int) $sermon->id;
            $query     = $db->getQuery(true);
            $query->select($db->quoteName(['study_id', 'filename']))
                ->select($db->quoteName('#__bsms_servers.server_path'))
                ->from($db->quoteName('#__bsms_mediafiles'))
                ->leftJoin(
                    $db->quoteName('#__bsms_servers') .
                    ' ON ' . $db->quoteName('#__bsms_mediafiles.server') . ' = ' . $db->quoteName('#__bsms_servers.id')
                )
                ->where($db->quoteName('study_id') . ' = :studyId')
                ->bind(':studyId', $sermon_id, ParameterType::INTEGER);
            $db->setQuery($query);
            $mediaFiles[$sermon->id] = $db->loadAssocList();
        }

        $this->_files = $mediaFiles;

        return $this->_files;
    }

    /**
     * Method to get the starting number of items for the data set.
     *
     * @return  int  The starting number of items available in the data set.
     *
     * @since   12.2
     */
    public function getStart(): int
    {
        return $this->getState('list.start');
    }

    /**
     * Get a list of teachers associated with series
     *
     * @return array
     * @since 9.0.0
     */
    public function getTeachers(): array
    {
        if (isset($this->filterCache['teachers'])) {
            return $this->filterCache['teachers'];
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $query->select($db->quoteName(['t.id', 't.teachername'], ['value', 'text']));
        $query->from($db->quoteName('#__bsms_teachers', 't'));
        $query->select($db->quoteName('series.access'));
        $query->join('INNER', $db->quoteName('#__bsms_series', 'series') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('series.teacher'));
        $query->group($db->quoteName('t.id'));
        $query->order($db->quoteName('t.teachername') . ' ASC');

        $db->setQuery($query);

        $this->filterCache['teachers'] = $db->loadObjectList();

        return $this->filterCache['teachers'];
    }

    /**
     * Get a list of years from studies associated with series
     *
     * @return array
     * @since 9.0.0
     */
    public function getYears(): array
    {
        if (isset($this->filterCache['years'])) {
            return $this->filterCache['years'];
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $query->select('DISTINCT YEAR(' . $db->quoteName('s.studydate') . ') as value');
        $query->select('YEAR(' . $db->quoteName('s.studydate') . ') as text');
        $query->from($db->quoteName('#__bsms_studies', 's'));
        $query->select($db->quoteName('series.access'));
        $query->join('INNER', $db->quoteName('#__bsms_series', 'series') . ' ON ' . $db->quoteName('s.series_id') . ' = ' . $db->quoteName('series.id'));
        $query->order($db->quoteName('value'));

        $db->setQuery($query);

        $this->filterCache['years'] = $db->loadObjectList();

        return $this->filterCache['years'];
    }

    /**
     * Get a list of all used series
     *
     * @return array
     * @throws \Exception
     * @since 7.0
     */
    public function getSeries(): array
    {
        if (isset($this->filterCache['series'])) {
            return $this->filterCache['series'];
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select($db->quoteName(['series.id', 'series.series_text', 'series.access'], ['value', 'text', 'access']));
        $query->from($db->quoteName('#__bsms_series', 'series'));
        $query->join('INNER', $db->quoteName('#__bsms_studies', 'study') . ' ON ' . $db->quoteName('study.series_id') . ' = ' . $db->quoteName('series.id'));
        $query->group($db->quoteName('series.id'));
        $query->order($db->quoteName('series.series_text'));

        $db->setQuery($query);
        $items = $db->loadObjectList();

        // Check permissions for this view by running through the records and removing those the user doesn't have permission to see
        $user   = $this->getCurrentUser();
        $groups = $user->getAuthorisedViewLevels();
        $count  = \count($items);

        if ($count > 0) {
            foreach ($items as $i => $iValue) {
                if ($iValue->access > 1) {
                    if (!\in_array($iValue->access, $groups, true)) {
                        unset($items[$i]);
                    }
                }
            }
        }

        $this->filterCache['series'] = $items;

        return $items;
    }

    /**
     * Get a list of all books
     *
     * @return array
     * @since 7.0
     */
    public function getBooks(): array
    {
        if (isset($this->filterCache['books'])) {
            return $this->filterCache['books'];
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select($db->quoteName(['books.id', 'books.bookname', 'books.id'], ['value', 'text', 'value']));
        $query->from($db->quoteName('#__bsms_books', 'books'));
        $query->order($db->quoteName('books.booknumber'));

        $db->setQuery($query);
        $books = $db->loadObjectList();

        foreach ($books as $book) {
            $book->text = Text::_($book->text);
        }

        $this->filterCache['books'] = $books;

        return $this->filterCache['books'];
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

        // Load the parameters.
        $params = $app->getParams();

        $template = Cwmparams::getTemplateparams();
        $admin    = Cwmparams::getAdmin();

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
            $this->setState('filter.book', 0);
            $this->setState('filter.teacher', 0);
            $this->setState('filter.series', 0);
            $this->setState('filter.messageType', 0);
            $this->setState('filter.year', 0);
            $this->setState('filter.topic', 0);
            $this->setState('filter.location', 0);
            $this->setState('filter.landingbook', 0);
            $this->setState('filter.landingteacher', 0);
            $this->setState('filter.landingseries', 0);
            $this->setState('filter.landingmessageType', 0);
            $this->setState('filter.landingyear', 0);
            $this->setState('filter.landingtopic', 0);
            $this->setState('filter.landinglocation', 0);
            $this->landing = 0;
        }

        $template->id = $t;
        $this->setState('template', $template);
        $this->setState('administrator', $admin);

        $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
        $this->setState('filter.language', $language);

        $level = $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level');
        $this->setState('filter.level', $level);

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
        $query->where('(' . $db->quoteName('series.published') . ' = 1 OR ' . $db->quoteName('study.series_id') . ' <= 0)');

        // Define now date for publish filter
        $nowDate = $db->quote((new Date())->toSql());

        // Filter by start and end dates.
        if (
            (!$user->authorise('core.edit.state', 'com_proclaim')) && (!$user->authorise(
                'core.edit',
                'com_proclaim'
            ))
        ) {
            $query->where('(' . $db->quoteName('study.publish_up') . ' = ' . $nullDate . ' OR ' . $db->quoteName('study.publish_up') . ' <= ' . $nowDate . ')')
                ->where('(' . $db->quoteName('study.publish_down') . ' = ' . $nullDate . ' OR ' . $db->quoteName('study.publish_down') . ' >= ' . $nowDate . ')');
        }

        // Begin the filters for menu items
        /** @var Registry $params */
        $params = $this->getState('params');

        $filters_group = [];

        // Teacher filter via junction table (matches any teacher role, not just primary)
        $teacherIds = [];

        $mteacher = $params->get('mteacher_id');

        if ($mteacher !== null && $mteacher[0] !== '-1') {
            $teacherIds = array_merge($teacherIds, array_map('intval', (array) $mteacher));
        }

        $lteacher = $params->get('lteacher_id');

        if ($lteacher !== null && $lteacher[0] !== '-1') {
            $teacherIds = array_merge($teacherIds, array_map('intval', (array) $lteacher));
        }

        $filterTeacher = $this->getState('filter.teacher');

        if (!empty($filterTeacher)) {
            $teacherIds[] = (int) $filterTeacher;
        }

        $teacherIds = array_unique(array_filter($teacherIds));

        if (!empty($teacherIds)) {
            $tSubquery = $db->getQuery(true)
                ->select('1')
                ->from($db->quoteName('#__bsms_study_teachers', 'stf'))
                ->where($db->quoteName('stf.study_id') . ' = ' . $db->quoteName('study.id'))
                ->whereIn($db->quoteName('stf.teacher_id'), $teacherIds);
            $query->where('EXISTS (' . $tSubquery . ')');
        }

        // Location ID
        if (
            $params->get('mlocations') !== null && $params->get('mlocations')[0] !== '-1' && empty(
                $this->getState(
                    'filter.location'
                )
            )
        ) {
            $filters_group[] = ['study.location_id' => $params->get('mlocations')];
        } elseif (
            $params->get('mlocations') !== null && $params->get(
                'mlocations'
            )[0] !== '-1' && !empty($this->getState('filter.location'))
        ) {
            $filters_group[] = ['study.location_id' => $params->get('mlocations')];
            $filters_group[] = ['study.location_id' => [$this->getState('filter.location')]];
        } elseif (!empty($this->getState('filter.location'))) {
            $filters_group[] = ['study.location_id' => [$this->getState('filter.location')]];
        }

        // Location ID from template
        if (
            $params->get('llocations') !== null && $params->get('llocations')[0] !== '-1' && empty(
                $this->getState(
                    'filter.location'
                )
            )
        ) {
            $filters_group[] = ['study.location_id' => $params->get('llocations')];
        } elseif (
            $params->get('llocations') !== null && $params->get(
                'llocations'
            )[0] !== '-1' && !empty($this->getState('filter.location'))
        ) {
            $filters_group[] = ['study.location_id' => $params->get('llocations')];
            $filters_group[] = ['study.location_id' => [$this->getState('filter.location')]];
        } elseif (!empty($this->getState('filter.location'))) {
            $filters_group[] = ['study.location_id' => [$this->getState('filter.location')]];
        }

        // Book Number ID
        if (
            $params->get('mbooknumber') !== null && $params->get('mbooknumber')[0] !== '-1' && empty(
                $this->getState(
                    'filter.book'
                )
            )
        ) {
            $filters_group[] = ['study.booknumber' => $params->get('mbooknumber')];
        } elseif (
            $params->get('mbooknumber') !== null && $params->get(
                'mbooknumber'
            )[0] !== '-1' && !empty($this->getState('filter.book'))
        ) {
            $filters_group[] = ['study.booknumber' => $params->get('mbooknumber')];
            $filters_group[] = ['study.booknumber' => [$this->getState('filter.book')]];
        } elseif (!empty($this->getState('filter.book'))) {
            $filters_group[] = ['study.booknumber' => [$this->getState('filter.book')]];
        }

        // Book Number ID from template
        if (
            $params->get('lbooknumber') !== null && $params->get('lbooknumber')[0] !== '-1' && empty(
                $this->getState(
                    'filter.book'
                )
            )
        ) {
            $filters_group[] = ['study.booknumber' => $params->get('lbooknumber')];
        } elseif (
            $params->get('lbooknumber') !== null && $params->get(
                'lbooknumber'
            )[0] !== '-1' && !empty($this->getState('filter.book'))
        ) {
            $filters_group[] = ['study.booknumber' => $params->get('lbooknumber')];
            $filters_group[] = ['study.booknumber' => [$this->getState('filter.book')]];
        } elseif (!empty($this->getState('filter.book'))) {
            $filters_group[] = ['study.booknumber' => [$this->getState('filter.book')]];
        }

        // Series ID
        if (
            $params->get('mseries_id') !== null && $params->get('mseries_id')[0] !== '-1' && empty(
                $this->getState(
                    'filter.series'
                )
            )
        ) {
            $filters_group[] = ['study.series_id' => $params->get('mseries_id')];
        } elseif (
            $params->get('mseries_id') !== null && $params->get(
                'mseries_id'
            )[0] !== '-1' && !empty($this->getState('filter.series'))
        ) {
            $filters_group[] = ['study.series_id' => $params->get('mseries_id')];
            $filters_group[] = ['study.series_id' => [$this->getState('filter.series')]];
        } elseif (!empty($this->getState('filter.series'))) {
            $filters_group[] = ['study.series_id' => [$this->getState('filter.series')]];
        }

        // Series ID from template
        if (
            $params->get('lseries_id') !== null && $params->get('lseries_id')[0] !== '-1' && empty(
                $this->getState(
                    'filter.series'
                )
            )
        ) {
            $filters_group[] = ['study.series_id' => $params->get('lseries_id')];
        } elseif (
            $params->get('lseries_id') !== null && $params->get(
                'lseries_id'
            )[0] !== '-1' && !empty($this->getState('filter.series'))
        ) {
            $filters_group[] = ['study.series_id' => $params->get('lseries_id')];
            $filters_group[] = ['study.series_id' => [$this->getState('filter.series')]];
        } elseif (!empty($this->getState('filter.series'))) {
            $filters_group[] = ['study.series_id' => [$this->getState('filter.series')]];
        }

        // Topic ID
        if (
            !\is_null($params->get('mtopic_id')) && $params->get('mtopic_id')[0] !== '-1' && empty(
                $this->getState(
                    'filter.topic'
                )
            )
        ) {
            $filters_group[] = ['st.topic_id' => $params->get('mtopic_id')];
        } elseif (
            $params->get('mtopic_id') !== null && $params->get(
                'mtopic_id'
            )[0] !== '-1' && !empty($this->getState('filter.topic'))
        ) {
            $filters_group[] = ['st.topic_id' => $params->get('mtopic_id')];
            $filters_group[] = ['st.topic_id' => [$this->getState('filter.topic')]];
        } elseif (!empty($this->getState('filter.topic'))) {
            $filters_group[] = ['st.topic_id' => [$this->getState('filter.topic')]];
        }

        // Topic ID from template
        if (
            $params->get('ltopic_id') !== null && $params->get('ltopic_id')[0] !== '-1' && empty(
                $this->getState(
                    'filter.topic'
                )
            )
        ) {
            $filters_group[] = ['st.topic_id' => $params->get('ltopic_id')];
        } elseif (
            $params->get('ltopic_id') !== null && $params->get(
                'ltopic_id'
            )[0] !== '-1' && !empty($this->getState('filter.topic'))
        ) {
            $filters_group[] = ['st.topic_id' => $params->get('ltopic_id')];
            $filters_group[] = ['st.topic_id' => [$this->getState('filter.topic')]];
        } elseif (!empty($this->getState('filter.topic'))) {
            $filters_group[] = ['st.topic_id' => [$this->getState('filter.topic')]];
        }

        // Message Type ID
        if (
            $params->get('mmessagetype') !== null && $params->get('mmessagetype')[0] !== '-1' && empty(
                $this->getState(
                    'filter.messagetype'
                )
            )
        ) {
            $filters_group[] = ['study.messagetype' => $params->get('mmessagetype')];
        } elseif (
            $params->get('mmessagetype') !== null
            && $params->get('mmessagetype')[0] !== '-1'
            && !empty($this->getState('filter.messagetype'))
        ) {
            $filters_group[] = ['study.messagetype' => $params->get('mmessagetype')];
            $filters_group[] = ['study.messagetype' => [$this->getState('filter.messagetype')]];
        } elseif (!empty($this->getState('filter.messagetype'))) {
            $filters_group[] = ['study.messagetype' => [$this->getState('filter.messagetype')]];
        }

        // Message Type ID from template
        if (
            $params->get('lmessagetype') !== null && $params->get('lmessagetype')[0] !== '-1' && empty(
                $this->getState(
                    'filter.messagetype'
                )
            )
        ) {
            $filters_group[] = ['study.messagetype' => $params->get('lmessagetype')];
        } elseif (
            $params->get('lmessagetype') !== null
            && $params->get('lmessagetype')[0] !== '-1'
            && !empty($this->getState('filter.messagetype'))
        ) {
            $filters_group[] = ['study.messagetype' => $params->get('lmessagetype')];
            $filters_group[] = ['study.messagetype' => [$this->getState('filter.messagetype')]];
        } elseif (!empty($this->getState('filter.messagetype'))) {
            $filters_group[] = ['study.messagetype' => [$this->getState('filter.messagetype')]];
        }

        // Year ID
        if (
            $params->get('years') !== null && $params->get('years')[0] !== '-1' && empty(
                $this->getState(
                    'filter.year'
                )
            )
        ) {
            $filters_group[] = ['YEAR(study.studydate)' => $params->get('years')];
        } elseif (
            $params->get('years') !== null && $params->get('years')[0] !== '-1' && !empty(
                $this->getState(
                    'filter.year'
                )
            )
        ) {
            $filters_group[] = ['YEAR(study.studydate)' => [$this->getState('filter.year')]];
            $filters_group[] = ['YEAR(study.studydate)' => $params->get('years')];
        } elseif (!empty($this->getState('filter.year'))) {
            $filters_group[] = ['YEAR(study.studydate)' => [$this->getState('filter.year')]];
        }

        // Year ID from template
        if (
            $params->get('lyears') !== null && $params->get('lyears')[0] !== '-1' && empty(
                $this->getState(
                    'filter.year'
                )
            )
        ) {
            $filters_group[] = ['YEAR(study.studydate)' => $params->get('lyears')];
        } elseif (
            $params->get('lyears') !== null && $params->get('lyears')[0] !== '-1' && !empty(
                $this->getState(
                    'filter.year'
                )
            )
        ) {
            $filters_group[] = ['YEAR(study.studydate)' => [$this->getState('filter.year')]];
            $filters_group[] = ['YEAR(study.studydate)' => $params->get('lyears')];
        } elseif (!empty($this->getState('filter.year'))) {
            $filters_group[] = ['YEAR(study.studydate)' => [$this->getState('filter.year')]];
        }

        // Work through each filter deceleration
        foreach ($filters_group as $filters) {
            if (\is_array($filters)) {
                // Work through the menu filters or search filters
                foreach ($filters as $filter => $filtervalue) {
                    if (\count($filtervalue) > 1) {
                        $where2   = [];
                        $subquery = '(';

                        foreach ($filtervalue as $filterid) {
                            $where2[] = $db->quoteName($filter) . ' = ' . (int)$filterid;
                        }

                        $subquery .= implode(' OR ', $where2);
                        $subquery .= ')';

                        $query->where($subquery);
                    } else {
                        foreach ($filtervalue as $filterid) {
                            if ((int)$filterid >= 1 && $filter !== 'study.booknumber') {
                                if ($this->landing === 1) {
                                    $$filterid = $this->getState($filter);
                                }

                                $query->where($db->quoteName($filter) . ' = ' . (int)$filterid);
                            }

                            if ((int)$filterid >= 1 && $filter === 'study.booknumber') {
                                $book     = $filterid;
                                $appInput = Factory::getApplication()->getInput();
                                $chb      = $appInput->get('minChapt', '', 'int');
                                $che      = $appInput->get('maxChapt', '', 'int');

                                if ($chb && $che) {
                                    $query->where(
                                        '(' . $db->quoteName('study.booknumber') . ' = ' . (int)$book .
                                        ' AND ' . $db->quoteName('study.chapter_begin') . ' >= ' . (int)$chb .
                                        ' AND ' . $db->quoteName('study.chapter_end') . ' <= ' . (int)$che . ')' .
                                        ' OR ' . $db->quoteName('study.booknumber2') . ' = ' . (int)$book
                                    );
                                } elseif ($chb) {
                                    $query->where(
                                        '(' . $db->quoteName('study.booknumber') . ' = ' . (int)$book . ' AND ' . $db->quoteName('study.chapter_begin') . ' >= ' .
                                        (int)$chb . ') OR ' . $db->quoteName('study.booknumber2') . ' = ' . (int)$book
                                    );
                                } elseif ($che) {
                                    $query->where(
                                        '(' . $db->quoteName('study.booknumber') . ' = ' . (int)$book . ' AND ' . $db->quoteName('study.chapter_end') . ' <= ' .
                                        (int)$che . ') OR ' . $db->quoteName('study.booknumber2') . ' = ' . (int)$book
                                    );
                                } else {
                                    $query->where(
                                        '(' . $db->quoteName('study.booknumber') . ' = ' . (int)$book . ' OR ' . $db->quoteName('study.booknumber2') . ' = ' . (int)$book . ')'
                                    );
                                }
                            }
                        }
                    }
                }
            }
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
                $db->quoteName('study.studytitle') . ' LIKE ' . $like
                . ' OR ' . $db->quoteName('study.studytext') . ' LIKE ' . $like
                . ' OR ' . $db->quoteName('study.studyintro') . ' LIKE ' . $like
                . ' OR ' . $db->quoteName('series.series_text') . ' LIKE ' . $like
                . ' OR ' . $db->quoteName('series.description') . ' LIKE ' . $like
                . ' OR ' . $db->quoteName('t.topic_text') . ' LIKE ' . $like
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

        foreach ($items as $item) {
            $sid                  = (int) $item->id;
            $stats                = $mediaStats[$sid] ?? null;
            $item->mids           = $stats->mids ?? null;
            $item->totalplays     = (int) ($stats->totalplays ?? 0);
            $item->totaldownloads = (int) ($stats->totaldownloads ?? 0);
            $item->study_id       = $sid;
        }

        // Batch-load all scripture references
        $scriptureMap = CwmscriptureHelper::getScripturesForStudies($studyIds);

        foreach ($items as $item) {
            $item->scriptures = $scriptureMap[(int) $item->id] ?? [];
        }

        // Batch-load all teachers (for teachers-list element)
        $teacherMap = CwmstudyteacherHelper::getTeachersForStudies($studyIds);

        foreach ($items as $item) {
            $item->teachers = $teacherMap[(int) $item->id] ?? [];
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
            ->from($db->quoteName('#__bsms_mediafiles'))
            ->where($db->quoteName('published') . ' = 1')
            ->whereIn($db->quoteName('study_id'), $studyIds)
            ->group($db->quoteName('study_id'));

        $db->setQuery($query);

        return $db->loadObjectList('study_id') ?: [];
    }
}
