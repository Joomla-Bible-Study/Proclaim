<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
namespace CWM\Component\Proclaim\Site\Helper;
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Table\CWMTemplateTable;
use Joomla\Registry\Registry;

/**
 * Class custom helper
 *
 * @package  Proclaim.Site
 * @since    8.0.0
 * */
class CWMCustom
{
	/**
	 * Get Custom page
	 *
	 * @param   int                      $rowid     ID of Row
	 * @param   string                   $custom    Custom String
	 * @param   object                   $row       Row info
	 * @param   Registry                 $params    Params for intro
	 * @param   CWMTemplateTable|Object  $template  Template ID
	 *
	 * @return array
	 *
	 * @throws \Exception
	 * @since    8.0.0
	 */
	public function getCustom($rowid, $custom, $row, $params, $template)
	{
		$isCustom     = $rowid === 24;
		$countbraces  = substr_count($custom, '{');
		$JBSMElements = new CWMListing;

		while ($countbraces > 0)
		{
			$bracebegin = strpos($custom, '{');
			$braceend   = strpos($custom, '}');
			$subcustom  = substr($custom, ($bracebegin + 1), (($braceend - $bracebegin) - 1));

			if (!$rowid || $isCustom)
			{
				$rowid = self::getElementnumber($subcustom);
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
	 * @param   string  $row  Row ID
	 *
	 * @return integer
	 *
	 * @since    8.0.0
	 */
	public static function getElementnumber($row): int
	{
		$rowID = 0;

		switch ($row)
		{
			case 'scripture1' :
				$rowID = 1;
				break;
			case 'scripture2' :
				$rowID = 2;
				break;
			case 'secondary' :
				$rowID = 3;
				break;
			case 'duration' :
				$rowID = 4;
				break;
			case 'studytitle' :
				$rowID = 5;
				break;
			case 'studyintro' :
				$rowID = 6;
				break;
			case 'teachername' :
				$rowID = 7;
				break;
			case 'teacher-title-name' :
				$rowID = 8;
				break;
			case 'teacher-image':
				$rowID = 30;
				break;
			case 'series_text' :
				$rowID = 9;
				break;
			case 'date' :
				$rowID = 10;
				break;
			case 'submitted' :
				$rowID = 11;
				break;
			case 'hits' :
				$rowID = 12;
				break;
			case 'studynumber' :
				$rowID = 13;
				break;
			case 'topic_text' :
				$rowID = 14;
				break;
			case 'location_text' :
				$rowID = 15;
				break;
			case 'message_type' :
				$rowID = 16;
				break;
			case 'details-text' :
				$rowID = 17;
				break;
			case 'details-text-pdf' :
				$rowID = 18;
				break;
			case 'details-pdf' :
				$rowID = 19;
				break;
			case 'media' :
				$rowID = 20;
				break;
			case 'store' :
				$rowID = 22;
				break;
			case 'filesize' :
				$rowID = 23;
				break;
			case 'thumbnail' :
				$rowID = 25;
				break;
			case 'series_thumbnail':
				$rowID = 26;
				break;
			case 'series_description':
				$rowID = 27;
				break;
			case 'plays':
				$rowID = 28;
				break;
			case 'downloads':
				$rowID = 29;
				break;
		}

		return $rowID;
	}
}
