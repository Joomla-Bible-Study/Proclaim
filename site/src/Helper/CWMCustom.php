<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
namespace CWM\Component\Proclaim\Site\Helper;
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Class custom helper
 *
 * @package  BibleStudy.Site
 * @since    8.0.0
 * */
class CWMCustom
{
	/**
	 * Get Custom page
	 *
	 * @param   int            $rowid     ID of Row
	 * @param   string         $custom    Custom String
	 * @param   object         $row       Row info
	 * @param   Registry       $params    Params for intro
	 * @param   TableTemplate  $template  Template ID
	 *
	 * @return array
	 *
	 * @since    8.0.0
	 */
	public function getCustom($rowid, $custom, $row, $params, $template)
	{
		$isCustom = ($rowid == 24) ? true : false;
		$countbraces = substr_count($custom, '{');
		$JBSMElements = new CWMListing;

		while ($countbraces > 0)
		{
			$bracebegin = strpos($custom, '{');
			$braceend   = strpos($custom, '}');
			$subcustom  = substr($custom, ($bracebegin + 1), (($braceend - $bracebegin) - 1));

			if (!$rowid || $isCustom)
			{
				$rowid = $this->getElementnumber($subcustom);
			}

			$elementid = $JBSMElements->getElement($rowid, $row, $params, $template, $type = 0);
			$custom    = substr_replace($custom, $elementid, $bracebegin, (($braceend - $bracebegin) + 1));
			$countbraces--;
		}

		$elementid = $custom;
		$elementid->id      = 'custom';

		return $elementid;
	}

	/**
	 * Get Element Number.
	 *
	 * @param   int  $rowid  Row ID
	 *
	 * @return int
	 *
	 * @since    8.0.0
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
