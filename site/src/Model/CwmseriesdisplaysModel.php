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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
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
     * @see     ListModel
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

        // Get show_archived parameter from menu, fall back to template default
        $showArchived = $params->get('show_archived', '');
        if ($showArchived === '' || $showArchived === null) {
            $showArchived = $params->get('sdefault_show_archived', '0');
        }
        $this->setState('filter.show_archived', $showArchived);

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
    protected function getStoreId($id = ''): string
    {
        // Compile the store id.
        $id .= ':' . serialize($this->getState('filter.published'));
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.language');
        $id .= ':' . serialize($this->getState('filter.teacher'));
        $id .= ':' . $this->getState('filter.show_archived');

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
        $query->from($db->quoteName('#__bsms_series', 'se'));
        $query->select(
            $db->quoteName('t.id', 'tid') . ', ' . $db->quoteName('t.teachername') . ', '
            . $db->quoteName('t.title', 'teachertitle') . ', '
            . $db->quoteName('t.teacher_thumbnail') . ', '
            . $db->quoteName('t.teacher_thumbnail', 'thumb')
        );
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_teachers', 't') . ' ON '
            . $db->quoteName('se.teacher') . ' = ' . $db->quoteName('t.id')
        );
        $query->select(
            $db->quoteName('s.id', 'sid') . ', ' . $db->quoteName('s.series_id') . ', '
            . $db->quoteName('s.studydate')
        );
        $query->join(
            'INNER',
            $db->quoteName('#__bsms_studies', 's') . ' ON '
            . $db->quoteName('s.series_id') . ' = ' . $db->quoteName('se.id')
        );
        $query->group($db->quoteName('se.id'));

        // Filter by access level.
        if ($this->getState('filter.access', true)) {
            $groups = $this->getState('filter.viewlevels', $user->getAuthorisedViewLevels());
            $query->whereIn($db->quoteName('se.access'), $groups);
        }

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $like = $db->quote('%' . $search . '%');
            $query->where($db->quoteName('se.description') . ' LIKE ' . $like);
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

        // Filter by published state based on show_archived parameter
        $showArchived = $this->getState('filter.show_archived', '0');
        switch ($showArchived) {
            case '1': // Archived only
                $query->where($db->quoteName('se.published') . ' = 2');
                break;
            case '2': // Both published and archived
                $query->where($db->quoteName('se.published') . ' IN (1, 2)');
                break;
            default: // Published only (backward compatible)
                $query->where($db->quoteName('se.published') . ' = 1');
                break;
        }

        // Filter by publish dates for non-admin users (like Joomla category date filtering)
        if (!$user->authorise('core.edit.state', 'com_proclaim') && !$user->authorise('core.edit', 'com_proclaim')) {
            $nullDate = $db->quote($db->getNullDate());
            $nowDate  = $db->quote((new Date())->toSql());
            $query->where('(' . $db->quoteName('se.publish_up') . ' = ' . $nullDate . ' OR ' . $db->quoteName('se.publish_up') . ' <= ' . $nowDate . ')')
                ->where('(' . $db->quoteName('se.publish_down') . ' = ' . $nullDate . ' OR ' . $db->quoteName('se.publish_down') . ' >= ' . $nowDate . ')');
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
}
