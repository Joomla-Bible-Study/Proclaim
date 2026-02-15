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

use CWM\Component\Proclaim\Administrator\Helper\Cwmtranslated;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

/**
 *  Class for Series List
 *
 * @package  Proclaim.Site
 * @since    8.0.0
 *
 *
 */
class Cwmserieslist extends Cwmlisting
{
    /**
     * Get Series ElementNumber
     *
     * @param   string|null  $subcustom  Subcustom element name
     *
     * @return int
     *
     * @since    8.0
     */
    public function getseriesElementnumber(?string $subcustom): int
    {
        $lookup = [
            'title'           => 1,
            'thumbnail'       => 2,
            'thumbnail-title' => 3,
            'teacher'         => 4,
            'teacherimage'    => 5,
            'teacher-title'   => 6,
            'description'     => 7,
        ];

        return $lookup[$subcustom] ?? 0;
    }

    /**
     * Get Series-list Exp
     *
     * @param   object    $row       JTable
     * @param   Registry  $params    Item Params
     * @param   object    $template  Template
     *
     * @return string
     *
     * @since    8.0
     */
    public function getSerieslistExp($row, $params, $template): string
    {
        $label = (string) $params->get('series_templatecode');
        $extra = [
            'url' => 'index.php?option=com_proclaim&amp;view=cwmseriesdisplay&amp;t=' . $template . '&amp;id=' . $row->id,
        ];

        return $this->replaceSeriesPlaceholders($row, $label, $extra);
    }

    /**
     * Get Series Details EXP
     *
     * @param   object    $row     JTable
     * @param   Registry  $params  Item Params
     *
     * @return string
     *
     * @since    8.0
     */
    public function getSeriesDetailsExp($row, $params): string
    {
        $label = (string) $params->get('series_detailcode');
        $extra = [
            'plays'     => $row->totalplays ?? '',
            'downloads' => $row->totaldownloads ?? '',
        ];

        return $this->replaceSeriesPlaceholders($row, $label, $extra);
    }

    /**
     * Replace common series placeholders in a template string
     *
     * @param   object  $row    Series row data
     * @param   string  $label  Template string with placeholders
     * @param   array   $extra  Additional replacements specific to the caller
     *
     * @return string
     *
     * @since   10.0.0
     */
    private function replaceSeriesPlaceholders(object $row, string $label, array $extra = []): string
    {
        $image = Cwmimages::getSeriesThumbnail($row->series_thumbnail);

        $replacements = [
            'teacher'      => $row->teachername ?? '',
            'teachertitle' => $row->teachertitle ?? '',
            'title'        => $row->series_text ?? '',
            'description'  => $row->description ?? '',
            'thumbnail'    => Cwmimages::renderPicture($image, $row->series_text ?? ''),
        ];

        $replacements = array_merge($replacements, $extra);

        return preg_replace_callback('/{{(\w+)}}/', function ($matches) use ($replacements) {
            return $replacements[$matches[1]] ?? $matches[0];
        }, $label);
    }

    /**
     * Get Series Studies Exp
     *
     * @param   int       $id        ID
     * @param   Registry  $params    Item Params
     * @param   object    $template  Template
     *
     * @return string
     *
     * @throws  \Exception
     * @since   8.0
     */
    public function getSeriesstudiesExp($id, $params, $template): string
    {
        $input   = Factory::getApplication()->getInput();
        $nolimit = $input->get('nolimit', 0, 'int');

        $limit = ($nolimit !== 1) ? (int) $params->get('series_detail_limit', 0) : 0;

        $items   = $this->getSeriesstudiesDBO($id, $params, $limit);
        $studies = (string) $params->get('series_headercode');

        $wrappers = [
            'T' => ['<table class="table" id="bsms_seriestable" width="100%">', '</table>'],
            'D' => ['<div>', '</div>'],
        ];

        $wrapCode  = $params->get('series_wrapcode');
        $wrapOpen  = $wrappers[$wrapCode][0] ?? '';
        $wrapClose = $wrappers[$wrapCode][1] ?? '';

        $studies .= $wrapOpen;

        foreach ($items as $row) {
            $studies .= $this->getListingExp($row, $params, $params->get('seriesdetailtemplateid'));
        }

        $studies .= $wrapClose;

        return $studies;
    }

