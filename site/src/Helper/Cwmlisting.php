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

use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmscriptureHelper;
use CWM\Component\Proclaim\Administrator\Helper\ScriptureReference;
use CWM\Component\Proclaim\Administrator\Table\CwmtemplateTable;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Image\Image;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/**
 * Proclaim listing class
 *
 * @since  7.0.0
 */
class Cwmlisting
{
    /** @var  Registry
     * @since 7.0
     */
    public Registry $params;

    /**
     * Get Fluid Listing
     *
     * @param   mixed      $items     Items
     * @param   Registry   $params    Page Params
     * @param   \stdClass  $template  Template name
     * @param   String     $type      Type of Listing
     *
     * @return string
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getFluidListing($items, Registry $params, \stdClass $template, string $type): string
    {
        $list         = null;
        $row          = [];
        $this->params = $params;
        $item         = '';

        if (!\is_array($items)) {
            $subarray    = [];
            $subarray[0] = $items;
            $items       = $subarray;
        }

        if ($type === 'sermons') {
            foreach ($items as $item) {
                if (isset($item->mids)) {
                    $medias[] = $this->getFluidMediaids($item);
                }
            }
        }

        if ($type === 'sermon' && !empty($items) && isset($items[0]) && \is_object($items[0])) {
            $medias = $this->getFluidMediaids($items[0]);
            $item   = $items[0];
        }

        // Get the media files in one query
        if (isset($medias)) {
            $mediafiles = $this->getMediaFiles($medias);
        }

        // Create an array from each param variable set
        // Find out what view we are in
        $extra = '';

        switch ($type) {
            case 'sermons':
                $extra = '';
                break;
            case 'sermon':
                $extra = 'd';
                break;
            case 'seriesdisplays':
                $extra = 's';
                break;
            case 'seriesdisplay':
                $extra = 'sd';
                break;
            case 'teachers':
                $extra = 'ts';
                break;
            case 'teacher':
                $extra = 'td';
                break;
            case 'module':
                $extra = 'm';
                break;
        }

        $listparams = [];

        // Standard params that check {name}row > 0
        $standardParams = [
            'scripture1', 'scripture2', 'scriptures', 'secondary', 'title', 'date', 'teacher', 'teacher-title',
            'duration', 'studyintro', 'series', 'description', 'seriesthumbnail', 'submitted',
            'hits', 'downloads', 'studynumber', 'topic', 'locations', 'jbsmedia', 'messagetype',
            'thumbnail', 'teacheremail', 'teacherweb', 'teacherphone', 'teacherfb', 'teachertw',
            'teacherblog', 'teachershort', 'teacherlong', 'teacheraddress', 'teacherlink1',
            'teacherlink2', 'teacherlink3', 'teacherlargeimage', 'teacherallinone',
        ];

        foreach ($standardParams as $paramName) {
            if ($params->get($extra . $paramName . 'row') > 0) {
                $listparams[] = $this->getListParamsArray($extra . $paramName);
            }
        }

        // Special case: teacherimage checks both teacherimagerow and teacherimagerrow
        if ($params->get($extra . 'teacherimagerow') > 0 || $params->get($extra . 'teacherimagerrow') > 0) {
            $listparams[] = $this->getListParamsArray($extra . 'teacherimage');
        }

        // Special case: seriesdescription maps to description
        if ($params->get($extra . 'seriesdescriptionrow') > 0) {
            $listparams[] = $this->getListParamsArray($extra . 'description');
        }

        // Special case: custom doesn't use > 0 comparison
        if ($params->get($extra . 'customrow')) {
            $listparams[] = $this->getListParamsArray($extra . 'custom');
        }

        $row1       = [];
        $row2       = [];
        $row3       = [];
        $row4       = [];
        $row5       = [];
        $row6       = [];
        $row1sorted = [];
        $row2sorted = [];
        $row3sorted = [];
        $row4sorted = [];
        $row5sorted = [];
        $row6sorted = [];

        // Create an array sorted by row and then by column
        foreach ($listparams as $listing) {
            if ($listing->row === '1') {
                $row1[] = $listing;
            }

            if ($listing->row === '2') {
                $row2[] = $listing;
            }

            if ($listing->row === '3') {
                $row3[] = $listing;
            }

            if ($listing->row === '4') {
                $row4[] = $listing;
            }

            if ($listing->row === '5') {
                $row5[] = $listing;
            }

            if ($listing->row === '6') {
                $row6[] = $listing;
            }
        }

        if (\count($row1)) {
            $row1sorted = $this->sortArrayofObjectByProperty($row1, 'col', $order = "ASC");
        }

        if (\count($row2)) {
            $row2sorted = $this->sortArrayofObjectByProperty($row2, 'col', $order = "ASC");
        }

        if (\count($row3)) {
            $row3sorted = $this->sortArrayofObjectByProperty($row3, 'col', $order = "ASC");
        }

        if (\count($row4)) {
            $row4sorted = $this->sortArrayofObjectByProperty($row4, 'col', $order = "ASC");
        }

        if (\count($row5)) {
            $row5sorted = $this->sortArrayofObjectByProperty($row5, 'col', $order = "ASC");
        }

        if (\count($row6)) {
            $row6sorted = $this->sortArrayofObjectByProperty($row6, 'col', $order = "ASC");
        }

        $listrows    = array_merge($row1sorted, $row2sorted, $row3sorted, $row4sorted, $row5sorted, $row6sorted);
        $listsorts   = [];
        $listsorts[] = $row1sorted;
        $listsorts[] = $row2sorted;
        $listsorts[] = $row3sorted;
        $listsorts[] = $row4sorted;
        $listsorts[] = $row5sorted;
        $listsorts[] = $row6sorted;



        // Start the table for the entire list
        $list .= '<div class="table-responsive" about="' . $type . '"><table class="table w-100 table-borderless">';

        // Check if we have a valid first item for header rows
        $hasValidFirstItem = !empty($items) && isset($items[0]) && \is_object($items[0]);

        if (($type === 'sermons') && $params->get('use_headers_list') > 0 && $hasValidFirstItem) {
            // Start the header
            $list .= '<thead class="' . $params->get('listheadertype') . '">';
            $list .= $this->getFluidRow($listrows, $listsorts, $items[0], $params, $template, $header = 1, $type);
            $list .= '</thead>';
        }

        if (($type === 'sermon') && $params->get('use_headers_view') > 0 && $hasValidFirstItem) {
            // Start the header
            $list .= '<thead class="' . $params->get('listheadertype') . '">';
            $list .= $this->getFluidRow($listrows, $listsorts, $items[0], $params, $template, $header = 1, $type);
            $list .= '</thead>';
        }

        if (($type === 'seriesdisplays') && $params->get('use_headers_series') == 1 && $hasValidFirstItem) {
            // Start the header
            $list .= '<thead class="' . $params->get('listheadertype') . '">';
            $list .= $this->getFluidRow(
                $listrows,
                $listsorts,
                $items[0],
                $params,
                $template,
                $header = 1,
                $type
            );
            $list .= '</thead>';
        }

        if ($type === 'seriesdisplay' && $hasValidFirstItem) {
            if ($params->get('use_header_seriesdisplay') > 0) {
                // Start the header
                $list .= '<thead class="' . $params->get('listheadertype') . '">';
                $list .= $this->getFluidRow(
                    $listrows,
                    $listsorts,
                    $items[0],
                    $params,
                    $template,
                    $header = 1,
                    $type
                );
                $list .= '</thead>';
            }

            $list .= $this->getFluidRow(
                $listrows,
                $listsorts,
                $items[0],
                $params,
                $template,
                $header = 0,
                $type
            );
        }

        if ($type === 'teacher' && $hasValidFirstItem) {
            if ($params->get('use_headers_teacher_details') > 0) {
                // Start the header
                $list .= '<thead class="' . $params->get('listheadertype') . '">';
                $list .= $this->getFluidRow(
                    $listrows,
                    $listsorts,
                    $items[0],
                    $params,
                    $template,
                    $header = 1,
                    $type
                );
                $list .= '</thead>';
            }

            $list .= $this->getFluidRow(
                $listrows,
                $listsorts,
                $items[0],
                $params,
                $template,
                $header = 0,
                $type
            );
        }

        if (($type === 'teachers') && $params->get('use_headers_teacher_list') > 0 && $hasValidFirstItem) {
            // Start the header
            $list .= '<thead class="' . $params->get('listheadertype') . '">';
            $list .= $this->getFluidRow(
                $listrows,
                $listsorts,
                $items[0],
                $params,
                $template,
                $header = 1,
                $type
            );
            $list .= '</thead>';
        }

        // Go through and attach the media files as an array to their study
        if (($type === 'sermons') && \is_array($items)) {
            foreach ($items as $item) {
                // Skip invalid items
                if (!\is_object($item)) {
                    continue;
                }

                $studymedia = [];

                if (isset($mediafiles)) {
                    foreach ($mediafiles as $mediafile) {
                        if ($mediafile->study_id === $item->id) {
                            $studymedia[] = $mediafile;
                        }
                    }
                }

                $item->mediafiles = $studymedia;

                $row[] = $this->getFluidRow(
                    $listrows,
                    $listsorts,
                    $item,
                    $params,
                    $template,
                    $header = 0,
                    $type
                );
                $row[] = '</td></tr><tr style="border-bottom: 1px solid darkgrey; padding-bottom: 5px;"</tr>';
            }
        }

        if ($type === 'sermon') {
            $studymedia = [];

            if (isset($mediafiles)) {
                foreach ($mediafiles as $mediafile) {
                    if ((int)$mediafile->study_id === (int)$item->id) {
                        $studymedia[] = $mediafile;
                    }
                }
            }

            if (isset($studymedia)) {
                $item->mediafiles = $studymedia;
            }

            $row[] = $this->getFluidRow($listrows, $listsorts, $item, $params, $template, $header = 0, $type);
        }

        if ($type === 'seriesdisplays') {
            foreach ($items as $item) {
                $row[] = $this->getFluidRow(
                    $listrows,
                    $listsorts,
                    $item,
                    $params,
                    $template,
                    $header = 0,
                    $type
                );
                $row[] = '</td></tr><tr style="border-bottom: 1px solid darkgrey; padding-bottom: 5px;"</tr>';
            }
        }

        if ($type === 'teachers') {
            foreach ($items as $item) {
                $row[] = $this->getFluidRow(
                    $listrows,
                    $listsorts,
                    $item,
                    $params,
                    $template,
                    $header = 0,
                    $type
                );
                $row[] = '</td></tr><tr style="border-bottom: 1px solid darkgrey; padding-bottom: 5px;"</tr>';
            }
        }

        foreach ($row as $value) {
            $list .= $value;
        }

        $list .= '</tbody></table></div>';

        return $list;
    }

    /**
     * Get Fluid Media Id's
     *
     * @param   Object  $item  Items info
     *
     * @return array
     *
     * @since 7.0
     */
    public function getFluidMediaids($item): array
    {
        $medias    = [];
        $mediatemp = explode(',', $item->mids ?? '');

        foreach ($mediatemp as $mtemp) {
            $medias[] = $mtemp;
        }

        return $medias;
    }

