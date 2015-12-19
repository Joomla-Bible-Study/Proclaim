<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 *  Class for Series List
 *
 * @package  BibleStudy.Site
 * @since    8.0.0
 *
 *
 */
class JBSMSerieslist extends JBSMListing
{
<<<<<<< HEAD
=======
	/**
	 * Get SeriesList
	 *
	 * @param   object    $row           JTable
	 * @param   JRegistry $params        Item Params
	 * @param   string    $oddeven       Odd Even
	 * @param   JRegistry $admin_params  Admin Params
	 * @param   object    $template      Template
	 * @param   string    $view          View
	 *
	 * @return string
	 */
	public function getSerieslist($row, $params, $oddeven, $admin_params, $template, $view)
	{
		$listing = '';

		// Set the slug if not present
		$row->slug = $row->alias ? ($row->id . ':' . $row->alias) : $row->id . ':'
			. str_replace(' ', '-', htmlspecialchars_decode($row->series_text, ENT_QUOTES));

		if ($params->get('series_show_description') == 0)
		{
			$listing .= '<tr class="onlyrow ' . $oddeven . '">';
		}
		else
		{
			$listing .= '<tr class="firstrow firstcol ' . $oddeven . '">';
		}

		$custom        = $params->get('seriescustom1');
		$listelementid = $params->get('serieselement1');
		$islink        = $params->get('seriesislink1');
		$r             = 'firstcol';
		$listelement   = $this->seriesGetelement($r, $row, $listelementid, $custom, $islink, $admin_params, $params, $view);
		$listing .= $listelement;

		if (!$listelementid)
		{
			$listing .= '<td class="firstrow firstcol">';
			$listing .= '</td>';
		}

		$custom        = $params->get('seriescustom2');
		$listelementid = $params->get('serieselement2');
		$islink        = $params->get('seriesislink2');
		$r             = '';
		$listelement   = $this->seriesGetelement($r, $row, $listelementid, $custom, $islink, $admin_params, $params, $view);
		$listing .= $listelement;

		if (!$listelementid)
		{
			$listing .= '<td >';
			$listing .= '</td>';
		}
		$custom        = $params->get('seriescustom3');
		$listelementid = $params->get('serieselement3');
		$islink        = $params->get('seriesislink3');
		$r             = '';
		$listelement   = $this->seriesGetelement($r, $row, $listelementid, $custom, $islink, $admin_params, $params, $view);
		$listing .= $listelement;

		if (!$listelementid)
		{
			$listing .= '<td >';
			$listing .= '</td>';
		}

		$custom        = $params->get('seriescustom4');
		$listelementid = $params->get('serieselement4');
		$islink        = $params->get('seriesislink4');
		$r             = 'lastcol';
		$listelement   = $this->seriesGetelement($r, $row, $listelementid, $custom, $islink, $admin_params, $params, $view);
		$listing .= $listelement;

		if (!$listelementid)
		{
			$listing .= '<td class="lastcol"></td>';
		}
		$listing .= '</tr>';

		// Add if last row to above

		if ($params->get('series_show_description') > 0)
		{
			$listing .= '<tr class="lastrow ' . $oddeven . '">';
			$listing .= '<td colspan="4" class="description">';

			if ($params->get('series_characters') && $view == 0)
			{
				$listing .= substr($row->description, 0, $params->get('series_characters'));
				$listing .= ' - ' . '<a href="'
					. JRoute::_('index.php?option=com_biblestudy&view=seriesdisplay&id=' . $row->slug . '&t=' . $params->get('seriesdetailtemplateid', 1))
					. '">' . JText::_('JBS_CMN_READ_MORE') . '</a>';
			}
			else
			{
				$listing .= $row->description;
			}
			$listing .= '</td></tr>';
		}

		return $listing;
	}

