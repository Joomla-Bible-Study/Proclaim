<?php

/**
 * Teacher Helper
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.images.class.php');
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.admin.class.php');

/**
 * Get Teacher
 * @param object $params
 * @param int $id
 * @param object $admin_params
 * @return string
 */
function getTeacher($params, $id, $admin_params) {
    $mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');
    $path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
    include_once($path1 . 'image.php');
    $teacher = null;
    $teacherid = null;
    $teacherids = new stdClass();
    $t = $params->get('teachertemplateid');
    if (!$t) {
        $t = JRequest::getVar('t', 1, 'get', 'int');
    }
    $viewtype = JRequest::getVar('view');
    if ($viewtype == 'sermons') {
        $teacherid = $params->get('listteachers');
        $teacherids = explode(",", $params->get('listteachers'));
    }
    if ($viewtype == 'sermon' && $id) {
        $teacherids->id = $id;
    }
    $teacher = '<table id = "teacher"><tr>';
    if (!isset($teacherids)) {
        return $teacher;
    }
    foreach ($teacherids as $teachers) {
        $database = JFactory::getDBO();
        $query = 'SELECT * FROM #__bsms_teachers' .
                '  WHERE id = ' . $teachers;
        $database->setQuery($query);
        $tresult = $database->loadObject();
        $i_path = null;
        //Check to see if there is a teacher image, if not, skip this step
        $images = new jbsImages();
        $image = $images->getTeacherThumbnail($tresult->teacher_thumbnail, $tresult->thumb);

        if (!$image) {
            $image->path = '';
            $image->width = 0;
            $image->height = 0;
        }
        $teacher .= '<td><table cellspacing ="0"><tr><td><img src="' . $image->path . '" border="1" width="' . $image->width . '" height="' . $image->height . '" ></td></tr>';

        $teacher .= '<tr><td>';
        if ($params->get('teacherlink') > 0) {
            $teacher .= '<a href="' . JRoute::_('index.php?option=com_biblestudy&amp;view=teacher&amp;id=' . $tresult->id . '&amp;t=' . $t) . '">';
        }
        $teacher .= $tresult->teachername;
        if ($params->get('teacherlink') > 0) {
            $teacher .='</a>';
        }
        $teacher .= '</td></tr></table></td>';
    }
    if ($params->get('intro_show') == 2 && $viewtype == 'sermons') {
        $teacher .= '<td><div id="listintro"><table id="listintro"><tr><td><p>' . $params->get('list_intro') . '</p></td></tr></table> </div></td>';
    }
    $teacher .= '</tr></table>';

    return $teacher;
}

/**
 * Get Teacher for LandingPage
 * @param objcet $params
 * @param int $id
 * @param object $admin_params
 * @return string
 */
