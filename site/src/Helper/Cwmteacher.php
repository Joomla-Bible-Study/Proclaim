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
use Joomla\CMS\Router\Route;
use Joomla\Component\Contact\Site\Model\ContactModel;
use Joomla\Registry\Registry;

/**
 * Class for Teachers Helper
 *
 * @package  Proclaim.Site
 * @since    8.0.0
 */
class Cwmteacher extends Cwmlisting
{
    /**
     * Get Teacher for Fluid layout
     *
     * @param   Registry  $params  Parameters
     *
     * @return array
     *
     * @throws \Exception
     * @since    8.0.0
     */
    public function getTeachersFluid($params): array
    {
        $input      = Factory::getApplication()->getInput();
        $id         = $input->get('id', '', 'int');
        $teachers   = [];
        $teacherIDs = [];
        $t          = $params->get('teachertemplateid');

        if (!$t) {
            $t = $input->get('t', 1, 'int');
        }

        if ($params->get('listteachers', '0')) {
            $teacherIDs = $params->get('listteachers');
        }

        if (!empty($teacherIDs)) {
            $database = Factory::getContainer()->get('DatabaseDriver');
            $query    = $database->getQuery(true);
            $query->select('*')
                ->from('#__bsms_teachers')
                ->where('id IN (' . implode(',', array_map('intval', $teacherIDs)) . ')');
            $database->setQuery($query);
            $results = $database->loadObjectList();

            foreach ($results as $result) {
                // Check to see if com_contact used instead
                if ($result->contact) {
                    $contactmodel = new ContactModel();
                    $contact      = $contactmodel->getItem($pk = $result->contact);

                    // Substitute contact info from com_contacts for duplicate fields
                    $result->title       = $contact->con_position;
                    $result->teachername = $contact->name;
                }

                if ($result->teacher_thumbnail) {
                    $image = $result->teacher_thumbnail;
                } else {
                    $image = $result->thumb;
                }

                if ($result->title) {
                    $teachername = $result->title . ' ' . $result->teachername;
                } else {
                    $teachername = $result->teachername;
                }

                $teachers[] = ['name' => $teachername, 'image' => $image, 't' => $t, 'id' => $result->id];
            }
        }

        return $teachers;
    }

    /**
     * Get a Teacher
     *
     * @param   Registry  $params  Item Params
     * @param   int       $id      Item ID
     *
     * @return string
     *
     * @throws \Exception
     * @todo       need to redo to bootstrap
     * @since      8.0.0
     * @deprecated 10.0.0 Use Cwmlisting::getFluidListing() instead
     */
    public function getTeacher($params, $id): string
    {
        $input = Factory::getApplication()->getInput();
        $t     = (int)$params->get('teachertemplateid');

        if (!$t) {
            $t = $input->get('t', 1, 'int');
        }

        $viewtype   = $input->get('view');
        $teacherids = [];

        if ($viewtype === 'sermons') {
            $listteachers = $params->get('listteachers');
            if ($listteachers) {
                $teacherids = array_map('intval', explode(",", $listteachers));
            }
        } elseif ($viewtype === 'sermon' && (int)$id !== 0) {
            $teacherids = [(int)$id];
        }

        $teacher = '<table class="table" id="teacher"><tr>';

        if (empty($teacherids)) {
            return $teacher . '</tr></table>';
        }

        // Fetch all teachers in one query
        $database = Factory::getContainer()->get('DatabaseDriver');
        $query    = $database->getQuery(true);
        $query->select('*')
            ->from('#__bsms_teachers')
            ->where('id IN (' . implode(',', $teacherids) . ')');
        $database->setQuery($query);
        $results = $database->loadObjectList('id');

        foreach ($teacherids as $teacherId) {
            if (!isset($results[$teacherId])) {
                continue;
            }
            $tresult = $results[$teacherId];

            // Check to see if there is a teacher image, if not, skip this step
            $image = Cwmimages::getTeacherThumbnail($tresult->teacher_thumbnail, $tresult->thumb);

            if (!$image) {
                $image->path   = '';
                $image->width  = 0;
                $image->height = 0;
            }

            $teacher .= '<td><table class="table cellspacing"><tr><td><img src="' . $image->path . '" width="' . $image->width
                . '" height="' . $image->height . '" alt="" /></td></tr>';

            $teacher .= '<tr><td>';

            if ($params->get('teacherlink') > 0) {
                $teacher .= '<a href="'
                    . Route::_(
                        'index.php?option=com_proclaim&amp;view=cwmteacher&amp;id='
                        . $tresult->id . '&amp;t=' . $t
                    ) . '">';
            }

            $teacher .= $tresult->teachername;

            if ($params->get('teacherlink') > 0) {
                $teacher .= '</a>';
            }

            $teacher .= '</td></tr></table></td>';
        }

        if ($viewtype === 'sermons' && (int)$params->get('intro_show') === 2) {
            $teacher .= '<td><div id="listintrodiv"><table class="table" id="listintrotable"><tr><td><p>';
            $teacher .= $params->get('list_intro') . '</p></td></tr></table> </div></td>';
        }

        $teacher .= '</tr></table>';

        return $teacher;
    }