    /**
     * Get Media Files
     *
     * @param   array  $medias  Media files
     *
     * @return mixed
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getMediaFiles(array $medias): array
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select(
            $db->quoteName('#__bsms_mediafiles') . '.*, '
            . $db->quoteName('#__bsms_servers.id', 'ssid') . ', '
            . $db->quoteName('#__bsms_servers.params', 'sparams') . ', '
            . $db->quoteName('#__bsms_servers.media', 'smedia') . ','
            . $db->quoteName('s.studytitle') . ', ' . $db->quoteName('s.studydate') . ', '
            . $db->quoteName('s.studyintro') . ', ' . $db->quoteName('s.teacher_id') . ','
            . $db->quoteName('s.booknumber') . ', ' . $db->quoteName('s.chapter_begin') . ', '
            . $db->quoteName('s.chapter_end') . ', ' . $db->quoteName('s.verse_begin') . ', '
            . $db->quoteName('s.verse_end') . ', ' . $db->quoteName('t.teachername') . ', '
            . $db->quoteName('t.id', 'tid') . ', ' . $db->quoteName('s.id', 'sid')
        );
        $query->from($db->quoteName('#__bsms_mediafiles'));
        $query->leftJoin(
            $db->quoteName('#__bsms_servers') . ' ON ('
            . $db->quoteName('#__bsms_servers.id') . ' = ' . $db->quoteName('#__bsms_mediafiles.server_id') . ')'
        );
        $query->leftJoin(
            $db->quoteName('#__bsms_studies', 's') . ' ON ('
            . $db->quoteName('s.id') . ' = ' . $db->quoteName('#__bsms_mediafiles.study_id') . ')'
        );
        $query->leftJoin(
            $db->quoteName('#__bsms_teachers', 't') . ' ON ('
            . $db->quoteName('t.id') . ' = ' . $db->quoteName('s.teacher_id') . ')'
        );

        $ids = [];

        foreach ($medias as $media) {
            if (\is_array($media)) {
                foreach ($media as $m) {
                    $ids[] = (int) $m;
                }
            } else {
                $ids[] = (int) $media;
            }
        }

        if (empty($ids)) {
            return [];
        }

        $query->where($db->quoteName('#__bsms_mediafiles.id') . ' IN (' . implode(',', array_unique($ids)) . ')');

        // Include archived media when showing archived messages
        $showArchived = '0';
        if (isset($this->params)) {
            $showArchived = $this->params->get('show_archived', '');
            if ($showArchived === '' || $showArchived === null) {
                $showArchived = $this->params->get('default_show_archived', '0');
            }
        }
        if ($showArchived === '1' || $showArchived === '2') {
            $query->where($db->quoteName('#__bsms_mediafiles.published') . ' IN (1, 2)');
        } else {
            $query->where($db->quoteName('#__bsms_mediafiles.published') . ' = 1');
        }

        $query->where(
            $db->quoteName('#__bsms_mediafiles.language') . ' IN ('
            . $db->quote(Factory::getApplication()->getLanguage()->getTag()) . ',' . $db->quote('*') . ')'
        );
        $query->order($db->quoteName('ordering') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList();
    }

    /**
     * Get list Params Array
     *
     * @param   string  $paramtext  Param Text
     *
     * @return \stdClass
     *
     * @since 7.0
     */
    public function getListParamsArray(string $paramtext): \stdClass
    {
        $l = new \stdClass();

        if ($paramtext === 'tdteacherimage') {
            if ($this->params->get($paramtext . 'rrow')) {
                $l->row = $this->params->get($paramtext . 'rrow');
            } else {
                $l->row = $this->params->get($paramtext . 'row');
            }
        } else {
            $l->row = $this->params->get($paramtext . 'row');
        }

        $l->col          = $this->params->get($paramtext . 'col');
        $l->colspan      = $this->params->get($paramtext . 'colspan');
        $l->element      = $this->params->get($paramtext . 'element');
        $l->custom       = $this->params->get($paramtext . 'custom');
        $l->linktype     = $this->params->get($paramtext . 'linktype');
        $l->name         = $paramtext;
        $l->customtext   = (string) $this->params->get($paramtext . 'text', '');
        $l->date_format  = $this->params->get($paramtext . 'date_format', '');
        $l->show_verses  = $this->params->get($paramtext . 'show_verses', '');

        return $l;
    }

    /**
     * Sort Array of Object by Property
     *
     * @param   array   $array     ?
     * @param   string  $property  ?
     * @param   string  $order     ?
     *
     * @return array
     *
     * @since 7.0
     */
    public function sortArrayofObjectByProperty(array $array, string $property, string $order = "ASC"): array
    {
        $cur           = 1;
        $stack[1]['l'] = 0;
        $stack[1]['r'] = \count($array) - 1;

        do {
            $l = $stack[$cur]['l'];
            $r = $stack[$cur]['r'];
            $cur--;

            do {
                $i   = $l;
                $j   = $r;
                $tmp = $array[(int)(($l + $r) / 2)];

                /*
                 Split the array in to parts
                // first: objects with "smaller" property $property
                // second: objects with "bigger" property $property
                */
                do {
                    while ($array[$i]->{$property} < $tmp->{$property}) {
                        $i++;
                    }

                    while ($tmp->{$property} < $array[$j]->{$property}) {
                        $j--;
                    }

                    // Swap elements of two parts if necessary
                    if ($i <= $j) {
                        $w         = $array[$i];
                        $array[$i] = $array[$j];
                        $array[$j] = $w;

                        $i++;
                        $j--;
                    }
                } while ($i <= $j);

                if ($i < $r) {
                    $cur++;
                    $stack[$cur]['l'] = $i;
                    $stack[$cur]['r'] = $r;
                }

                $r = $j;
            } while ($l < $r);
        } while ($cur !== 0);

        // Added ordering.
        if ($order === "DESC") {
            $array = array_reverse($array);
        }

        return $array;
    }

