<?php
/**
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JLoader::register('JBSMImages', BIBLESTUDY_PATH_LIB . '/biblestudy.images.class.php');
JLoader::register('JBSMListing', BIBLESTUDY_PATH_LIB . '/biblestudy.listing.class.php');

/**
 * Class for Teachers Helper
 *
 * @package  BibleStudy.Site
 * @since    8.0.0
 */
class JBSMTeacher extends JBSMListing
{
	/**
	 * Get Teacher
	 *
	 * @param   object  $params        Item Params
	 * @param   int     $id            Item ID
	 * @param   object  $admin_params  Admin Params
	 *
	 * @return string
	 */
	public function getTeacher($params, $id, $admin_params)
	{
		$mainframe   = JFactory::getApplication();
		$input       = new JInput;
		$option      = $input->get('option', '', 'cmd');
		$JViewLegacy = new JViewLegacy;
		$JViewLegacy->loadHelper('image');
		$teacher    = null;
		$teacherid  = null;
		$teacherids = new stdClass;
		$t          = $params->get('teachertemplateid');

		if (!$t)
		{
			$t = $input->get('t', 1, 'int');
		}
		$viewtype = $input->get('view');

		if ($viewtype == 'sermons')
		{
			$teacherid  = $params->get('listteachers');
			$teacherids = explode(",", $params->get('listteachers'));
		}
		if ($viewtype == 'sermon' && $id != 0)
		{
			$teacherids->id = $id;
		}
		$teacher = '<table id = "teacher"><tr>';

		if (!isset($teacherids))
		{
			return $teacher;
		}
		foreach ($teacherids as $teachers)
		{
			$database = JFactory::getDBO();
			$query    = 'SELECT * FROM #__bsms_teachers' .
					'  WHERE id = ' . $teachers;
			$database->setQuery($query);
			$tresult = $database->loadObject();
			$i_path  = null;

			// Check to see if there is a teacher image, if not, skip this step
			$images = new JBSMImages;
			$image  = $images->getTeacherThumbnail($tresult->teacher_thumbnail, $tresult->thumb);

			if (!$image)
			{
				$image->path   = '';
				$image->width  = 0;
				$image->height = 0;
			}
			$teacher .= '<td><table cellspacing ="0"><tr><td><img src="' . $image->path . '" border="1" width="' . $image->width
					. '" height="' . $image->height . '" alt="" /></td></tr>';

			$teacher .= '<tr><td>';

			if ($params->get('teacherlink') > 0)
			{
				$teacher .= '<a href="' . JRoute::_('index.php?option=com_biblestudy&amp;view=teacher&amp;id=' . $tresult->id . '&amp;t=' . $t) . '">';
			}
			$teacher .= $tresult->teachername;

			if ($params->get('teacherlink') > 0)
			{
				$teacher .= '</a>';
			}
			$teacher .= '</td></tr></table></td>';
		}
		if ($params->get('intro_show') == 2 && $viewtype == 'sermons')
		{
			$teacher .= '<td><div id="listintro"><table id="listintro"><tr><td><p>' . $params->get('list_intro') . '</p></td></tr></table> </div></td>';
		}
		$teacher .= '</tr></table>';

		return $teacher;
	}

	/**
	 * Get TeacherList Exp
	 *
	 * @param   object  $row           Table info
	 * @param   object  $params        Item Params
	 * @param   string  $oddeven       Odd Even
	 * @param   object  $admin_params  Admin Params
	 * @param   object  $template      Template
	 *
	 * @return object
	 */
	public function getTeacherListExp($row, $params, $oddeven, $admin_params, $template)
	{
		$JViewLegacy = new JViewLegacy;
		$JViewLegacy->loadHelper('image');
		$images     = new JBSMImages;
		$imagelarge = $images->getTeacherThumbnail($row->teacher_image, $row->image);

		$imagesmall = $images->getTeacherThumbnail($row->teacher_thumbnail, $row->thumb);

		$label = $params->get('teacher_templatecode');
		$label = str_replace('{{teacher}}', $row->teachername, $label);
		$label = str_replace('{{title}}', $row->title, $label);
		$label = str_replace('{{phone}}', $row->phone, $label);
		$label = str_replace('{{website}}', '<A href="' . $row->website . '">Website</a>', $label);
		$label = str_replace('{{information}}', $row->information, $label);
		$label = str_replace('{{image}}', '<img src="' . $imagelarge->path . '" width="' . $imagelarge->width . '" height="' . $imagelarge->height . '" />', $label);
		$label = str_replace('{{short}}', $row->short, $label);
		$label = str_replace('{{thumbnail}}', '<img src="' . $imagesmall->path . '" width="' . $imagesmall->width . '" height="' . $imagesmall->height . '" />', $label);
		$label = str_replace('{{url}}', JRoute::_('index.php?option=com_biblestudy&amp;view=teacherdisplay&amp;id=' . $row->id . '&amp;t=' . $template), $label);

		return $label;
	}