	// Elements are: series title, series image, series pastor + image, description
	/**
	 * Get SeriesLink
	 *
	 * @param   string    $islink        Is a link
	 * @param   object    $row           Row Info
	 * @param   string    $element       Element
	 * @param   JRegistry $params        Item Params
	 * @param   JRegistry $admin_params  Admin Params
	 *
	 * @return string
	 */
	public function getSerieslink($islink, $row, $element, $params, $admin_params)
	{
		if ($islink == 1)
		{
			$link = '<a href="'
				. JRoute::_('index.php?option=com_biblestudy&view=seriesdisplay&id=' . $row->slug . '&t=' . $params->get('seriesdetailtemplateid', 1))
				. '">' . $element . '</a>';
		}
		else
		{
			$link = '<a href="' . JRoute::_('index.php?option=com_biblestudy&view=teacher&id=' . $row->id . '&t=' . $params->get('teachertemplateid', 1))
				. '">' . $element . '</a>';
		}

		return $link;
	}

	/**
	 * Get StudiesLink
	 *
	 * @param   string    $islink        Is a Link
	 * @param   object    $row           JTable
	 * @param   object    $element       Element
	 * @param   JRegistry $params        Item Params
	 * @param   JRegistry $admin_params  Admin Params
	 *
	 * @return string
	 */
	public function getStudieslink($islink, $row, $element, $params, $admin_params)
	{
		$link = '<a href="' . JRoute::_('index.php?option=com_biblestudy&view=sermon&id=' . $row->id . '&t=' . $params->get('detailstemplateid', 1))
			. '">' . $element . '</a>';

		return $link;
	}

	/**
	 * Series Get Element
	 *
	 * @param   string    $r              ?
	 * @param   object    $row            JTable
	 * @param   int       $listelementid  Elemint ID
	 * @param   string    $custom         Costum
	 * @param   string    $islink         Is a Link
	 * @param   JRegistry $admin_params   Admin Params
	 * @param   JRegistry $params         Item Params
	 * @param   string    $view           View
	 *
	 * @return string
	 */
	public function seriesGetelement($r, $row, $listelementid, $custom, $islink, $admin_params, $params, $view)
	{
		$element = '';

		switch ($listelementid)
		{
			case 1:
				$element = $row->series_text;

				if ($islink > 0)
				{
					$element = $this->getSerieslink($islink, $row, $element, $params, $admin_params);
				}
				$element = '<td class="' . $r . ' title">' . $element . '</td>';
				break;
			case 2:
				$images = new JBSMImages;
				$image  = $images->getSeriesThumbnail($row->series_thumbnail);

				$element = '<img src="' . $image->path . '" height="' . $image->height . '" width="' . $image->width . '" alt="' . $row->series_text . '">';

				if ($islink > 0 && $view == 0)
				{
					$element = $this->getSerieslink($islink, $row, $element, $params, $admin_params);
				}
				$element = '<td class="' . $r . ' thumbnail image">' . $element . '</td>';
				break;
			case 3:
				$images   = new JBSMImages;
				$image    = $images->getSeriesThumbnail($row->series_thumbnail);
				$element1 = '<td class="' . $r . ' thumbnail"> <table class="table" id="seriestable"><tr class="noborder"><td>';
				$element2 = '<img src="' . $image->path . '" height="' . $image->height . '" width="' . $image->width . '" alt="' . $row->series_text . '">';
				$element3 = '</td></tr>';
				$element4 = $row->series_text;

				if ($islink > 0 && $view == 0)
				{
					$element4 = $this->getSerieslink($islink, $row, $element4, $params, $admin_params);
				}
				$element = $element1 . $element2 . $element3 . '</td></tr>';
				$element .= '<tr class="noborder"><td class="' . $r . ' title">' . $element4 . '</td>';
				$element .= '</tr></table></td>';
				break;
			case 4:
				$element = $row->teachertitle . ' - ' . $row->teachername;

				if ($islink > 0)
				{
					$element = $this->getSerieslink($islink, $row, $element, $params, $admin_params);
				}
				$element = '<td class="' . $r . ' teacher">' . $element . '</td>';
				break;
			case 5:
				$images = new JBSMImages;
				$image  = $images->getTeacherThumbnail($row->teacher_thumbnail, $row->thumb);

				$element = '<img src="' . $image->path . '" height="' . $image->height . '" width="' . $image->width . '" alt="' . $row->teachername . '">';

				if ($islink > 0)
				{
					$element = $this->getSerieslink($islink, $row, $element, $params, $admin_params);
				}
				$element = '<td class="' . $r . ' teacher image">' . $element . '</td>';
				break;
			case 6:
				$element1 = '<table class="table" id="seriestable"><tr class="noborder"><td class="' . $r . ' teacher">';
				$images   = new JBSMImages;
				$image    = $images->getTeacherThumbnail($row->teacher_thumbnail, $row->thumb);

				if ($image->height != 0 && $image->width != 0)
				{
					$element2 = '<img src="' . $image->path . '" height="' . $image->height . '" width="' . $image->width . '" alt="' . $row->teachername . '">';
				}
				else
				{
					$element2 = '';
				}
				$element3 = '</td></tr><tr class="noborder"><td class="' . $r . ' teacher">';

				if ($row->teachertitle && $row->teachername)
				{
					$element4 = $row->teachertitle . ' - ' . $row->teachername;
				}
				else
				{
					$element4 = null;
				}

				if ($islink > 0)
				{
					$element4 = $this->getSerieslink($islink, $row, $element4, $params, $admin_params);
				}
				$element = $element1 . $element2 . $element3 . $element4 . '</td></tr></table>';
				$element = '<td class="' . $r . ' teacher image">' . $element . '</td>';
				break;
			case 7:
				$element = $row->description;

				if ($islink > 0)
				{
					$element = $this->getSerieslink($islink, $row, $element, $params, $admin_params);
				}
				$element = '<td class="' . $r . ' description"><p>' . $element . '</p></td>';
				break;
		}

		return $element;
	}
>>>>>>> Joomla-Bible-Study/master

