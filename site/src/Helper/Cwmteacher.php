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

use CWM\Component\Proclaim\Administrator\Table\CwmtemplateTable;
use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;
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
     * @throws Exception
     * @since    8.0.0
     */
    public function getTeachersFluid($params): array
    {
        $input      = Factory::getApplication()->input;
        $id         = $input->get('id', '', 'int');
        $teachers   = array();
        $teacherIDs = [];
        $t          = $params->get('teachertemplateid');

        if (!$t) {
            $t = $input->get('t', 1, 'int');
        }

        if ($params->get('listteachers', '0')) {
            $teacherIDs = $params->get('listteachers');
        }

        foreach ($teacherIDs as $teach) {
            $database = Factory::getContainer()->get('DatabaseDriver');
            $query    = $database->getQuery(true);
            $query->select('*')->from('#__bsms_teachers')->where('id = ' . $teach);
            $database->setQuery($query);
            $result = $database->loadObject();

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

            $teachers[] = array('name' => $teachername, 'image' => $image, 't' => $t, 'id' => $result->id);
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
     * @throws Exception
     * @todo     need to redo to bootstrap
     * @since    8.0.0
     */
    public function getTeacher($params, $id): string
    {
        $input       = Factory::getApplication()->input;
        $htmlView = new HtmlView();
        $htmlView->loadHelper('image');
        $teacherids = new \stdClass();
        $t          = (int)$params->get('teachertemplateid');

        if (!$t) {
            $t = $input->get('t', 1, 'int');
        }

        $viewtype = $input->get('view');

        if ($viewtype === 'sermons') {
            $teacherids = explode(",", $params->get('listteachers'));
        }

        if ($viewtype === 'sermon' && (int)$id !== 0) {
            $teacherids->id = $id;
        }

        $teacher = '<table class="table" id="teacher"><tr>';

        if (!isset($teacherids)) {
            return $teacher;
        }

        foreach ($teacherids as $teachers) {
            $database = Factory::getContainer()->get('DatabaseDriver');
            $query    = $database->getQuery(true);
            $query->select('*')->from('#__bsms_teachers')->where('id = ' . $teachers);
            $database->setQuery($query);
            $tresult = $database->loadObject();

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
     * @param   object            $row       Table info
     * @param   object            $params    Item Params
     * @param   CwmtemplateTable  $template  Template
     *
     * @return array|string|string[]
     *
     * @since    8.0.0
     */
    public function getTeacherListExp($row, $params, $template)
    {
        $htmlView = new HtmlView();
        $htmlView->loadHelper('image');
        $imagelarge = Cwmimages::getTeacherThumbnail($row->teacher_image, $row->image);

        $imagesmall = Cwmimages::getTeacherThumbnail($row->teacher_thumbnail, $row->thumb);

        $label = $params->get('teacher_templatecode');
        $label = str_replace('{{teacher}}', $row->teachername, $label);
        $label = str_replace('{{title}}', $row->title, $label);
        $label = str_replace('{{phone}}', $row->phone, $label);
        $label = str_replace('{{website}}', '<A href="' . $row->website . '">Website</a>', $label);
        $label = str_replace('{{information}}', $row->information, $label);
        $label = str_replace(
            '{{image}}',
            '<img src="' . $imagelarge->path . '" width="' . $imagelarge->width .
            '" height="' . $imagelarge->height . '" />',
            $label
        );
        $label = str_replace('{{short}}', $row->short, $label);
        $label = str_replace(
            '{{thumbnail}}',
            '<img src="' . $imagesmall->path . '" width="' . $imagesmall->width .
            '" height="' . $imagesmall->height . '" />',
            $label
        );

        return str_replace(
            '{{url}}',
            Route::_(
                'index.php?option=com_proclaim&amp;view=cwmteacherdisplay&amp;id=' .
                $row->id . '&amp;t=' . (int)$template
            ),
            $label
        );
    }

    /**
     * Get Teacher Details Exp
     *
     * @param   object    $row     Table Row
     * @param   Registry  $params  Item Params
     *
     * @return object
     *
     * @since    8.0.0
     */
    public function getTeacherDetailsExp($row, $params)
    {
        $htmlView = new HtmlView();
        $htmlView->loadHelper('image');

        // Get the image folders and images
        $imagelarge = Cwmimages::getTeacherThumbnail($row->teacher_image, $row->image);

        $imagesmall = Cwmimages::getTeacherThumbnail($row->teacher_thumbnail, $row->thumb);

        $label = $params->get('teacher_detailtemplate');
        $label = str_replace('{{teacher}}', $row->teachername, $label);
        $label = str_replace('{{title}}', $row->title, $label);
        $label = str_replace('{{phone}}', $row->phone, $label);
        $label = str_replace('{{website}}', '<A href="' . $row->website . '">Website</a>', $label);
        $label = str_replace('{{information}}', $row->information, $label);
        $label = str_replace(
            '{{image}}',
            '<img src="' . $imagelarge->path . '" width="' . $imagelarge->width . '" height="'
            . $imagelarge->height . '" />',
            $label
        );
        $label = str_replace('{{short}}', $row->short, $label);

        return str_replace(
            '{{thumbnail}}',
            '<img src="' . $imagesmall->path . '" width="' . $imagesmall->width . '" height="'
            . $imagesmall->height . '" />',
            $label
        );
    }

    /**
     * Get Teacher Studies Exp
     *
     * @param   int       $id      Item ID
     * @param   Registry  $params  Item Params
     *
     * @return string
     *
     * @throws Exception
     * @since    8.0.0
     */
    public function getTeacherStudiesExp($id, $params): string
    {
        $limit   = '';
        $input   = Factory::getApplication()->input;
        $nolimit = $input->get('nolimit', '', 'int');

        if ($params->get('series_detail_limit')) {
            $limit = $params->get('series_detail_limit');
        }

        if ($nolimit === 1) {
            $limit = '';
        }

        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select(
            '#__bsms_studies.*, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername,'
            . ' #__bsms_series.id AS sid, #__bsms_series.series_text, #__bsms_message_type.id AS mid,'
            . ' #__bsms_message_type.message_type AS message_type, #__bsms_books.bookname,'
            . ' group_concat(#__bsms_topics.id separator ", ") AS tp_id, group_concat(#__bsms_topics.topic_text separator ", ") as topic_text'
        )
            ->from('#__bsms_studies')
            ->leftJoin('#__bsms_studytopics ON (#__bsms_studies.id = #__bsms_studytopics.study_id)')
            ->leftJoin('#__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)')
            ->leftJoin('#__bsms_teachers ON (#__bsms_studies.teacher_id = #__bsms_teachers.id)')
            ->leftJoin('#__bsms_series ON (#__bsms_studies.series_id = #__bsms_series.id)')
            ->leftJoin('#__bsms_message_type ON (#__bsms_studies.messagetype = #__bsms_message_type.id)')
            ->leftJoin('#__bsms_topics ON (#__bsms_topics.id = #__bsms_studytopics.topic_id)')
            ->where('#__bsms_teachers.id = ' . $id)->where('#__bsms_studies.published = ' . 1)
            ->group('#__bsms_studies.id')
            ->order('studydate desc');
        $db->setQuery($query, 0, $limit);
        $items = $db->loadObjectList();

        // Check permissions for this view by running through the records and removing those the user doesn't have permission to see

        $user   = Factory::getApplication()->getIdentity();
        $groups = $user->getAuthorisedViewLevels();

        foreach ($items as $i => $iValue) {
            if (($iValue->access > 1) && !in_array($iValue->access, $groups, true)) {
                unset($items[$i]);
            }
        }

        $studieslimit = $params->get('studies', 10);

        $studies = '';

        switch ($params->get('wrapcode')) {
            case '0':
                // Do Nothing
                break;
            case 'T':
                // Table
                $studies .= '<table class="table" id="bsms_studytable" width="100%">';
                break;
            case 'D':
                // DIV
                $studies .= '<div>';
                break;
        }

        $params->get('headercode');
        $j = 0;

        foreach ($items as $row) {
            if ($j > $studieslimit) {
                break;
            }

            $studies .= $this->getListingExp($row, $params, $params->get('studieslisttemplateid'));
            $j++;
        }

        switch ($params->get('wrapcode')) {
            case '0':
                // Do Nothing
                break;
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
}