    /**
     * Get Fluid Row
     *
     * @param   array      $listrows   ?
     * @param   array      $listsorts  ?
     * @param   Object     $item       ?
     * @param   Registry   $params     Item Params
     * @param   \stdClass  $template  Template info
     * @param   integer    $header     ?
     * @param   string     $type       ?
     *
     * @return string
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getFluidRow(
        array $listrows,
        array $listsorts,
        object $item,
        Registry $params,
        $template,
        int $header,
        string $type
    ): string {
        $span        = '';
        $headerstyle = '';

        if ($header === 1) {
            $headerstyle = "style=\"display: none;\"";
        }

        $extra = '';

        switch ($type) {
            case 'sermon':
                $extra = 'd';
                break;
            case 'seriesdisplays':
                $extra = 's';
                break;
            case 'seriesdisplay':
                $extra = 'sd';
                break;
            case 'sermons':
                break;
            case 'teachers':
                $extra = 'ts';
                break;
            case 'teacher':
                $extra = 'td';
                break;
        }

        $pull        = $params->get($extra . 'rowspanitempull');
        $rowspanitem = $params->get($extra . 'rowspanitem', 0);

        switch ($rowspanitem) {
            // Teacher Thumbnail
            case 1:
                if (!empty($item->thumb)) {
                    $span = $this->useJImage(
                        $item->thumb,
                        $item->teachername,
                        '',
                        '',
                        '',
                        $params->get('rowspanitemimage')
                    );
                } else {
                    $span = null;
                }

                if (!empty($item->teacher_thumbnail) && $span === null) {
                    $span = $this->useJImage(
                        $item->teacher_thumbnail,
                        $item->teachername,
                        '',
                        '',
                        '',
                        $params->get('rowspanitemimage')
                    );
                }
                break;

                // Study Thumbnail
            case 2:
                if (isset($item->thumbnailm)) {
                    $span = $this->useJImage(
                        $item->thumbnailm,
                        Text::_('JBS_CMN_THUMBNAIL'),
                        '',
                        '',
                        '',
                        $params->get('rowspanitemimage')
                    );
                }

                if ($params->get('studyimage', '-1') !== '-1') {
                    $span = $this->useJImage(
                        JPATH_SITE . '/media/com_proclaim/images/stockimages/' . $params->get('studyimage'),
                        Text::_('JBS_CMN_THUMBNAIL'),
                        '',
                        '',
                        '',
                        $params->get('rowspanitemimage')
                    );
                }

                break;

                // Series Thumbnail
            case 3:
                if (!empty($item->series_thumbnail)) {
                    $span = $this->useJImage(
                        $item->series_thumbnail,
                        Text::_('JBS_CMN_SERIES'),
                        '',
                        '',
                        '',
                        $params->get('rowspanitemimage')
                    );
                }
                break;

                // Teacher Large image
            case 4:
                if (!empty($item->teacher_image)) {
                    $span = $this->useJImage(
                        $item->teacher_image,
                        $item->teachername,
                        '',
                        '',
                        '',
                        $params->get('rowspanitemimage')
                    );
                }
                break;
        }

        $rowspanitemspan = $params->get($extra . 'rowspanitemspan');
        $rowspanbalance  = 12 - (int)$rowspanitemspan;

        $frow = '';

        $row1count  = 0;
        $row2count  = 0;
        $row3count  = 0;
        $row4count  = 0;
        $row5count  = 0;
        $row6count  = 0;
        $row1count2 = 0;
        $row2count2 = 0;
        $row3count2 = 0;
        $row4count2 = 0;
        $row5count2 = 0;
        $row6count2 = 0;

        if ($span) {
            $frow .= '<div class="row" about="' . $type . '">';
            $frow .= '<div class="col-' . $rowspanitemspan . ' ' . $pull
                . '" id="jbsmspan-image"><div ' . $headerstyle . '>' . $span . '</div></div>';
            $frow .= '<div class="col-' . $rowspanbalance . '" about="' . $type . '">';
        }

        foreach ($listsorts as $sort) {
            if (\count($sort)) {
                foreach ($sort as $s) {
                    if ($s->row === '1') {
                        $row1count++;
                        $row1count2++;
                    }

                    if ($s->row === '2') {
                        $row2count++;
                        $row2count2++;
                    }

                    if ($s->row === '3') {
                        $row3count++;
                        $row3count2++;
                    }

                    if ($s->row === '4') {
                        $row4count++;
                        $row4count2++;
                    }

                    if ($s->row === '5') {
                        $row5count++;
                        $row5count2++;
                    }

                    if ($s->row === '6') {
                        $row6count++;
                        $row6count2++;
                    }
                }
            }
        }

        $thadd = '';

        foreach ($listrows as $row) {
            if ($row->row === '1') {
                if ($row1count === $row1count2 && $header === 0) {
                    $rowClass = '';
                    if (isset($item->published) && (int) $item->published === 2) {
                        $rowClass = ' class="proclaim-archived"';
                    }
                    $frow .= '<tr scope="row"' . $rowClass . '>';
                }

                if ($header === 1) {
                    if ($row->colspan > 0) {
                        $thadd = 'colspan="' . $row->colspan . '"';
                    } else {
                        $thadd = '';
                    }
                    $frow .= '<th scope="col" ' . $thadd . '>' . $this->getFluidData($item, $row, $params, $template, 1, $type) . '</th>';
                } else {
                    $frow .= $this->getFluidData($item, $row, $params, $template, 0, $type);
                }

                $row1count--;

                if ($row1count === 0) {
                    $frow .= '</tr>';
                }
            }

            if ($row->row === '2') {
                if ($row2count === $row2count2) {
                    $frow .= '<tr scope="row">';
                }

                if ($header === 1) {
                    if ($row->colspan > 0) {
                        $thadd = 'colspan="' . $row->colspan . '"';
                    } else {
                        $thadd = '';
                    }
                    $frow .= '<th scope="col" ' . $thadd . '>' . $this->getFluidData($item, $row, $params, $template, 1, $type) . '</th>';
                } else {
                    $frow .= $this->getFluidData($item, $row, $params, $template, 0, $type);
                }

                $row2count--;

                if ($row2count === 0) {
                    $frow .= '</tr>';
                }
            }

            if ($row->row === '3') {
                if ($row3count === $row3count2) {
                    $frow .= '<tr scope="row">';
                }

                if ($header === 1) {
                    if ($row->colspan > 0) {
                        $thadd = 'colspan="' . $row->colspan . '"';
                    } else {
                        $thadd = '';
                    }
                    $frow .= '<th scope="col" ' . $thadd . '>' . $this->getFluidData($item, $row, $params, $template, 1, $type) . '</th>';
                } else {
                    $frow .= $this->getFluidData($item, $row, $params, $template, 0, $type);
                }

                $row3count--;

                if ($row3count === 0) {
                    $frow .= '</tr>';
                }
            }

            if ($row->row === '4') {
                if ($row4count === $row4count2) {
                    $frow .= '<tr scope="row">';
                }

                if ($header === 1) {
                    if ($row->colspan > 0) {
                        $thadd = 'colspan="' . $row->colspan . '"';
                    } else {
                        $thadd = '';
                    }
                    $frow .= '<th scope="col" ' . $thadd . '>' . $this->getFluidData($item, $row, $params, $template, 1, $type) . '</th>';
                } else {
                    $frow .= $this->getFluidData($item, $row, $params, $template, 0, $type);
                }

                $row4count--;

                if ($row4count === 0) {
                    $frow .= '</tr>';
                }
            }

            if ($row->row === '5') {
                if ($row5count === $row5count2) {
                    $frow .= '<tr scope="row">';
                }

                if ($header === 1) {
                    if ($row->colspan > 0) {
                        $thadd = 'colspan="' . $row->colspan . '"';
                    } else {
                        $thadd = '';
                    }
                    $frow .= '<th scope="col" ' . $thadd . '>' . $this->getFluidData($item, $row, $params, $template, 1, $type) . '</th>';
                } else {
                    $frow .= $this->getFluidData($item, $row, $params, $template, 0, $type);
                }

                $row5count--;

                if ($row5count === 0) {
                    $frow .= '</tr>';
                }
            }

            if ($row->row === '6') {
                if ($row6count === $row6count2) {
                    $frow .= '<tr scope="row">';
                }

                if ($header === 1) {
                    if ($row->colspan > 0) {
                        $thadd = 'colspan="' . $row->colspan . '"';
                    } else {
                        $thadd = '';
                    }
                    $frow .= '<th scope="col" ' . $thadd . '>' . $this->getFluidData($item, $row, $params, $template, 1, $type) . '</th>';
                } else {
                    $frow .= $this->getFluidData($item, $row, $params, $template, 0, $type);
                }

                $row6count--;

                if ($row6count === 0) {
                    $frow .= '</tr>';
                }
            }
        }

        return $frow;
    }

    /**
     * Use JImage Class
     *
     * @param   string   $path    Path to File
     * @param   ?string  $alt     Alternate Text
     * @param   ?string  $id      CSS ID for the image
     * @param   ?string  $width   Width
     * @param   ?string  $height  Height
     * @param   ?string  $class   CSS Class
     *
     * @return bool|string
     *
     * @since 9.0.0
     */
    public function useJImage(
        string $path,
        ?string $alt = null,
        ?string $id = null,
        ?string $width = null,
        ?string $height = null,
        ?string $class = null
    ): bool|string {
        $path = HTMLHelper::_('cleanImageURL', $path);

        try {
            $image = Image::getImageFileProperties(JPATH_ROOT . DIRECTORY_SEPARATOR . $path->url);
        } catch (\Exception $e) {
            return false;
        }

        if ($id) {
            $id = ' id="' . $id . '" ';
        }

        if ($width) {
            $width = ' width="' . $width . '" ';
        } else {
            $width = ' width="' . $image->width . '" ';
        }

        if ($height) {
            $height = ' height="' . $height . '" ';
        } else {
            $height = ' height="' . $image->height . '" ';
        }

        if ($class) {
            $class = ' class="' . $class . '" ';
        }

        return '<img src="' . Uri::base() . $path->url . '"' . $id . $width . $height
            . 'alt="' . $alt . '" ' . $class . ' />';
    }