	/**
	 * Series Get Custom
	 *
	 * @param   string    $r              ?
	 * @param   object    $row            JTable
	 * @param   object    $customelement  ?
	 * @param   string    $custom         ?
	 * @param   string    $islink         Is a Link
	 * @param   Registry  $params         Item Params
	 *
	 * @return string
	 */
	public function seriesGetcustom($r, $row, $customelement, $custom, $islink, $params)
	{
		$countbraces = substr_count($custom, '{');
		$braceend    = 0;

		while ($countbraces > 0)
		{
			$bracebegin    = strpos($custom, '{');
			$braceend      = strpos($custom, '}');
			$subcustom     = substr($custom, ($bracebegin + 1), (($braceend - $bracebegin) - 1));
			$customelement = $this->getseriesElementnumber($subcustom);

			// @Fixme Need to find working replacement for this function.
			$element       = $this->seriesGetelement($r, $row, $customelement, $custom, $islink, $params, $view = null);
			$custom        = substr_replace($custom, $element, $bracebegin, (($braceend - $bracebegin) + 1));
			$countbraces--;
		}

		return $custom;
	}

	/**
	 * Get Series ElementNumber
	 *
	 * @param   string  $subcustom  ?
	 *
	 * @return int
	 */
	public function getseriesElementnumber($subcustom)
	{
		$customelement = null;

		switch ($subcustom)
		{
			case 'title':
				$customelement = 1;
				break;

			case 'thumbnail':
				$customelement = 2;
				break;

			case 'thumbnail-title':
				$customelement = 3;
				break;

			case 'teacher':
				$customelement = 4;
				break;

			case 'teacherimage':
				$customelement = 5;
				break;

			case 'teacher-title':
				$customelement = 6;
				break;

			case 'description':
				$customelement = 7;
				break;
		}

		return $customelement;
	}

