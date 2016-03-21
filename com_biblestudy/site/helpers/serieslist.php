<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
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