    /**
     * Get Fluid Date
     *
     * @param   Object     $item      Study item
     * @param   Object     $row       Row Setup data
     * @param   Registry   $params    Parameters for the study
     * @param   \stdClass  $template  Template table
     * @param   int        $header    Header will display if 1, Do not display if 0
     * @param   string     $type      Type of Fluid Data
     *
     * @return string
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getFluidData(
        object $item,
        object $row,
        Registry $params,
        \stdClass $template,
        int $header,
        string $type
    ): string {
        $data = '';

        // Match the data in $item to a row/col in $row->name
        $extra = '';

        switch ($type) {
            case 'sermon':
                $extra = 'd';
                break;
            case 'seriesdisplays':
                $extra = 's';
                break;
            case 'seriesdisplay':
                $extra = 'sd';
                break;
            case 'teachers':
                $extra = 'ts';
                break;
            case 'teacher':
                $extra = 'td';
                break;
        }

        switch ($row->name) {
            case $extra . 'custom':
                if ($header === 1) {
                    $data = '';
                } else {
                    $data = $this->getFluidCustom((string) $row->customtext, $item, $params, $template, $type);
                }
                break;

            case $extra . 'teacherallinone':
                if ($header === 1) {
                    $data .= 'Teacher Contact';
                } else {
                    if (isset($item->email)) {
                        ($item->email ? $data = '<a href="mailto:' . $item->email . '">
				<span class="fas fa-envelope" style="font-size:20px;" title="Website"></span></a>' : $data);

                        if ($item->website) {
                            $data .= '<a href="' . $this->ensureScheme($item->website) . '" target="_blank">
						<span class="fas fa-globe" style="font-size:20px;" title="Website"></span></a>';
                        }

                        if ($item->facebooklink) {
                            $data .= '<a href="' . $this->ensureScheme($item->facebooklink) . '" target="_blank">
						<span class="fab fa-facebook" style="font-size:20px;" title="Facebook"></span></a>';
                        }

                        if ($item->twitterlink) {
                            $data .= '<a href="' . $this->ensureScheme($item->twitterlink) . '" target="_blank">
						<span class="fab fa-twitter" style="font-size:20px;" title="Twitter"></span></a>';
                        }

                        if ($item->bloglink) {
                            $data .= '<a href="' . $this->ensureScheme($item->bloglink) . '" target="_blank">
						<span class="fas fa-sticky-note" style="font-size:20px;" title="Blog"></span></a>';
                        }

                        if ($item->link1) {
                            $data .= '<a href="' . $this->ensureScheme($item->link1) . '" target="_blank"><span style="padding-left: 4px; padding-right: 4px;">' . $item->linklabel1 . '</span></a>';
                        }

                        if ($item->link2) {
                            $data .= '<a href="' . $this->ensureScheme($item->link2, 'http://') . '" target="_blank"><span style="padding-left: 4px; padding-right: 4px;">' . $item->linklabel2 . '</span></a>';
                        }

                        if ($item->link3) {
                            $data .= '<a href="' . $this->ensureScheme($item->link3) . '" target="_blank"><span style="padding-left: 4px; padding-right: 4px;">' . $item->link3label . '</span></a>';
                        }
                    }
                }
                break;

            case $extra . 'teacherlong':
                if ($header === 1) {
                    $data = Text::_('JBS_TCH_INFORMATION');
                } else {
                    ($item->information ? $data = HTMLHelper::_(
                        'content.prepare',
                        $item->information,
                        '',
                        'com_proclaim.' . $type
                    ) : $data = '');
                }
                break;

            case $extra . 'teacheraddress':
                if ($header === 1) {
                    $data = Text::_('JBS_TCH_ADDRESS');
                } else {
                    ($item->address ? $data = $item->address : $data = '');
                }
                break;

            case $extra . 'teacherlink1':
                if ($header === 1) {
                    $data = Text::_('JBS_TCH_LINK1');
                } elseif ($item->link1) {
                    $data = '<a href="' . $this->ensureScheme($item->link1) . '" target="_blank"><span style="padding-left: 5px; padding-right: 5px;">' . $item->linklabel1 . '</span></a>';
                }
                break;

            case $extra . 'teacherlink2':
                if ($header === 1) {
                    $data = Text::_('JBS_TCH_LINK2');
                } elseif ($item->link2) {
                    $data = '<a href="' . $this->ensureScheme($item->link2, 'http://') . '" target="_blank"><span style="padding-left: 5px; padding-right: 5px;">' . $item->linklabel2 . '</span></a>';
                }
                break;

            case $extra . 'teacherlink3':
                if ($header === 1) {
                    $data = Text::_('JBS_TCH_LINK3');
                } elseif ($item->link3) {
                    $data = '<a href="' . $this->ensureScheme($item->link3) . '" target="_blank">' . $item->linklabel3 . '</a>';
                }
                break;
            case $extra . 'teacheremail':
                if ($header === 1) {
                    $data = Text::_('JBS_TCH_EMAIL');
                } else {
                    ($item->email ? $data                                                     = '<a href="mailto:' . $item->email . '">
					<span class="fas fa-envelope" style="font-size:20px;" title="Email"></span></a>' : $data = '');
                }
                break;

            case $extra . 'teacherweb':
                if ($header === 1) {
                    $data = Text::_('JBS_TCH_WEBSITE');
                } elseif ($item->website) {
                    $data = '<a href="' . $this->ensureScheme($item->website) . '" target="_blank">
                        <span class="fas fa-globe" style="font-size:20px;" title="Website"></span></a>';
                }
                break;

            case $extra . 'teacherphone':
                if ($header === 1) {
                    $data = Text::_('JBS_TCH_PHONE');
                } else {
                    (isset($item->phone) ? $data = '<a href="tel:' . preg_replace('/[^0-9]/', '', $item->phone) . '" target="_blank">' . $item->phone . '</a>' : $data);
                }
                break;

            case $extra . 'teacherfb':
                if ($header === 1) {
                    $data = Text::_('JBS_TCH_FACEBOOK');
                } elseif ($item->facebooklink) {
                    $data = '<a href="' . $this->ensureScheme($item->facebooklink) . '" target="_blank">
							<span class="fab fa-facebook" style="font-size:20px;" title="Facebook"></span></a>';
                }
                break;

            case $extra . 'teachertw':
                if ($header === 1) {
                    $data = Text::_('JBS_TCH_TWITTER');
                } elseif ($item->twitterlink) {
                    $data = '<a href="' . $this->ensureScheme($item->twitterlink) . '" target="_blank">
							<span class="fas fa-twitter" style="font-size:20px;" title="Twitter"></span></a>';
                }
                break;

            case $extra . 'teacherblog':
                if ($header === 1) {
                    $data = Text::_('JBS_TCH_BLOG');
                } elseif ($item->bloglink) {
                    $data = '<a href="' . $this->ensureScheme($item->bloglink) . '" target="_blank">
							<span class="fas fa-sticky-note" style="font-size:20px;" title="Blog"></span></a>';
                }
                break;

            case $extra . 'teachershort':
                if ($header === 1) {
                    $data = Text::_('JBS_TCH_SHORT_LIST');
                } else {
                    (isset($item->short) ? $data = HTMLHelper::_(
                        'content.prepare',
                        $item->short,
                        '',
                        'com_proclaim.' . $type
                    ) : $data = '');
                }
                break;

            case $extra . 'scripture1':
                $esv          = 0;
                $scripturerow = 1;

                if ($header === 1) {
                    $data = Text::_('JBS_CMN_SCRIPTURE');
                } else {
                    (isset($item->booknumber) ? $data = $this->getScripture(
                        $params,
                        $item,
                        $esv,
                        $scripturerow,
                        $row
                    ) : $data);
                }
                break;
            case $extra . 'scripture2':
                $esv          = 0;
                $scripturerow = 2;

                if ($header === 1) {
                    $data = Text::_('JBS_CMN_SCRIPTURE');
                } else {
                    (isset($item->booknumber2) ? $data = $this->getScripture(
                        $params,
                        $item,
                        $esv,
                        $scripturerow,
                        $row
                    ) : $data);
                }
                break;
            case $extra . 'scriptures':
                if ($header === 1) {
                    $data = Text::_('JBS_CMN_ALL_SCRIPTURES');
                } else {
                    $data = $this->getAllScriptures($params, $item, $row);
                }
                break;
            case $extra . 'secondary':
                if ($header === 1) {
                    $data = Text::_('JBS_CMN_SECONDARY_REFERENCES');
                } else {
                    $data = $item->secondary ?? '';
                }
                break;
            case $extra . 'title':
                if ($header === 1) {
                    $data = Text::_('JBS_CMN_TITLE');
                } else {
                    isset($item->studytitle) ? $data = stripslashes($item->studytitle) : $data;
                    // Add archive badge if item is archived and badge is enabled
                    $showBadge = $params->get('show_archive_badge', '');
                    if ($showBadge === '' || $showBadge === null) {
                        $showBadge = $params->get('default_show_archive_badge', '1');
                    }
                    if (
                        isset($item->published) && (int)$item->published === 2
                        && (int)$showBadge === 1
                    ) {
                        $data .= ' <span class="badge bg-secondary proclaim-archive-badge">'
                            . Text::_('JBS_CMN_ARCHIVE_BADGE') . '</span>';
                    }
                }
                break;
            case $extra . 'date':
                if ($header === 1) {
                    $data = Text::_('JBS_CMN_STUDY_DATE');
                } else {
                    isset($item->studydate) ? $data = $this->getStudyDate($params, $item->studydate, $row) : $data;
                }
                break;
            case $extra . 'teacher':
                if ($header === 1) {
                    $data = Text::_('JBS_CMN_TEACHER');
                } else {
                    (isset($item->teachername) ? $data = $item->teachername : $data);
                }

                break;
            case $extra . 'teacher-title':
                if ($header === 1) {
                    $data = Text::_('JBS_CMN_TEACHER');
                } elseif (isset($item->title, $item->teachername)) {
                    $data = $item->title . ' ' . $item->teachername;
                } else {
                    $data = $item->teachername;
                }
                break;
            case $extra . 'studyintro':
                if ($header === 1) {
                    $data = Text::_('JBS_CMN_STUDY_INTRO');
                } else {
                    isset($item->studyintro) ? $data = HTMLHelper::_(
                        'content.prepare',
                        $item->studyintro,
                        '',
                        'com_proclaim.' . $type
                    ) : $data;
                }
                break;
            case $extra . 'series':
                if ($header === 1) {
                    $data = Text::_('JBS_CMN_SERIES');
                } else {
                    (isset($item->series_text) ? $data = $item->series_text : $data);
                }
                break;
            case $extra . 'seriesthumbnail':
                if ($header === 1) {
                    $data = Text::_('JBS_CMN_THUMBNAIL');
                } elseif ($item->series_thumbnail) {
                    $data = $this->useJImage($item->series_thumbnail, Text::_('JBS_CMN_THUMBNAIL'));
                }
                break;
            case $extra . 'teacherlargeimage':
                if ($header === 1) {
                    $data = Text::_('JBS_TCH_TEACHER_IMAGE');
                } elseif (isset($item->teacher_image) && !empty($item->teacher_image)) {
                    $data = $this->useJImage($item->teacher_image, Text::_('JBS_CMN_THUMBNAIL'));
                }
                break;
            case $extra . 'description':
                if ($header === 1) {
                    $data = Text::_('JBS_CMN_SERIES_DESCRIPTION');
                } else {
                    if ($type === 'seriesdisplays' || $type === 'seriesdisplay') {
                        (isset($item->description) ? $data = HTMLHelper::_(
                            'content.prepare',
                            $item->description,
                            '',
                            'com_proclaim.' . $type
                        ) : $data);

                        if ($params->get('series_characters')) {
                            $d    = substr($data, 0, $params->get('series_characters'));
                            $data = substr($d, 0, strrpos($d, '. '));
                        }

                        if ($data) {
                            $data .= '.';
                        }
                    } else {
                        (isset($item->sdescription) ? $data = HTMLHelper::_(
                            'content.prepare',
                            $item->sdescription,
                            '',
                            'com_proclaim.' . $type
                        ) : $data);

                        if ($params->get('series_characters')) {
                            $d    = substr($data, 0, $params->get('series_characters'));
                            $data = substr($d, 0, strrpos($d, '. '));
                        }

                        if ($data) {
                            $data .= '.';
                        }
                    }

                    if ($type === 'seriesdisplays') {
                        (isset($item->description) ? $data = stripslashes($item->description) : $data = '');

                        if ($params->get('series_characters')) {
                            $d    = substr($data, 0, $params->get('series_characters'));
                            $data = substr($d, 0, strrpos($d, '. '));
                        }

                        if ($data) {
                            $data .= '.';
                        }
                    }
                }
                break;
            case $extra . 'submitted':
                if ($header === 1) {
                    $data = Text::_('JBS_CMN_SUBMITTED_BY');
                } else {
                    (isset($item->submitted) ? $data = $item->submitted : $data);
                }
                break;
            case $extra . 'hits':
                if ($header === 1) {
                    $data = Text::_('JBS_CMN_VIEWS');
                } else {
                    (isset($item->hits) ? $data = $item->hits : $data);
                }
                break;
            case $extra . 'downloads':
                if ($header === 1) {
                    $data = Text::_('JBS_CMN_DOWNLOADS');
                } else {
                    (isset($item->downloads) ? $data = $item->downloads : $data);
                }
                break;
            case $extra . 'studynumber':
                if ($header === 1) {
                    $data = Text::_('JBS_CMN_STUDYNUMBER');
                } else {
                    (isset($item->studynumber) ? $data = $item->studynumber : $data);
                }
                break;
            case $extra . 'topic':
                if ($header === 1) {
                    $data = Text::_('JBS_CMN_TOPIC');
                } elseif (isset($item->topics_text)) {
                    if (substr_count($item->topics_text, ',')) {
                        $topics = explode(',', $item->topics_text);

                        foreach ($topics as $key => $value) {
                            $topics[$key] = Text::_($value);
                        }

                        $data = implode(', ', $topics);
                    } else {
                        (isset($item->topics_text) ? $data = Text::_($item->topics_text) : $data = '');
                    }
                }
                break;
            case $extra . 'locations':
                if ($header === 1) {
                    $data = Text::_('JBS_CMN_LOCATION');
                } else {
                    (isset($item->location_text) ? $data = $item->location_text : $data = '');
                }
                break;
            case $extra . 'jbsmedia':
                if ($header === 1) {
                    $data = Text::_('JBS_CMN_MEDIA');
                } else {
                    $data = $this->getFluidMediaFiles($item, $params, $template);
                }
                break;
            case $extra . 'messagetype':
                if ($header === 1) {
                    $data = Text::_('JBS_CMN_MESSAGETYPE');
                } else {
                    (isset($item->message_type) ? $data = $item->message_type : $data);
                }
                break;
            case $extra . 'thumbnail':
                if ($header === 1) {
                    $data = Text::_('JBS_CMN_THUMBNAIL');
                } elseif ($item->thumbnailm || $params->get('studyimage', '-1') !== '-1') {
                    $data = $this->useJImage($item->thumbnailm, Text::_('JBS_CMN_THUMBNAIL'));

                    if ($params->get('studyimage', '-1') !== '-1') {
                        $data = $this->useJImage(
                            'media/com_proclaim/images/stockimages/'
                            . $params->get('studyimage'),
                            Text::_('JBS_CMN_THUMBNAIL')
                        );
                    }
                }
                break;
            case $extra . 'teacherimage':
                if ($header === 1) {
                    $data = Text::_('JBS_TCH_TEACHER_IMAGE');
                } elseif ($type === 'seriesdisplays' || $type === 'seriesdisplay' || $type === 'teachers' || $type === 'teacher') {
                    if (!empty($item->teacher_thumbnail)) {
                        $data = $this->useJImage($item->teacher_thumbnail, Text::_('JBS_CMN_THUMBNAIL'));
                    }
                } elseif ($item->thumb) {
                    $data = $this->useJImage($item->thumb, Text::_('JBS_CMN_THUMBNAIL'));
                }
                break;
        }

        $style       = '';
        $customclass = '';

        if (isset($row->custom)) {
            if (str_contains($row->custom, 'style=')) {
                $style = $row->custom;
            } else {
                $customclass = $row->custom;
            }
        }

        $classelement = $this->createelement($row->element);

        if ($classelement) {
            if (isset($style)) {
                $style = ' ' . $style;
            }

            $classopen  = '<' . $classelement . $style . '>';
            $classclose = '</' . $classelement . '>';
        } else {
            $classopen  = '';
            $classclose = '';
        }

        // See whether the element is a link to something and get the link from the function
        $link = 0;

        if ($type === 'sermons' || $type === 'sermon' || $type === 'seriesdisplays' || $type === 'teachers') {
            if ($row->linktype > 0 && $header === 0) {
                if ($type === 'seriesdisplays') {
                    $item->teacher_id = $item->teacher;
                }

                if ($type === 'teachers') {
                    $item->teacher_id = $item->id;
                }

                $link = $this->getLink($row->linktype, $item->id, $item->teacher_id, $params, $item, $template, $type);
            }
        }

        $tdadd = '';
        $frow  = '';

        if ($row->colspan > 0) {
            $tdadd = 'colspan="' . $row->colspan . '"';
        }

        if ($customclass) {
            $tdadd .= ' class="' . $customclass . '"';
        }

        if ($header === 0) {
            $frow = '<td scope="col"' . $tdadd . '>';
        }

        if ($header === 1) {
            $frow = '';
        }

        if ($link) {
            $frow .= $link;
        }

        if ($data && $header === 0) {
            $frow .= $classopen . $data;
        }

        if ($data && $header === 1) {
            $frow .= $data;
        }

        if ($link) {
            $frow .= '</a>';
        }

        // If ($header === 0){ $frow .= $classclose . '</td>';}
        if ($header === 0) {
            $frow .= '</' . $classclose . '</td>';
        }

        if ($header === 1) {
            $frow .= '</th>';
        }

        return $frow;
    }

    /**
     * Get Fluid Custom
     *
     * @param   String     $custom    Custom String
     * @param   Object     $item      Study Item
     * @param   Registry   $params    Params
     * @param   \stdClass  $template  Template Table Data
     * @param   String     $type      Type of data
     *
     * @return mixed
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getFluidCustom(string $custom, object $item, Registry $params, $template, string $type): string
    {
        $countbraces = substr_count($custom, '{');

        while ($countbraces > 0) {
            $bracebegin = strpos($custom, '{');
            $braceend   = strpos($custom, '}');
            $subcustom  = substr($custom, ($bracebegin + 1), (($braceend - $bracebegin) - 1));

            $element = $this->getElement($subcustom, $item, $params, $template, $type);
            $custom  = substr_replace($custom, $element, $bracebegin, (($braceend - $bracebegin) + 1));
            $countbraces--;
        }

        return $custom;
    }

    /**
     * Get Element
     *
     * @param   String     $custom    Custom String
     * @param   Object     $row       Row Data
     * @param   Registry   $params    Params
     * @param   \stdClass  $template  Template Data
     * @param   String     $type      Type of element
     *
     * @return mixed
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getElement(string $custom, object $row, Registry $params, $template, string $type): mixed
    {
        $element = null;

        switch ($custom) {
            case 'scripture1':
                $esv          = 0;
                $scripturerow = 1;
                $element      = $this->getScripture($params, $row, $esv, $scripturerow);
                break;
            case 'scripture2':
                $esv          = 0;
                $scripturerow = 2;
                $element      = $this->getScripture($params, $row, $esv, $scripturerow);
                break;
            case 'secondary':
                $element = $row->secondary_reference;
                break;
            case 'title':
                $element = $row->studytitle ?? '';
                // Add archive badge if item is archived and badge is enabled
                $showBadge = $params->get('show_archive_badge', '');
                if ($showBadge === '' || $showBadge === null) {
                    $showBadge = $params->get('default_show_archive_badge', '1');
                }
                if (
                    isset($row->published) && (int)$row->published === 2
                    && (int)$showBadge === 1
                ) {
                    $element .= ' <span class="badge bg-secondary proclaim-archive-badge">'
                        . Text::_('JBS_CMN_ARCHIVE_BADGE') . '</span>';
                }
                break;
            case 'studyintro':
                if (isset($row->studyintro)) {
                    $element = HTMLHelper::_('content.prepare', $row->studyintro, '', 'com_proclaim.' . $type);
                } else {
                    $element = '';
                }
                break;
            case 'teacher':
                // Teacher name and title
                if (isset($row->teachertitle, $row->teachername)) {
                    $element = $row->teachertitle . ' ' . $row->teachername;
                } else {
                    $element = $row->teachername;
                }
                break;
            case 'studynumber':
                $element = $row->studynumber ?? '';
                break;
            case 'series_text':
                // Series title
                $element = $row->series_text ?? '';
                break;
            case 'series_thumbnail':
                if ($row->series_thumbnail) {
                    $element = $this->useJImage($row->series_thumbnail, $row->series_text);
                } else {
                    $element = '';
                }
                break;
            case 'submitted':
                if (isset($row->submitted)) {
                    $element = $row->submitted;
                } else {
                    $element = '';
                }
                break;
            case 'teacherimage':
                if (isset($row->teacher_thumbnail)) {
                    $element = $this->useJImage($row->teacher_thumbnail, $row->teachername);
                } else {
                    $element = '';
                };
                break;
            case 'teachername':
                if (isset($row->teachername)) {
                    $element = $row->teachername;
                } else {
                    $element = '';
                }
                break;
            case 'jbsmedia':
                if (isset($row->mids)) {
                    $medias          = $this->getFluidMediaids($row);
                    $mediafiles      = $this->getMediaFiles($medias);
                    $row->mediafiles = $mediafiles;
                    $element         = $this->getFluidMediaFiles($row, $params, $template);
                } else {
                    $element = '';
                }
                break;
            case 'thumbnail':
                // Assume study thumbnail
                $element = $this->useJImage($row->thumbnailm, $row->studytitle);

                if ($params->get('studyimage', '-1') !== '-1') {
                    // Clean up extra data in the image
                    $hash = str_contains((string) $params->get('studyimage'), '#');

                    if ($hash) {
                        $imageparam   = $params->get('studyimage');
                        $hashlocation = strpos($imageparam, '#');
                        $image        = substr($imageparam, 0, $hashlocation);
                        $element      = $this->useJImage($image, $row->studytitle);
                    }
                }

                break;
            case 'studytitle':
                (isset($row->studytitle) ? $element = $row->studytitle : $element = '');
                break;
            case 'teacher-title-name':
                if (isset($row->teachertitle, $row->teachername)) {
                    $element = $row->teachertitle . ' ' . $row->teachername;
                } else {
                    $element = '';
                }
                break;

            case 'topics':
                if (isset($row->topics_text)) {
                    if (substr_count($row->topics_text, ',')) {
                        $topics = explode(',', $row->topics_text);

                        foreach ($topics as $key => $value) {
                            $topics[$key] = Text::_($value);
                        }

                        $element = implode(', ', $topics);
                    } else {
                        (isset($row->topics_text) ? $element = Text::_($row->topics_text) : $element = '');
                    }
                }
                break;
            case 'message_type':
                if (isset($row->message_type)) {
                    $element = $row->message_type;
                } else {
                    $element = '';
                }
                break;
            case 'location_text':
                if (isset($row->location_text)) {
                    $element = $row->location_text;
                } else {
                    $element = '';
                }
                break;
            case 'date':
                if (isset($row->studydate)) {
                    $element = $this->getStudyDate($params, $row->studydate);
                } else {
                    $element = '';
                }
                break;
            case 'series_description':
                if (isset($row->sdescription)) {
                    if ($type === 'seriesdisplays' || $type === 'seriesdisplay') {
                        $element = HTMLHelper::_('content.prepare', $row->description, '', 'com_proclaim.' . $type);
                    } else {
                        $element = HTMLHelper::_('content.prepare', $row->sdescription, '', 'com_proclaim.' . $type);
                    }
                } else {
                    $element = '';
                }
                break;
            case 'hits':
                if (isset($row->hits)) {
                    $element = Text::_('JBS_CMN_HITS') . ' ' . $row->hits;
                } else {
                    $element = '';
                }
                break;
        }

        return $element;
    }

    /**
     * Cache for book name lookups
     *
     * @var array
     * @since 10.0.0
     */
    private static array $bookNameCache = [];

