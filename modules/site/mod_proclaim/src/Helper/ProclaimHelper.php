<?php

/**
 * Helper for
 *
 * @package     Proclaim
 * @subpackage  mod.proclaim
 * @copyright   (C) 2025 CWM Team All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://www.christianwebministries.org
 * */

namespace CWM\Module\Proclaim\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Registry\Registry;

/**
 * BibleStudy mod helper
 *
 * @package     Proclaim
 * @subpackage  mod.proclaim
 * @since       7.1.0
 */
class ProclaimHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Get Latest
     *
     * @param   Registry         $params  Item Params
     * @param   SiteApplication  $app
     *
     * @return array
     *
     * @throws \Exception
     * @since 7.1.0
     */
    public function getLatest(Registry $params, SiteApplication $app): array
    {
        $user = $app->getSession()->get('user');

        $groups = [1];

        if (isset($user)) {
            $groups = $user->getAuthorisedViewLevels();
        }

        $db = $this->getDatabase();
        $db->setQuery('SET SQL_BIG_SELECTS=1');
        $db->execute();

        $query = $db->getQuery(true);

        $orderparam = $params->get('order', '1');
        $order      = ($orderparam === '2') ? 'ASC' : 'DESC';
        $condition  = ($params->get('condition') > 0) ? ' AND ' : ' OR ';

        $query->select(
            $db->quoteName(
                [
                    'study.id',
                    'study.published',
                    'study.studydate',
                    'study.studytitle',
                    'study.booknumber',
                    'study.chapter_begin',
                    'study.verse_begin',
                    'study.chapter_end',
                    'study.verse_end',
                    'study.hits',
                    'study.alias',
                    'study.studyintro',
                    'study.teacher_id',
                    'study.secondary_reference',
                    'study.booknumber2',
                    'study.location_id',
                    'study.params',
                    'study.modified_by',
                    'study.publish_down',
                    'study.series_id',
                    'study.download_id',
                    'study.thumbnailm',
                    'study.thumbhm',
                    'study.thumbwm',
                    'study.access',
                    'study.user_name',
                    'study.user_id',
                    'study.studynumber',
                    'study.chapter_begin2',
                    'study.chapter_end2',
                    'study.verse_end2',
                    'study.verse_begin2',
                ]
            )
        );

        $query->select('uam.name as modified_by_name');

        // Use created if modified is 0
        $query->select(
            'CASE WHEN study.modified = ' . $db->quote($db->getNullDate()) .
            ' THEN study.studydate ELSE study.modified END as modified'
        );

        // Use created if publish_up is 0
        $query->select(
            'CASE WHEN study.publish_up = ' . $db->quote($db->getNullDate()) .
            ' THEN study.studydate ELSE study.publish_up END as publish_up'
        );

        $query->select($query->length('study.studytext') . ' AS readmore');
        $query->select(
            'CASE WHEN CHAR_LENGTH(study.alias) THEN CONCAT_WS(\':\', study.id, study.alias) ELSE study.id END as slug'
        );

        $query->from($db->quoteName('#__bsms_studies', 'study'));

        // Join over Message Types
        $query->select($db->quoteName('messageType.message_type', 'messageType'));
        $query->join('LEFT', $db->quoteName('#__bsms_message_type', 'messageType') . ' ON messageType.id = study.messagetype');

        // Join over Teachers
        $query->select(
            $db->quoteName(
                [
                    'teacher.teachername',
                    'teacher.title',
                    'teacher.teacher_thumbnail',
                    'teacher.thumbh',
                    'teacher.thumbw',
                ],
                [
                    'teachername',
                    'title',
                    'thumb',
                    null,
                    null,
                ]
            )
        );
        $query->join('LEFT', $db->quoteName('#__bsms_teachers', 'teacher') . ' ON teacher.id = study.teacher_id');

        // Join over Series
        $query->select($db->quoteName(['series.series_text', 'series.series_thumbnail']));
        $query->select($db->quoteName('series.description', 'sdescription'));
        $query->join('LEFT', $db->quoteName('#__bsms_series', 'series') . ' ON series.id = study.series_id');

        // Join over Books
        $query->select($db->quoteName('book.bookname'));
        $query->join('LEFT', $db->quoteName('#__bsms_books', 'book') . ' ON book.booknumber = study.booknumber');

        $query->select($db->quoteName('book2.bookname', 'bookname2'));
        $query->join('LEFT', $db->quoteName('#__bsms_books', 'book2') . ' ON book2.booknumber = study.booknumber2');

        // Join over MediaFiles and Plays/Downloads
        $query->select('GROUP_CONCAT(DISTINCT ' . $db->quoteName('mediafile.id') . ') as mids');
        $query->select('SUM(' . $db->quoteName('mediafile.plays') . ') AS totalplays');
        $query->select('SUM(' . $db->quoteName('mediafile.downloads') . ') as totaldownloads');
        $query->select($db->quoteName('mediafile.study_id'));
        $query->join('LEFT', $db->quoteName('#__bsms_mediafiles', 'mediafile') . ' ON mediafile.study_id = study.id');

        // Join over topics
        $query->select('GROUP_CONCAT(DISTINCT ' . $db->quoteName('st.topic_id') . ')');
        $query->join('LEFT', $db->quoteName('#__bsms_studytopics', 'st') . ' ON study.id = st.study_id');
        $query->select('GROUP_CONCAT(DISTINCT ' . $db->quoteName('t.id') . ')');
        $query->select('GROUP_CONCAT(DISTINCT ' . $db->quoteName('t.topic_text') . ') as topics_text');
        $query->select('GROUP_CONCAT(DISTINCT ' . $db->quoteName('t.params') . ')');
        $query->join('LEFT', $db->quoteName('#__bsms_topics', 't') . ' ON t.id = st.topic_id');

        // Join over the users for the author and modified_by names.
        $query->select("CASE WHEN study.user_name > ' ' THEN study.user_name ELSE users.name END AS submitted")
            ->select($db->quoteName('users.email', 'author_email'))
            ->join('LEFT', $db->quoteName('#__users', 'users') . ' ON study.user_id = users.id')
            ->join('LEFT', $db->quoteName('#__users', 'uam') . ' ON uam.id = study.modified_by');

        $query->group(
            $db->quoteName(
                [
                    'study.id',
                    'book.bookname',
                    'book2.bookname',
                ]
            )
        );

        // Filter only for authorized view
        $query->where('(' . $db->quoteName('series.access') . ' IN (' . implode(',', $groups) . ') OR ' . $db->quoteName('study.series_id') . ' <= 0)');
        $query->where($db->quoteName('study.access') . ' IN (' . implode(',', $groups) . ')');

        // Select only published studies
        $query->where($db->quoteName('study.published') . ' = 1');
        $query->where('(' . $db->quoteName('series.published') . ' = 1 OR ' . $db->quoteName('study.series_id') . ' <= 0)');

        // Define null and now dates
        $nullDate = $db->quote($db->getNullDate());
        $nowDate  = $db->quote(Factory::getDate()->toSql(true));

        // Filter by start and end dates.
        if (!$user->authorise('core.edit.state', 'com_proclaim') && !$user->authorise('core.edit', 'com_proclaim')) {
            $query->where('(' . $db->quoteName('study.publish_up') . ' = ' . $nullDate . ' OR ' . $db->quoteName('study.publish_up') . ' <= ' . $nowDate . ')')
                ->where('(' . $db->quoteName('study.publish_down') . ' = ' . $nullDate . ' OR ' . $db->quoteName('study.publish_down') . ' >= ' . $nowDate . ')');
        }

        // Apply filters
        $this->applyFilter($query, $params->get('teacher_id'), 'study.teacher_id', $condition);
        $this->applyFilter($query, $params->get('locations'), 'study.location_id', $condition);
        $this->applyFilter($query, $params->get('booknumber'), 'study.booknumber', $condition);
        $this->applyFilter($query, $params->get('series_id'), 'study.series_id', $condition);
        $this->applyFilter($query, $params->get('topic_id'), 'st.topic_id', $condition);
        $this->applyFilter($query, $params->get('messagetype'), 'study.messagetype', $condition);
        $this->applyFilter($query, $params->get('year'), 'YEAR(study.studydate)', $condition);

        // Filter by language
        $language = $params->get('language', '*');
        $lang     = Factory::getApplication()->getLanguage();

        if ($lang || $language !== '*') {
            $query->where(
                $db->quoteName('study.language') . ' IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')'
            );
        }

        $query->order($db->quoteName('studydate') . ' ' . $order);
        $db->setQuery((string)$query, 0, $params->get('moduleitems', '5'));

        return $db->loadObjectList();
    }

    /**
     * Apply filter to query
     *
     * @param   \Joomla\Database\DatabaseQuery  $query      The query object
     * @param   mixed                           $filters    The filter values
     * @param   string                          $field      The field to filter on
     * @param   string                          $condition  The condition (AND/OR)
     *
     * @return  void
     *
     * @since   10.0.0
     */
    private function applyFilter($query, $filters, string $field, string $condition): void
    {
        if (empty($filters)) {
            return;
        }

        if (!\is_array($filters)) {
            $filters = [$filters];
        }

        $validFilters = [];

        foreach ($filters as $filter) {
            if ($filter !== -1 && $filter !== 0 && $filter !== '' && $filter !== null) {
                $validFilters[] = (int)$filter;
            }
        }

        if (empty($validFilters)) {
            return;
        }

        if (\count($validFilters) > 1) {
            $where = [];

            foreach ($validFilters as $filter) {
                $where[] = $field . ' = ' . $filter;
            }

            $query->where('(' . implode(' OR ', $where) . ')');
        } else {
            $query->where($field . ' = ' . $validFilters[0], $condition);
        }
    }
}
