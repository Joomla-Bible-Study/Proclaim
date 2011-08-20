<?php 

/**
 * @version $Id: location.php 1 $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

defined('_JEXEC') or die('Restriced Access');

function getLocationsLandingPage($params, $id, $admin_params)
{
	$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'image.php');
	include_once($path1.'helper.php');
	$location = null;
	$teacherid = null;
	$template = $params->get('studieslisttemplateid',1);
	$limit = $params->get('landinglocationslimit');
	if (!$limit) {
		$limit = 10000;
	}
	if (!$t) {
		$t = JRequest::getVar('t',1,'get','int');
	}

	$location = "\n" . '<table id="landing_table" width=100%>';
	$db	=& JFactory::getDBO();
	$query = 'select distinct a.* from #__bsms_locations a inner join #__bsms_studies b on a.id = b.location_id';

	$db->setQuery($query);

	$tresult = $db->loadObjectList();
	$t = 0;
	$i = 0;

	$location .= "\n\t" . '<tr>';
	$showdiv = 0;
	foreach ($tresult as &$b) {

		if ($t >= $limit)
		{
			if ($showdiv < 1)
			{
				if ($i == 1) {
					$location .= "\n\t\t" . '<td  id="landing_td"></td>' . "\n\t\t" . '<td id="landing_td"></td>';
					$location .= "\n\t" . '</tr>';
				};
				if ($i == 2) {
					$location .= "\n\t\t" . '<td  id="landing_td"></td>';
					$location .= "\n\t" . '</tr>';
				};

				$location .= "\n" .'</table>';
				$location .= "\n\t" . '<div id="showhidelocations" style="display:none;"> <!-- start show/hide locations div-->';
				$location .= "\n" . '<table width = "100%" id="landing_table">';
					
				$i = 0;
				$showdiv = 1;
			}
		}   
            if ($i == 0) {
                $location .= "\n\t" . '<tr>';
            }
            $location .= "\n\t\t" . '<td id="landing_td">';
		    $location .= '<a href="index.php?option=com_biblestudy&view=studieslist&filter_location='.$b->id.'&filter_teacher=0&filter_series=0&filter_topic=0&filter_book=0&filter_year=0&filter_messagetype=0&t='.$template.'">';
		    
		    $location .= $b->location_text;
    		
            $location .='</a>';
            
            $location .= '</td>';
            $i++;
            $t++;
            if ($i == 3) {
                $location .= "\n\t" . '</tr>';
                $i = 0;
            }
        }
        if ($i == 1) {
            $location .= "\n\t\t" . '<td  id="landing_td"></td>' . "\n\t\t" . '<td id="landing_td"></td>';
        };
        if ($i == 2) {
            $location .= "\n\t\t" . '<td  id="landing_td"></td>';
        };

		$location .= $b->location_text;

		$location .='</a>';

		$location .= '</td>';
		$i++;
		$t++; //dump ($t, 't: ');
		if ($i == 3) {
			$location .= "\n\t" . '</tr>';
			$i = 0;
		}
	if ($i == 1) {
		$location .= "\n\t\t" . '<td  id="landing_td"></td>' . "\n\t\t" . '<td id="landing_td"></td>';
	};
	if ($i == 2) {
		$location .= "\n\t\t" . '<td  id="landing_td"></td>';
	};

	$location .= "\n". '</table>' ."\n";

	if ($showdiv == 1)
	{

		$location .= "\n\t". '</div> <!-- close show/hide locations div-->';
		$showdiv = 2;
	}
	$location .= '<div id="landing_separator"></div>';

	return $location;
}