    /**
     * Get Scripture
     *
     * @param   Registry  $params        Item Params
     * @param   object    $row           Row Info
     * @param   int       $esv           ESV String
     * @param   int       $scripturerow  Scripture Row
     *
     * @return string
     *
     * @since 7.0
     */
    public function getScripture(
        Registry $params,
        object $row,
        int $esv,
        int $scripturerow,
        ?object $elementConfig = null
    ): string {
        if (!isset($row->id)) {
            return '';
        }

        $booknumber  = (int) ($row->booknumber ?? 0);
        $booknumber2 = (int) ($row->booknumber2 ?? 0);

        if ($booknumber <= 0 && $booknumber2 !== 0) {
            return '';
        }

        // Extract scripture data based on which row we're processing
        if ($scripturerow === 2 && $booknumber2 > 1) {
            $bookNum      = $booknumber2;
            $ch_b         = (int) ($row->chapter_begin2 ?? 0);
            $ch_e         = (int) ($row->chapter_end2 ?? 0);
            $v_b          = (int) ($row->verse_begin2 ?? 0);
            $v_e          = (int) ($row->verse_end2 ?? 0);
            $book         = Text::_($row->bookname2 ?? '');
            $bibleVersion = $row->bible_version2 ?? '';
        } elseif ($scripturerow === 1 && $booknumber > 1) {
            $bookNum      = $booknumber;
            $ch_b         = (int) ($row->chapter_begin ?? 0);
            $ch_e         = (int) ($row->chapter_end ?? 0);
            $v_b          = (int) ($row->verse_begin ?? 0);
            $v_e          = (int) ($row->verse_end ?? 0);
            $book         = isset($row->bookname) ? Text::_($row->bookname) : $this->getBookNameFromDb($booknumber);
            $bibleVersion = $row->bible_version ?? '';
        } else {
            return '';
        }

        if (empty($book) || $bookNum === 0) {
            return '';
        }

        // Check for element-specific show_verses (empty string means use global)
        // Default to 0 (chapters only) if not found in params
        $show_verses = (int) $params->get('show_verses', 0);
        if ($elementConfig !== null && isset($elementConfig->show_verses) && $elementConfig->show_verses !== '') {
            $show_verses = (int) $elementConfig->show_verses;
        }

        // Check for element-specific show_version setting
        $showVersion = (int) $params->get('show_version', 1);

        if ($elementConfig !== null && isset($elementConfig->show_version) && $elementConfig->show_version !== '') {
            $showVersion = (int) $elementConfig->show_version;
        }

        $versionSuffix = '';

        if ($showVersion === 1 && !empty($bibleVersion)) {
            $versionSuffix = ' ' . strtoupper($bibleVersion);
        }

        if ($bookNum > 166 || $show_verses === 2) {
            return $book . $versionSuffix;
        }

        // Chapters only mode (show_verses === 0)
        if ($show_verses === 0) {
            $ref = $ch_e > $ch_b
                ? $book . ' ' . $ch_b . '-' . $ch_e
                : $book . ' ' . $ch_b;

            return $ref . $versionSuffix;
        }

        // Full reference with verses (show_verses === 1 or esv mode)
        return $this->formatScriptureReference($book, $ch_b, $ch_e, $v_b, $v_e) . $versionSuffix;
    }

