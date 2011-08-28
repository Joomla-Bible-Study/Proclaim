<?php 

/**
 * @version $Id$
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

defined('_JEXEC') or die();

function getDuration($params, $row)
{

	$duration = $row->media_hours.$row->media_minutes.$row->media_seconds;
	if (!$duration) {
		$duration = null; return $duration;
	}
	$duration_type = $params->get('duration_type',2);
	$hours = $row->media_hours;
	$minutes = $row->media_minutes;
	$seconds = $row->media_seconds;

	switch ($duration_type) {
		case 1:
			if (!$hours){
				$duration = $minutes.' mins '.$seconds.' secs';
			}
			else {
				$duration = $hours.' hour(s) '.$minutes.' mins '.$seconds.' secs';
			}
			break;
		case 2:
			if (!$hours){
				$duration = $minutes.':'.$seconds;
			}
			else {
				$duration = $hours.':'.$minutes.':'.$seconds;
			}
			break;
		default:
			$duration = $hours.':'.$minutes.':'.$seconds;
		break;
	} // end switch

	return $duration;
}