    /**
     * Get TeacherList Exp
     *
     * @param   object  $row       Table info
     * @param   object  $params    Item Params
     * @param   int     $template  Template ID
     *
     * @return string
     *
     * @since      8.0.0
     * @deprecated 10.0.0 Use Cwmlisting::getFluidListing() instead
     */
    public function getTeacherListExp($row, $params, $template): string
    {
        $label = (string) $params->get('teacher_templatecode');
        $extra = [
            'url' => Route::_(
                'index.php?option=com_proclaim&amp;view=cwmteacherdisplay&amp;id=' .
                $row->id . '&amp;t=' . (int) $template
            ),
        ];

        return $this->replaceTeacherPlaceholders($row, $label, $extra);
    }

    /**
     * Get Teacher Details Exp
     *
     * @param   object    $row     Table Row
     * @param   Registry  $params  Item Params
     *
     * @return string
     *
     * @since      8.0.0
     * @deprecated 10.0.0 Use Cwmlisting::getFluidListing() instead
     */
    public function getTeacherDetailsExp($row, $params): string
    {
        $label = (string) $params->get('teacher_detailtemplate');

        return $this->replaceTeacherPlaceholders($row, $label);
    }

    /**
     * Replace common teacher placeholders in a template string
     *
     * @param   object  $row    Teacher row data
     * @param   string  $label  Template string with placeholders
     * @param   array   $extra  Additional replacements specific to the caller
     *
     * @return string
     *
     * @since   10.0.0
     */
    private function replaceTeacherPlaceholders(object $row, string $label, array $extra = []): string
    {
        $imageLarge = Cwmimages::getTeacherThumbnail($row->teacher_image ?? null, $row->image ?? null);
        $imageSmall = Cwmimages::getTeacherThumbnail($row->teacher_thumbnail ?? null, $row->thumb ?? null);

        $replacements = [
            'teacher'     => $row->teachername ?? '',
            'title'       => $row->title ?? '',
            'phone'       => $row->phone ?? '',
            'website'     => '<a href="' . ($row->website ?? '') . '">Website</a>',
            'information' => $row->information ?? '',
            'image'       => '<img src="' . $imageLarge->path . '" width="' . $imageLarge->width .
                             '" height="' . $imageLarge->height . '" />',
            'short'     => $row->short ?? '',
            'thumbnail' => '<img src="' . $imageSmall->path . '" width="' . $imageSmall->width .
                             '" height="' . $imageSmall->height . '" />',
        ];

        $replacements = array_merge($replacements, $extra);

        return preg_replace_callback('/{{(\w+)}}/', function ($matches) use ($replacements) {
            return $replacements[$matches[1]] ?? $matches[0];
        }, $label);
    }