    /**
     * Get all scripture references for a message, rendered as a semicolon-separated string.
     *
     * Uses the junction table scriptures if available, falls back to legacy scripture1/scripture2.
     *
     * @param   Registry  $params         Template parameters
     * @param   object    $row            Message row
     * @param   ?object   $elementConfig  Element configuration from Layout Editor
     *
     * @return  string  Rendered scripture references separated by "; "
     *
     * @since  10.1.0
     */
    public function getAllScriptures(
        Registry $params,
        object $row,
        ?object $elementConfig = null
    ): string {
        // Use junction table scriptures if available
        if (!empty($row->scriptures) && \is_array($row->scriptures)) {
            $parts = [];

            foreach ($row->scriptures as $ref) {
                if (!($ref instanceof ScriptureReference)) {
                    continue;
                }

                if ($ref->booknumber <= 0) {
                    // Unparsed raw text
                    if ($ref->referenceText !== '') {
                        $parts[] = $ref->referenceText;
                    }

                    continue;
                }

                // Build a virtual row to reuse getScripture()
                $virtualRow = (object) [
                    'id'            => $row->id ?? 0,
                    'booknumber'    => $ref->booknumber,
                    'chapter_begin' => $ref->chapterBegin,
                    'verse_begin'   => $ref->verseBegin,
                    'chapter_end'   => $ref->chapterEnd,
                    'verse_end'     => $ref->verseEnd,
                    'bookname'      => CwmscriptureHelper::getBookName($ref->booknumber)
                        ? array_search($ref->booknumber, array_column(CwmscriptureHelper::getAllBooks(), 'booknumber'))
                        : '',
                    'bible_version' => $ref->bibleVersion,
                    'booknumber2'   => 0,
                ];

                // Get the translated book name key for the virtual row
                $bookKey = '';

                foreach (CwmscriptureHelper::getAllBooks() as $book) {
                    if ($book['booknumber'] === $ref->booknumber) {
                        $bookKey = $book['key'];

                        break;
                    }
                }

                $virtualRow->bookname = $bookKey;

                $rendered = $this->getScripture($params, $virtualRow, 0, 1, $elementConfig);

                if ($rendered !== '') {
                    $parts[] = $rendered;
                }
            }

            if (!empty($parts)) {
                return implode('; ', $parts);
            }
        }

        // Fallback to legacy scripture1/scripture2
        $parts = [];
        $s1    = $this->getScripture($params, $row, 0, 1, $elementConfig);
        $s2    = $this->getScripture($params, $row, 0, 2, $elementConfig);

        if ($s1 !== '') {
            $parts[] = $s1;
        }

        if ($s2 !== '') {
            $parts[] = $s2;
        }

        return implode('; ', $parts);
    }

