<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

/**
 * Helper to get related studies using weighted multi-dimension scoring.
 *
 * Scoring weights:
 * - Same series:   3 points
 * - Same teacher:  2 points
 * - Same topic:    2 points per overlap
 * - Same book:     1 point per overlap
 * - Metakey match: 1 point per keyword
 *
 * @package  Proclaim.Site
 * @since    7.1.0
 */
class Cwmrelatedstudies
{
    /**
     * Score map: study_id => total score
     *
     * @var  array<int, int>
     * @since 10.1.0
     */
    public array $scores = [];

    /**
     * Get Related studies as card HTML.
     *
     * @param   object    $row     Study data
     * @param   Registry  $params  Item Params
     *
     * @return string|bool  HTML string of card grid, or false if no related studies
     *
     * @throws \Exception
     * @since    7.2
     */
    public function getRelated(object $row, Registry $params): string|bool
    {
        $this->scores = [];
        $studyId      = (int) ($row->id ?? 0);
        $limit        = (int) $params->get('related_limit', 5);

        if ($studyId === 0) {
            return false;
        }

        $db     = Factory::getContainer()->get('DatabaseDriver');
        $user   = Factory::getApplication()->getIdentity();
        $groups = $user->getAuthorisedViewLevels();

        // Dimension 1: Same series (3 points)
        $seriesId = (int) ($row->series_id ?? 0);

        if ($seriesId > 0) {
            $this->scoreBySeries($db, $studyId, $seriesId, $groups);
        }

        // Dimension 2: Same teacher (2 points)
        $teacherId = (int) ($row->teacher_id ?? 0);

        if ($teacherId > 0) {
            $this->scoreByTeacher($db, $studyId, $teacherId, $groups);
        }

        // Dimension 3: Topic overlap (2 points each)
        $this->scoreByTopics($db, $studyId, $groups);

        // Dimension 4: Scripture book overlap (1 point each)
        $this->scoreByBooks($db, $studyId, $groups);

        // Dimension 5: Metakey overlap (1 point each)
        $keywords = (string) $params->get('metakey');

        if (!empty($keywords)) {
            $this->scoreByMetakeys($db, $studyId, $keywords, $groups);
        }

        if (empty($this->scores)) {
            return false;
        }

        return $this->buildCards($db, $studyId, $limit, $params);
    }

