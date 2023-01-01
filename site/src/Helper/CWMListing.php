<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Helper;

// No Direct Access
defined('_JEXEC') or die;

// Always load JBSM API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

if (file_exists($api))
{
	require_once $api;
}

use CWM\Component\Proclaim\Administrator\Helper\CWMHelper;
use CWM\Component\Proclaim\Administrator\Table\CWMTemplateTable;
use Joomla\CMS\Factory;
use Joomla\CMS\Html\HtmlHelper;
use Joomla\CMS\Image\Image;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

/**
 * BibleStudy listing class
 *
 * @since  7.0.0
 */
class CWMListing
{
	/** @var  Registry
	 * @since 7.0
	 */
	public $params;

	/**
	 * Get Fluid Listing
	 *
	 * @param   array|object             $items     Items
	 * @param   Registry                 $params    Page Params
	 * @param   CWMTemplateTable|Object  $template  Template name
	 * @param   string                   $type      Type of Listing
	 *
	 * @return string
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function getFluidListing($items, Registry $params, $template, string $type)
	{
		$list         = null;
		$row          = array();
		$this->params = $params;
		$item         = '';

		if ($type === 'sermons')
		{
			foreach ($items as $item)
			{
				if (isset($item->mids))
				{
					$medias[] = $this->getFluidMediaids($item);
				}
			}
		}

		if ($type === 'sermon')
		{
			$medias = $this->getFluidMediaids($items);
			$item   = $items;
		}

		// Get the media files in one query
		if (isset($medias))
		{
			$mediafiles = $this->getMediaFiles($medias);
		}

		// Create an array from each param variable set
		// Find out what view we are in
		$extra = '';

		switch ($type)
		{
			case 'sermons':
				$extra = '';
				break;
			case 'sermon':
				$extra = 'd';
				break;
			case 'seriesdisplays':
				$extra = 's';
				break;
			case 'seriesdisplay':
				$extra = 'sd';
				break;
			case 'teachers':
				$extra = 'ts';
				break;
			case 'teacher':
				$extra = 'td';
				break;
			case 'module':
				$extra = 'm';
				break;
		}

		$listparams = array();

		if ($params->get($extra . 'scripture1row') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'scripture1');
		}

		if ($params->get($extra . 'scripture2row') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'scripture2');
		}

		if ($params->get($extra . 'secondaryrow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'secondary');
		}

		if ($params->get($extra . 'titlerow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'title');
		}

		if ($params->get($extra . 'daterow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'date');
		}

		if ($params->get($extra . 'teacherrow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'teacher');
		}

		if ($params->get($extra . 'teacher-titlerow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'teacher-title');
		}

		if ($params->get($extra . 'durationrow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'duration');
		}

		if ($params->get($extra . 'studyintrorow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'studyintro');
		}

		if ($params->get($extra . 'seriesrow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'series');
		}

		if ($params->get($extra . 'descriptionrow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'description');
		}

		if ($params->get($extra . 'seriesthumbnailrow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'seriesthumbnail');
		}

		if ($params->get($extra . 'submittedrow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'submitted');
		}

		if ($params->get($extra . 'hitsrow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'hits');
		}

		if ($params->get($extra . 'downloadsrow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'downloads');
		}

		if ($params->get($extra . 'studynumberrow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'studynumber');
		}

		if ($params->get($extra . 'topicrow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'topic');
		}

		if ($params->get($extra . 'locationsrow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'locations');
		}

		if ($params->get($extra . 'jbsmediarow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'jbsmedia');
		}

		if ($params->get($extra . 'messagetyperow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'messagetype');
		}

		if ($params->get($extra . 'thumbnailrow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'thumbnail');
		}

		if ($params->get($extra . 'teacherimagerow') > 0 || $params->get($extra . 'teacherimagerrow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'teacherimage');
		}

		if ($params->get($extra . 'seriesdescriptionrow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'description');
		}

		if ($params->get($extra . 'teacheremailrow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'teacheremail');
		}

		if ($params->get($extra . 'teacherwebrow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'teacherweb');
		}

		if ($params->get($extra . 'teacherphonerow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'teacherphone');
		}

		if ($params->get($extra . 'teacherfbrow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'teacherfb');
		}

		if ($params->get($extra . 'teachertwrow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'teachertw');
		}

		if ($params->get($extra . 'teacherblogrow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'teacherblog');
		}

		if ($params->get($extra . 'teachershortrow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'teachershort');
		}

		if ($params->get($extra . 'teacherlongrow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'teacherlong');
		}

		if ($params->get($extra . 'teacheraddressrow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'teacheraddress');
		}

		if ($params->get($extra . 'teacherlink1row') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'teacherlink1');
		}

		if ($params->get($extra . 'teacherlink2row') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'teacherlink2');
		}

		if ($params->get($extra . 'teacherlink3row') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'teacherlink3');
		}

		if ($params->get($extra . 'teacherlargeimagerow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'teacherlargeimage');
		}

		if ($params->get($extra . 'teacherallinonerow') > 0)
		{
			$listparams[] = $this->getListParamsArray($extra . 'teacherallinone');
		}

		if ($params->get($extra . 'customrow'))
		{
			$listparams[] = $this->getListParamsArray($extra . 'custom');
		}

		$row1       = array();
		$row2       = array();
		$row3       = array();
		$row4       = array();
		$row5       = array();
		$row6       = array();
		$row1sorted = array();
		$row2sorted = array();
		$row3sorted = array();
		$row4sorted = array();
		$row5sorted = array();
		$row6sorted = array();

		// Create an array sorted by row and then by column
		foreach ($listparams as $listing)
		{
			if ($listing->row === '1')
			{
				$row1[] = $listing;
			}

			if ($listing->row === '2')
			{
				$row2[] = $listing;
			}

			if ($listing->row === '3')
			{
				$row3[] = $listing;
			}

			if ($listing->row === '4')
			{
				$row4[] = $listing;
			}

			if ($listing->row === '5')
			{
				$row5[] = $listing;
			}

			if ($listing->row === '6')
			{
				$row6[] = $listing;
			}
		}

		if (count($row1))
		{
			$row1sorted = $this->sortArrayofObjectByProperty($row1, 'col', $order = "ASC");
		}

		if (count($row2))
		{
			$row2sorted = $this->sortArrayofObjectByProperty($row2, 'col', $order = "ASC");
		}

		if (count($row3))
		{
			$row3sorted = $this->sortArrayofObjectByProperty($row3, 'col', $order = "ASC");
		}

		if (count($row4))
		{
			$row4sorted = $this->sortArrayofObjectByProperty($row4, 'col', $order = "ASC");
		}

		if (count($row5))
		{
			$row5sorted = $this->sortArrayofObjectByProperty($row5, 'col', $order = "ASC");
		}

		if (count($row6))
		{
			$row6sorted = $this->sortArrayofObjectByProperty($row6, 'col', $order = "ASC");
		}

		$listrows    = array_merge($row1sorted, $row2sorted, $row3sorted, $row4sorted, $row5sorted, $row6sorted);
		$listsorts   = array();
		$listsorts[] = $row1sorted;
		$listsorts[] = $row2sorted;
		$listsorts[] = $row3sorted;
		$listsorts[] = $row4sorted;
		$listsorts[] = $row5sorted;
		$listsorts[] = $row6sorted;

		$oddeven = 1;

		// Start the table for the entire list
		$list .= '<div class="table-responsive" about="' . $type . '"><table class="table w-auto table-borderless">';

		if ($type === 'sermons')
		{
			if ($params->get('use_headers_list') > 0)
			{
				// Start the header
				$list .= '<thead class="' . $params->get('listheadertype') . '">';
				$list .= $this->getFluidRow($listrows, $listsorts, $item, $params, $template, $oddeven, $header = 1, $type);
				$list .= '</thead>';
			}
		}

		if ($type === 'sermon')
		{
			if ($params->get('use_headers_view') > 0)
			{
				// Start the header
				$list .= '<thead class="' . $params->get('listheadertype') . '">';
				$list .= $this->getFluidRow($listrows, $listsorts, $item, $params, $template, $oddeven, $header = 1, $type);
				$list .= '</thead>';
			}
		}

		if ($type === 'seriesdisplays')
		{
			if ($params->get('use_headers_series') == 1)
			{
				// Start the header
				$list .= '<thead class="' . $params->get('listheadertype') . '" colspan="12">';
				$list .= $this->getFluidRow($listrows, $listsorts, $items[0], $params, $template, $oddeven, $header = 1, $type);
				$list .= '</thead>';
			}
		}

		if ($type === 'seriesdisplay')
		{
			if ($params->get('use_header_seriesdisplay') > 0)
			{
				$oddeven = $params->get('seriesdisplay_color');

				// Start the header
				$list .= '<thead class="' . $params->get('listheadertype') . '">';
				$list .= $this->getFluidRow($listrows, $listsorts, $items, $params, $template, $oddeven, $header = 1, $type);
				$list .= '</thead>';
			}

			$list .= $this->getFluidRow($listrows, $listsorts, $items, $params, $template, $oddeven, $header = 0, $type);
		}

		if ($type === 'teacher')
		{
			if ($params->get('use_headers_teacher_details') > 0)
			{
				$oddeven = $params->get('teacherdisplay_color', 'white');

				// Start the header
				$list .= '<thead class="' . $params->get('listheadertype') . '">';
				$list .= $this->getFluidRow($listrows, $listsorts, $items, $params, $template, $oddeven, $header = 1, $type);
				$list .= '</thead>';
			}

			$list .= $this->getFluidRow($listrows, $listsorts, $items, $params, $template, $oddeven, $header = 0, $type);
		}

		if ($type === 'teachers')
		{
			if ($params->get('use_headers_teacher_list') > 0)
			{
				// Start the header
				$list .= '<thead class="' . $params->get('listheadertype') . '">';
				$list .= $this->getFluidRow($listrows, $listsorts, $items, $params, $template, $oddeven, $header = 1, $type);
				$list .= '</thead>';
			}
		}

		// Go through and attach the media files as an array to their study
		if ($type === 'sermons')
		{
			foreach ($items as $item)
			{
				$studymedia = array();

				if (isset($mediafiles))
				{
					foreach ($mediafiles as $mediafile)
					{
						if ($mediafile->study_id === $item->id)
						{
							$studymedia[] = $mediafile;
						}
					}
				}

				if (isset($studymedia))
				{
					$item->mediafiles = $studymedia;
				}

				$row[] = $this->getFluidRow($listrows, $listsorts, $item, $params, $template, $oddeven, $header = 0, $type);
				$row[] = '</td></tr><tr style="border-bottom: 1px solid darkgrey; padding-bottom: 5px;"</tr>';
			}
		}

		if ($type === 'sermon')
		{
			$studymedia = array();

			if (isset($mediafiles))
			{
				foreach ($mediafiles as $mediafile)
				{
					if ((int) $mediafile->study_id === (int) $item->id)
					{
						$studymedia[] = $mediafile;
					}
				}
			}

			if (isset($studymedia))
			{
				$item->mediafiles = $studymedia;
			}

			$row[] = $this->getFluidRow($listrows, $listsorts, $item, $params, $template, $oddeven, $header = 0, $type);
		}

		if ($type === 'seriesdisplays')
		{
			foreach ($items as $item)
			{
				$row[] = $this->getFluidRow($listrows, $listsorts, $item, $params, $template, $oddeven, $header = 0, $type);
				$row[] = '</td></tr><tr style="border-bottom: 1px solid darkgrey; padding-bottom: 5px;"</tr>';
			}
		}

		if ($type === 'teachers')
		{
			foreach ($items as $item)
			{
				$row[] = $this->getFluidRow($listrows, $listsorts, $item, $params, $template, $oddeven, $header = 0, $type);
				$row[] = '</td></tr><tr style="border-bottom: 1px solid darkgrey; padding-bottom: 5px;"</tr>';
			}
		}

		foreach ($row as $value)
		{
			$list .= $value;
		}

		$list .= '</tbody></table></div>';

		return $list;
	}

	/**
	 * Get Fluid Media Id's
	 *
	 * @param   array  $item  Items info
	 *
	 * @return array
	 *
	 * @since 7.0
	 */
	public function getFluidMediaids($item)
	{
		$medias    = array();
		$mediatemp = explode(',', $item->mids);

		foreach ($mediatemp as $mtemp)
		{
			$medias[] = $mtemp;
		}

		return $medias;
	}

