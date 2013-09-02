<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Class custom helper
 *
 * @package  BibleStudy.Site
 * @since    8.0.0
 * */
class JBSMCustom extends JBSMElements
{
	/**
	 * Get Custom page
	 *
	 * @param   int       $rowid         ID of Row
	 * @param   string    $custom        Custom String
	 * @param   JTable    $row           Row info
	 * @param   JRegistry $params        Params for intro
	 * @param   JRegistry $admin_params  Admin Params
	 * @param   int       $template      Template ID
	 *
	 * @return object
	 */
	public function getCustom($rowid, $custom, $row, $params, $admin_params, $template)
	{
		$elementid   = new stdClass;
		$countbraces = substr_count($custom, '{');

		while ($countbraces > 0)
		{
			$bracebegin = strpos($custom, '{');
			$braceend   = strpos($custom, '}');
			$subcustom  = substr($custom, ($bracebegin + 1), (($braceend - $bracebegin) - 1));

			if (!$rowid)
			{
				$rowid = $this->getElementnumber($subcustom);
			}
			$elementid = $this->getElementid($rowid, $row, $params, $admin_params, $template);
			$custom    = substr_replace($custom, $elementid->element, $bracebegin, (($braceend - $bracebegin) + 1));
			$countbraces--;
		}
		$elementid->element = $custom;
		$elementid->id      = 'custom';

		return $elementid;
	}

	/**
	 * Get Element Number.
	 *
	 * @param   int $rowid  Row ID
	 *
	 * @return int
	 */
	public static function getElementnumber($rowid)
	{
		switch ($rowid)
		{
			case 'scripture1' :
				$rowid = 1;
				break;
			case 'scripture2' :
				$rowid = 2;
				break;
			case 'secondary' :
				$rowid = 3;
				break;
			case 'duration' :
				$rowid = 4;
				break;
			case 'studytitle' :
				$rowid = 5;
				break;
			case 'studyintro' :
				$rowid = 6;
				break;
			case 'teachername' :
				$rowid = 7;
				break;
			case 'teacher-title-name' :
				$rowid = 8;
				break;
			case 'teacher-image':
				$rowid = 30;
				break;
			case 'series_text' :
				$rowid = 9;
				break;
			case 'date' :
				$rowid = 10;
				break;
			case 'submitted' :
				$rowid = 11;
				break;
			case 'hits' :
				$rowid = 12;
				break;
			case 'studynumber' :
				$rowid = 13;
				break;
			case 'topic_text' :
				$rowid = 14;
				break;
			case 'location_text' :
				$rowid = 15;
				break;
			case 'message_type' :
				$rowid = 16;
				break;
			case 'details-text' :
				$rowid = 17;
				break;
			case 'details-text-pdf' :
				$rowid = 18;
				break;
			case 'details-pdf' :
				$rowid = 19;
				break;
			case 'media' :
				$rowid = 20;
				break;
			case 'store' :
				$rowid = 22;
				break;
			case 'filesize' :
				$rowid = 23;
				break;
			case 'thumbnail' :
				$rowid = 25;
				break;
			case 'series_thumbnail':
				$rowid = 26;
				break;
			case 'series_description':
				$rowid = 27;
				break;
			case 'plays':
				$rowid = 28;
				break;
			case 'downloads':
				$rowid = 29;
				break;
		}

		return $rowid;
	}

}
