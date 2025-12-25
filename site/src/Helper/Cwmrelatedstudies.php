<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

/**
 * helper to get related studies to the current one
 *
 * @package  Proclaim.Site
 * @since    7.1.0
 */
class Cwmrelatedstudies
{
    /**
     * Remove array declaration for php 7.3.x
     *
     * @var  array Score
     *
     * @since    7.2
     */
    public array $score;

    /**
     * Get Related
     *
     * @param   object    $row     Study data
     * @param   Registry  $params  Item Params
     *
     * @return string|bool
     *
     * @throws \Exception
     * @since    7.2
     */
    public function getRelated(object $row, Registry $params): string|bool
    {
        $this->score = [];
        $keywords    = (string) $params->get('metakey');
        $topics      = $row->tp_id ?? '';
        $topicsList  = $this->getTopics();

        if (empty($keywords) && empty($topics) && empty($row->studyintro)) {
            return false;
        }

        $studies = $this->getStudies();

        if (empty($studies)) {
            return false;
        }

        foreach ($studies as $study) {
            // Don't compare with itself
            if ((int) $study->id === (int) $row->id) {
                continue;
            }

            $compare = '';

            if (!empty($study->params) && $study->params !== '{}') {
                $sparams = new Registry($study->params);
                $compare = (string) $sparams->get('metakey');
            }

            if (!empty($keywords) && !empty($compare)) {
                $this->parseKeys($keywords, $compare, (int) $study->id);
            }

            if (!empty($topicsList) && !empty($study->tp_id)) {
                $this->parseKeys($topicsList, (string) $study->tp_id, (int) $study->id);
            }
        }

        if (empty($this->score)) {
            return false;
        }

        return $this->getRelatedLinks((int) $row->id);
    }

    /**
     * Get Topics for rendering.
     *
     * @return string
     *
     * @since    7.2
     */
    public function getTopics(): string
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select($db->quoteName('id'))
            ->from($db->quoteName('#__bsms_topics'))
            ->where($db->quoteName('published') . ' = 1');

        $db->setQuery($query);

        return implode(',', $db->loadColumn() ?: []);
    }

    /**
     * Get Studies
     *
     * @return array
     *
     * @throws \Exception
     * @since    7.2
     */
    public function getStudies(): array
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $user  = Factory::getApplication()->getIdentity();
        $groups = $user->getAuthorisedViewLevels();

        $query = $db->getQuery(true);
        $query->select($db->quoteName(['s.id', 's.params', 's.access']))
            ->from($db->quoteName('#__bsms_studies', 's'))
            ->select('GROUP_CONCAT(stp.id SEPARATOR ", ") AS tp_id')
            ->leftJoin($db->quoteName('#__bsms_studytopics', 'tp') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('tp.study_id'))
            ->leftJoin($db->quoteName('#__bsms_topics', 'stp') . ' ON ' . $db->quoteName('stp.id') . ' = ' . $db->quoteName('tp.topic_id'))
            ->where($db->quoteName('s.published') . ' = 1')
            ->where($db->quoteName('s.access') . ' IN (' . implode(',', $groups) . ')')
            ->group($db->quoteName('s.id'));

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Parse keys
     *
     * @param   string  $source   String of source
     * @param   string  $compare  String to compare
     * @param   int     $id       ID of study
     *
     * @return void
     *
     * @since    7.2
     */
    public function parseKeys(string $source, string $compare, int $id): void
    {
        $sourceArray  = array_map('trim', explode(',', $source));
        $compareArray = array_map('trim', explode(',', $compare));

        foreach ($sourceArray as $sItem) {
            if ($sItem !== '' && in_array($sItem, $compareArray, true)) {
                $this->score[] = $id;

                return;
            }
        }
    }

    /**
     * Look for Related Links.
     *
     * @param   int  $id  Id to link to
     *
     * @return string
     *
     * @throws \Exception
     * @since    7.2
     */
    public function getRelatedLinks(int $id): string
    {
        $db   = Factory::getContainer()->get('DatabaseDriver');
        $this->score = array_unique($this->score);
        $link = implode(', ', $this->score);

        $query = $db->getQuery(true);
        $query->select($db->quoteName(['s.studytitle', 's.alias', 's.id', 's.booknumber', 's.chapter_begin']))
            ->from($db->quoteName('#__bsms_studies', 's'))
            ->select($db->quoteName('b.bookname'))
            ->leftJoin($db->quoteName('#__bsms_books', 'b') . ' ON ' . $db->quoteName('b.booknumber') . ' = ' . $db->quoteName('s.booknumber'))
            ->where($db->quoteName('s.id') . ' IN (' . $link . ')')
            ->where($db->quoteName('s.id') . ' != ' . (int) $id);

        $db->setQuery($query);
        $studyrecords = $db->loadObjectList() ?: [];

        if (empty($studyrecords)) {
            return '';
        }

        $related = '<select onchange="window.location.href=this.value" id="urlList" class="form-select chzn-color-state valid form-control-success"><option value="">' .
            Text::_('JBS_CMN_SELECT_RELATED_STUDY') . '</option>';
        $input   = Factory::getApplication()->getInput();
        $templateId = $input->get('t', 1, 'int');

        foreach ($studyrecords as $studyrecord) {
            $url = Route::_('index.php?option=com_proclaim&view=cwmsermon&id=' . (int) $studyrecord->id . '&t=' . $templateId);
            $title = $studyrecord->studytitle;

            if (!empty($studyrecord->bookname)) {
                $title .= ' - ' . Text::_($studyrecord->bookname) . ' ' . $studyrecord->chapter_begin;
            }

            $related .= '<option value="' . $url . '">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</option>';
        }

        $related .= '</select>';

        return '<div class="related col-lg-4"><form action="#">' . $related . '</form></div>';
    }
}
