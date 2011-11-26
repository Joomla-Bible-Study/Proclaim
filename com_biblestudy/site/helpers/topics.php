<?php

/**
 * @version $Id$
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

defined('_JEXEC') or die('Restriced Access');

function getTopicsLandingPage($params, $id, $admin_params)
{
	$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
	$path1 = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_biblestudy'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR;
	include_once($path1.'image.php');
	include_once($path1.'helper.php');
	$topic = null;
	$teacherid = null;
	$template = $params->get('studieslisttemplateid');
	$limit = $params->get('landingtopicslimit');
	if (!$limit) {$limit = 10000;}
	if (!$t) {$t = JRequest::getVar('t',1,'get','int');}

		$topic = "\n" . '<table id="landing_table" width=100%>';
		$db	=& JFactory::getDBO();
		$query = 'SELECT DISTINCT #__bsms_topics.id, #__bsms_topics.topic_text, #__bsms_topics.params AS topic_params '
				. 'FROM #__bsms_studies '
				. 'LEFT JOIN #__bsms_studytopics ON (#__bsms_studies.id = #__bsms_studytopics.study_id) '
				. 'LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studytopics.topic_id) '
				. 'WHERE #__bsms_topics.published = 1 '
				. 'ORDER BY #__bsms_topics.topic_text ASC';
		$db->setQuery($query);
		
        $tresult = $db->loadObjectList();
        $t = 0;
        $i = 0;
        
        $topic .= "\n\t" . '<tr>';
        $showdiv = 0;
        foreach ($tresult as &$b) {
            
            if ($t >= $limit)
		{
			if ($showdiv < 1)
			{
				if ($i == 1) {
					$topic .= "\n\t\t" . '<td  id="landing_td"></td>' . "\n\t\t" . '<td id="landing_td"></td>';
					$topic .= "\n\t" . '</tr>';
				};
				if ($i == 2) {
					$topic .= "\n\t\t" . '<td  id="landing_td"></td>';
					$topic .= "\n\t" . '</tr>';
				};

				 
				$topic .= "\n" .'</table>';
				$topic .= "\n\t" . '<div id="showhidetopics" style="display:none;"> <!-- start show/hide topics div-->';
				$topic .= "\n" . '<table width = "100%" id="landing_table">';

				$i = 0;
				$showdiv = 1;
			}
		}
		
            if ($i == 0) {
                $topic .= "\n\t" . '<tr>';
            }
            $topic .= "\n\t\t" . '<td id="landing_td">';
		    $topic .= '<a href="index.php?option=com_biblestudy&view=studieslist&filter_topic='.$b->id.'&filter_teacher=0&filter_series=0&filter_location=0&filter_book=0&filter_year=0&filter_messagetype=0&t='.$template.'">';
		    
		    $topic .= getTopicItemTranslated($b);
    		
            $topic .='</a>';
            
            $topic .= '</td>';
            $i++;
            $t++; //dump ($t, 't: ');
            if ($i == 3) {
                $topic .= "\n\t" . '</tr>';
                $i = 0;
            }
        }
        if ($i == 1) {
            $topic .= "\n\t\t" . '<td  id="landing_td"></td>' . "\n\t\t" . '<td id="landing_td"></td>';
        };
        if ($i == 2) {
            $topic .= "\n\t\t" . '<td  id="landing_td"></td>';
        };

	$topic .= "\n". '</table>' ."\n";

	if ($showdiv == 1)
	{

		$topic .= "\n\t". '</div> <!-- close show/hide topics div-->';
		$showdiv = 2;
	}
	$topic .= '<div id="landing_separator"></div>';

	return $topic;
}