	/**
	 * Get Serieslist Exp
	 *
	 * @param   object                    $row       JTable
	 * @param   Joomla\Registry\Registry  $params    Item Params
	 * @param   object                    $template  Template
	 *
	 * @return object
	 */
	public function getSerieslistExp($row, $params, $template)
	{
		$t      = $params->get('serieslisttemplateid');
		$images = new JBSMImages;
		$image  = $images->getSeriesThumbnail($row->series_thumbnail);

		$label = $params->get('series_templatecode');
		$label = str_replace('{{teacher}}', $row->teachername, $label);
		$label = str_replace('{{teachertitle}}', $row->teachertitle, $label);
		$label = str_replace('{{title}}', $row->series_text, $label);
		$label = str_replace('{{description}}', $row->description, $label);
		$label = str_replace('{{thumbnail}}', '<img src="' . $image->path . '" width="' . $image->width . '" height="' . $image->height . '" />', $label);
		$label = str_replace('{{url}}', 'index.php?option=com_biblestudy&amp;view=seriesdisplay&amp;t=' . $template . '&amp;id=' . $row->id, $label);

		return $label;
	}

	/**
	 * Get Series Details EXP
	 *
	 * @param   object                    $row       JTable
	 * @param   Joomla\Registry\Registry  $params    Item Params
	 * @param   object                    $template  Template
	 *
	 * @return object
	 */
	public function getSeriesDetailsExp($row, $params, $template)
	{
		$images = new JBSMImages;
		$image  = $images->getSeriesThumbnail($row->series_thumbnail);
		$label  = $params->get('series_detailcode');
		$label  = str_replace('{{teacher}}', $row->teachername, $label);
		$label  = str_replace('{{teachertitle}}', $row->teachertitle, $label);
		$label  = str_replace('{{description}}', $row->description, $label);
		$label  = str_replace('{{title}}', $row->series_text, $label);
		$label  = str_replace('{{thumbnail}}', '<img src="' . $image->path . '" width="' . $image->width . '" height="' . $image->height . '" />', $label);
		$label  = str_replace('{{plays}}', $row->totalplays, $label);
		$label  = str_replace('{{downloads}}', $row->totaldownloads, $label);

		return $label;
	}

	/**
	 * Get Series Studies Exp
	 *
	 * @param   int                       $id        ID
	 * @param   Joomla\Registry\Registry  $params    Item Params
	 * @param   object                    $template  Template
	 *
	 * @return string
	 */
	public function getSeriesstudiesExp($id, $params, $template)
	{
		$input   = new JInput;
		$limit   = '';
		$nolimit = $input->get('nolimit', '', 'int');

		if ($params->get('series_detail_limit'))
		{
			$limit = ' LIMIT ' . $params->get('series_detail_limit');
		}
		if ($nolimit == 1)
		{
			$limit = '';
		}
		// Fixme Need to find working replacement for this function.
		$items   = $this->getSeriesstudiesDBO($id, $params, $limit);
		$numrows = count($items);

		$studies = '';

		switch ($params->get('series_wrapcode'))
		{
			case '0':
				// Do Nothing
				break;
			case 'T':
				// Table
				$studies .= '<table class="table" id="bsms_seriestable" width="100%">';
				break;
			case 'D':
				// DIV
				$studies .= '<div>';
				break;
		}
		echo $params->get('series_headercode');

		// Check permissions for this view by running through the records and removing those the user doesn't have permission to see
		$user   = JFactory::getUser();
		$groups = $user->getAuthorisedViewLevels();
		$count  = count($items);

		for ($i = 0; $i < $count; $i++)
		{

			if ($items[$i]->access > 1)
			{
				if (!in_array($items[$i]->access, $groups))
				{
					unset($items[$i]);
				}
			}
		}
		foreach ($items AS $row)
		{
			$oddeven = 0;
			$studies .= $this->getListingExp($row, $params, $params->get('seriesdetailtemplateid'));
		}

		switch ($params->get('series_wrapcode'))
		{
			case '0':
				// Do Nothing
				break;
			case 'T':
				// Table
				$studies .= '</table>';
				break;
			case 'D':
				// DIV
				$studies .= '</div>';
				break;
		}
		echo $params->get('series_headercode');

		return $studies;
	}

}
