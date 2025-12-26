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
        $image = Cwmimages::getSeriesThumbnail($row->series_thumbnail);
        $label = (string) $params->get('series_templatecode');

        return preg_replace_callback('/{{(teacher|teachertitle|title|description|thumbnail|url)}}/', function ($matches) use ($row, $image, $template) {
            switch ($matches[1]) {
                case 'teacher':
                    return $row->teachername;
                case 'teachertitle':
                    return $row->teachertitle;
                case 'title':
                    return $row->series_text;
                case 'description':
                    return $row->description;
                case 'thumbnail':
                    return '<img src="' . $image->path . '" width="' . $image->width . '" height="' . $image->height . '" />';
                case 'url':
                    return 'index.php?option=com_proclaim&amp;view=cwmseriesdisplay&amp;t=' . $template . '&amp;id=' . $row->id;
            }

            return $matches[0];
        }, $label);
    }

    /**
     * Get Series Details EXP
     *
     * @param   object    $row       JTable
     * @param   Registry  $params    Item Params
     * @param   object    $template  Template
     *
     * @return string
     *
     * @since    8.0
     */
    public function getSeriesDetailsExp($row, $params, $template): string
    {
        $image = Cwmimages::getSeriesThumbnail($row->series_thumbnail);
        $label = (string) $params->get('series_detailcode');

        return preg_replace_callback('/{{(teacher|teachertitle|description|title|thumbnail|plays|downloads)}}/', function ($matches) use ($row, $image) {
            switch ($matches[1]) {
                case 'teacher':
                    return $row->teachername;
                case 'teachertitle':
                    return $row->teachertitle;
                case 'description':
                    return $row->description;
                case 'title':
                    return $row->series_text;
                case 'thumbnail':
                    return '<img src="' . $image->path . '" width="' . $image->width . '" height="' . $image->height . '" />';
                case 'plays':
                    return $row->totalplays;
                case 'downloads':
                    return $row->totaldownloads;
            }

            return $matches[0];
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
        $limit   = '';

        if ($params->get('series_detail_limit')) {
            $limit = ' LIMIT ' . $params->get('series_detail_limit');
        }

        if ($nolimit === 1) {
            $limit = '';
        }

        $items   = $this->getSeriesstudiesDBO($id, $params, $limit);
        $studies = (string) $params->get('series_headercode');

        switch ($params->get('series_wrapcode')) {
            case 'T':
                // Table
                $studies .= '<table class="table" id="bsms_seriestable" width="100%">';
                break;
            case 'D':
                // DIV
                $studies .= '<div>';
                break;
        }

        // Check permissions for this view by running through the records and removing those the user doesn't have permission to see
        $user   = Factory::getApplication()->getIdentity();
        $groups = $user->getAuthorisedViewLevels();

        foreach ($items as $i => $row) {
            if (($row->access > 1) && !\in_array($row->access, $groups)) {
                unset($items[$i]);
                continue;
            }

            $studies .= $this->getListingExp($row, $params, $params->get('seriesdetailtemplateid'));
        }

        switch ($params->get('series_wrapcode')) {
            case 'T':
                // Table
                $studies .= '</table>';
                break;
            case 'D':
                // DIV
                $studies .= '</div>';
                break;
        }

        return $studies;
    }

    /**
     * Get SeriesStudies DBO
     *
     * @param   int       $id      ID
     * @param   Registry  $params  Item Params
     * @param   string    $limit   Limit of Records
     *
     * @return array
     *
     * @throws  \Exception
     * @since   8.0
     */
    public function getSeriesstudiesDBO($id, $params, $limit = null): array
    {
        $db       = Factory::getContainer()->get('DatabaseDriver');
        $user     = Factory::getApplication()->getIdentity();
        $language = $db->quote(Factory::getApplication()->getLanguage()->getTag()) . ',' . $db->quote('*');
        $setLimit = 0;

        if ($limit) {
            preg_match('!\d+!', $limit, $matches);
            $setLimit = (int) ($matches[0] ?? 0);
        }

        // Compute view access permissions.
        $groups = implode(',', $user->getAuthorisedViewLevels());
        $query  = $db->getQuery(true);
        $query->select(
            's.*, se.id AS seid, t.id AS tid, t.teachername, t.title AS teachertitle, t.thumb, t.thumbh, t.thumbw, '
            . ' t.teacher_thumbnail, se.series_text, se.description AS sdescription, '
            . ' se.series_thumbnail, #__bsms_message_type.id AS mid,'
            . ' #__bsms_message_type.message_type AS message_type, #__bsms_books.bookname,'
            . ' group_concat(#__bsms_topics.id separator ", ") AS tp_id, group_concat(#__bsms_topics.topic_text separator ", ")'
            . ' as topic_text, group_concat(#__bsms_topics.params separator ", ") as topic_params, '
            . ' #__bsms_locations.id AS lid, #__bsms_locations.location_text '
        )
            ->from('#__bsms_studies AS s')
            ->leftJoin('#__bsms_series AS se ON (s.series_id = se.id)')
            ->leftJoin('#__bsms_teachers AS t ON (s.teacher_id = t.id)')
            ->leftJoin('#__bsms_books ON (s.booknumber = #__bsms_books.booknumber)')
            ->leftJoin('#__bsms_message_type ON (s.messagetype = #__bsms_message_type.id)')
            ->leftJoin('#__bsms_studytopics ON (#__bsms_studytopics.study_id = s.id)')
            ->leftJoin('#__bsms_topics ON (#__bsms_topics.id = #__bsms_studytopics.topic_id)')
            ->leftJoin('#__bsms_locations ON (s.location_id = #__bsms_locations.id)')
            ->where('s.series_id = ' . (int) $id)
            ->where('s.published = ' . 1)
            ->where('s.language in (' . $language . ')')
            ->where('s.access IN (' . $groups . ')')
            ->group('s.id')
            ->order(
                $params->get('series_detail_sort', 'studydate') . ' ' . $params->get('series_detail_order', 'desc')
            );

        $db->setQuery($query, 0, $setLimit);
        $items = $db->loadObjectList() ?: [];

        foreach ($items as $item) {
            // Concat topic_text and concat topic_params do not fit, so translate individually
            $item->topics_text = Cwmtranslated::getConcatTopicItemTranslated($item);
        }

        return $items;
    }
}
