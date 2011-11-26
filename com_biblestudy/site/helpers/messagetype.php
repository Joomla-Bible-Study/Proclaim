<?php

/**
 * @version $Id$
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

defined('_JEXEC') or die('Restriced Access');

function getMessageTypesLandingPage($params, $id, $admin_params)
{
	$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
	$path1 = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_biblestudy'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR;
	include_once($path1.'image.php');
	include_once($path1.'helper.php');
	$addItemid = '';
	$addItemid = getItemidLink($isplugin=0, $admin_params);
	$messagetype = null;
	$teacherid = null;
	$template = $params->get('studieslisttemplateid',1);
	$limit = $params->get('landingmessagetypeslimit');
	if (!$limit) {
		$limit = 10000;
	}

	if (!$t) {
		$t = JRequest::getVar('t',1,'get','int');
	}

	$messagetype = "\n" . '<table id="landing_table" width="100%">';
	$db	=& JFactory::getDBO();
	$query = 'select distinct a.* from #__bsms_message_type a inner join #__bsms_studies b on a.id = b.messagetype';

	$db->setQuery($query);

	$tresult = $db->loadObjectList();
	$t = 0;
	$i = 0;
	 
	$messagetype .= "\n\t" . '<tr>';
	$showdiv = 0;
	foreach ($tresult as &$b) {

		if ($t >= $limit)
		{
			if ($showdiv < 1)
			{
				if ($i == 1) {
					$messagetype .= "\n\t\t" . '<td  id="landing_td"></td>' . "\n\t\t" . '<td id="landing_td"></td>';
					$messagetype .= "\n\t" . '</tr>';
				};
				if ($i == 2) {
					$messagetype .= "\n\t\t" . '<td  id="landing_td"></td>';
					$messagetype .= "\n\t" . '</tr>';
				};
					
				$messagetype .= "\n" .'</table>';
				$messagetype .= "\n\t" . '<div id="showhidemessagetypes" style="display:none;"> <!-- start show/hide messagetype div-->';
				$messagetype .= "\n" .'<table width = "100%" id="landing_table">';

				$i = 0;
				$showdiv = 1;
			}
		}

		if ($i == 0) {
			$messagetype .= "\n\t" . '<tr>';
		}
		$messagetype .= "\n\t\t" . '<td id="landing_td">';

		$messagetype .= '<a href="index.php?option=com_biblestudy&view=studieslist&filter_messagetype='.$b->id.'&filter_book=0&filter_teacher=0&filter_series=0&filter_topic=0&filter_location=0&filter_year=0&t='.$template.'">';

		$messagetype .= $b->message_type;

		$messagetype .='</a>';

		$messagetype .= '</td>';

		$i++;
		$t++;
		if ($i == 3) {
			$messagetype .= "\n\t" . '</tr>';
			$i = 0;
		}
	}
	if ($i == 1) {
		$messagetype .= "\n\t\t" . '<td  id="landing_td"></td>' . "\n\t\t" . '<td id="landing_td"></td>';
	};
	if ($i == 2) {
		$messagetype .= "\n\t\t" . '<td  id="landing_td"></td>';
	};

	$messagetype .= "\n". '</table>' ."\n";

	if ($showdiv == 1)
	{

		$messagetype .= "\n\t". '</div> <!-- close show/hide messagetype div-->';
		$showdiv = 2;
	}
	$messagetype .= '<div id="landing_separator"></div>';

	return $messagetype;
}