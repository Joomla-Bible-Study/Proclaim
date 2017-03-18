<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2017 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
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
	 *
	 * @since    8.0
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
	 *
	 * @since    8.0
	 */
	public function getSerieslistExp($row, $params, $template)
	{
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
	 *
	 * @since    8.0
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
	 *
	 * @since    8.0
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

	/**
	 * Get SeriesStudies DBO
	 *
	 * @param   int       $id      ID
	 * @param   Registry  $params  Item Params
	 * @param   string    $limit   Limit of Records
	 *
	 * @return array
	 *
	 * @since    8.0
	 */
	public function getSeriesstudiesDBO($id, $params, $limit = null)
	{
		$app       = JFactory::getApplication();
		$db        = JFactory::getDbo();
		$user      = JFactory::getUser();
		$language  = $language = $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*');
		$set_limit = null;

		if ($limit)
		{
			preg_match_all('!\d+!', $limit, $set_limit);
			$set_limit = implode(' ', $set_limit[0]);
		}

		// Compute view access permissions.
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query  = $db->getQuery(true);
		$query->select('s.*, se.id AS seid, t.id AS tid, t.teachername, t.title AS teachertitle, t.thumb, t.thumbh, t.thumbw, '
			. ' t.teacher_thumbnail, se.series_text, se.description AS sdescription, '
			. ' se.series_thumbnail, #__bsms_message_type.id AS mid,'
			. ' #__bsms_message_type.message_type AS message_type, #__bsms_books.bookname,'
			. ' group_concat(#__bsms_topics.id separator ", ") AS tp_id, group_concat(#__bsms_topics.topic_text separator ", ")'
			. ' as topic_text, group_concat(#__bsms_topics.params separator ", ") as topic_params, '
			. ' #__bsms_locations.id AS lid, #__bsms_locations.location_text ')
			->from('#__bsms_studies AS s')
			->leftJoin('#__bsms_series AS se ON (s.series_id = se.id)')
			->leftJoin('#__bsms_teachers AS t ON (s.teacher_id = t.id)')
			->leftJoin('#__bsms_books ON (s.booknumber = #__bsms_books.booknumber)')
			->leftJoin('#__bsms_message_type ON (s.messagetype = #__bsms_message_type.id)')
			->leftJoin('#__bsms_studytopics ON (#__bsms_studytopics.study_id = s.id)')
			->leftJoin('#__bsms_topics ON (#__bsms_topics.id = #__bsms_studytopics.topic_id)')
			->leftJoin('#__bsms_locations ON (s.location_id = #__bsms_locations.id)')
			->where('s.series_id = ' . $id)
			->where('s.published = ' . 1)
			->where('s.language in (' . $language . ')')
			->where('s.access IN (' . $groups . ')')
			->group('s.id')
			->group($params->get('series_detail_sort', 'studydate') . ' ' . $params->get('series_detail_order', 'desc'));
		$db->setQuery($query, 0, $set_limit);
		$results = $db->loadObjectList();
		$items   = $results;

		foreach ($items as $item)
		{
			// Concat topic_text and concat topic_params do not fit, so translate individually
			$topics_text       = JBSMTranslated::getConcatTopicItemTranslated($item);
			$item->topics_text = $topics_text;
		}

		return $items;
	}
}
