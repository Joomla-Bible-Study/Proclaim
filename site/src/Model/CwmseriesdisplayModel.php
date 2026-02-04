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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * Model class for SeriesDisplay
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class CwmseriesdisplayModel extends ItemModel
{
    /**
     * Model context string.
     *
     * @var        string
     *
     * @since 7.0
     */
    protected $context = 'com_proclaim.seriesdisplay';

    /**
     * Method to get study data.
     *
     * @param   int  $pk  The id of the study.
     *
     * @return    mixed    Menu item data object on success, false on failure.
     *
     * @throws \Exception
     *
     * @since 7.1.0
     * @todo  look are removing this may not used. bcc
     */
    public function getItem($pk = null): mixed
    {
        // Initialise variables.
        $pk = (!empty($pk)) ? $pk : (int) $this->getState('series.id');

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
                    ['t.id', 't.teachername', 't.title', 't.thumb', 't.thumbh', 't.thumbw', 't.teacher_thumbnail'],
                    ['tid', null, 'teachertitle', null, null, null, null]
                )
            );
            $query->join('LEFT', $db->quoteName('#__bsms_teachers', 't') . ' ON se.teacher = t.id');
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

    /**
     * Get Studies
     *
     * @return mixed
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getStudies(): mixed
    {
        $app = Factory::getApplication();
        $sid = (int) $app->getUserState('sid');

        /** @var Registry $params */
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
                'study.id, study.published, study.studydate, study.studytitle, study.booknumber, study.chapter_begin,
		                study.verse_begin, study.chapter_end, study.verse_end, study.hits, study.alias, study.studyintro,
		                study.teacher_id, study.secondary_reference, study.booknumber2, study.location_id, ' .
                // Use created if modified is 0
                'CASE WHEN study.modified = ' . $db->quote($nullDate) .
                ' THEN study.studydate ELSE study.modified END AS modified, ' .
                'study.modified_by, user_name AS modified_by_name,' .
                // Use created if publish_up is 0
                'CASE WHEN study.publish_up = ' . $db->quote($nullDate) .
                ' THEN study.studydate ELSE study.publish_up END AS publish_up,' .
                'study.publish_down,
		                study.series_id, study.download_id, study.thumbnailm, study.thumbhm, study.thumbwm,
		                study.access, study.user_name, study.user_id, study.studynumber, study.chapter_begin2, study.chapter_end2,
		                study.verse_end2, study.verse_begin2, ' . $query->length('study.studytext') . ' AS readmore,'
            )
            . ' CASE WHEN CHAR_LENGTH(study.alias) THEN CONCAT_WS(\':\', study.id, study.alias) ELSE study.id END AS slug '
        );
        $query->from($db->quoteName('#__bsms_studies', 'study'));

        // Join over Message Types
        $query->select($db->quoteName('messageType.message_type', 'message_type'));
        $query->join('LEFT', $db->quoteName('#__bsms_message_type', 'messageType') . ' ON messageType.id = study.messagetype');

        // Join over Teachers
        $query->select(
            $db->quoteName('teacher.teachername', 'teachername') . ', ' .
            $db->quoteName('teacher.title', 'teachertitle') . ', ' .
            $db->quoteName('teacher.teacher_thumbnail', 'thumb') . ', ' .
            $db->quoteName('teacher.thumbh') . ', ' .
            $db->quoteName('teacher.thumbw')
        );
        $query->join('LEFT', $db->quoteName('#__bsms_teachers', 'teacher') . ' ON teacher.id = study.teacher_id');

        // Join over Series
        $query->select(
            $db->quoteName(
                ['series.series_text', 'series.series_thumbnail', 'series.description', 'series.access'],
                [null, null, 'sdescription', 'series_access']
            )
        );
        $query->join('LEFT', $db->quoteName('#__bsms_series', 'series') . ' ON series.id = study.series_id');

        // Join over Books
        $query->select($db->quoteName('book.bookname'));
        $query->join('LEFT', $db->quoteName('#__bsms_books', 'book') . ' ON book.booknumber = study.booknumber');

        $query->select($db->quoteName('book2.bookname', 'bookname2'));
        $query->join('LEFT', $db->quoteName('#__bsms_books', 'book2') . ' ON book2.booknumber = study.booknumber2');

        // Join over Plays/Downloads
        $query->select(
            'SUM(' . $db->quoteName('mediafile.plays') . ') AS totalplays, ' .
            'SUM(' . $db->quoteName('mediafile.downloads') . ') AS totaldownloads, ' .
            $db->quoteName('mediafile.study_id')
        );
        $query->join('LEFT', $db->quoteName('#__bsms_mediafiles', 'mediafile') . ' ON mediafile.study_id = study.id');

        // Join over Locations
        $query->select($db->quoteName('locations.location_text'));
        $query->join('LEFT', $db->quoteName('#__bsms_locations', 'locations') . ' ON study.location_id = locations.id');

        // Join over topics
        $query->select('GROUP_CONCAT(DISTINCT ' . $db->quoteName('st.topic_id') . ')');
        $query->join('LEFT', $db->quoteName('#__bsms_studytopics', 'st') . ' ON study.id = st.study_id');
        $query->select(
            'GROUP_CONCAT(DISTINCT ' . $db->quoteName('t.id') . '), ' .
            'GROUP_CONCAT(DISTINCT ' . $db->quoteName('t.topic_text') . ') AS topics_text, ' .
            'GROUP_CONCAT(DISTINCT ' . $db->quoteName('t.params') . ')'
        );
        $query->join('LEFT', $db->quoteName('#__bsms_topics', 't') . ' ON t.id = st.topic_id');

        // Join over users
        $query->select($db->quoteName('users.name', 'submitted'));
        $query->join('LEFT', $db->quoteName('#__users', 'users') . ' ON study.user_id = users.id');

        $query->group($db->quoteName('study.id'));

        $query->select('GROUP_CONCAT(DISTINCT ' . $db->quoteName('m.id') . ') AS mids');
        $query->join('LEFT', $db->quoteName('#__bsms_mediafiles', 'm') . ' ON study.id = m.study_id');

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
        $showArchived = $params->get('show_archived', '');
        if ($showArchived === '' || $showArchived === null) {
            $showArchived = $t_params->get('default_show_archived', '0');
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

        $query->extendWhere(
            'AND',
            [
                $db->quoteName('series.published') . ' = 1',
                $db->quoteName('study.series_id') . ' <= 0',
            ],
            'OR'
        );

        $query->where($db->quoteName('study.series_id') . ' = :sid')
            ->bind(':sid', $sid, ParameterType::INTEGER);

        // Order by order filter
        $orderparam = (int) $params->get('default_order');

        if (empty($orderparam)) {
            $orderparam = $t_params->get('series_detail_order', '1');
        }

        $order = ($orderparam === 2) ? 'ASC' : 'DESC';

        $query->order($db->quoteName('study.studydate') . ' ' . $order);
        $db->setQuery($query, 0, (int) $t_params->get('series_detail_limit', 20));
        $studies = $db->loadObjectList();

        if (\count($studies) < 1) {
            return false;
        }

        return $studies;
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

        // Load state from the request.
        $pk = $app->input->get('id', '', 'int');
        $this->setState('series.id', $pk);

        $offset = $app->input->get('limitstart', '', 'int');
        $this->setState('list.offset', $offset);

        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);
        $template = Cwmparams::getTemplateparams();
        $admin    = Cwmparams::getAdmin();

        $template->params->merge($params);
        $template->params->merge($admin->params);
        $params = $template->params;

        $t = (int) $params->get('seriesid');

        if (!$t) {
            $t = $app->input->get('t', 1, 'int');
        }

        $template->id = $t;

        $this->setState('template', $template);
        $this->setState('administrator', $admin);

        $user = $app->getIdentity();

        if (
            (!$user->authorise('core.edit.state', 'com_proclaim')) && (!$user->authorise(
                'core.edit',
                'com_proclaim'
            ))
        ) {
            $this->setState('filter.published', 1);
            $this->setState('filter.archived', 2);
        }
    }
}
