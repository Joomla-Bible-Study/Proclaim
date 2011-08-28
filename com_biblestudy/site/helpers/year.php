<?php

/**
 * @version $Id$
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

defined('_JEXEC') or die('Restriced Access');

function getYearsLandingPage($params, $id, $admin_params)
{
	$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'image.php');
	include_once($path1.'helper.php');
	$year = null;
	$teacherid = null;
	$template = $params->get('studieslisttemplateid');
	$limit = $params->get('landingyearslimit');
	if (!$limit) {
		$limit = 10000;
	}

	$year = "\n" . '<table id="landing_table" width="100%">';
	$db	=& JFactory::getDBO();
	$query = 'select distinct year(studydate) as theYear from #__bsms_studies order by year(studydate) desc';

	$db->setQuery($query);

	$tresult = $db->loadObjectList();
	$t = 0;
	$i = 0;

	$year .= "\n\t" . '<tr>';
	$showdiv = 0;
	foreach ($tresult as &$b) {
		 
		if ($t >= $limit)
		{
			if ($showdiv < 1)
			{
				if ($i == 1) {
					$year .= "\n\t\t" . '<td  id="landing_td"></td>' . "\n\t\t" . '<td id="landing_td"></td>';
					$year .= "\n\t" . '</tr>';
				};
				if ($i == 2) {
					$year .= "\n\t\t" . '<td  id="landing_td"></td>';
					$year .= "\n\t" . '</tr>';
				};
					
				$year .= "\n" .'</table>';
				$year .= "\n\t" . '<div id="showhideyears" style="display:none;"> <!-- start show/hide years div-->';
				$year .= "\n" .'<table width = "100%" id="landing_table">';

				$i = 0;
				$showdiv = 1;
			}
		}

		if ($i == 0) {
			$year .= "\n\t" . '<tr>';
		}
		$year .= "\n\t\t" . '<td id="landing_td">';

		    $year .= '<a href="index.php?option=com_biblestudy&view=studieslist&filter_year='.$b->theYear.'&filter_teacher=0&filter_series=0&filter_topic=0&filter_location=0&filter_book=0&filter_messagetype=0&t='.$template.'">';
		    
		    $year .= $numRows;
		    $year .= $b->theYear;
    		
            $year .='</a>';
            
            $year .= '</td>';
            $i++;
            $t++;
            if ($i == 3) {
                $year .= "\n\t" . '</tr>';
                $i = 0;
            }
            
        }
        if ($i == 1) {
            $year .= "\n\t\t" . '<td  id="landing_td"></td>' . "\n\t\t" . '<td id="landing_td"></td>';
        };
        if ($i == 2) {
            $year .= "\n\t\t" . '<td  id="landing_td"></td>';
        };
        
        $year .= "\n". '</table>' ."\n";

		$year .= $numRows;
		$year .= $b->theYear;

		$year .='</a>';

		$year .= '</td>';
		$i++;
		$t++; //dump ($t, 't: ');
		if ($i == 3) {
			$year .= "\n\t" . '</tr>';
			$i = 0;
		}

	if ($i == 1) {
		$year .= "\n\t\t" . '<td  id="landing_td"></td>' . "\n\t\t" . '<td id="landing_td"></td>';
	};
	if ($i == 2) {
		$year .= "\n\t\t" . '<td  id="landing_td"></td>';
	};

	$year .= "\n". '</table>' ."\n";

	if ($showdiv == 1)
	{

		$year .= "\n\t". '</div> <!-- close show/hide years div-->';
		$showdiv = 2;
	}
	$year .= '<div id="landing_separator"></div>';

	return $year;
}