    /**
     * Get book name from database with caching
     *
     * @param   int  $booknumber  The book number to look up
     *
     * @return string The translated book name or empty string
     *
     * @since 10.0.0
     */
    private function getBookNameFromDb(int $booknumber): string
    {
        if ($booknumber <= 0) {
            return '';
        }

        if (isset(self::$bookNameCache[$booknumber])) {
            return self::$bookNameCache[$booknumber];
        }

        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select($db->quoteName('bookname'))
            ->from($db->quoteName('#__bsms_books'))
            ->where($db->quoteName('booknumber') . ' = ' . $booknumber);
        $db->setQuery($query);
        $bookname = $db->loadResult();

        $result                           = $bookname ? Text::_($bookname) : '';
        self::$bookNameCache[$booknumber] = $result;

        return $result;
    }

    /**
     * Format a scripture reference string
     *
     * @param   string  $book  Book name
     * @param   int     $ch_b  Chapter begin
     * @param   int     $ch_e  Chapter end
     * @param   int     $v_b   Verse begin
     * @param   int     $v_e   Verse end
     *
     * @return string Formatted scripture reference
     *
     * @since 10.0.0
     */
    private function formatScriptureReference(string $book, int $ch_b, int $ch_e, int $v_b, int $v_e): string
    {
        // No verses - just book and chapter(s)
        if ($v_b === 0) {
            return $ch_e > $ch_b
                ? $book . ' ' . $ch_b . '-' . $ch_e
                : $book . ' ' . $ch_b;
        }

        // Same chapter
        if ($ch_e === $ch_b || $ch_e === 0) {
            if ($v_e === 0 || $v_e === $v_b) {
                // Single verse: "Book 1:5"
                return $book . ' ' . $ch_b . ':' . $v_b;
            }

            // Verse range in same chapter: "Book 1:5-10"
            return $book . ' ' . $ch_b . ':' . $v_b . '-' . $v_e;
        }

        // Different chapters with verses: "Book 1:5-2:10"
        if ($v_e === 0) {
            return $book . ' ' . $ch_b . ':' . $v_b . '-' . $ch_e;
        }

        return $book . ' ' . $ch_b . ':' . $v_b . '-' . $ch_e . ':' . $v_e;
    }

    /**
     * Get Fluid Media Files
     *
     * @param   Object     $item      Study item
     * @param   Registry   $params    Params
     * @param   \stdClass  $template  Template return
     *
     * @return string
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public function getFluidMediaFiles(object $item, Registry $params, \stdClass $template): string
    {
        $med = new Cwmmedia();

        $mediarow = '<div class="bsms_media_container row" style="float: left;
                      position: relative;
                      left: 50%;
                      transform: translateX(-50%);"  >';

        foreach ($item->mediafiles as $media) {
            $mediarow .= '<div id="bsms_media_file' . $media->id . '" class="col bsms_media_file" >' .
                $med->getFluidMedia($media, $params, $template) . '</div>';
        }

        $mediarow .= '</div>';
        $mediarow .= '<div style="clear:both;"></div>';

        return $mediarow;
    }

    /**
     * Get StudyDate
     *
     * @param   Registry     $params     Item Params
     * @param   string       $studydate  Study Date
     * @param   object|null  $row        Element row settings (optional, for element-specific date format)
     *
     * @return string
     *
     * @since 7.0
     */
    public function getStudyDate(Registry $params, string $studydate, ?object $row = null): string
    {
        $customDate = $params->get('custom_date_format');

        // Check for element-specific date format (empty string means use global)
        // Default to 0 (Sep 1, 2012 format) if not found in params
        $dateFormat = $params->get('date_format', 0);
        if ($row !== null && isset($row->date_format) && $row->date_format !== '') {
            $dateFormat = $row->date_format;
        }

        if (empty($customDate)) {
            try {
                switch ($dateFormat) {
                    case 0:
                        $date = HTMLHelper::_('date', $studydate, "M j, Y", null);
                        break;
                    case 1:
                        $date = HTMLHelper::_('date', $studydate, "M J", null);
                        break;
                    case 2:
                        $date = HTMLHelper::_('date', $studydate, "n/j/Y", null);
                        break;
                    case 4:
                        $date = HTMLHelper::_('date', $studydate, "l, F j, Y", null);
                        break;
                    case 5:
                        $date = HTMLHelper::_('date', $studydate, "F j, Y", null);
                        break;
                    case 6:
                        $date = HTMLHelper::_('date', $studydate, "j F Y", null);
                        break;
                    case 7:
                        $date = date("j/n/Y", strtotime($studydate));
                        break;
                    case 8:
                        $date = HTMLHelper::_('date', $studydate, Text::_('DATE_FORMAT_LC'), null);
                        break;
                    case 9:
                        $date = HTMLHelper::_('date', $studydate, "Y/M/D", null);
                        break;
                    default:
                        $date = HTMLHelper::_('date', $studydate, "n/j", null);
                        break;
                }
            } catch (\Exception $e) {
                return $studydate;
            }
        } else {
            try {
                $date = HTMLHelper::_('date', $studydate, $customDate);
            } catch (\Exception $e) {
                return $studydate;
            }
        }

        return $date;
    }

    /**
     * Create a Element
     *
     * @param   string  $element  Case that will chose the element
     *
     * @return string
     *
     * @since 7.0
     */
    public function createelement($element): string
    {
        switch ($element) {
            case 1:
                $classelement = 'p';
                break;
            case 2:
                $classelement = 'h1';
                break;
            case 3:
                $classelement = 'h2';
                break;
            case 4:
                $classelement = 'h3';
                break;
            case 5:
                $classelement = 'h4';
                break;
            case 6:
                $classelement = 'h5';
                break;
            case 7:
                $classelement = 'blockquote';
                break;
            case 8:
                $classelement = 'div';
                break;
            default:
                $classelement = 'span';
                break;
        }

        return $classelement;
    }

    /**
     *  Get Link
     *
     * @param   int        $islink    What type of link
     * @param   string     $id3       Id3 data
     * @param   int        $tid       Template ID
     * @param   Registry   $params    Params
     * @param   \stdClass  $row       Row data
     * @param   \stdClass  $template  Template Table Data
     * @param   string     $type      Type for Series display
     *
     * @return string
     *
     * @throws \Exception
     * @since 7.0
     */
    private function getLink(
        int $islink,
        string $id3,
        int $tid,
        Registry $params,
        \stdClass $row,
        $template,
        string $type
    ): string {
        $column = '';

        switch ($islink) {
            case 1:
                $link = Route::_(
                    Cwmhelperroute::getArticleRoute($row->slug) . '&t=' . $params->get('detailstemplateid')
                );

                if ($type === 'seriesdisplays') {
                    $link = Route::_(
                        Cwmhelperroute::getSeriesRoute($row->slug) . '&t=' . $params->get('detailstemplateid')
                    );
                }

                $column = '<a href="' . $link . '">';
                break;

            case 3:
                $link   = Route::_(Cwmhelperroute::getTeacherRoute($tid) . '&t=' . $params->get('teachertemplateid'));
                $column .= '<a href="' . $link . '">';
                break;

            case 4:
                // Case 4 is a details link with a tooltip

                $link = Route::_(
                    Cwmhelperroute::getArticleRoute($row->slug) . '&t=' . $params->get('detailstemplateid')
                );

                $column = Cwmhelper::getTooltip($row, $params, $template);
                $column .= '<a href="' . $link . '">';

                break;

            case 5:
                $column = Cwmhelper::getTooltip($row, $params, $template);
                break;

            case 6:
                // Case 6 is for a link to the 1st article in the media file records
                $column .= '<a href="' . $this->getOtherlinks($id3, $islink, $params) . '">';
                break;

            case 7:
                // Case 7 is for Virtuemart - only if integration is enabled
                $componentParams = ComponentHelper::getParams('com_proclaim');
                if ((int) $componentParams->get('enable_virtuemart', 0)) {
                    $column .= '<a href="' . $this->getOtherlinks($id3, $islink, $params) . '">';
                }
                // If integration disabled, no link is created (fallback to plain text)
                break;

            case 8:
                // Case 8 is for Docman - only if integration is enabled
                $componentParams = ComponentHelper::getParams('com_proclaim');
                if ((int) $componentParams->get('enable_docman', 0)) {
                    $column .= '<a href="' . $this->getOtherlinks($id3, $islink, $params) . '">';
                }
                // If integration disabled, no link is created (fallback to plain text)
                break;

            case 9:
                // Case 9 is a link to download
                $column .= '<a href="index.php?option=com_proclaim&amp;view=Cwmsermon&amp;mid=' .
                    $row->download_id . '&amp;task=download">';
                break;

            case 10:
                // Case 10 is a link to the series page
                $seriesId = $row->sid ?? $row->series_id ?? 0;

                if ($seriesId) {
                    $link = Route::_(
                        Cwmhelperroute::getSeriesRoute($seriesId) . '&t=' . $params->get('seriesdetailtemplateid')
                    );
                    $column = '<a href="' . $link . '">';
                }

                break;
        }

        return $column;
    }