    /**
     * Get SeriesStudies DBO
     *
     * @param   int       $id      ID
     * @param   Registry  $params  Item Params
     * @param   int       $limit   Limit of Records (0 = no limit)
     *
     * @return array
     *
     * @throws  \Exception
     * @since   8.0
     */
    public function getSeriesstudiesDBO(int $id, Registry $params, int $limit = 0): array
    {
        $db       = Factory::getContainer()->get('DatabaseDriver');
        $user     = Factory::getApplication()->getIdentity();
        $language = $db->quote(Factory::getApplication()->getLanguage()->getTag()) . ',' . $db->quote('*');

        // Compute view access permissions.
        $groups = implode(',', $user->getAuthorisedViewLevels());
        $query  = $db->getQuery(true);
        $query->select(
            $db->quoteName('s') . '.*, '
            . $db->quoteName('se.id', 'seid') . ', '
            . $db->quoteName('t.id', 'tid') . ', ' . $db->quoteName('t.teachername') . ', '
            . $db->quoteName('t.title', 'teachertitle') . ', '
            . $db->quoteName('t.teacher_thumbnail') . ', ' . $db->quoteName('se.series_text') . ', '
            . $db->quoteName('se.description', 'sdescription') . ', '
            . $db->quoteName('se.series_thumbnail') . ', '
            . $db->quoteName('#__bsms_message_type.id', 'mid') . ', '
            . $db->quoteName('#__bsms_message_type.message_type', 'message_type') . ', '
            . $db->quoteName('#__bsms_books.bookname') . ', '
            . 'GROUP_CONCAT(' . $db->quoteName('#__bsms_topics.id') . ' SEPARATOR ", ") AS ' . $db->quoteName('tp_id') . ', '
            . 'GROUP_CONCAT(' . $db->quoteName('#__bsms_topics.topic_text') . ' SEPARATOR ", ") AS ' . $db->quoteName('topic_text') . ', '
            . 'GROUP_CONCAT(' . $db->quoteName('#__bsms_topics.params') . ' SEPARATOR ", ") AS ' . $db->quoteName('topic_params') . ', '
            . $db->quoteName('#__bsms_locations.id', 'lid') . ', '
            . $db->quoteName('#__bsms_locations.location_text')
        )
            ->from($db->quoteName('#__bsms_studies', 's'))
            ->leftJoin(
                $db->quoteName('#__bsms_series', 'se') . ' ON ('
                . $db->quoteName('s.series_id') . ' = ' . $db->quoteName('se.id') . ')'
            )
            ->leftJoin(
                $db->quoteName('#__bsms_study_teachers', 'st') . ' ON ('
                . $db->quoteName('st.study_id') . ' = ' . $db->quoteName('s.id')
                . ' AND ' . $db->quoteName('st.ordering') . ' = 0)'
            )
            ->leftJoin(
                $db->quoteName('#__bsms_teachers', 't') . ' ON ('
                . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.teacher_id') . ')'
            )
            ->leftJoin(
                $db->quoteName('#__bsms_books') . ' ON ('
                . $db->quoteName('s.booknumber') . ' = ' . $db->quoteName('#__bsms_books.booknumber') . ')'
            )
            ->leftJoin(
                $db->quoteName('#__bsms_message_type') . ' ON ('
                . $db->quoteName('s.messagetype') . ' = ' . $db->quoteName('#__bsms_message_type.id') . ')'
            )
            ->leftJoin(
                $db->quoteName('#__bsms_studytopics') . ' ON ('
                . $db->quoteName('#__bsms_studytopics.study_id') . ' = ' . $db->quoteName('s.id') . ')'
            )
            ->leftJoin(
                $db->quoteName('#__bsms_topics') . ' ON ('
                . $db->quoteName('#__bsms_topics.id') . ' = ' . $db->quoteName('#__bsms_studytopics.topic_id') . ')'
            )
            ->leftJoin(
                $db->quoteName('#__bsms_locations') . ' ON ('
                . $db->quoteName('s.location_id') . ' = ' . $db->quoteName('#__bsms_locations.id') . ')'
            )
            ->where($db->quoteName('s.series_id') . ' = ' . (int) $id)
            ->where($db->quoteName('s.published') . ' = 1')
            ->where($db->quoteName('s.language') . ' IN (' . $language . ')')
            ->where($db->quoteName('s.access') . ' IN (' . $groups . ')')
            ->group($db->quoteName('s.id'))
            ->order(
                $db->quoteName($params->get('series_detail_sort', 'studydate')) . ' ' . $params->get('series_detail_order', 'DESC')
            );

        $db->setQuery($query, 0, $limit);
        $items = $db->loadObjectList() ?: [];

        foreach ($items as $item) {
            // Concat topic_text and concat topic_params do not fit, so translate individually
            $item->topics_text = Cwmtranslated::getConcatTopicItemTranslated($item);
        }

        return $items;
    }
}