function getTeacherLandingPage($params, $id, $admin_params) {
    $mainframe = JFactory::getApplication();
    $db = JFactory::getDBO();
    $user = JFactory::getUser();
    $option = JRequest::getCmd('option');
    $path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
    include_once($path1 . 'image.php');
    include_once($path1 . 'helper.php');

    $teacher = null;
    $teacherid = null;

    $template = $params->get('teachertemplateid', 1);
    $limit = $params->get('landingteacherslimit', 10000);
    $teacheruselimit = $params->get('landingteachersuselimit', 0);
    $menu = $mainframe->getMenu();
    $item = $menu->getActive();
    $registry = new JRegistry;
    if (isset($item->params)) {
        $registry->loadJSON($item->params);
        $m_params = $registry;
        $menu_order = $m_params->get('teachers_order');
    } else {
        $menu_order = null;
    }
    if ($menu_order) {
        switch ($menu_order) {
            case 2:
                $order = 'ASC';
                break;
            case 1:
                $order = 'DESC';
                break;
        }
    } else {
        $order = $params->get('landing_default_order', 'ASC');
    }
    // Compute view access permissions.
    $groups = implode(',', $user->getAuthorisedViewLevels());

    $query = $db->getQuery(true);
    $query->select('distinct a.*')
            ->from('#__bsms_teachers a')
            ->select('b.access')
            ->innerJoin('#__bsms_studies b on a.id = b.teacher_id')
            ->where('a.list_show = 1 and a.published = 1')
            ->where('b.access IN (' . $groups . ')')
            ->where('a.landing_show > 0')
            ->order('a.ordering, a.teachername ' . $order);

    $db->setQuery($query);

    $tresult = $db->loadObjectList();
    $count = count($tresult);
    $t = 0;
    $i = 0;
    $c = 0;

    $teacher = "\n" . '<table class="landing_table" width="100%"><tr>';
    $showdiv = 0;
    switch ($teacheruselimit) {
        case 0:
            foreach ($tresult as $b) {


                if ($t >= $limit) {
                    if ($showdiv < 1) {
                        if ($i == 1) {
                            $teacher .= "\n\t\t" . '<td  class="landing_td"></td>' . "\n\t\t" . '<td class="landing_td"></td>';
                            $teacher .= "\n\t" . '</tr>';
                        };
                        if ($i == 2) {
                            $teacher .= "\n\t\t" . '<td  class="landing_td"></td>';
                            $teacher .= "\n\t" . '</tr>';
                        };

                        $teacher .= "\n" . '</table>';
                        $teacher .= "\n\t" . '<div class="showhideteachers" style="display:none;"> <!-- start show/hide teacher div-->';
                        $teacher .= "\n" . '<table width = "100%" class="landing_table">';

                        $i = 0;
                        $showdiv = 1;
                    }
                }
                $teacher .= "\n\t\t" . '<td class="landing_td">';

                if ($params->get('linkto') == 0) {
                    $teacher .= '<a href="' . JRoute::_('index.php?option=com_biblestudy&amp;view=sermons&amp;t=' . $template) . '&amp;filter_teacher=' . $b->id . '&amp;filter_book=0&amp;filter_series=0&amp;filter_topic=0&amp;filter_location=0&amp;filter_year=0&amp;filter_messagetype=0">';
                } else {

                    $teacher .= '<a href="' . JRoute::_('index.php?option=com_biblestudy&amp;view=teacher&id=' . $b->id . '&t=' . $template) . '">';
                };
                $teacher .= $b->teachername;

                $teacher .='</a>';

                $teacher .= '</td>';
                $i++;
                $t++;
                $c++;
                if ($i == 3 && $count != $c) {
                    $teacher .= "\n\t" . '</tr><tr>';
                    $i = 0;
                } elseif($i == 3) {
                    $teacher .= "\n\t" . '</tr>';
                    $i = 0;
                }
            }
            if ($i == 1) {
                $teacher .= "\n\t\t" . '<td  class="landing_td"></td>' . "\n\t\t" . '<td class="landing_td"></td>';
            };
            if ($i == 2) {
                $teacher .= "\n\t\t" . '<td  class="landing_td"></td>';
            };

            $teacher .= "\n" . '</tr></table>' . "\n";

            if ($showdiv == 1) {

                $teacher .= "\n\t" . '</div> <!-- close show/hide teacher div-->';
                $showdiv = 2;
            }
            $teacher .= '<div class="landing_separator"></div>';

            $teacher .= '</div>';
            break;

        case 1:

            $teacher = '<div class="landingtable" style="display:inline;">';
            foreach ($tresult as $b) {
                if ($b->landing_show == 1) {
                    $teacher .= '<div class="landingrow">';
                    if ($params->get('linkto') == 0) {
                        $teacher .= '<div class="landingcell"><a class="landinglink="' . JRoute::_('index.php?option=com_biblestudy&amp;view=sermons&amp;t=' . $template) . '&amp;filter_teacher=' . $b->id . '&amp;filter_book=0&amp;filter_series=0&amp;filter_topic=0&amp;filter_location=0&amp;filter_year=0&amp;filter_messagetype=0">';
                    } else {

                        $teacher .= '<div class="landingcell"><a class="landinglink" href="' . JRoute::_('index.php?option=com_biblestudy&amp;view=teacher&amp;id=' . $b->id . '&amp;t=' . $template) . '">';
                    };
                    $teacher .= $b->teachername;

                    $teacher .='</div></div></a>';
                }
            }
            $teacher .= '</div>';
            $teacher .= '<div id="showhideteachers" style="display:none;">';
            foreach ($tresult as $b) {
                if ($b->landing_show == 2) {
                    $teacher .= '<div class="landingrow">';
                    if ($params->get('linkto') == 0) {
                        $teacher .= '<div class="landingcell"><a class="landinglink" href="' . JRoute::_('index.php?option=com_biblestudy&amp;view=sermons&amp;t=' . $template) . '&amp;filter_teacher=' . $b->id . '&amp;filter_book=0&amp;filter_series=0&amp;filter_topic=0&amp;filter_location=0&amp;filter_year=0&amp;filter_messagetype=0">';
                    } else {

                        $teacher .= '<div class="landingcell"><a class="landinglink" href="' . JRoute::_('index.php?option=com_biblestudy&amp;view=teacher&amp;id=' . $b->id . '&amp;t=' . $template) . '">';
                    };
                    $teacher .= $b->teachername;

                    $teacher .='</a></div></div>';
                }
            }

            $teacher .= '</div>';
            $teacher .= '<div class="landing_separator"></div>';
            break;
    }

    return $teacher;
}