	/**
	 * Get Media Files
	 *
	 * @param   array  $medias  Media files
	 *
	 * @return mixed
	 *
	 * @since 7.0
	 */
	public function getMediaFiles($medias)
	{
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select('#__bsms_mediafiles.*, #__bsms_servers.id AS ssid, #__bsms_servers.params AS sparams, #__bsms_servers.media AS smedia,'
			. ' s.studytitle, s.studydate, s.studyintro, s.teacher_id,'
			. ' s.booknumber, s.chapter_begin, s.chapter_end, s.verse_begin, s.verse_end, t.teachername, t.id as tid, s.id as sid, s.studyintro'
		);
		$query->from('#__bsms_mediafiles');
		$query->leftJoin('#__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server_id)');
		$query->leftJoin('#__bsms_studies AS s ON (s.id = #__bsms_mediafiles.study_id)');
		$query->leftJoin('#__bsms_teachers AS t ON (t.id = s.teacher_id)');
		$where2   = array();
		$subquery = '(';

		foreach ($medias as $media)
		{
			if (is_array($media))
			{
				foreach ($media as $m)
				{
					$where2[] = '#__bsms_mediafiles.id = ' . (int) $m;
				}
			}
			else
			{
				$where2[] = '#__bsms_mediafiles.id = ' . (int) $media;
			}
		}

		$subquery .= implode(' OR ', $where2);
		$subquery .= ')';
		$query->where($subquery);
		$query->where('#__bsms_mediafiles.published = 1');
		$query->where('#__bsms_mediafiles.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		$query->order('ordering ASC');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Get list Params Array
	 *
	 * @param   string  $paramtext  Param Text
	 *
	 * @return \stdClass
	 *
	 * @since 7.0
	 */
	public function getListParamsArray($paramtext)
	{
		$l = new \stdClass;

		if ($paramtext === 'tdteacherimage')
		{
			if ($this->params->get($paramtext . 'rrow'))
			{
				$l->row = $this->params->get($paramtext . 'rrow');
			}
			else
			{
				$l->row = $this->params->get($paramtext . 'row');
			}
		}
		else
		{
			$l->row = $this->params->get($paramtext . 'row');
		}

		$l->col        = $this->params->get($paramtext . 'col');
		$l->colspan    = $this->params->get($paramtext . 'colspan');
		$l->element    = $this->params->get($paramtext . 'element');
		$l->custom     = $this->params->get($paramtext . 'custom');
		$l->linktype   = $this->params->get($paramtext . 'linktype');
		$l->name       = $paramtext;
		$l->customtext = $this->params->get($paramtext . 'text');

		return $l;
	}

	/**
	 * Sort Array of Object by Property
	 *
	 * @param   array   $array     ?
	 * @param   string  $property  ?
	 * @param   string  $order     ?
	 *
	 * @return array
	 *
	 * @since 7.0
	 */
	public function sortArrayofObjectByProperty($array, $property, $order = "ASC")
	{
		$cur           = 1;
		$stack[1]['l'] = 0;
		$stack[1]['r'] = count($array) - 1;

		do
		{
			$l = $stack[$cur]['l'];
			$r = $stack[$cur]['r'];
			$cur--;

			do
			{
				$i   = $l;
				$j   = $r;
				$tmp = $array[(int) (($l + $r) / 2)];

				/*
				 Split the array in to parts
				// first: objects with "smaller" property $property
				// second: objects with "bigger" property $property */
				do
				{
					while ($array[$i]->{$property} < $tmp->{$property})
					{
						$i++;
					}

					while ($tmp->{$property} < $array[$j]->{$property})
					{
						$j--;
					}

					// Swap elements of two parts if necesary
					if ($i <= $j)
					{
						$w         = $array[$i];
						$array[$i] = $array[$j];
						$array[$j] = $w;

						$i++;
						$j--;
					}
				} while ($i <= $j);

				if ($i < $r)
				{
					$cur++;
					$stack[$cur]['l'] = $i;
					$stack[$cur]['r'] = $r;
				}

				$r = $j;
			} while ($l < $r);
		} while ($cur !== 0);

		// Added ordering.
		if ($order === "DESC")
		{
			$array = array_reverse($array);
		}

		return $array;
	}

	/**
	 * Get Fluid Row
	 *
	 * @param   array                    $listrows   ?
	 * @param   array                    $listsorts  ?
	 * @param   Object                   $item       ?
	 * @param   Registry                 $params     Item Params
	 * @param   CWMTemplateTable|Object  $template   Template info
	 * @param   string                   $oddeven    ?
	 * @param   integer                  $header     ?
	 * @param   string                   $type       ?
	 *
	 * @return string
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function getFluidRow(array $listrows, array $listsorts, object $item, Registry $params, $template, string $oddeven, int $header, string $type): string
	{
		$span        = '';
		$headerstyle = '';

		if ($header === 1)
		{
			$headerstyle = "style=\"display: none;\"";
		}

		$extra = '';

		switch ($type)
		{
			case 'sermon':
				$extra = 'd';
				break;
			case 'seriesdisplays':
				$extra = 's';
				break;
			case 'seriesdisplay':
				$extra = 'sd';
				break;
			case 'sermons':

				break;
			case 'teachers':
				$extra = 'ts';
				break;
			case 'teacher':
				$extra = 'td';
				break;
		}

		$pull        = $params->get($extra . 'rowspanitempull');
		$rowspanitem = $params->get($extra . 'rowspanitem', 0);

		switch ($rowspanitem)
		{
			// Teacher Thumbnail
			case 1:
				if (isset($item->thumb) && !empty($item->thumb))
				{
					$span = $this->useJImage($item->thumb, $item->teachername, '', '', '', $params->get('rowspanitemimage'));
				}
				else
				{
					$span = null;
				}

				if (!empty($item->teacher_thumbnail) && is_null($span))
				{
					if (isset($item->teacher_thumbnail))
					{
						$span = $this->useJImage($item->teacher_thumbnail, $item->teachername, '', '', '', $params->get('rowspanitemimage'));
					}
					else
					{
						$span = '';
					}
				}
				break;

			// Study Thumbnail
			case 2:
				if ((isset($item->thumbnailm) && !empty($item->thumbnailm)) || isset($item->thumbnailm))
				{
					$span = $this->useJImage($item->thumbnailm, Text::_('JBS_CMN_THUMBNAIL'), '', '', '', $params->get('rowspanitemimage'));
				}

				if ($params->get('studyimage') !== '-1')
				{
					$span = $this->useJImage(JPATH_SITE . '/media/com_proclaim/images/stockimages/' . $params->get('studyimage'), Text::_('JBS_CMN_THUMBNAIL'), '', '', '', $params->get('rowspanitemimage'));
				}
				else
				{
					$span = '';
				}

				break;

			// Series Thumbnail
			case 3:
				if (isset($item->series_thumbnail) && !empty($item->series_thumbnail))
				{
					$span = $this->useJImage($item->series_thumbnail, Text::_('JBS_CMN_SERIES'), '', '', '', $params->get('rowspanitemimage'));
				}
				else
				{
					$span = '';
				}
				break;

			// Teacher Large image
			case 4:
				if (isset($item->teacher_image) && !empty($item->teacher_image))
				{
					$span = $this->useJImage($item->teacher_image, $item->teachername, '', '', '', $params->get('rowspanitemimage'));
				}
				else
				{
					$span = '';
				}
				break;
		}

		$rowspanitemspan = $params->get($extra . 'rowspanitemspan');
		$rowspanbalance  = 12 - intval($rowspanitemspan);

		$frow = '';

		$row1count  = 0;
		$row2count  = 0;
		$row3count  = 0;
		$row4count  = 0;
		$row5count  = 0;
		$row6count  = 0;
		$row1count2 = 0;
		$row2count2 = 0;
		$row3count2 = 0;
		$row4count2 = 0;
		$row5count2 = 0;
		$row6count2 = 0;

		foreach ($listsorts as $sort)
		{
			if (count($sort))
			{
				foreach ($sort as $s)
				{
					if ($s->row === '1')
					{
						$row1count++;
						$row1count2++;
					}

					if ($s->row === '2')
					{
						$row2count++;
						$row2count2++;
					}

					if ($s->row === '3')
					{
						$row3count++;
						$row3count2++;
					}

					if ($s->row === '4')
					{
						$row4count++;
						$row4count2++;
					}

					if ($s->row === '5')
					{
						$row5count++;
						$row5count2++;
					}

					if ($s->row === '6')
					{
						$row6count++;
						$row6count2++;
					}
				}
			}
		}

		$thadd = '';

		foreach ($listrows as $row)
		{
			if ($row->row === '1')
			{
				if ($row1count === $row1count2 & $header === 0)
				{
					$frow .= '<tr scope="row">';
				}

				if ($header === 1)
				{
					if ($row->colspan > 0)
					{
						$thadd = 'colspan="' . $row->colspan . '"';
					}

					// If ($extra == 's'){$thadd = 'class="col-12"';}
					// $thadd = '';
					$frow .= '<th scope="col"' . $thadd . '>' . $this->getFluidData($item, $row, $params, $template, $header = 1, $type);
				}
				else
				{
					$frow .= $this->getFluidData($item, $row, $params, $template, $header = 0, $type);
				}

				$row1count--;

				if ($row1count === 0 && !$header)
				{
					$frow .= '</tr>';
				}

				if ($row1count === 0 && $header === 1)
				{
					$frow .= '</th></tr>';
				}
			}

			if ($row->row === '2')
			{
				if ($row2count === $row2count2 && $header === 0)
				{
					$frow .= '<tr scope="row">';
				}

				if ($header === 1)
				{
					if ($row->colspan > 0)
					{
						$thadd = 'colspan="' . $row->colspan . '"';
					}

					// $thadd = '';
					$frow .= '<tr scope="row"> <th ' . $thadd . ' scope="col">' . $this->getFluidData($item, $row, $params, $template, $header = 1, $type);
				}
				else
				{
					$frow .= $this->getFluidData($item, $row, $params, $template, $header = 0, $type);
				}

				$row2count--;

				if ($row2count === 0 && $header === 0)
				{
					$frow .= '</tr>';
				}

				if ($row2count === 0 && $header === 1)
				{
					$frow .= '</th></tr>';
				}
			}

			if ($row->row === '3')
			{
				if ($row3count === $row3count2 && $header === 0)
				{
					$frow .= '<tr scope="row">';
				}

				if ($header === 1)
				{
					if ($row->colspan > 0)
					{
						$thadd = 'colspan="' . $row->colspan . '"';
					}

					// $thadd = '';
					$frow .= '<tr scope="row"><th ' . $thadd . ' scope="col">' . $this->getFluidData($item, $row, $params, $template, $header = 1, $type);
				}
				else
				{
					$frow .= $this->getFluidData($item, $row, $params, $template, $header = 0, $type);
				}

				$row3count--;

				if ($row3count === 0 && $header === 0)
				{
					$frow .= '</tr>';
				}

				if ($row3count === 0 && $header === 1)
				{
					$frow .= '</th></tr>';
				}
			}

			if ($row->row === '4')
			{
				if ($row4count === $row4count2 && $header === 0)
				{
					$frow .= '<tr scope="row">';
				}

				if ($header === 1)
				{
					if ($row->colspan > 0)
					{
						$thadd = 'colspan="' . $row->colspan . '"';
					}

					// $thadd = '';
					$frow .= '<tr scope="row"><th ' . $thadd . ' scope="col">' . $this->getFluidData($item, $row, $params, $template, $header = 1, $type);
				}
				else
				{
					$frow .= $this->getFluidData($item, $row, $params, $template, $header = 0, $type);
				}

				$row4count--;

				if ($row4count === 0 && $header === 0)
				{
					$frow .= '</tr>';
				}

				if ($row4count === 0 && $header === 1)
				{
					$frow .= '</th></tr>';
				}
			}

			if ($row->row === '5')
			{
				if ($row5count === $row5count2 && $header === 0)
				{
					$frow .= '<tr scope="row">';
				}

				if ($header === 1)
				{
					if ($row->colspan > 0)
					{
						$thadd = 'colspan="' . $row->colspan . '"';
					}

					// $thadd = '';
					$frow .= '<tr scope="row"><th ' . $thadd . ' scope="col">' . $this->getFluidData($item, $row, $params, $template, $header = 1, $type);
				}
				else
				{
					$frow .= $this->getFluidData($item, $row, $params, $template, $header = 0, $type);
				}

				$row5count--;

				if ($row5count === 0 && $header === 0)
				{
					$frow .= '</tr>';
				}

				if ($row5count === 0 && $header === 1)
				{
					$frow .= '</th></tr>';
				}
			}

			if ($row->row === '6')
			{
				if ($row6count === $row6count2 && $header === 0)
				{
					$frow .= '<tr scope="row">';
				}

				if ($header === 1)
				{
					if ($row->colspan > 0)
					{
						$thadd = 'colspan="' . $row->colspan . '"';
					}

					// $thadd = '';
					$frow .= '<tr scope="row"><th ' . $thadd . ' scope="col">' . $this->getFluidData($item, $row, $params, $template, $header = 1, $type);
				}
				else
				{
					$frow .= $this->getFluidData($item, $row, $params, $template, $header = 0, $type);
				}

				$row6count--;

				if ($row6count === 0 && $header === 0)
				{
					$frow .= '</tr>';
				}

				if ($row6count === 0 && $header === 1)
				{
					$frow .= '</th></tr>';
				}
			}
		}

		return $frow;
	}

	/**
	 * Get Fluid Date
	 *
	 * @param   Object                   $item      Study item
	 * @param   Object                   $row       Row Setup data
	 * @param   Registry                 $params    Parameters for the study
	 * @param   CWMTemplateTable|Object  $template  Template table
	 * @param   integer                  $header    Header will display if 1, Do not display if 0
	 * @param   string                   $type      Type of Fluid Data
	 *
	 * @return string
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function getFluidData(object $item, object $row, Registry $params, $template, int $header, $type): string
	{
		$registry = new Registry;

		if (isset($item->params))
		{
			$registry->loadString($item->params);
		}

		/** @var string $data */
		$data = '';

		// Match the data in $item to a row/col in $row->name
		$extra = '';

		switch ($type)
		{
			case 'sermon':
				$extra = 'd';
				break;
			case 'seriesdisplays':
				$extra = 's';
				break;
			case 'seriesdisplay':
				$extra = 'sd';
				break;
			case 'teachers':
				$extra = 'ts';
				break;
			case 'teacher':
				$extra = 'td';
				break;
		}

		switch ($row->name)
		{
			case $extra . 'custom':

				if ($header === 1)
				{
					$data = '';
				}
				else
				{
					$data = $this->getFluidCustom($row->customtext, $item, $params, $template, $type);
				}
				break;

			case $extra . 'teacherallinone':
				if ($header == 1)
				{
					$data .= 'Teacher Contact';
				}
				else
				{
					if (isset($item->email))
					{
						($item->email ? $data = '<a href="mailto:' . $item->email . '">
				<span class="fas fa-envelope" style="font-size:20px;" title="Website"></span></a>' : $data = '');

						if ($item->website)
						{
							if (substr_count($item->website, 'http://', 0))
							{
								$data .= '<a href="' . $item->website . '" target="_blank">
						<span class="fas fa-globe" style="font-size:20px;" title="Website"></span></a>';
							}
							else
							{
								$data .= '<a href="http://' . $item->website . '" target="_blank">
						<span class="fas fa-globe" style="font-size:20px;" title="Website"></span></a>';
							}
						}

						if ($item->facebooklink)
						{
							if (substr_count($item->facebooklink, 'http://', 0))
							{
								$data .= '<a href="' . $item->facebooklink . '" target="_blank">
						<span class="fab fa-facebook" style="font-size:20px;" title="Facebook"></span></a>';
							}
							else
							{
								$data .= '<a href="http://' . $item->facebooklink . '" target="_blank">
						<span class="fab fa-facebook" style="font-size:20px;" title="Facebook"></span></a>';
							}
						}

						if ($item->twitterlink)
						{
							if (substr_count($item->twitterlink, 'http://', 0))
							{
								$data .= '<a href="' . $item->twitterlink . '" target="_blank">
						<span class="fab fa-twitter" style="font-size:20px;" title="Twitter"></span></a>';
							}
							else
							{
								$data .= '<a href="http://' . $item->twitterlink . '" target="_blank">
						<span class="fab fa-twitter" style="font-size:20px;" title="Twitter"></span></a>';
							}
						}

						if ($item->bloglink)
						{
							if (substr_count($item->bloglink, 'http://', 0, 7))
							{
								$data .= '<a href="' . $item->bloglink . '" target="_blank">
						<span class="fas fa-sticky-note" style="font-size:20px;" title="Blog"></span></a>';
							}
							else
							{
								$data .= '<a href="http://' . $item->bloglink . '" target="_blank">
						<span class="fas fa-sticky-note" style="font-size:20px;" title="Blog"></span></a>';
							}
						}

						if ($item->link1)
						{
							if (substr_count($item->link1, 'http://', 0))
							{
								$data .= '<a href="' . $item->link1 . '" target="_blank">' . $item->link1label . '</a>';
							}
							else
							{
								$data .= '<a href="http://' . $item->link1 . '" target="_blank">' . $item->link1label . '</a>';
							}
						}

						if ($item->link2)
						{
							if (substr_count($item->link2, 'http://', 0))
							{
								$data .= '<a href="' . $item->link2 . '" target="_blank">' . $item->link2label . '</a>';
							}
							else
							{
								$data .= '<a href="http://' . $item->link2 . '" target="_blank">' . $item->link2label . '</a>';
							}
						}

						if ($item->link3)
						{
							if (substr_count($item->link3, 'http://', 0))
							{
								$data .= '<a href="' . $item->link3 . '" target="_blank">' . $item->link3label . '</a>';
							}
							else
							{
								$data .= '<a href="http://' . $item->link3 . '" target="_blank">' . $item->link3label . '</a>';
							}
						}
					}
				}
				break;

			case $extra . 'teacherlong':
				if ($header === 1)
				{
					$data = Text::_('JBS_TCH_INFORMATION');
				}
				else
				{
					($item->information ? $data = HtmlHelper::_('content.prepare', $item->information, '', 'com_proclaim.' . $type) : $data = '');
				}
				break;

			case $extra . 'teacheraddress':
				if ($header === 1)
				{
					$data = Text::_('JBS_TCH_ADDRESS');
				}
				else
				{
					($item->address ? $data = $item->address : $data = '');
				}
				break;

			case $extra . 'teacherlink1':
				if ($header === 1)
				{
					$data = Text::_('JBS_TCH_LINK1');
				}
				else
				{
					if ($item->link1)
					{
						if (substr_count($item->link1, 'http://', 0))
						{
							$data = '<a href="' . $item->link1 . '" target="_blank">' . $item->linklabel1 . '</a>';
						}
						else
						{
							$data = '<a href="http://' . $item->link1 . '" target="_blank">' . $item->linklabel1 . '</a>';
						}
					}
				}
				break;

			case $extra . 'teacherlink2':
				if ($header === 1)
				{
					$data = Text::_('JBS_TCH_LINK2');
				}
				else
				{
					if ($item->link2)
					{
						if (substr_count($item->link2, 'http://', 0))
						{
							$data = '<a href="' . $item->link2 . '" target="_blank">' . $item->linklabel2 . '</a>';
						}
						else
						{
							$data = '<a href="http://' . $item->link2 . '" target="_blank">' . $item->linklabel2 . '</a>';
						}
					}
				}
				break;

			case $extra . 'teacherlink3':
				if ($header === 1)
				{
					$data = Text::_('JBS_TCH_LINK3');
				}
				else
				{
					if ($item->link3)
					{
						if (substr_count($item->link3, 'http://', 0))
						{
							$data = '<a href="' . $item->link3 . '" target="_blank">' . $item->linklabel3 . '</a>';
						}
						else
						{
							$data = '<a href="http://' . $item->link3 . '" target="_blank">' . $item->linklabel3 . '</a>';
						}
					}
				}
				break;
			case $extra . 'teacheremail':
				if ($header === 1)
				{
					$data = Text::_('JBS_TCH_EMAIL');
				}
				else
				{
					($item->email ? $data = '<a href="mailto:' . $item->email . '">
					<span class="fas fa-envelope" style="font-size:20px;" title="Email"></span></a>' : $data = '');
				}
				break;

			case $extra . 'teacherweb':
				if ($header === 1)
				{
					$data = Text::_('JBS_TCH_WEBSITE');
				}
				else
				{
					if ($item->website)
					{
						if (substr_count($item->website, 'http://', 0))
						{
							$data = '<a href="' . $item->website . '" target="_blank">
							<span class="fas fa-globe" style="font-size:20px;" title="Website"></span></a>';
						}
						else
						{
							$data = '<a href="http://' . $item->website . '" target="_blank">
							<span class="fas fa-globe" style="font-size:20px;" title="Website"></span></a>';
						}
					}
				}
				break;

			case $extra . 'teacherphone':
				if ($header === 1)
				{
					$data = Text::_('JBS_TCH_PHONE');
				}
				else
				{
					(isset($item->phone) ? $data = $item->phone : $data = '');
				}
				break;

			case $extra . 'teacherfb':
				if ($header === 1)
				{
					$data = Text::_('JBS_TCH_FACEBOOK');
				}
				else
				{
					if ($item->facebooklink)
					{
						if (substr_count($item->facebooklink, 'http://', 0))
						{
							$data = '<a href="' . $item->facebooklink . '" target="_blank">
							<span class="fab fa-facebook" style="font-size:20px;" title="Facebook"></span></a>';
						}
						else
						{
							$data = '<a href="http://' . $item->facebooklink . '" target="_blank">
							<span class="fab fa-facebook" style="font-size:20px;" title="Facebook"></span></a>';
						}
					}
				}
				break;

			case $extra . 'teachertw':
				if ($header === 1)
				{
					$data = Text::_('JBS_TCH_TWITTER');
				}
				else
				{
					if ($item->twitterlink)
					{
						if (substr_count($item->twitterlink, 'http://', 0))
						{
							$data = '<a href="' . $item->twitterlink . '" target="_blank">
							<span class="fas fa-twitter" style="font-size:20px;" title="Twitter"></span></a>';
						}
						else
						{
							$data = '<a href="http://' . $item->twitterlink . '" target="_blank">
							<span class="fas fa-twitter" style="font-size:20px;" title="Twitter"></span></a>';
						}
					}
				}

				break;

			case $extra . 'teacherblog':
				if ($header === 1)
				{
					$data = Text::_('JBS_TCH_BLOG');
				}
				else
				{
					if ($item->bloglink)
					{
						if (substr_count($item->bloglink, 'http://', 0, 7))
						{
							$data = '<a href="' . $item->bloglink . '" target="_blank">
							<span class="fas fa-sticky-note" style="font-size:20px;" title="Blog"></span></a>';
						}
						else
						{
							$data = '<a href="http://' . $item->bloglink . '" target="_blank">
							<span class="fas fa-sticky-note" style="font-size:20px;" title="Blog"></span></a>';
						}
					}
				}

				break;

			case $extra . 'teachershort':
				if ($header === 1)
				{
					$data = Text::_('JBS_TCH_SHORT_LIST');
				}
				else
				{
					(isset($item->short) ? $data = HtmlHelper::_('content.prepare', $item->short, '', 'com_proclaim.' . $type) : $data = '');
				}
				break;

			case $extra . 'scripture1':
				$esv          = 0;
				$scripturerow = 1;

				if ($header === 1)
				{
					$data = Text::_('JBS_CMN_SCRIPTURE');
				}
				else
				{
					(isset($item->booknumber) ? $data = $this->getScripture($params, $item, $esv, $scripturerow) : $data = '');
				}
				break;
			case $extra . 'scripture2':
				$esv          = 0;
				$scripturerow = 2;

				if ($header === 1)
				{
					$data = Text::_('JBS_CMN_SCRIPTURE');
				}
				else
				{
					(isset($item->booknumber2) ? $data = $this->getScripture($params, $item, $esv, $scripturerow) : $data = '');
				}
				break;
			case $extra . 'secondary':
				if ($header === 1)
				{
					$data = Text::_('JBS_CMN_SECONDARY_REFERENCES');
				}
				else
				{
					$data = $item->secondary ?? '';
				}
				break;
			case $extra . 'title':
				if ($header === 1)
				{
					$data = Text::_('JBS_CMN_TITLE');
				}
				else
				{
					isset($item->studytitle) ? $data = stripslashes($item->studytitle) : $data = '';
				}
				break;
			case $extra . 'date':
				if ($header === 1)
				{
					$data = Text::_('JBS_CMN_STUDY_DATE');
				}
				else
				{
					isset($item->studydate) ? $data = $this->getStudyDate($params, $item->studydate) : $data = '';
				}
				break;
			case $extra . 'teacher':
				if ($header === 1)
				{
					$data = Text::_('JBS_CMN_TEACHER');
				}
				else
				{
					(isset($item->teachername) ? $data = $item->teachername : $data = '');
				}

				break;
			case $extra . 'teacher-title':
				if ($header === 1)
				{
					$data = Text::_('JBS_CMN_TEACHER');
				}
				elseif (isset($item->title) && isset($item->teachername))
				{
					$data = $item->title . ' ' . $item->teachername;
				}
				else
				{
					$data = $item->teachername;
				}
				break;
			case $extra . 'studyintro':
				if ($header === 1)
				{
					$data = Text::_('JBS_CMN_STUDY_INTRO');
				}
				else
				{
					isset($item->studyintro) ? $data = HtmlHelper::_('content.prepare', $item->studyintro, '', 'com_proclaim.' . $type) : $data = '';
				}
				break;
			case $extra . 'series':
				if ($header === 1)
				{
					$data = Text::_('JBS_CMN_SERIES');
				}
				else
				{
					(isset($item->series_text) ? $data = $item->series_text : $data = '');
				}
				break;
			case $extra . 'seriesthumbnail':
				if ($header === 1)
				{
					$data = Text::_('JBS_CMN_THUMBNAIL');
				}
				else
				{
					if ($item->series_thumbnail)
					{
						$data = $this->useJImage($item->series_thumbnail, Text::_('JBS_CMN_THUMBNAIL'));
					}
					else
					{
						$data = '';
					}
				}
				break;
			case $extra . 'teacherlargeimage':
				if ($header === 1)
				{
					$data = Text::_('JBS_TCH_TEACHER_IMAGE');
				}
				elseif (isset($item->teacher_image) && !empty($item->teacher_image))
				{
					$data = $this->useJImage($item->teacher_image, Text::_('JBS_CMN_THUMBNAIL'));
				}
				else
				{
					$data = '';
				}
				break;
			case $extra . 'description':
				if ($type === 'seriesdisplays' || ($type === 'seriesdisplay' && $header !== 1))
				{
					(isset($item->description) ? $data = HTMLHelper::_('content.prepare', $item->description, '', 'com_proclaim.' . $type) : $data = '');

					if ($params->get('series_characters'))
					{
						$d    = substr($data, 0, $params->get('series_characters'));
						$data = substr($d, 0, strrpos($d, '. '));
					}

					if ($data)
					{
						$data .= '.';
					}
				}

				else
				{
					(isset($item->sdescription) ? $data = HtmlHelper::_('content.prepare', $item->sdescription, '', 'com_proclaim.' . $type) : $data = '');

					if ($params->get('series_characters'))
					{
						$d    = substr($data, 0, $params->get('series_characters'));
						$data = substr($d, 0, strrpos($d, '. '));
					}

					if ($data)
					{
						$data .= '.';
					}
				}

				if ($type === 'seriesdisplays' && !$header)
				{
					(isset($item->description) ? $data = stripslashes($item->description) : $data = '');

					if ($params->get('series_characters'))
					{
						$d    = substr($data, 0, $params->get('series_characters'));
						$data = substr($d, 0, strrpos($d, '. '));
					}

					if ($data)
					{
						$data .= '.';
					}
				}
				break;
			case $extra . 'submitted':
				if ($header === 1)
				{
					$data = Text::_('JBS_CMN_SUBMITTED_BY');
				}
				else
				{
					(isset($item->submitted) ? $data = $item->submitted : $data = '');
				}
				break;
			case $extra . 'hits':
				if ($header === 1)
				{
					$data = Text::_('JBS_CMN_VIEWS');
				}
				else
				{
					(isset($item->hits) ? $data = $item->hits : $data = '');
				}
				break;
			case $extra . 'downloads':
				if ($header === 1)
				{
					$data = Text::_('JBS_CMN_DOWNLOADS');
				}
				else
				{
					(isset($item->downloads) ? $data = $item->downloads : $data = '');
				}
				break;
			case $extra . 'studynumber':
				if ($header === 1)
				{
					$data = Text::_('JBS_CMN_STUDYNUMBER');
				}
				else
				{
					(isset($item->studynumber) ? $data = $item->studynumber : $data = '');
				}
				break;
			case $extra . 'topic':
				if ($header === 1)
				{
					$data = Text::_('JBS_CMN_TOPIC');
				}
				elseif (isset($item->topics_text))
				{
					if (substr_count($item->topics_text, ','))
					{
						$topics = explode(',', $item->topics_text);

						foreach ($topics as $key => $value)
						{
							$topics[$key] = Text::_($value);
						}

						$data = implode(', ', $topics);
					}
					else
					{
						(isset($item->topics_text) ? $data = Text::_($item->topics_text) : $data = '');
					}
				}
				break;
			case $extra . 'locations':
				if ($header === 1)
				{
					$data = Text::_('JBS_CMN_LOCATION');
				}
				else
				{
					(isset($item->location_text) ? $data = $item->location_text : $data = '');
				}
				break;
			case $extra . 'jbsmedia':
				if ($header === 1)
				{
					$data = Text::_('JBS_CMN_MEDIA');
				}
				else
				{
					$data = $this->getFluidMediaFiles($item, $params, $template);
				}
				break;
			case $extra . 'messagetype':
				if ($header === 1)
				{
					$data = Text::_('JBS_CMN_MESSAGETYPE');
				}
				else
				{
					(isset($item->message_type) ? $data = $item->message_type : $data = '');
				}
				break;
			case $extra . 'thumbnail':
				if ($header === 1)
				{
					$data = Text::_('JBS_CMN_THUMBNAIL');
				}
				elseif ($item->thumbnailm || $params->get('studyimage') !== '-1')
				{
					$data = $this->useJImage($item->thumbnailm, Text::_('JBS_CMN_THUMBNAIL'));

					if ($params->get('studyimage') !== '-1')
					{
						$data = $this->useJImage('media/com_proclaim/images/stockimages/' . $registry->get('studyimage'), Text::_('JBS_CMN_THUMBNAIL'));
					}
				}
				else
				{
					$data = '';
				}
				break;
			case $extra . 'teacherimage':
				if ($header === 1)
				{
					$data = Text::_('JBS_TCH_TEACHER_IMAGE');
				}
				elseif ($type === 'seriesdisplays' || $type === 'seriesdisplay' || $type === 'teachers' || $type === 'teacher')
				{
					if (isset($item->teacher_thumbnail) && !empty($item->teacher_thumbnail))
					{
						$data = $this->useJImage($item->teacher_thumbnail, Text::_('JBS_CMN_THUMBNAIL'));
					}
					else
					{
						$data = '';
					}
				}
				else
				{
					if ($item->thumb)
					{
						$data = $this->useJImage($item->thumb, Text::_('JBS_CMN_THUMBNAIL'));
					}
					else
					{
						$data = '';
					}
				}
				break;
		}

		$style       = '';
		$customclass = '';

		if (isset($row->custom))
		{
			if (strpos($row->custom, 'style=') !== false)
			{
				$style = $row->custom;
			}
			else
			{
				$customclass = $row->custom;
			}
		}

		$classelement = $this->createelement($row->element);

		/*
		if ($header === '1')
		{
			$classelement = '';
			$style        = 'style="font-weight:bold;"';
		}
		*/
		if ($classelement)
		{
			if (isset($style))
			{
				$style = ' ' . $style;
			}

			$classopen  = '<' . $classelement . $style . '>';
			$classclose = '</' . $classelement . '>';
		}
		else
		{
			$classopen  = '';
			$classclose = '';
		}

		// See whether the element is a link to something and get the link from the function
		$link = 0;

		if ($type === 'sermons' || $type === 'seriesdisplays' || $type === 'teachers')
		{
			if ($row->linktype > 0 && $header === 0)
			{
				if ($type === 'seriesdisplays')
				{
					$item->teacher_id = $item->teacher;
				}

				if ($type === 'teachers')
				{
					$item->teacher_id = $item->id;
				}

				$link = $this->getLink($row->linktype, $item->id, $item->teacher_id, $params, $item, $template);
			}
		}

		$tdadd = '';

		if ($row->colspan > 0)
		{
			$tdadd = 'colspan="' . $row->colspan . '"';
		}

		if ($header === 0)
		{
			$frow = '<td scope="col"' . $tdadd . '>';
		}

		// If ($header === 1) {$frow = '<th>';}
		if ($header === 1)
		{
			$frow = '';
		}

		if ($customclass)
		{
			$frow .= ' ' . $customclass;
		}

		// $frow .= '" about="' . $row->name . '">' . $classopen;

		if ($link)
		{
			$frow .= $link;
		}

		if ($data && $header === 0)
		{
			$frow .= $classopen . $data;
		}

		if ($data && $header === 1)
		{
			$frow .= $data;
		}

		if ($link)
		{
			$frow .= '</a>';
		}

		// If ($header === 0){ $frow .= $classclose . '</td>';}
		if ($header === 0)
		{
			$frow .= '</' . $classclose . '</td>';
		}

		if ($header === 1)
		{
			$frow .= '</th>';
		}

		return $frow;
	}

	/**
	 * Get Fluid Custom
	 *
	 * @param   String                   $custom    Custom String
	 * @param   Object                   $item      Study Item
	 * @param   Registry                 $params    Params
	 * @param   CWMTemplateTable|Object  $template  Template Table Data
	 * @param   String                   $type      Type of data
	 *
	 * @return array|string|string[]
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function getFluidCustom(string $custom, object $item, Registry $params, $template, string $type)
	{
		$countbraces = substr_count($custom, '{');

		while ($countbraces > 0)
		{
			$bracebegin = strpos($custom, '{');
			$braceend   = strpos($custom, '}');
			$subcustom  = substr($custom, ($bracebegin + 1), (($braceend - $bracebegin) - 1));

			$element = $this->getElement($subcustom, $item, $params, $template, $type);
			$custom  = substr_replace($custom, $element, $bracebegin, (($braceend - $bracebegin) + 1));
			$countbraces--;
		}

		return $custom;
	}

	/**
	 * Get Element
	 *
	 * @param   String                   $custom    Custom String
	 * @param   Object                   $row       Row Data
	 * @param   Registry                 $params    Params
	 * @param   CWMTemplateTable|Object  $template  Template Data
	 * @param   String                   $type      Type of element
	 *
	 * @return mixed|null|string
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function getElement(string $custom, object $row, Registry $params, $template, string $type)
	{
		$element = null;

		switch ($custom)
		{
			case 'scripture1':
				$esv          = 0;
				$scripturerow = 1;
				$element      = $this->getScripture($params, $row, $esv, $scripturerow);
				break;
			case 'scripture2':
				$esv          = 0;
				$scripturerow = 2;
				$element      = $this->getScripture($params, $row, $esv, $scripturerow);
				break;
			case 'secondary':
				$element = $row->secondary_reference;
				break;
			case 'title':
				$element = $row->studytitle ?? '';
				break;
			case 'studyintro':
				if (isset($row->studyintro))
				{
					$element = HtmlHelper::_('content.prepare', $row->studyintro, '', 'com_proclaim.' . $type);
				}
				else
				{
					$element = '';
				}
				break;
			case 'teacher':
				// Teacher name and title
				if (isset($row->teachertitle) && isset($row->teachername))
				{
					$element = $row->teachertitle . ' ' . $row->teachername;
				}
				else
				{
					$element = '';
				}
				break;
			case 'studynumber':
				$element = $row->studynumber ?? '';
				break;
			case 'series_text':
				// Series title
				$element = $row->series_text ?? '';
				break;
			case 'series_thumbnail':
				if ($row->series_thumbnail)
				{
					$element = $this->useJImage($row->series_thumbnail, $row->series_text);
				}
				else
				{
					$element = '';
				}
				break;
			case 'submitted':
				if (isset($row->submitted))
				{
					$element = $row->submitted;
				}
				else
				{
					$element = '';
				}
				break;
			case 'teacherimage':
				if (isset($row->teacher_thumbnail))
				{
					$element = $this->useJImage($row->teacher_thumbnail, $row->teachername);
				}
				else
				{
					$element = '';
				};
				break;
			case 'teachername':
				if (isset($row->teachername))
				{
					$element = $row->teachername;
				}
				else
				{
					$element = '';
				}
				break;
			case 'jbsmedia':
				if (isset($row->mids))
				{
					$medias          = $this->getFluidMediaids($row);
					$mediafiles      = $this->getMediaFiles($medias);
					$row->mediafiles = $mediafiles;
					$element         = $this->getFluidMediaFiles($row, $params, $template);
				}
				else
				{
					$element = '';
				}
				break;
			case 'thumbnail':
				// Assume study thumbnail
				$element = $this->useJImage($row->thumbnailm, $row->studytitle);

				if ($params->get('studyimage') !== '-1')
				{
					// Clean up extra data in the image
					$hash = str_contains($params->get('studyimage'), '#');

					if ($hash == 1)
					{
						$imageparam   = $params->get('studyimage');
						$hashlocation = strpos($imageparam, '#');
						$image        = substr($imageparam, 0, $hashlocation);
						$element      = $this->useJImage($image, $row->studytitle);
					}
				}

				break;
			case 'studytitle':
				(isset($row->studytitle) ? $element = $row->studytitle : $element = '');
				break;
			case 'teacher-title-name':
				if (isset($row->teachertitle, $row->teachername))
				{
					$element = $row->teachertitle . ' ' . $row->teachername;
				}
				else
				{
					$element = '';
				}
				break;

			case 'topics':
				if (isset($row->topics_text))
				{
					if (substr_count($row->topics_text, ','))
					{
						$topics = explode(',', $row->topics_text);

						foreach ($topics as $key => $value)
						{
							$topics[$key] = Text::_($value);
						}

						$element = implode(', ', $topics);
					}
					else
					{
						(isset($row->topics_text) ? $element = Text::_($row->topics_text) : $element = '');
					}
				}
				break;
			case 'message_type':
				if (isset($row->message_type))
				{
					$element = $row->message_type;
				}
				else
				{
					$element = '';
				}
				break;
			case 'location_text':
				if (isset($row->location_text))
				{
					$element = $row->location_text;
				}
				else
				{
					$element = '';
				}
				break;
			case 'date':
				if (isset($row->studydate))
				{
					$element = $this->getStudyDate($params, $row->studydate);
				}
				else
				{
					$element = '';
				}
				break;
			case 'series_description':

				if (isset($row->sdescription))
				{
					if ($type === 'seriesdisplays' || $type === 'seriesdisplay')
					{
						$element = HtmlHelper::_('content.prepare', $row->description, '', 'com_proclaim.' . $type);
					}
					else
					{
						$element = HtmlHelper::_('content.prepare', $row->sdescription, '', 'com_proclaim.' . $type);
					}
				}
				else
				{
					$element = '';
				}
				break;
			case 'hits':
				if (isset($row->hits))
				{
					$element = Text::_('JBS_CMN_HITS') . ' ' . $row->hits;
				}
				else
				{
					$element = '';
				}
				break;
		}

		return $element;
	}

	/**
	 * Get Scripture
	 *
	 * @param   Registry  $params        Item Params
	 * @param   object    $row           Row Info
	 * @param   int       $esv           ESV String
	 * @param   int       $scripturerow  Scripture Row
	 *
	 * @return string
	 *
	 * @since 7.0
	 */
	public function getScripture(Registry $params, $row, int $esv, int $scripturerow)
	{
		$scripture = '';
		$book      = '';

		if (!isset($row->id))
		{
			return null;
		}

		if (!isset($row->booknumber) || empty($row->booknumber))
		{
			$row->booknumber = 0;
		}

		if (!isset($row->booknumber2))
		{
			$row->booknumber2 = 0;
		}

		if ($scripturerow === 2 && $row->booknumber2 >= 1)
		{
			$booknumber = $row->booknumber2;
			$ch_b       = $row->chapter_begin2;
			$ch_e       = $row->chapter_end2;
			$v_b        = $row->verse_begin2;
			$v_e        = $row->verse_end2;
			$book       = Text::_($row->bookname2);
		}
		elseif ($scripturerow === 1 && isset($row->booknumber) >= 1)
		{
			$booknumber = $row->booknumber;
			$ch_b       = $row->chapter_begin;
			$ch_e       = $row->chapter_end;
			$v_b        = $row->verse_begin;
			$v_e        = $row->verse_end;

			if (isset($row->bookname))
			{
				$book = Text::_($row->bookname);
			}
		}

		if (!isset($booknumber) || $booknumber === "-1")
		{
			return $scripture;
		}

		$show_verses = (int) $params->get('show_verses');

		if (!isset($row->bookname))
		{
			$scripture = '';

			return $scripture;
		}

		$b1  = ' ';
		$b2  = ':';
		$b2a = ':';
		$b3  = '-';

		if ($show_verses === 1)
		{
			/** @var $ch_b string */
			/** @var $v_b string */
			/** @var $ch_e string */
			/** @var $v_e string */
			if ($ch_e === $ch_b)
			{
				$ch_e = '';
				$b2a  = '';
			}

			if ($ch_e === $ch_b && $v_b === $v_e)
			{
				$b3   = '';
				$ch_e = '';
				$b2a  = '';
				$v_e  = '';
			}

			if (empty($v_b) || $v_b === '0')
			{
				$v_b = '';
				$v_e = '';
				$b2a = '';
				$b2  = '';
			}

			if (empty($v_e) || $v_e === '0')
			{
				$v_e = '';
				$b2a = '';
			}

			if (empty($ch_e) || $ch_e === '0')
			{
				$b2a  = '';
				$ch_e = '';

				if (empty($v_e) || $v_e === '0')
				{
					$b3 = '';
				}
			}

			$scripture = $book . $b1 . $ch_b . $b2 . $v_b . $b3 . $ch_e . $b2a . $v_e;
		}

		// Else
		if ($show_verses === 0)
		{
			/** @var $ch_e string */
			/** @var $ch_b string */
			if ($ch_e > $ch_b)
			{
				$scripture = $book . $b1 . $ch_b . $b3 . $ch_e;
			}
			else
			{
				$scripture = $book . $b1 . $ch_b;
			}
		}

		if ($esv === 1)
		{
			/** @var $ch_b string */
			/** @var $v_b string */
			/** @var $ch_e string */
			/** @var $v_e string */
			if ($ch_e === $ch_b)
			{
				$ch_e = '';
				$b2a  = '';
			}

			if (empty($v_b) || $v_b === '0')
			{
				$v_b = '';
				$v_e = '';
				$b2a = '';
				$b2  = '';
			}

			if (empty($v_e) || $v_e === '0')
			{
				$v_e = '';
				$b2a = '';
			}

			if (empty($ch_e) || $ch_e === '0')
			{
				$b2a  = '';
				$ch_e = '';

				if (empty($v_e) || $v_e === '0')
				{
					$b3 = '';
				}
			}

			$scripture = $book . $b1 . $ch_b . $b2 . $v_b . $b3 . $ch_e . $b2a . $v_e;
		}

		if ($row->booknumber > 166)
		{
			$scripture = $book;
		}

		if ($show_verses === 2)
		{
			$scripture = $book;
		}

		return $scripture;
	}

	/**
	 * Get Fluid Media Files
	 *
	 * @param   Object                   $item      Study item
	 * @param   Registry                 $params    Params
	 * @param   CWMTemplateTable|Object  $template  Template return
	 *
	 * @return string
	 *
	 * @throws \Exception
	 * @since 9.0.0
	 */
	public function getFluidMediaFiles(object $item, Registry $params, $template): string
	{
		$med = new CWMMedia;

		$mediarow = '<div class="bsms_media_container row" style="float:left;"  >';

		foreach ($item->mediafiles as $media)
		{
			$mediarow .= '<div id="bsms_media_file' . $media->id . '" class="col bsms_media_file" >' .
				$med->getFluidMedia($media, $params, $template) . '</div>';
		}

		$mediarow .= '</div>';
		$mediarow .= '<div style="clear:both;"></div>';

		return $mediarow;
	}

	/**
	 * Get StudyDate
	 *
	 * @param   Registry  $params     Item Params
	 * @param   string    $studydate  Study Date
	 *
	 * @return string
	 *
	 * @since 7.0
	 */
	public function getStudyDate(Registry $params, string $studydate): string
	{
		$customDate = $params->get('custom_date_format');

		if (empty($customDate))
		{
			try
			{
				switch ($params->get('date_format'))
				{
					case 0:
						$date = HtmlHelper::_('date', $studydate, "M j, Y", null);
						break;
					case 1:
						$date = HtmlHelper::_('date', $studydate, "M J", null);
						break;
					case 2:
						$date = HtmlHelper::_('date', $studydate, "n/j/Y", null);
						break;
					case 4:
						$date = HtmlHelper::_('date', $studydate, "l, F j, Y", null);
						break;
					case 5:
						$date = HtmlHelper::_('date', $studydate, "F j, Y", null);
						break;
					case 6:
						$date = HtmlHelper::_('date', $studydate, "j F Y", null);
						break;
					case 7:
						$date = date("j/n/Y", strtotime($studydate));
						break;
					case 8:
						$date = HtmlHelper::_('date', $studydate, Text::_('DATE_FORMAT_LC'), null);
						break;
					case 9:
						$date = HtmlHelper::_('date', $studydate, "Y/M/D", null);
						break;
					default:
						$date = HtmlHelper::_('date', $studydate, "n/j", null);
						break;
				}
			}
			catch (Exception $e)
			{
				return $studydate;
			}
		}
		else
		{
			try
			{
				$date = HtmlHelper::_('date', $studydate, $customDate);
			}
			catch (\Exception $e)
			{
				return $studydate;
			}
		}

		return $date;
	}

	/**
	 * Use JImage Class
	 *
	 * @param   String       $path    Path to File
	 * @param   String|null  $alt     Alternate Text
	 * @param   String|null  $id      CSS ID for the image
	 * @param   int|null     $width   Width
	 * @param   int|null     $height  Height
	 * @param   string|null  $class   CSS Class
	 *
	 * @return boolean|\stdClass
	 *
	 * @since 9.0.0
	 */
	public function useJImage(string $path, string $alt = null, string $id = null, int $width = null, int $height = null, string $class = null)
	{
		$return = false;

		try
		{
			$image = Image::getImageFileProperties($path);
		}
		catch (\Exception $e)
		{
			$return = false;
		}

		if ($id)
		{
			$id = ' id="' . $id . '" ';
		}

		if ($width)
		{
			$width = ' width="' . $width . '" ';
		}

		if ($height)
		{
			$height = ' height="' . $height . '" ';
		}

		if ($class)
		{
			$class = ' class="' . $class . '" ';
		}

		if (!empty($path) && isset($image->attributes))
		{
			$return = '<img src="' . Uri::base() . $path . '"' . $id . $width . $height
				. 'alt="' . $alt . '" ' . $class . $image->attributes . ' />';
		}

		return $return;
	}

	/**
	 *  Get Link
	 *
	 * @param   bool                     $islink      True is a link, False is not
	 * @param   string                   $id3         Id3 data
	 * @param   int                      $tid         Template ID
	 * @param   Registry                 $params      Params
	 * @param   object                   $row         Row data
	 * @param   CWMTemplateTable|Object  $templateid  Template Table Data
	 *
	 * @return string
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	private function getLink(bool $islink, string $id3, int $tid, Registry $params, object $row, $templateid): string
	{
		$input  = new Input;
		$view   = $input->getString('view', '');
		$column = '';

		switch ($islink)
		{
			case 1 :
				$link = Route::_('index.php?option=com_proclaim&view=CWMSermon&id=' . $row->slug . '&t=' . $params->get('detailstemplateid'));

				if ($view === 'seriesdisplays')
				{
					$link = Route::_('index.php?option=com_proclaim&view=CWMSeriesDisplay&id=' . $row->slug .
						'&t=' . $params->get('detailstemplateid')
					);
				}

				$column = '<a href="' . $link . '">';
				break;

			case 3 :
				$link   = Route::_('index.php?option=com_proclaim&view=CWMTeacher&id=' . $tid . '&t=' . $params->get('teachertemplateid'));
				$column .= '<a href="' . $link . '">';
				break;

			case 4 :
				// Case 4 is a details link with tooltip

				$link = Route::_(CWMHelperRoute::getArticleRoute($row->slug) . '&t=' . $params->get('detailstemplateid'));

				$column = CWMHelper::getTooltip($row, $params, $templateid);
				$column .= '<a href="' . $link . '">';

				break;

			case 5 :
				$column = CWMHelper::getTooltip($row, $params, $templateid);
				break;

			case 6 :
				// Case 6 is for a link to the 1st article in the media file records
				$column .= '<a href="' . $this->getOtherlinks($id3, $islink, $params) . '">';
				break;

			case 7 :
				// Case 7 is for Virtuemart
				$column .= '<a href="' . $this->getOtherlinks($id3, $islink, $params) . '">';
				break;

			case 8 :
				// Case 8 is for Docman
				$column .= '<a href="' . $this->getOtherlinks($id3, $islink, $params) . '">';
				break;

			case 9 :
				// Case 9 is a link to download
				$column .= '<a href="index.php?option=com_proclaim&amp;view=CWMSermon&amp;mid=' .
					$row->download_id . '&amp;task=download">';
		}

		return $column;
	}

	/**
	 * Get Other Links
	 *
	 * @param   int       $id3     Study ID ID
	 * @param   string    $islink  Is a Link
	 * @param   Registry  $params  Item Params
	 *
	 * @return string
	 *
	 * @since 7.0
	 */
	public function getOtherlinks(int $id3, string $islink, Registry $params)
	{
		$link  = '';
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select('#__bsms_mediafiles.*')
			->from('#__bsms_mediafiles')
			->where('study_id = ' . $db->q($id3))
			->where('#__bsms_mediafiles.published = 1');
		$db->setQuery($query);
		$db->execute();
		$num_rows = $db->getNumRows();

		if ($num_rows > 0)
		{
			$mediafiles = $db->loadObjectList();

			foreach ($mediafiles as $media)
			{
				switch ($islink)
				{
					case 6:
						if ($media->article_id > 0)
						{
							$link = 'index.php?option=com_content&view=article&id=' . $media->article_id;
						}
						break;

					case 7:
						if ($media->virtueMart_id > 0)
						{
							$link = 'index.php?option=com_virtuemart&page=shop.product_details&flypage='
								. $params->get('store_page', 'flypage.tpl') . '&product_id=' . $media->virtueMart_id;
						}
						break;

					case 8:
						if ($media->docMan_id > 0)
						{
							$link = 'index.php?option=com_docman&task=doc_download&gid=' . $media->docMan_id;
						}
						break;
				}
			}
		}

		return $link;
	}

	/**
	 * Get Listing Exp
	 *
	 * @param   object                   $row       Item Info
	 * @param   Registry                 $params    Item Params
	 * @param   CWMTemplateTable|Object  $template  Template
	 *
	 * @return object
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function getListingExp(object $row, Registry $params, $template): object
	{
		$Media  = new CWMMedia;
		$images = new CWMImages;
		$image  = CWMImages::getStudyThumbnail($row->thumbnailm);
		$label  = $params->get('templatecode');
		$label  = str_replace('{{teacher}}', $row->teachername, $label);
		$label  = str_replace('{{title}}', $row->studytitle, $label);
		$label  = str_replace('{{date}}', $this->$params, $row->studydate, $label);
		$label  = str_replace('{{studyintro}}', $row->studyintro, $label);
		$label  = str_replace('{{scripture}}', $this->getScripture($params, $row, 0, 1), $label);
		$label  = str_replace('{{topics}}', $row->topic_text, $label);
		$label  = str_replace('{{url}}', Route::_('index.php?option=com_proclaim&view=CWMSermon&id=' . $row->id . '&t=' . $template->id), $label);
		$label  = str_replace('{{thumbnail}}', $this->useJImage($image->path, "", "bsms_studyThumbnail" . $row->id, $image->width, $image->height), $label);
		$label  = str_replace('{{seriestext}}', $row->series_text, $label);
		$label  = str_replace('{{messagetype}}', $row->message_type, $label);
		$label  = str_replace('{{bookname}}', $row->bookname, $label);
		$label  = str_replace('{{topics}}', $row->topic_text, $label);
		$label  = str_replace('{{hits}}', $row->hits, $label);
		$label  = str_replace('{{location}}', $row->location_text, $label);
		$label  = str_replace('{{plays}}', $row->totalplays, $label);
		$label  = str_replace('{{downloads}}', $row->totaldownloads, $label);

		// For now we need to use the existing mediatable function to get all the media
		$mediaTable = $Media->getFluidMedia($row, $params, $template);
		$label      = str_replace('{{media}}', $mediaTable, $label);

		// Need to add template items for media...

		return $label;
	}

	/**
	 * Get Passage
	 *
	 * @param   Registry  $params  Item Params
	 * @param   object    $row     Item Info
	 *
	 * @return string
	 *
	 * @since 7.0
	 */
	public function getPassage(Registry $params, object $row): string
	{
		$esv          = 1;
		$scripturerow = 1;
		$scripture    = $this->getScripture($params, $row, $esv, $scripturerow);

		if ($scripture)
		{
			$key      = "IP";
			$response = "" . $scripture . " (ESV)";
			$passage  = urlencode($scripture);
			$options  = "include-passage-references=false";
			$url      = "http://www.esvapi.org/v2/rest/passageQuery?key=$key&passage=$passage&$options";

			// This tests to see if the curl functions are there. It will return false if curl not installed
			$p = (get_extension_funcs("curl"));

			if ($p)
			{
				// If curl is installed then we go on

				// This will return false if curl is not enabled
				$ch = curl_init($url);

				if ($ch)
				{
					// This will return false if curl is not enabled
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					$response .= curl_exec($ch);
					curl_close($ch);
				}
			}
		}
		else
		{
			$response = Text::_('JBS_STY_NO_PASSAGE_INCLUDED');
		}

		return $response;
	}

	/**
	 * Share Helper file
	 *
	 * @param   string    $link    Link
	 * @param   object    $row     Item Info
	 * @param   Registry  $params  Item Params
	 *
	 * @return null|string
	 *
	 * @since 7.0
	 */
	public function getShare(string $link, object $row, Registry $params): ?string
	{

		$shareit = '<div class="row">';

		$shareit .= '<div class="pull-right">
						<!-- AddThis Button BEGIN -->
						<a class="addthis_button" href="//www.addthis.com/bookmark.php?v=250">
						<img src="//s7.addthis.com/static/btn/v2/lg-share-en.gif" width="125" height="16" alt="Share" />
						</a>
						<script type="text/javascript">var addthis_config = {"data_track_clickback":true};</script>
						<script type="text/javascript" src="//s7.addthis.com/js/250/addthis_widget.js"></script>
						<!-- AddThis Button END -->
						</div>';

		$shareit .= '</div>';

		return $shareit;
	}

	/**
	 * make a URL small
	 *
	 * @param   string  $url      Url
	 * @param   string  $login    Login
	 * @param   string  $appkey   AppKey
	 * @param   string  $format   Format
	 * @param   string  $version  Version
	 *
	 * @return string
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	private function make_bitly_url(string $url, string $login, string $appkey, string $format = 'xml', string $version = '2.0.1'): string
	{
		// Create the URL
		$bitly = 'http://api.bit.ly/shorten?version=' . $version . '&longUrl=' . urlencode($url) . '&login='
			. $login . '&apiKey=' . $appkey . '&format=' . $format;

		// Get the url
		// Could also use cURL here
		$response = file_get_contents($bitly);

		// Parse depending on desired format
		if (strtolower($format) === 'json')
		{
			$json  = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
			$short = $json['results'][$url]['shortUrl'];
		}
		else
		{
			// Xml
			$xml   = simplexml_load_string($response);
			$short = 'http://bit.ly/' . $xml->results->nodeKeyVal->hash;
		}

		return $short;
	}

	/**
	 * Create a Element
	 *
	 * @param   string|null  $element  Case that will chose the element
	 *
	 * @return string
	 *
	 * @since 7.0
	 */
	public function createelement(?string $element): string
	{
		switch ($element)
		{
			case 1:
				$classelement = 'p';
				break;
			case 2:
				$classelement = 'h1';
				break;
			case 3:
				$classelement = 'h2';
				break;
			case 4:
				$classelement = 'h3';
				break;
			case 5:
				$classelement = 'h4';
				break;
			case 6:
				$classelement = 'h5';
				break;
			case 7:
				$classelement = 'blockquote';
				break;
			default:
				$classelement = 'span';
				break;
		}

		return $classelement;
	}
}