	/**
	 * Get Teacher Details Exp
	 *
	 * @param   object     $row           Table Row
	 * @param   JRegistry  $params        Item Params
	 * @param   int        $template      Template
	 * @param   JRegistry  $admin_params  Admin Params
	 *
	 * @return object
	 */
	public function getTeacherDetailsExp($row, $params, $template, $admin_params)
	{
		$JViewLegacy = new JViewLegacy;
		$JViewLegacy->loadHelper('image');

		// Get the image folders and images
		$images     = new JBSMImages;
		$imagelarge = $images->getTeacherThumbnail($row->teacher_image, $row->image);

		$imagesmall = $images->getTeacherThumbnail($row->teacher_thumbnail, $row->thumb);

		$label = $params->get('teacher_detailtemplate');
		$label = str_replace('{{teacher}}', $row->teachername, $label);
		$label = str_replace('{{title}}', $row->title, $label);
		$label = str_replace('{{phone}}', $row->phone, $label);
		$label = str_replace('{{website}}', '<A href="' . $row->website . '">Website</a>', $label);
		$label = str_replace('{{information}}', $row->information, $label);
		$label = str_replace(
			'{{image}}', '<img src="' . $imagelarge->path . '" width="' . $imagelarge->width . '" height="'
				. $imagelarge->height . '" />', $label
		);
		$label = str_replace('{{short}}', $row->short, $label);
		$label = str_replace(
			'{{thumbnail}}', '<img src="' . $imagesmall->path . '" width="' . $imagesmall->width . '" height="'
				. $imagesmall->height . '" />', $label
		);

		return $label;
	}

	/**
	 * Get Teacher Studies Exp
	 *
	 * @param   int        $id            Item ID
	 * @param   JRegistry  $params        Item Params
	 * @param   JRegistry  $admin_params  Admin Params
	 * @param   int        $template      Template
	 *
	 * @return string
	 *
	 * @todo need to re-wright the sql.
	 */
	public function getTeacherStudiesExp($id, $params, $admin_params, $template)
	{
		$limit   = '';
		$input   = new JInput;
		$nolimit = $input->get('nolimit', '', 'int');

		if ($params->get('series_detail_limit'))
		{
			$limit = ' LIMIT ' . $params->get('series_detail_limit');
		}
		if ($nolimit == 1)
		{
			$limit = '';
		}
		$db    = JFactory::getDBO();
		$query = 'SELECT s.series_id FROM #__bsms_studies AS s WHERE s.published = 1 AND s.series_id = ' . $id;
		$db->setQuery($query);
		$allrows = $db->loadObjectList();
		$rows    = $db->getAffectedRows();

		$query = 'SELECT #__bsms_studies.*, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername,'
				. ' #__bsms_series.id AS sid, #__bsms_series.series_text, #__bsms_message_type.id AS mid,'
				. ' #__bsms_message_type.message_type AS message_type, #__bsms_books.bookname,'
				. ' group_concat(#__bsms_topics.id separator ", ") AS tp_id, group_concat(#__bsms_topics.topic_text separator ", ") as topic_text'
				. ' FROM #__bsms_studies'
				. ' left join #__bsms_studytopics ON (#__bsms_studies.id = #__bsms_studytopics.study_id)'
				. ' LEFT JOIN #__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)'
				. ' LEFT JOIN #__bsms_teachers ON (#__bsms_studies.teacher_id = #__bsms_teachers.id)'
				. ' LEFT JOIN #__bsms_series ON (#__bsms_studies.series_id = #__bsms_series.id)'
				. ' LEFT JOIN #__bsms_message_type ON (#__bsms_studies.messagetype = #__bsms_message_type.id)'
				. ' LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studytopics.topic_id)'
				. ' where #__bsms_teachers.id = ' . $id . ' AND #__bsms_studies.published = 1 '
				. ' GROUP BY #__bsms_studies.id'
				. ' order by studydate desc'
				. $limit;

		$db->setQuery($query);
		$items = $db->loadObjectList();

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

		$studieslimit = $params->get('studies', 10);

		$studies = '';

		switch ($params->get('wrapcode'))
		{
			case '0':
				// Do Nothing
				break;
			case 'T':
				// Table
				$studies .= '<table id="bsms_studytable" width="100%">';
				break;
			case 'D':
				// DIV
				$studies .= '<div>';
				break;
		}

		$params->get('headercode');
		$j = 0;

		foreach ($items AS $row)
		{
			if ($j > $studieslimit)
			{
				break;
			}
			$studies .= $this->getListingExp($row, $params, $admin_params, $params->get('studieslisttemplateid'));
			$j++;
		}

		switch ($params->get('wrapcode'))
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

		return $studies;
	}
}
