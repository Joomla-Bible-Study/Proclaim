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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use JApplicationSite;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseQuery;

/**
 * Model class for SeriesDisplays
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class CwmseriesdisplaysModel extends ListModel
{
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
    protected function populateState($ordering = null, $direction = null): void
    {
        /** @type JApplicationSite $app */
        $app = Factory::getApplication();

        // Adjust the context to support modal layouts.
        $input  = Factory::getApplication()->input;
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

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $series = $this->getUserStateFromRequest($this->context . '.filter.series', 'filter_series');
        $this->setState('filter.series', $series);

        $this->setState('filter.language', LanguageHelper::getLanguages());

        $teacher = $this->getUserStateFromRequest($this->context . '.filter.teacher', 'filter_teacher', '');
        $this->setState('filter.teacher', $teacher);

        $year = $this->getUserStateFromRequest($this->context . '.filter.year', 'filter_year', '');
        $this->setState('filter.year', $year);

        // Process show_noauth parameter
        if (!$params->get('show_noauth')) {
            $this->setState('filter.access', true);
        } else {
            $this->setState('filter.access', false);
        }

        $this->setState('layout', $input->get('layout', '', 'cmd'));
        parent::populateState('se.id', 'ASC');
        $value = $input->get('start', '', 'int');
        $this->setState('list.start', $value);
    }

    /**
     * Build an SQL query to load the list data
     *
     * @return  DatabaseQuery  A DatabaseQuery object to retrieve the data set.
     *
     * @throws \Exception
     * @since   7.0
     */
    protected function getListQuery(): DatabaseQuery
    {
        $db              = Factory::getContainer()->get('DatabaseDriver');
        $template_params = Cwmparams::getTemplateparams();
        $t_params        = $template_params->params;
        $app             = Factory::getApplication();
        $params          = ComponentHelper::getParams('com_proclaim');

        $sitemenu  = $app->getMenu();
        $menuitems = $sitemenu->getItems(array(), array());

        foreach ($menuitems as $menuitem) {
            $menuparams = $menuitem->getParams();
        }

        $query = $db->getQuery(true);
        $query->select(
            'se.*,CASE WHEN CHAR_LENGTH(se.alias) THEN CONCAT_WS(\':\', se.id, se.alias) ELSE se.id END as slug'
        );
        $query->from('#__bsms_series as se');
        $query->select(
            't.id as tid, t.teachername, t.title as teachertitle, t.thumb, t.thumbh, t.thumbw, t.teacher_thumbnail'
        );
        $query->join('LEFT', '#__bsms_teachers as t on se.teacher = t.id');
        $query->select('s.id as sid, s.series_id, s.studydate');
        $query->join('INNER', '#__bsms_studies as s on s.series_id = se.id');
        $query->group('se.id');
        $where = $this->buildContentWhere();
        $query->where($where);

        // Filter by language
        $language = $params->get('language', '*');

        if ($this->getState('filter.language') || $language !== '*') {
            $query->where('se.language in (' . $db->quote($app->getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
        }

        $orderDir = $t_params->get('series_list_order', 'DESC');
        $orderCol = $t_params->get('series_order_field', 'series_text');
        $query->order($orderCol . ' ' . $orderDir);

        return $query;
    }

    /**
     * Build Content of series
     *
     * @return string
     *
     * @throws \Exception
     * @since 7.0
     */
    public function buildContentWhere(): string
    {
        $mainframe      = Factory::getApplication();
        $input          = $mainframe->input;
        $option         = $input->get('option', '', 'cmd');
        $params         = ComponentHelper::getParams($option);
        $filter_series  = $mainframe->getUserStateFromRequest($option . 'filter_series', 'filter_series', 0, 'int');
        $filter_teacher = $mainframe->getUserStateFromRequest($option . 'filter_teacher', 'filter_teacher', 0, 'int');
        $filter_year    = $mainframe->getUserStateFromRequest($option . 'filter_year', 'filter_year', 0, 'int');
        $where          = array();
        $where[]        = ' se.published = 1';

        if ($filter_series > 0) {
            $where[] = ' se.id = ' . (int)$filter_series;
        }

        if ($filter_teacher > 0) {
            $where[] = ' se.teacher = ' . (int)$filter_teacher;
        }

        if ($filter_year > 0) {
            $where[] = ' YEAR(s.studydate) = ' . (int)$filter_year;
        }

        $where = (count($where) ? implode(' AND ', $where) : '');

        $where2   = array();
        $continue = 0;

        if ($params->get('series_id') && !$filter_series) {
            $filters = $params->get('series_id');

            switch ($filters) {
                case is_array($filters):
                    foreach ($filters as $filter) {
                        if ($filter === '-1') {
                            break;
                        }

                        $continue = 1;
                        $where2[] = 'se.id = ' . (int)$filter;
                    }
                    break;

                case '-1':
                    break;

                default:
                    $continue = 1;
                    $where2[] = 'se.id = ' . (int)$filters;
                    break;
            }
        }

        $where2 = (count($where2) ? ' ' . implode(' OR ', $where2) : '');

        if ($continue > 0) {
            $where .= ' AND ( ' . $where2 . ')';
        }

        return $where;
    }
}
