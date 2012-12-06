<?php

/**
 * BibleStudy Stats Class
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.admin.class.php');
$path1 = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
include_once($path1 . 'helper.php');

/**
 * BibleStudy Stats Class
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class jbStats {

    /**
     * Top Score Site
     * @return string
     */
    function top_score_site() {
        $input = new JInput;
        $t = $input->get('t', 1, 'int');

        $admin_params = getAdminsettings();
        $limit = $admin_params->get('popular_limit', '25');
        $top = '<select onchange="goTo()" id="urlList"><option value="">' . JText::_('JBS_CMN_SELECT_POPULAR_STUDY') . '</option>';
        $final = array();
        $final2 = array();

        $db = JFactory::getDBO();
        $db->setQuery('SELECT m.study_id, s.access, s.published AS spub, sum(m.downloads + m.plays) as added FROM #__bsms_mediafiles AS m
		LEFT JOIN #__bsms_studies AS s ON (m.study_id = s.id)
			where m.published = 1 GROUP BY m.study_id');
        $format = $admin_params->get('format_popular', '0');

        // $db->query();

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

        foreach ($items as $result) {
            $db->setQuery('SELECT #__bsms_studies.studydate, #__bsms_studies.studytitle, #__bsms_studies.hits, #__bsms_studies.id,
            #__bsms_mediafiles.study_id from #__bsms_studies LEFT JOIN #__bsms_mediafiles ON (#__bsms_studies.id = #__bsms_mediafiles.study_id)
            WHERE #__bsms_mediafiles.study_id = ' . (int) $result->study_id);
            // $db->query();
            $hits = $db->loadObject();
            if (!$hits->studytitle) {
                $name = $hits->id;
            } else {
                $name = $hits->studytitle;
            }
            if ($format < 1) {
                $total = $result->added + $hits->hits;
            }
            else
                $total = $result->added;
            $selectvalue = JRoute::_('index.php?option=com_biblestudy&view=sermon&id=' . $hits->id . '&t=' . $t);
            $selectdisplay = $name . ' - ' . JText::_('JBS_CMN_SCORE') . ': ' . $total;
            $final2 = array('score' => $total, 'select' => $selectvalue, 'display' => $selectdisplay);
            $final[] = $final2;
        }
        rsort($final);
        array_splice($final, $limit);

        foreach ($final as $topscore) {

            $top .= '<option value="' . $topscore['select'] . '">' . $topscore['display'] . '</option>';
        }
        $top .= '</select>';
        return $top;
    }

}