    /**
     * Get Teacher Studies Exp
     *
     * @param   int       $id      Item ID
     * @param   Registry  $params  Item Params
     *
     * @return string
     *
     * @throws \Exception
     * @since      8.0.0
     * @deprecated 10.0.0 Use Cwmlisting::getFluidListing() instead
     */
    public function getTeacherStudiesExp(int $id, Registry $params): string
    {
        $input   = Factory::getApplication()->getInput();
        $nolimit = $input->get('nolimit', 0, 'int');

        // Determine effective limit (use the smaller of the two configured limits)
        $detailLimit  = (int) $params->get('series_detail_limit', 0);
        $studiesLimit = (int) $params->get('studies', 10);

        if ($nolimit === 1) {
            $limit = 0;
        } elseif ($detailLimit > 0 && $studiesLimit > 0) {
            $limit = min($detailLimit, $studiesLimit);
        } else {
            $limit = $detailLimit ?: $studiesLimit;
        }

        $db     = Factory::getContainer()->get('DatabaseDriver');
        $user   = Factory::getApplication()->getIdentity();
        $groups = implode(',', $user->getAuthorisedViewLevels());

        $query = $db->getQuery(true);
        $query->select(
            '#__bsms_studies.*, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername,'
            . ' #__bsms_series.id AS sid, #__bsms_series.series_text, #__bsms_message_type.id AS mid,'
            . ' #__bsms_message_type.message_type AS message_type, #__bsms_books.bookname,'
            . ' group_concat(#__bsms_topics.id separator ", ") AS tp_id,'
            . ' group_concat(#__bsms_topics.topic_text separator ", ") as topic_text'
        )
            ->from('#__bsms_studies')
            ->leftJoin('#__bsms_studytopics ON (#__bsms_studies.id = #__bsms_studytopics.study_id)')
            ->leftJoin('#__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)')
            ->leftJoin('#__bsms_teachers ON (#__bsms_studies.teacher_id = #__bsms_teachers.id)')
            ->leftJoin('#__bsms_series ON (#__bsms_studies.series_id = #__bsms_series.id)')
            ->leftJoin('#__bsms_message_type ON (#__bsms_studies.messagetype = #__bsms_message_type.id)')
            ->leftJoin('#__bsms_topics ON (#__bsms_topics.id = #__bsms_studytopics.topic_id)')
            ->where('#__bsms_teachers.id = ' . $id)
            ->where('#__bsms_studies.published = 1')
            ->where('#__bsms_studies.access IN (' . $groups . ')')
            ->group('#__bsms_studies.id')
            ->order('studydate desc');

        $db->setQuery($query, 0, $limit);
        $items = $db->loadObjectList() ?: [];

        $studies = $this->getWrapOpen((string) $params->get('wrapcode'), 'bsms_studytable');

        foreach ($items as $row) {
            $studies .= $this->getListingExp($row, $params, $params->get('studieslisttemplateid'));
        }

        $studies .= $this->getWrapClose((string) $params->get('wrapcode'));

        return $studies;
    }

    /**
     * Get opening wrapper HTML based on wrapcode
     *
     * @param   string  $wrapcode  Wrap code (0, T, or D)
     * @param   string  $id        Optional ID for the element
     *
     * @return string Opening wrapper HTML
     *
     * @since 10.0.0
     */
    private function getWrapOpen(string $wrapcode, string $id = ''): string
    {
        return match ($wrapcode) {
            'T'     => '<table class="table" id="' . $id . '" width="100%">',
            'D'     => '<div>',
            default => '',
        };
    }

    /**
     * Get closing wrapper HTML based on wrapcode
     *
     * @param   string  $wrapcode  Wrap code (0, T, or D)
     *
     * @return string Closing wrapper HTML
     *
     * @since 10.0.0
     */
    private function getWrapClose(string $wrapcode): string
    {
        return match ($wrapcode) {
            'T'     => '</table>',
            'D'     => '</div>',
            default => '',
        };
    }
}