/**
 * Get TeacherList Exp
 * @param object $row
 * @param object $params
 * @param string $oddeven
 * @param object $admin_params
 * @param object $template
 * @return object
 */
function getTeacherListExp($row, $params, $oddeven, $admin_params, $template) {
    $path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
    include_once($path1 . 'elements.php');
    include_once($path1 . 'scripture.php');
    include_once($path1 . 'custom.php');
    include_once($path1 . 'image.php');
    $images = new jbsImages();
    $imagelarge = $images->getTeacherThumbnail($row->teacher_image, $row->image);

    $imagesmall = $images->getTeacherThumbnail($row->teacher_thumbnail, $row->thumb);

    $label = $params->get('teacher_templatecode');
    $label = str_replace('{{teacher}}', $row->teachername, $label);
    $label = str_replace('{{title}}', $row->title, $label);
    $label = str_replace('{{phone}}', $row->phone, $label);
    $label = str_replace('{{website}}', '<A href="' . $row->website . '">Website</a>', $label);
    $label = str_replace('{{information}}', $row->information, $label);
    $label = str_replace('{{image}}', '<img src="' . $imagelarge->path . '" width="' . $imagelarge->width . '" height="' . $imagelarge->height . '" />', $label);
    $label = str_replace('{{short}}', $row->short, $label);
    $label = str_replace('{{thumbnail}}', '<img src="' . $imagesmall->path . '" width="' . $imagesmall->width . '" height="' . $imagesmall->height . '" />', $label);
    $label = str_replace('{{url}}', JRoute::_('index.php?option=com_biblestudy&amp;view=teacherdisplay&amp;id=' . $row->id . '&amp;t=' . $template), $label);
    return $label;
}

/**
 * Get Teacher Details Exp
 * @param object $row
 * @param object $params
 * @param object $template
 * @param object $admin_params
 * @return object
 */
function getTeacherDetailsExp($row, $params, $template, $admin_params) {
    $path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
    include_once($path1 . 'elements.php');
    include_once($path1 . 'scripture.php');
    include_once($path1 . 'custom.php');
    include_once($path1 . 'image.php');


    //Get the image folders and images
    $images = new jbsImages();
    $imagelarge = $images->getTeacherThumbnail($row->teacher_image, $row->image);

    $imagesmall = $images->getTeacherThumbnail($row->teacher_thumbnail, $row->thumb);


    $label = $params->get('teacher_detailtemplate');
    $label = str_replace('{{teacher}}', $row->teachername, $label);
    $label = str_replace('{{title}}', $row->title, $label);
    $label = str_replace('{{phone}}', $row->phone, $label);
    $label = str_replace('{{website}}', '<A href="' . $row->website . '">Website</a>', $label);
    $label = str_replace('{{information}}', $row->information, $label);
    $label = str_replace('{{image}}', '<img src="' . $imagelarge->path . '" width="' . $imagelarge->width . '" height="' . $imagelarge->height . '" />', $label);
    $label = str_replace('{{short}}', $row->short, $label);
    $label = str_replace('{{thumbnail}}', '<img src="' . $imagesmall->path . '" width="' . $imagesmall->width . '" height="' . $imagesmall->height . '" />', $label);
    // $label = str_replace('{{url}}', JRoute::_('index.php?option=com_biblestudy&amp;view=teacherdisplay&amp;id='.$row->id .'&amp;t='.$template), $label);
    return $label;
}