    /**
     * Score studies in the same series.
     *
     * @param   object  $db        Database driver
     * @param   int     $studyId   Current study ID
     * @param   int     $seriesId  Series ID to match
     * @param   array   $groups    Authorised view levels
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private function scoreBySeries(object $db, int $studyId, int $seriesId, array $groups): void
    {
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__bsms_studies'))
            ->where($db->quoteName('series_id') . ' = ' . $seriesId)
            ->where($db->quoteName('id') . ' != ' . $studyId)
            ->where($db->quoteName('published') . ' = 1')
            ->where($db->quoteName('access') . ' IN (' . implode(',', $groups) . ')');

        $db->setQuery($query);
        $ids = $db->loadColumn();

        foreach ($ids as $id) {
            $this->addScore((int) $id, 3);
        }
    }

    /**
     * Score studies by the same teacher.
     *
     * @param   object  $db         Database driver
     * @param   int     $studyId    Current study ID
     * @param   int     $teacherId  Teacher ID to match
     * @param   array   $groups     Authorised view levels
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private function scoreByTeacher(object $db, int $studyId, int $teacherId, array $groups): void
    {
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__bsms_studies'))
            ->where($db->quoteName('teacher_id') . ' = ' . $teacherId)
            ->where($db->quoteName('id') . ' != ' . $studyId)
            ->where($db->quoteName('published') . ' = 1')
            ->where($db->quoteName('access') . ' IN (' . implode(',', $groups) . ')');

        $db->setQuery($query);
        $ids = $db->loadColumn();

        foreach ($ids as $id) {
            $this->addScore((int) $id, 2);
        }
    }

    /**
     * Score studies by topic overlap (2 points per shared topic).
     *
     * @param   object  $db       Database driver
     * @param   int     $studyId  Current study ID
     * @param   array   $groups   Authorised view levels
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private function scoreByTopics(object $db, int $studyId, array $groups): void
    {
        // Get current study's topic IDs
        $query = $db->getQuery(true)
            ->select($db->quoteName('topic_id'))
            ->from($db->quoteName('#__bsms_studytopics'))
            ->where($db->quoteName('study_id') . ' = ' . $studyId);

        $db->setQuery($query);
        $topicIds = $db->loadColumn();

        if (empty($topicIds)) {
            return;
        }

        // Find other studies with overlapping topics, counting overlaps
        $query = $db->getQuery(true)
            ->select($db->quoteName('st.study_id'))
            ->select('COUNT(*) AS ' . $db->quoteName('overlap'))
            ->from($db->quoteName('#__bsms_studytopics', 'st'))
            ->innerJoin(
                $db->quoteName('#__bsms_studies', 's')
                . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('st.study_id')
            )
            ->where($db->quoteName('st.topic_id') . ' IN (' . implode(',', array_map('intval', $topicIds)) . ')')
            ->where($db->quoteName('st.study_id') . ' != ' . $studyId)
            ->where($db->quoteName('s.published') . ' = 1')
            ->where($db->quoteName('s.access') . ' IN (' . implode(',', $groups) . ')')
            ->group($db->quoteName('st.study_id'));

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        foreach ($rows as $r) {
            $this->addScore((int) $r->study_id, 2 * (int) $r->overlap);
        }
    }

    /**
     * Score studies by scripture book overlap (1 point per shared book).
     *
     * Checks both junction table and legacy booknumber field.
     *
     * @param   object  $db       Database driver
     * @param   int     $studyId  Current study ID
     * @param   array   $groups   Authorised view levels
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private function scoreByBooks(object $db, int $studyId, array $groups): void
    {
        // Collect book numbers from both junction table and legacy column in one query
        $query = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('booknumber'))
            ->from($db->quoteName('#__bsms_study_scriptures'))
            ->where($db->quoteName('study_id') . ' = ' . $studyId)
            ->where($db->quoteName('booknumber') . ' > 0')
            ->union(
                $db->getQuery(true)
                    ->select($db->quoteName('booknumber'))
                    ->from($db->quoteName('#__bsms_studies'))
                    ->where($db->quoteName('id') . ' = ' . $studyId)
                    ->where($db->quoteName('booknumber') . ' > 0')
            );

        $db->setQuery($query);
        $bookNumbers = array_unique(array_filter(array_map('intval', $db->loadColumn() ?: [])));

        if (empty($bookNumbers)) {
            return;
        }

        $bookList = implode(',', $bookNumbers);

        // Find matches via junction table
        $query = $db->getQuery(true)
            ->select($db->quoteName('ss.study_id'))
            ->select('COUNT(DISTINCT ' . $db->quoteName('ss.booknumber') . ') AS ' . $db->quoteName('overlap'))
            ->from($db->quoteName('#__bsms_study_scriptures', 'ss'))
            ->innerJoin(
                $db->quoteName('#__bsms_studies', 's')
                . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('ss.study_id')
            )
            ->where($db->quoteName('ss.booknumber') . ' IN (' . $bookList . ')')
            ->where($db->quoteName('ss.study_id') . ' != ' . $studyId)
            ->where($db->quoteName('s.published') . ' = 1')
            ->where($db->quoteName('s.access') . ' IN (' . implode(',', $groups) . ')')
            ->group($db->quoteName('ss.study_id'));

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        foreach ($rows as $r) {
            $this->addScore((int) $r->study_id, (int) $r->overlap);
        }

        // Also match via legacy booknumber column
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__bsms_studies'))
            ->where($db->quoteName('booknumber') . ' IN (' . $bookList . ')')
            ->where($db->quoteName('id') . ' != ' . $studyId)
            ->where($db->quoteName('published') . ' = 1')
            ->where($db->quoteName('access') . ' IN (' . implode(',', $groups) . ')');

        $db->setQuery($query);
        $ids = $db->loadColumn();

        foreach ($ids as $id) {
            $this->addScore((int) $id, 1);
        }
    }

    /**
     * Score studies by metakey overlap (1 point per shared keyword).
     *
     * @param   object  $db        Database driver
     * @param   int     $studyId   Current study ID
     * @param   string  $keywords  Comma-separated keywords
     * @param   array   $groups    Authorised view levels
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private function scoreByMetakeys(object $db, int $studyId, string $keywords, array $groups): void
    {
        $keys = array_filter(array_map('trim', explode(',', $keywords)));

        if (empty($keys)) {
            return;
        }

        // Use SQL LIKE matching to avoid loading all studies into PHP.
        // Each keyword match adds 1 point. We run one query per keyword
        // but only return IDs (no heavy params deserialization).
        foreach ($keys as $key) {
            $key = trim($key);

            if ($key === '') {
                continue;
            }

            $escaped = $db->quote('%' . $db->escape($key, true) . '%');
            $query   = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__bsms_studies'))
                ->where($db->quoteName('id') . ' != ' . $studyId)
                ->where($db->quoteName('published') . ' = 1')
                ->where($db->quoteName('access') . ' IN (' . implode(',', $groups) . ')')
                ->where($db->quoteName('params') . ' LIKE ' . $escaped);

            $db->setQuery($query);
            $ids = $db->loadColumn();

            foreach ($ids as $id) {
                $this->addScore((int) $id, 1);
            }
        }
    }

    /**
     * Add points to a study's score.
     *
     * @param   int  $studyId  Study ID
     * @param   int  $points   Points to add
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function addScore(int $studyId, int $points): void
    {
        if (!isset($this->scores[$studyId])) {
            $this->scores[$studyId] = 0;
        }

        $this->scores[$studyId] += $points;
    }

    /**
     * Build card grid HTML for the top-scoring related studies.
     *
     * @param   object  $db       Database driver
     * @param   int     $studyId  Current study ID (excluded)
     * @param   int     $limit    Max cards to show
     *
     * @return  string  HTML card grid
     *
     * @throws \Exception
     * @since   10.1.0
     */
    private function buildCards(object $db, int $studyId, int $limit, Registry $params): string
    {
        // Sort by score desc
        arsort($this->scores);

        // Take top N
        $topIds = \array_slice(array_keys($this->scores), 0, $limit);

        if (empty($topIds)) {
            return '';
        }

        $idList = implode(',', $topIds);

        $query = $db->getQuery(true)
            ->select($db->quoteName([
                's.id', 's.studytitle', 's.alias', 's.studydate',
                's.booknumber', 's.chapter_begin', 's.thumbnailm', 's.image',
            ]))
            ->select($db->quoteName('t.teachername'))
            ->select($db->quoteName('b.bookname'))
            ->from($db->quoteName('#__bsms_studies', 's'))
            ->leftJoin(
                $db->quoteName('#__bsms_teachers', 't')
                . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('s.teacher_id')
            )
            ->leftJoin(
                $db->quoteName('#__bsms_books', 'b')
                . ' ON ' . $db->quoteName('b.booknumber') . ' = ' . $db->quoteName('s.booknumber')
            )
            ->where($db->quoteName('s.id') . ' IN (' . $idList . ')')
            ->where($db->quoteName('s.id') . ' != ' . $studyId);

        $db->setQuery($query);
        $studies = $db->loadObjectList('id') ?: [];

        if (empty($studies)) {
            return '';
        }

        $input      = Factory::getApplication()->getInput();
        $templateId = $input->get('t', 1, 'int');

        // Load related-studies CSS via WAM
        Factory::getApplication()->getDocument()->getWebAssetManager()
            ->useStyle('com_proclaim.related-studies');

        $html = '<div class="proclaim-related-studies">';
        $html .= '<h4>' . Text::_('JBS_CMN_RELATED_STUDIES') . '</h4>';
        $html .= '<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">';

        // Output in score order
        foreach ($topIds as $id) {
            if (!isset($studies[$id])) {
                continue;
            }

            $study = $studies[$id];
            $url   = Route::_(
                'index.php?option=com_proclaim&view=cwmsermon&id=' . (int) $study->id . '&t=' . $templateId
            );
            $title = htmlspecialchars($study->studytitle, ENT_QUOTES, 'UTF-8');

            $html .= '<div class="col">';
            $html .= '<a href="' . $url . '" class="proclaim-related-card">';
            $html .= '<div class="card">';

            // Optional thumbnail — prefer image column, fall back to deriving from thumbnailm
            $studyImg = $study->image ?? '';

            if (!empty($studyImg)) {
                $imageObj    = Cwmimages::getImagePath($studyImg);
                $pictureHtml = Cwmimages::renderPicture($imageObj, $study->studytitle, 'card-img-top');

                if ($pictureHtml !== '') {
                    $html .= $pictureHtml;
                }
            } elseif (!empty($study->thumbnailm)) {
                $imageObj    = Cwmimages::getStudyOriginal($study->thumbnailm);
                $pictureHtml = Cwmimages::renderPicture($imageObj, $study->studytitle, 'card-img-top');

                if ($pictureHtml !== '') {
                    $html .= $pictureHtml;
                }
            }

            $html .= '<div class="card-body">';
            $html .= '<h5 class="card-title">' . $title . '</h5>';
            $html .= '<p class="proclaim-related-meta">';

            if (!empty($study->teachername)) {
                $html .= '<span>' . htmlspecialchars($study->teachername, ENT_QUOTES, 'UTF-8') . '</span>';
            }

            if (!empty($study->studydate)) {
                $dateFormat = (int) $params->get('date_format', 0);
                $customDate = $params->get('custom_date_format', '');

                if (!empty($customDate)) {
                    $formattedDate = HTMLHelper::_('date', $study->studydate, $customDate, null);
                } else {
                    $formattedDate = match ($dateFormat) {
                        1       => HTMLHelper::_('date', $study->studydate, 'M J', null),
                        2       => HTMLHelper::_('date', $study->studydate, 'n/j/Y', null),
                        4       => HTMLHelper::_('date', $study->studydate, 'l, F j, Y', null),
                        5       => HTMLHelper::_('date', $study->studydate, 'F j, Y', null),
                        6       => HTMLHelper::_('date', $study->studydate, 'j F Y', null),
                        7       => date('j/n/Y', strtotime($study->studydate)),
                        8       => HTMLHelper::_('date', $study->studydate, Text::_('DATE_FORMAT_LC'), null),
                        default => HTMLHelper::_('date', $study->studydate, 'M j, Y', null),
                    };
                }

                $html .= '<span>' . $formattedDate . '</span>';
            }

            $html .= '</p>';
            $html .= '</div></div></a></div>';
        }

        $html .= '</div></div>';

        return $html;
    }
}
