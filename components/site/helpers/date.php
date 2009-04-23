<?php defined('_JEXEC') or die('Restricted access');

function getstudyDate($params, $studydate) {
switch ($params->get('date_format'))
	{
	 case 0:
		$date	= date('M j, Y', strtotime($studydate));
		break;
	 case 1:
		$date	= date('M j', strtotime($studydate) );
		break;
	 case 2:
		$date	= date('n/j/Y',  strtotime($studydate));
		break;
	 case 3:
		$date	= date('n/j', strtotime($studydate));
		break;
	 case 4:
		$date	= date('l, F j, Y',  strtotime($studydate));
		break;
	 case 5:
		$date	= date('F j, Y',  strtotime($studydate));
		break;
	 case 6:
		$date = date('j F Y', strtotime($studydate));
		break;
	 case 7:
		$date = date('j/n/Y', strtotime($studydate));
		break;
	 case 8:
		$date = JHTML::_('date', $studydate, JText::_('DATE_FORMAT_LC'));
		break;
         case 9:
                $date = date('Y/M/D', strtotime($studydate));
                break;
	 default:
		$date = date('n/j', strtotime($studydate));
		break;
	}
   return $date;
}