/**
 * Get Teacher Studies Exp
 * @param int $id
 * @param object $params
 * @param object $admin_params
 * @param object $template
 * @return string
 */
function getTeacherStudiesExp($id, $params, $admin_params, $template) {

    $path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;

    include_once($path1 . 'listing.php');

    $path2 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR;


    $limit = '';
    $nolimit = JRequest::getVar('nolimit', 'int', 0);

    if ($params->get('series_detail_limit')) {
        $limit = ' LIMIT ' . $params->get('series_detail_limit');
    }
    if ($nolimit == 1) {
        $limit = '';
    }
    $db = JFactory::getDBO();
    $query = 'SELECT s.series_id FROM #__bsms_studies AS s WHERE s.published = 1 AND s.series_id = ' . $id;
    $db->setQuery($query);
    $allrows = $db->loadObjectList();
    $rows = $db->getAffectedRows();

    $query = 'SELECT #__bsms_studies.*, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername,'
            . ' #__bsms_series.id AS sid, #__bsms_series.series_text, #__bsms_message_type.id AS mid,'
            . ' #__bsms_message_type.message_type AS message_type, #__bsms_books.bookname,'
            . ' group_concat(#__bsms_topics.id separator ", ") AS tp_id, group_concat(#__bsms_topics.topic_text separator ", ") as topic_text'
            . ' FROM #__bsms_studies'
            . ' left join #__bsms_studytopics ON (#__bsms_studies.id = #__bsms_studytopics.study_id)'
            . ' LEFT JOIN #__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)'
            . ' LEFT JOIN #__bsms_teachers ON (#__bsms_studies.teacher_id = #__bsms_teachers.id)'
            . ' LEFT JOIN #__bsms_series ON (#__bsms_studies.series_id = #__bsms_series.id)'
            . ' LEFT JOIN #__bsms_message_type ON (#__bsms_studies.messagetype = #__bsms_message_type.id)'
            . ' LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studytopics.topic_id)'
            . ' where #__bsms_teachers.id = ' . $id . ' AND #__bsms_studies.published = 1 '
            . ' GROUP BY #__bsms_studies.id'
            . ' order by studydate desc'
            . $limit;

    $db->setQuery($query);
    $items = $db->loadObjectList();

    //check permissions for this view by running through the records and removing those the user doesn't have permission to see


    $user = JFactory::getUser();
    $groups = $user->getAuthorisedViewLevels();
    $count = count($items);

    for ($i = 0; $i < $count; $i++) {

        if ($items[$i]->access > 1) {
            if (!in_array($items[$i]->access, $groups)) {
                unset($items[$i]);
            }
        }
    }

    $studieslimit = $params->get('studies', 10);

    $studies = '';

    switch ($params->get('wrapcode')) {
        case '0':
            //Do Nothing
            break;
        case 'T':
            //Table
            $studies .= '<table id="bsms_studytable" width="100%">';
            break;
        case 'D':
            //DIV
            $studies .= '<div>';
            break;
    }

    $params->get('headercode');
    $j = 0;
    foreach ($items AS $row) {
        if ($j > $studieslimit) {
            break;
        }
        $studies .= getListingExp($row, $params, $admin_params, $params->get('studieslisttemplateid'));
        $j++;
    }

    switch ($params->get('wrapcode')) {
        case '0':
            //Do Nothing
            break;
        case 'T':
            //Table
            $studies .= '</table>';
            break;
        case 'D':
            //DIV
            $studies .= '</div>';
            break;
    }
    return $studies;
}