    /**
     * Get Other Links
     *
     * @param   int       $id3     Study ID
     * @param   string    $islink  Is a Link
     * @param   Registry  $params  Item Params
     *
     * @return string
     *
     * @since 7.0
     */
    public function getOtherlinks($id3, $islink, $params): string
    {
        $link  = '';
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select($db->quoteName('#__bsms_mediafiles') . '.*')
            ->from($db->quoteName('#__bsms_mediafiles'))
            ->where($db->quoteName('study_id') . ' = ' . $db->quote($id3));

        // Include archived media when showing archived messages
        $showArchived = $params->get('show_archived', '');
        if ($showArchived === '' || $showArchived === null) {
            $showArchived = $params->get('default_show_archived', '0');
        }
        if ($showArchived === '1' || $showArchived === '2') {
            $query->where($db->quoteName('#__bsms_mediafiles.published') . ' IN (1, 2)');
        } else {
            $query->where($db->quoteName('#__bsms_mediafiles.published') . ' = 1');
        }

        $db->setQuery($query);
        $db->execute();
        $num_rows = $db->getNumRows();

        if ($num_rows > 0) {
            $mediafiles = $db->loadObjectList();

            foreach ($mediafiles as $media) {
                switch ($islink) {
                    case 6:
                        if ($media->article_id > 0) {
                            $link = 'index.php?option=com_content&view=article&id=' . $media->article_id;
                        }
                        break;

                    case 7:
                        // VirtueMart - only if integration is enabled
                        $componentParams = ComponentHelper::getParams('com_proclaim');
                        if ((int) $componentParams->get('enable_virtuemart', 0) && $media->virtueMart_id > 0) {
                            $link = 'index.php?option=com_virtuemart&page=shop.product_details&flypage='
                                . $params->get('store_page', 'flypage.tpl') . '&product_id=' . $media->virtueMart_id;
                        }
                        break;

                    case 8:
                        // DOCman - only if integration is enabled
                        $componentParams = ComponentHelper::getParams('com_proclaim');
                        if ((int) $componentParams->get('enable_docman', 0) && $media->docMan_id > 0) {
                            $link = 'index.php?option=com_docman&task=doc_download&gid=' . $media->docMan_id;
                        }
                        break;
                }
            }
        }

        return $link;
    }

    /**
     * Get Listing Exp
     *
     * @param   object            $row       Item Info
     * @param   Registry          $params    Item Params
     * @param   CwmtemplateTable  $template  Template
     *
     * @return string
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getListingExp($row, $params, $template): string
    {
        $label = $params->get('templatecode');

        if (empty($label)) {
            return '';
        }

        $image     = Cwmimages::getStudyThumbnail($row->thumbnailm ?? '');
        $thumbnail = $image ? $this->useJImage(
            $image->path,
            "",
            "bsms_studyThumbnail" . $row->id,
            $image->width,
            $image->height
        ) : '';

        // Build replacements array for single str_replace call
        $replacements = [
            '{{teacher}}'     => $row->teachername ?? '',
            '{{title}}'       => $row->studytitle ?? '',
            '{{date}}'        => isset($row->studydate) ? $this->getStudyDate($params, $row->studydate) : '',
            '{{studyintro}}'  => $row->studyintro ?? '',
            '{{scripture}}'   => $this->getScripture($params, $row, 0, 1),
            '{{topics}}'      => $row->topic_text ?? '',
            '{{url}}'         => Route::_('index.php?option=com_proclaim&view=Cwmsermon&id=' . $row->id . '&t=' . $template->id),
            '{{thumbnail}}'   => $thumbnail,
            '{{seriestext}}'  => $row->series_text ?? '',
            '{{messagetype}}' => $row->message_type ?? '',
            '{{bookname}}'    => $row->bookname ?? '',
            '{{hits}}'        => $row->hits ?? '',
            '{{location}}'    => $row->location_text ?? '',
            '{{plays}}'       => $row->totalplays ?? '',
            '{{downloads}}'   => $row->totaldownloads ?? '',
        ];

        $label = str_replace(array_keys($replacements), array_values($replacements), $label);

        // Only process media if the placeholder exists in the template
        if (str_contains($label, '{{media}}')) {
            $media      = new Cwmmedia();
            $mediaTable = $media->getFluidMedia($row, $params, $template);
            $label      = str_replace('{{media}}', $mediaTable, $label);
        }

        return $label;
    }

    /**
     * Share Helper file
     *
     * @param   string    $link    Link
     * @param   object    $row     Item Info
     * @param   Registry  $params  Item Params
     *
     * @return null|string
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getShare($link, $row, Registry $params): ?string
    {
        // Get a study title and prepare for sharing
        $title = $row->studytitle ?? '';
        $title = htmlspecialchars(strip_tags($title), ENT_QUOTES, 'UTF-8');

        // Get description from intro or first part of the study text
        $description = '';

        if (!empty($row->studyintro)) {
            $description = strip_tags($row->studyintro);
        } elseif (!empty($row->studytext)) {
            $description = strip_tags($row->studytext);
        }

        $description = mb_substr($description, 0, 200);

        // Get image URL for sharing
        $imageUrl = '';

        if (!empty($row->thumbnailm)) {
            $imageUrl = Uri::root() . $row->thumbnailm;
        } elseif (!empty($row->image)) {
            $imageUrl = Uri::root() . $row->image;
        }

        // Ensure link is absolute
        if (!str_starts_with($link, 'http')) {
            $link = Uri::root() . ltrim($link, '/');
        }

        $shareit = '<div class="proclaim-share float-end">';

        // AddToAny with data attributes for specific content
        $shareit .= '<!-- AddToAny Share Buttons -->
            <div class="a2a_kit a2a_kit_size_32 a2a_default_style"
                 data-a2a-url="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '"
                 data-a2a-title="' . $title . '">
                <a class="a2a_button_facebook"></a>
                <a class="a2a_button_x"></a>
                <a class="a2a_button_email"></a>
                <a class="a2a_button_copy_link"></a>
                <a class="a2a_dd" href="https://www.addtoany.com/share"></a>
            </div>';

        $shareit .= '</div>';

        // Add script and configuration once per page
        $app = Factory::getApplication();

        if (!$app instanceof CMSApplicationInterface) {
            return $shareit;
        }

        $doc = $app->getDocument();

        // Build configuration script with email template
        // Note: ${link} and ${title} are AddToAny template placeholders (not PHP variables)
        $escapedDesc = addslashes($description ?: 'Check out this message');
        $config      = "var a2a_config = a2a_config || {};
a2a_config.onclick = 1;
a2a_config.num_services = 8;
a2a_config.thanks = { postShare: true, ad: false };
a2a_config.templates = a2a_config.templates || {};
a2a_config.templates.email = {
    subject: '\${title}',
    body: '" . $escapedDesc . "\\n\\n\${link}'
};";

        // Add image for Open Graph sharing if available
        if ($imageUrl) {
            $config .= "\na2a_config.linkurl_default = '" . addslashes($link) . "';";
        }

        $doc->addScriptDeclaration($config);

        // Add the AddToAny script (async for performance)
        $doc->addScript(
            'https://static.addtoany.com/menu/page.js',
            [],
            ['defer' => true, 'async' => true]
        );

        return $shareit;
    }

    /**
     * Ensure URL has a scheme (http:// or https://)
     *
     * @param   string  $url     URL to check
     * @param   string  $scheme  Default scheme to add if missing
     *
     * @return string URL with scheme
     *
     * @since 10.0.0
     */
    private function ensureScheme(string $url, string $scheme = 'https://'): string
    {
        if (parse_url($url, PHP_URL_SCHEME) !== null) {
            return $url;
        }

        return $scheme . $url;
    }

    /**
     * Run Content Plugins on item text
     *
     * @param   object  $item    Item info with text property
     * @param   object  $params  Item params
     *
     * @return object Item with processed text and event data
     *
     * @throws \Exception
     * @since 10.0.0
     */
    public function runContentPlugins(object $item, object $params): object
    {
        // We don't need offset, but it is a required argument for the plugin dispatcher
        $offset = 0;
        PluginHelper::importPlugin('content');

        // Run content plugins
        $dispatcher            = Factory::getApplication();
        $contentEventArguments = [
            'context' => 'com_proclaim.sermon',
            'subject' => &$item,
            'params'  => &$params,
            'page'    => $offset,
        ];

        $dispatcher->triggerEvent('onContentPrepare', $contentEventArguments);

        $item->event                        = new \stdClass();
        $results                            = $dispatcher->triggerEvent('onContentAfterTitle', $contentEventArguments);
        $item->event->afterDisplayTitle     = trim(implode("\n", $results));

        $results                            = $dispatcher->triggerEvent('onContentBeforeDisplay', $contentEventArguments);
        $item->event->beforeDisplayContent  = trim(implode("\n", $results));

        $results                            = $dispatcher->triggerEvent('onContentAfterDisplay', $contentEventArguments);
        $item->event->afterDisplayContent   = trim(implode("\n", $results));

        return $item;
    }
}
