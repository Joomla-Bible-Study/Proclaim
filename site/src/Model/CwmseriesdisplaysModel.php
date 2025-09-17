<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Model;

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Model class for SeriesDisplays
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class CwmseriesdisplaysModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @throws \Exception
     * @since   11.1
     * @see     JController
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
            'id',
            'se.id',
            'published',
            'se.published',
            's.studydate',
            's.studytitle',
            's.studytitle',
            'ordering',
            's.ordering',
            'bookname',
            'book.bookname',
            't.teachername',
            'series_text',
            's.seriesid',
            's.series_id',
            's.hits',
            'access',
            'access_level',
            'language',
            's.language',
            'search',

            ];
        }

        $this->input = Factory::getApplication();

        parent::__construct($config);
    }



    /**
     * Get a list of teachers associated with series
     *
     * @return mixed
     * @since 9.0.0
     */
    public function getTeachers(): array
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('t.id AS value, t.teachername AS text');
        $query->from('#__bsms_teachers AS t');
        $query->select('series.access');
        $query->join('INNER', '#__bsms_series AS series ON t.id = series.teacher');
        $query->group('t.id');
        $query->order('t.teachername ASC');

        $db->setQuery($query->__toString());

        return $db->loadObjectList();
    }

    /**
     * Get a list of teachers associated with series
     *
     * @return mixed
     * @since 9.0.0
     */
    public function getYears(): array
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('DISTINCT YEAR(s.studydate) as value, YEAR(s.studydate) as text');
        $query->from('#__bsms_studies as s');
        $query->select('series.access');
        $query->join('INNER', '#__bsms_series as series on s.series_id = series.id');
        $query->order('value');

        $db->setQuery($query->__toString());

        return $db->loadObjectList();
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
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);

        $query->select('series.id AS value, series.series_text AS text, series.access');
        $query->from('#__bsms_series AS series');
        $query->join('INNER', '#__bsms_studies AS study ON study.series_id = series.id');
        $query->group('series.id');
        $query->order('series.series_text');

        $db->setQuery($query->__toString());
        $items = $db->loadObjectList();

        // Check permissions for this view by running through the records and removing those the user doesn't have permission to see
        $user   = Factory::getApplication()->getIdentity();
        $groups = $user->getAuthorisedViewLevels();
        $count  = count($items);

        if ($count > 0) {
            foreach ($items as $i => $iValue) {
                if ($iValue->access > 1) {
                    if (!in_array($iValue->access, $groups, true)) {
                        unset($items[$i]);
                    }
                }
            }
        }

        return $items;
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
     * @since   11.1
     */
    protected function populateState($ordering = 'series_text', $direction = 'DESC'): void
    {
        /** @type \JApplicationSite $app */
        $app = Factory::getApplication();

        $forcedLanguage = $app->getInput()->get('forcedLanguage', '', 'cmd');

        // Adjust the context to support modal layouts.
        $input  = $app->getInput();
        $layout = $input->get('layout');

        if ($layout) {
            $this->context .= '.' . $layout;
        }

        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);
        $template = Cwmparams::getTemplateparams();
        $admin    = Cwmparams::getAdmin();

        $template->params->merge($params);
        $template->params->merge($admin->params);
        $params = $template->params;

        $t = (int)$params->get('seriesid');

        if (!$t) {
            $t = $input->get('t', 1, 'int');
        }

        $template->id = $t;

        $this->setState('template', $template);
        $this->setState('administrator', $admin);

        // List state information
        $value = $input->get('limit', $app->get('list_limit', 0), 'uint');
        $this->setState('list.limit', $value);

        $value = $input->get('limitstart', 0, 'uint');
        $this->setState('list.start', $value);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $series = $this->getUserStateFromRequest($this->context . '.filter.series', 'filter_series');
        $this->setState('filter.series', $series);

        $this->setState('filter.language', LanguageHelper::getLanguages());

        $teacher = $this->getUserStateFromRequest($this->context . '.filter.teacher', 'filter_teacher');
        $this->setState('filter.teacher', $teacher);

        // Process show_noauth parameter
        if (!$params->get('show_noauth')) {
            $this->setState('filter.access', true);
        } else {
            $this->setState('filter.access', false);
        }
        $orderCol = $input->get('filter_order');

        if (!\in_array($orderCol, $this->filter_fields, true)) {
            $orderCol = 'se.series_text';
        }

        $this->setState('list.ordering', $orderCol);

        // From landing page filter passing
        $listOrder = $input->get('filter_order_Dir', 'DESC');

        if (!\in_array(strtoupper($listOrder), ['ASC', 'DESC', ''])) {
            $direction = 'DESC';
        }

        $this->setState('list.direction', $direction);

        $user = $this->getCurrentUser();

        if ((!$user->authorise('core.edit.state', 'com_proclaim')) && (!$user->authorise('core.edit', 'com_proclaim'))) {
            // Filter on published for those who do not have edit or edit.state rights.
            $this->setState('filter.published', ContentComponent::CONDITION_PUBLISHED);
        }

        $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
        $this->setState('filter.language', $language);

        $this->setState('layout', $input->get('layout', '', 'cmd'));
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
     * @since   1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . serialize($this->getState('filter.published'));
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.language');
        $id .= ':' . serialize($this->getState('filter.teacher'));

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data
     *
     * @return  QueryInterface  A DatabaseQuery object to retrieve the data set.
     *
     * @throws \Exception
     * @since   7.0
     */
    protected function getListQuery(): QueryInterface
    {
        $user = $this->getCurrentUser();

        // Create a new query object.
        $db = $this->getDatabase();

        $query           = $db->getQuery(true);
        $params          = ComponentHelper::getParams('com_proclaim');

        $query->select(
            $this->getState(
                'list.select',
                'se.*,CASE WHEN CHAR_LENGTH(se.alias) THEN CONCAT_WS(\':\', se.id, se.alias) ELSE se.id END as slug'
            )
        );
        $query->from('#__bsms_series as se');
        $query->select(
            't.id as tid, t.teachername, t.title as teachertitle, t.thumb, t.thumbh, t.thumbw, t.teacher_thumbnail'
        );
        $query->join('LEFT', '#__bsms_teachers as t on se.teacher = t.id');
        $query->select('s.id as sid, s.series_id, s.studydate');
        $query->join('INNER', '#__bsms_studies as s on s.series_id = se.id');
        $query->group('se.id');

        // Filter by access level.
        if ($this->getState('filter.access', true)) {
            $groups = $this->getState('filter.viewlevels', $user->getAuthorisedViewLevels());
            $query->whereIn($db->quoteName('se.access'), $groups);
        }

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $like = $db->quote('%' . $search . '%');
            $query->where('se.description LIKE ' . $like);
        }

        // Filter by language
        if ($this->getState('filter.language')) {
            $query->whereIn($db->quoteName('se.language'), [$this->getState('filter.language')], ParameterType::STRING);
        } elseif (Multilanguage::isEnabled()) {
            $query->whereIn($db->quoteName('se.language'), [Factory::getApplication()->getLanguage()->getTag(), '*'], ParameterType::STRING);
        }

        // Filter by a single Teacher
        $teacher = $this->getState('filter.teacher');

        if (is_numeric($teacher)) {
            $teacher   = (int) $teacher;
            $type      = $this->getState('filter.teacher.include', true) ? ' = ' : ' <> ';
            $query->where($db->quoteName('se.teacher') . $type . ':teacher')
                ->bind(':teacher', $teacher, ParameterType::INTEGER);
        }

        //Filter by year
        /* $year = $this->getState('filter.year');
         if (is_numeric($year)) {
             $year = (int) $year;
             $type      = $this->getState('filter.year.include', true) ? ' = ' : ' <> ';
             $query->having($db->quoteName('YEAR(s.studydate)') . $type . ':year')
                 ->bind(':year', $year, ParameterType::INTEGER);
         } */
        // Add the list ordering clause.
        $orderCol  = $this->getState('list.fullordering');
        $orderDirn = '';

        if (empty($orderCol) || $orderCol === " ") {
            $orderCol = $this->getState('list.ordering', 'se.series_text');
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
     * Method to get the starting number of items for the data set.
     *
     * @return  int  The starting number of items available in the data set.
     *
     * @since   3.0.1
     */
    public function getStart(): int
    {
        return (int) $this->getState('list.start');
    }


}
