<?php
/**
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR . '/components/com_biblestudy/lib/biblestudy.defines.php';
JLoader::register('jbsImages', BIBLESTUDY_PATH_ADMIN_HELPERS . '/image.php');
JLoader::register('JBSMCustom', JPATH_BASE . '/components/com_biblestudy/helper/custom.php');

// ???? not sure if we need to load this ???
JLoader::register('jbsMedia', BIBLESTUDY_PATH_LIB . '/biblestudy.media.class.php');

/**
 * Class for Elements
 *
 * @package    BibleStudy.Site
 * @since      8.0.0
 */
class JBSMElements
{
	/**
	 * @var string
	 */
	public static $extension = 'com_biblestudy';

	/**
	 * Get Elementid
	 *
	 * @param   int        $rowid         ID
	 * @param   object     $row           Table info
	 * @param   JRegistry  $params        Component / System Params
	 * @param   object     $admin_params  Admin Settings
	 * @param   object     $template    Template
	 *
	 * @todo Redo to MVC Standers under a class
	 * @return object
	 */
	public static function getElementid($rowid, $row, $params, $admin_params, $template)
	{
		// Start Element ID
		$elementid = new stdClass;

		$db = JFactory::getDBO();

		switch ($rowid)
		{
			case 1:
				$elementid->id         = 'scripture1';
				$elementid->headertext = JText::_('JBS_CMN_SCRIPTURE');
				$esv                   = 0;
				$scripturerow          = 1;
				$elementid->element    = self::getScripture($params, $row, $esv, $scripturerow);
				break;
			case 2:
				$elementid->id         = 'scripture2';
				$elementid->headertext = JText::_('JBS_CMN_SCRIPTURE');
				$esv                   = 0;
				$scripturerow          = 2;
				$elementid->element    = self::getScripture($params, $row, $esv, $scripturerow);
				break;
			case 3:
				$elementid->id         = 'secondary';
				$elementid->headertext = JText::_('JBS_CMN_SECONDARY_REFERENCES');
				$elementid->element    = $row->secondary_reference;
				break;
			case 4:
				$elementid->id         = 'duration';
				$elementid->headertext = JText::_('JBS_CMN_DURATION');
				$elementid->element    = self::getDuration($params, $row);
				break;
			case 5:
				$elementid->id         = 'title';
				$elementid->headertext = JText::_('JBS_CMN_TITLE');

				if (isset($row->studytitle))
				{
					$elementid->element = $row->studytitle;
				}
				else
				{
					$elementid->element = '';
				}
				break;
			case 6:
				$elementid->id         = 'studyintro';
				$elementid->headertext = JText::_('JBS_CMN_INTRODUCTION');

				if (isset($row->studyintro))
				{
					$elementid->element = $row->studyintro;
				}
				else
				{
					$elementid->element = '';
				}
				break;
			case 7:
				$elementid->id         = 'teacher';
				$elementid->headertext = JText::_('JBS_CMN_TEACHER');
				$elementid->element    = $row->teachername;
				break;
			case 8:
				$elementid->id         = 'teacher';
				$elementid->headertext = JText::_('JBS_CMN_TEACHER');
				$elementid->element    = $row->teachertitle . ' ' . $row->teachername;
				break;
			case 9:
				$elementid->id         = 'series';
				$elementid->headertext = JText::_('JBS_CMN_SERIES');
				$elementid->element    = $row->series_text;
				break;
			case 10:
				$elementid->id         = 'date';
				$elementid->headertext = JText::_('JBS_CMN_STUDY_DATE');
				$elementid->element    = $row->studydate;
				break;
			case 11:
				$elementid->id         = 'submitted';
				$elementid->headertext = JText::_('JBS_CMN_SUBMITTED_BY');
				$elementid->element    = $row->submitted;
				break;
			case 12:
				$elementid->id         = 'hits';
				$elementid->headertext = JText::_('JBS_CMN_VIEWS');
				$elementid->element    = JText::_('JBS_CMN_HITS') . ' ' . $row->hits;
				break;
			case 13:
				$elementid->id         = 'studynumber';
				$elementid->headertext = JText::_('JBS_CMN_STUDYNUMBER');
				$elementid->element    = $row->studynumber;
				break;
			case 14:
				$elementid->id         = 'topic';
				$elementid->headertext = JText::_('JBS_CMN_TOPIC');

				if (substr_count($row->topics_text, ','))
				{
					$topics = explode(',', $row->topics_text);

					foreach ($topics as $key => $value)
					{
						$topics[$key] = JText::_($value);
					}
					$elementid->element = implode(', ', $topics);
				}
				else
				{
					$elementid->element = JText::_($row->topics_text);
				}
				break;
			case 15:
				$elementid->id         = 'location';
				$elementid->headertext = JText::_('JBS_CMN_LOCATION');
				$elementid->element    = $row->location_text;
				break;
			case 16:
				$elementid->id         = 'messagetype';
				$elementid->headertext = JText::_('JBS_CMN_MESSAGE_TYPE');
				$elementid->element    = $row->message_type;
				break;
			case 17:
				$elementid->id         = 'details';
				$elementid->headertext = JText::_('JBS_CMN_DETAILS');
				$textorpdf             = 'text';
				$elementid->element    = self::getTextlink($params, $row, $textorpdf, $admin_params, $template);
				break;
			case 18:
				$elementid->id         = 'details';
				$elementid->headertext = JText::_('JBS_CMN_DETAILS');
				$textorpdf             = 'text';
				$elementid->element    = '<table class="detailstable"><tbody><tr><td>';
				$elementid->element .= self::getTextlink($params, $row, $textorpdf, $admin_params, $template) . '</td><td>';
				$textorpdf = 'pdf';
				$elementid->element .= self::getTextlink(
					$params,
					$row,
					$textorpdf,
					$admin_params,
					$template
				) . '</td></tr></table>';
				break;
			case 19:
				$elementid->id         = 'details';
				$elementid->headertext = JText::_('JBS_CMN_DETAILS');
				$textorpdf             = 'pdf';
				$elementid->element    = self::getTextlink($params, $row, $textorpdf, $admin_params, $template);
				break;
			case 20:
				$elementid->id         = 'jbsmedia';
				$elementid->headertext = JText::_('JBS_CMN_MEDIA');
				$elementid->element    = self::getMediaTable($row, $params, $admin_params);
				break;
			case 22:
				$elementid->id         = 'store';
				$elementid->headertext = JText::_('JBS_CMN_STORE');
				$elementid->element    = self::getStore($params, $row);
				break;
			case 23:
				$elementid->id         = 'filesize';
				$elementid->headertext = JText::_('JBS_CMN_FILESIZE');
				$query_media1          = 'SELECT #__bsms_mediafiles.id AS mid, #__bsms_mediafiles.size, #__bsms_mediafiles.published, #__bsms_mediafiles.study_id'
						. ' FROM #__bsms_mediafiles'
						. ' WHERE #__bsms_mediafiles.study_id = ' . $row->id
						. ' AND #__bsms_mediafiles.published = 1 ORDER BY ordering, #__bsms_mediafiles.id ASC LIMIT 1';

				$db->setQuery($query_media1);
				$media1             = $db->loadObject();
				$elementid->element = self::getFilesize($media1->size);
				break;
			case 25:
				$elementid->id         = 'thumbnail';
				$elementid->headertext = JText::_('JBS_CMN_THUMBNAIL');

				if ($row->thumbnailm)
				{
					$images             = new jbsImages;
					$image              = $images->getStudyThumbnail($row->thumbnailm);
					$elementid->element = '<img src="' . JURI::base() . $image->path . '" width="' . $image->width
							. '" height="' . $image->height . '" alt="' . $row->studytitle . '">';
				}
				else
				{
					$elementid->element = '';
				}
				break;
			case 26:
				$elementid->id         = 'series_thumbnail';
				$elementid->headertext = JText::_('JBS_CMN_THUMBNAIL');

				if ($row->series_thumbnail)
				{
					$images             = new jbsImages;
					$image              = $images->getSeriesThumbnail($row->series_thumbnail);
					$elementid->element = '<img src="' . JURI::base() . $image->path . '" width="' . $image->width . '" height="'
							. $image->height . '" alt="' . $row->series_text . '">';
				}
				else
				{
					$elementid->element = '';
				}
				break;
			case 27:
				$elementid->id         = 'series_description';
				$elementid->headertext = JText::_('JBS_CMN_DESCRIPTION');
				$elementid->element    = $row->sdescription;
				break;
			case 28:
				$elementid->id         = 'plays';
				$elementid->headertext = JText::_('JBS_CMN_PLAYS');
				$elementid->element    = $row->totalplays;
				break;
			case 29:
				$elementid->id         = 'downloads';
				$elementid->headertext = JText::_('JBS_CMN_DOWNLOADS');
				$elementid->element    = $row->totaldownloads;
				break;
			case 30:
				$timages              = new jbsImages;
				$elementid->id        = 'teacher-image';
				$elementid->headetext = JText::_('JBS_CMN_TEACHER_IMAGE');
				$query                = "SELECT thumb, teacher_thumbnail FROM #__bsms_teachers WHERE id = $row->teacher_id";
				$db->setQuery($query);
				$thumb = $db->loadObject();

				if ($thumb->teacher_thumbnail)
				{
					$timage = $timages->getTeacherImage($thumb->teacher_thumbnail);
				}
				else
				{
					$timage = $timage = $timages->getTeacherImage($thumb->thumb);
				}
				$elementid->element = '<img src="' . $timage->path . '" width="' . $timage->width . '" height="' . $timage->height . '" />';
				break;
			case 100:
				$elementid->id         = '';
				$elementid->headertext = '';
				$elementid->element    = '';
				break;
		}

		return $elementid;
	}

	/**
	 * Get Scripture
	 *
	 * @param   object  $params        Item Params
	 * @param   object  $row           Row Info
	 * @param   string  $esv           ESV String
	 * @param   string  $scripturerow  Scripture Row
	 *
	 * @return string
	 */
	public static function getScripture($params, $row, $esv, $scripturerow)
	{
		$scripture = '';

		if (!isset($row->id))
		{
			return null;
		}

		if (!isset($row->booknumber))
		{
			$row->booknumber = 0;
		}

		if (!isset($row->booknumber2))
		{
			$row->booknumber2 = 0;
		}

		if ($scripturerow == 2 && $row->booknumber2 >= 1)
		{
			$booknumber = $row->booknumber2;
			$ch_b       = $row->chapter_begin2;
			$ch_e       = $row->chapter_end2;
			$v_b        = $row->verse_begin2;
			$v_e        = $row->verse_end2;
		}
		elseif ($scripturerow == 1 && isset($row->booknumber) >= 1)
		{
			$booknumber = $row->booknumber;
			$ch_b       = $row->chapter_begin;
			$ch_e       = $row->chapter_end;
			$v_b        = $row->verse_begin;
			$v_e        = $row->verse_end;
		}

		if (!isset($booknumber))
		{
			return $scripture;
		}
		$show_verses = $params->get('show_verses');
		$db          = JFactory::getDBO();
		$query       = 'SELECT #__bsms_studies.*, #__bsms_books.bookname, #__bsms_books.id as bid '
				. ' FROM #__bsms_studies'
				. ' LEFT JOIN #__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)'
				. '  WHERE #__bsms_studies.id = ' . $row->id;
		$db->setQuery($query);
		$bookresults  = $db->loadObject();
		$affectedrows = count($bookresults);

		if ($affectedrows < 1)
		{
			return null;
		}
		$query = 'SELECT bookname, booknumber FROM #__bsms_books WHERE booknumber = ' . $booknumber;
		$db->setQuery($query);
		$booknameresults = $db->loadObject();

		if (!isset($booknameresults))
		{
			$scripture = '';

			return $scripture;
		}
		if ($booknameresults->bookname)
		{
			$book = JText::_($booknameresults->bookname);
		}
		else
		{
			$book = '';
		}
		$b1  = ' ';
		$b2  = ':';
		$b2a = ':';
		$b3  = '-';

		if ($show_verses == 1)
		{
			/** @var $ch_b string */
			/** @var $v_b string */
			/** @var $ch_e string */
			/** @var $v_e string */
			if ($ch_e == $ch_b)
			{
				$ch_e = '';
				$b2a  = '';
			}
			if ($ch_e == $ch_b && $v_b == $v_e)
			{
				$b3   = '';
				$ch_e = '';
				$b2a  = '';
				$v_e  = '';
			}
			if ($v_b == 0)
			{
				$v_b = '';
				$v_e = '';
				$b2a = '';
				$b2  = '';
			}
			if ($v_e == 0)
			{
				$v_e = '';
				$b2a = '';
			}
			if ($ch_e == 0)
			{
				$b2a  = '';
				$ch_e = '';

				if ($v_e == 0)
				{
					$b3 = '';
				}
			}
			$scripture = $book . $b1 . $ch_b . $b2 . $v_b . $b3 . $ch_e . $b2a . $v_e;
		}
		// Else
		if ($show_verses == 0)
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
		if ($esv == 1)
		{
			/** @var $ch_b string */
			/** @var $v_b string */
			/** @var $ch_e string */
			/** @var $v_e string */
			if ($ch_e == $ch_b)
			{
				$ch_e = '';
				$b2a  = '';
			}
			if ($v_b == 0)
			{
				$v_b = '';
				$v_e = '';
				$b2a = '';
				$b2  = '';
			}
			if ($v_e == 0)
			{
				$v_e = '';
				$b2a = '';
			}
			if ($ch_e == 0)
			{
				$b2a  = '';
				$ch_e = '';

				if ($v_e == 0)
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

		if ($show_verses == 2)
		{
			$scripture = $book;
		}

		return $scripture;
	}

	/**
	 * Get Duration
	 *
	 * @param   object  $params  Item Params
	 * @param   object  $row     Row info
	 *
	 * @return  null|string
	 */
	public static function getDuration($params, $row)
	{

		$duration = $row->media_hours . $row->media_minutes . $row->media_seconds;

		if (!$duration)
		{
			$duration = null;

			return $duration;
		}
		$duration_type = $params->get('duration_type', 2);
		$hours         = $row->media_hours;
		$minutes       = $row->media_minutes;
		$seconds       = $row->media_seconds;

		switch ($duration_type)
		{
			case 1:
				if (!$hours)
				{
					$duration = $minutes . ' mins ' . $seconds . ' secs';
				}
				else
				{
					$duration = $hours . ' hour(s) ' . $minutes . ' mins ' . $seconds . ' secs';
				}
				break;
			case 2:
				if (!$hours)
				{
					$duration = $minutes . ':' . $seconds;
				}
				else
				{
					$duration = $hours . ':' . $minutes . ':' . $seconds;
				}
				break;
			default:
				$duration = $hours . ':' . $minutes . ':' . $seconds;
				break;

		} // End switch

		return $duration;
	}

	/**
	 * Get StudyDate
	 *
	 * @param   object  $params     Item Params
	 * @param   string  $studydate  Study Date
	 *
	 * @return string
	 */
	public static function getstudyDate($params, $studydate)
	{
		switch ($params->get('date_format'))
		{
			case 0:
				$date = JHTML::_('date', $studydate, "M j, Y");
				break;
			case 1:
				$date = JHTML::_('date', $studydate, "M J");
				break;
			case 2:
				$date = JHTML::_('date', $studydate, "n/j/Y");
				break;
			case 3:
				$date = JHTML::_('date', $studydate, "n/j");
				break;
			case 4:
				$date = JHTML::_('date', $studydate, "l, F j, Y");
				break;
			case 5:
				$date = JHTML::_('date', $studydate, "F j, Y");
				break;
			case 6:
				$date = JHTML::_('date', $studydate, "j F Y");
				break;
			case 7:
				$date = JHTML::_('date', $studydate, "j/n/Y");
				break;
			case 8:
				$date = JHTML::_('date', $studydate, JText::_('DATE_FORMAT_LC'));
				break;
			case 9:
				$date = JHTML::_('date', $studydate, "Y/M/D");
				break;
			default:
				$date = JHTML::_('date', $studydate, "n/j");
				break;
		}

		$customDate = $params->get('custom_date_format');

		if ($customDate != '')
		{
			$date = JHTML::_('date', $studydate, $customDate);
		}

		return $date;
	}

	/**
	 * Function to get File Size
	 *
	 * @param   string  $file_size  Size in bytes
	 *
	 * @return null|string
	 */
	protected static function getFilesize($file_size)
	{
		if (!$file_size)
		{
			$file_size = null;

			return $file_size;
		}
		switch ($file_size)
		{
			case $file_size < 1024 :
				$file_size = $file_size . ' ' . 'Bytes';
				break;
			case $file_size < 1048576 :
				$file_size = $file_size / 1024;
				$file_size = number_format($file_size, 0);
				$file_size = $file_size . ' ' . 'KB';
				break;
			case $file_size < 1073741824 :
				$file_size = $file_size / 1024;
				$file_size = $file_size / 1024;
				$file_size = number_format($file_size, 1);
				$file_size = $file_size . ' ' . 'MB';
				break;
			case $file_size > 1073741824 :
				$file_size = $file_size / 1024;
				$file_size = $file_size / 1024;
				$file_size = $file_size / 1024;
				$file_size = number_format($file_size, 1);
				$file_size = $file_size . ' ' . 'GB';
				break;
		}

		return $file_size;
	}

	/**
	 * Get Textlink
	 *
	 * @param   object  $params        Item Params
	 * @param   object  $row           JTable
	 * @param   string  $textorpdf     Text Or PDF location
	 * @param   object  $admin_params  Admin info
	 * @param   object  $template      Template info
	 *
	 * @return string
	 */
	public static function getTextlink($params, $row, $textorpdf, $admin_params, $template)
	{
		$linktext = null;

		$images = new jbsImages;
		$input  = new JInput;
		$t      = $input->get('t', 1, 'int');

		if (!$template->text || !substr_count($template->text, '/'))
		{
			$i_path    = 'media/com_biblestudy/images/textfile24.png';
			$textimage = $images->getImagePath($i_path);
			$src       = JURI::base() . $textimage->path;
			$height    = $textimage->height;
			$width     = $textimage->width;
		}
		elseif (substr_count($template->text, 'http://'))
		{
			$src    = $template->text;
			$height = '24';
			$width  = '24';
		}
		else
		{
			$i_path    = $template->text;
			$textimage = $images->getImagePath($i_path);
			$src       = JURI::base() . $textimage->path;
			$height    = $textimage->height;
			$width     = $textimage->width;
		}

		$link         = JRoute::_('index.php?option=com_biblestudy&view=sermon' . '&id=' . $row->id . '&t=' . $t) . JHTML::_('behavior.tooltip');
		$details_text = $params->get('details_text');

		if ($params->get('tooltip') > 0)
		{
			$linktext = JBSMHelper::getTooltip($row->id, $row, $params, $admin_params, $template);

		} // End of is show tooltip

		$linktext .= '
	<a href="' . $link . '"><img src="' . $src . '" alt="' . $details_text . '" width="' . $width . '" height="' . $height . '" border="0" />';

		if ($params->get('tooltip') > 0)
		{
			$linktext .= '</span>';
		}
		$linktext .= '</a></span>';

		return $linktext;
	}

	/**
	 * Get MediaTable
	 *
	 * @param   object  $params        Item Params
	 * @param   object  $row           JTable
	 * @param   object  $admin_params  Admin Info
	 *
	 * @return boolean|null|string
	 */
	public function getMediatable($params, $row, $admin_params)
	{
		// @todo not sure if we should be loading parameter. ?bcc to Tom
		jimport('joomla.html.parameter');
		$getMedia = new jbsMedia;
		jimport('joomla.application.component.helper');

		if (!$row->id)
		{
			return false;
		}

		$database  = JFactory::getDBO();
		$database->setQuery('SELECT * FROM #__bsms_admin WHERE id = 1');
		$admin = $database->loadObjectList();

		$images       = new jbsImages;
		$download_tmp = $images->getMediaImage($admin[0]->params->default_download_image, $media = null);

		$download_image = $download_tmp->path;

		// Predefine var
		$media1_link  = null;
		$downloadlink = null;
		$filesize     = null;

		$query = 'SELECT #__bsms_mediafiles.*,'
				. ' #__bsms_servers.id AS ssid, #__bsms_servers.server_path AS spath,'
				. ' #__bsms_folders.id AS fid, #__bsms_folders.folderpath AS fpath,'
				. ' #__bsms_media.id AS mid, #__bsms_media.media_image_path AS impath, #__bsms_media.media_image_name AS imname, #__bsms_media.path2 AS path2,'
				. ' #__bsms_media.media_alttext AS malttext,'
				. ' #__bsms_mimetype.id AS mtid, #__bsms_mimetype.mimetext'
				. ' FROM #__bsms_mediafiles'
				. ' LEFT JOIN #__bsms_media ON (#__bsms_media.id = #__bsms_mediafiles.media_image)'
				. ' LEFT JOIN #__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server)'
				. ' LEFT JOIN #__bsms_folders ON (#__bsms_folders.id = #__bsms_mediafiles.path)'
				. ' LEFT JOIN #__bsms_mimetype ON (#__bsms_mimetype.id = #__bsms_mediafiles.mime_type)'
				. ' WHERE #__bsms_mediafiles.study_id = ' . $row->id
				. ' AND #__bsms_mediafiles.published = 1 ORDER BY ordering ASC, #__bsms_mediafiles.mime_type ASC';
		$database->setQuery($query);
		$media1 = $database->loadObjectList('id');
		$rows2  = count($media1);

		$compat_mode = $admin_params->get('compat_mode');

		if ($rows2 < 1)
		{
			$mediatable = null;

			return $mediatable;
		}


		$mediatable = '<div><table class="mediatable"><tbody><tr>';
		$row_count  = 0;

		foreach ($media1 as $media)
		{

			$row_count++;

			// Load the parameters
			// Convert parameter fields to objects.
			$registry = new JRegistry;
			$registry->loadString($media->params);
			$itemparams = $registry;
			$input      = new JInput;
			$Itemid     = $input->get('Itemid', '1', 'int');
			$template   = $input->get('t', '1', 'int');
			$images     = new jbsImages;
			$image      = $images->getMediaImage($media->path2, $media->impath);


			$mediatable .= '<td>';


			// @todo - not sure how much of this is needed
			$filesize = self::getFilesize($media->size);

			// This one IS needed
			$duration = self::getDuration($params, $row);
			$mimetype = $media->mimetext;
			$src      = JURI::base() . $image->path;
			$height   = $image->height;
			$width    = $image->width;
			$path1    = $media->spath . $media->fpath . $media->filename;

			if (!stristr('http://', $path1))
			{
				$path1 = 'http://' . $path1;
			}
			$playerwidth  = $params->get('player_width');
			$playerheight = $params->get('player_height');

			if ($itemparams->get('playerheight'))
			{
				$playerheight = $itemparams->get('playerheight');
			}
			if ($itemparams->get('playerwidth'))
			{
				$playerwidth = $itemparams->get('playerwidth');
			}
			$playerwidth  = $playerwidth + 20;
			$playerheight = $playerheight + $params->get('popupmargin', '50');

			/* Players - from Template:
			   media_player = internal player for all files
			   useravr = use avr for all files
			   useav = use All Videos plugin for all files
			   popuptype = whether AVR should be window or lightbox (handled in avr code)
			   media_player = use internal player for all files
			   internal_popup = whether direct or internal player should be popup or inline
			   From media file:
			   player 0 = direct, 1 = internal, 2 = AVR, 3 = AV
			   internal_popup 0 = inline 1 = popup, 2 = global settings */

			$playertype = 0;

			if ($params->get('media_player') == 1 || $itemparams->get('player') == 1)
			{
				$playertype = 1;
			}

			if ($params->get('useavr') == 1 || $itemparams->get('player') == 2)
			{
				$playertype = 2;
			}

			if ($params->get('useav') == 1 || $itemparams->get('player') == 3)
			{
				$playertype = 3;
			}
			// $item comes from the individual media file 0 = inline, 1 = popup, 3 = use global settings
			$item           = $itemparams->get('internal_popup');
			$internal_popup = $params->get('internal_popup', 0);

			if ($item < 3)
			{
				$type = $internal_popup;
			}
			else
			{
				$type = $item;
			}

			switch ($playertype)
			{
				case 0:

					if ($params->get('direct_internal', 0) == 1)
					{
						$media1_link = "<a href=\"#\" onclick=\"window.open('index.php?option=com_biblestudy&amp;view=popup&amp;Itemid="
								. $Itemid . "&amp;template=" . $template . "&amp;mediaid=" . $media->id . "', 'newwindow','width="
								. $playerwidth . ",height=" . $playerheight . "'); return false\"\"><img src='" . $src . "' height='"
								. $height . "' width='" . $width . "' title='" . $mimetype . " " . $duration . " " . $filesize . "' alt='"
								. $media->malttext . "' /></a>";


						if ($type == 0)
						{
							// FIXME look like this function is no longer in teh code table need to find what it did.
							$media1_link = $getMedia->getInternalLink($media, $width, $height, $src, $params, $image, $row_count, $path1);
						}
					}
					else
					{
						$media1_link = '<a href="' . $path1 . '" title="' . $media->malttext . ' - ' . $media->comment . ' ' . $duration . ' '
								. $filesize . '" target="' . $media->special . '"><img src="' . $src
								. '" alt="' . $media->malttext . ' - ' . $media->comment . ' - ' . $duration . ' ' . $filesize . '" width="' . $width
								. '" height="' . $height . '" border="0" /></a>';
					}

					$media1_link .= '<a href="' . $path1 . '" onclick="window.open(\'index.php?option=com_biblestudy&amp;view=popup&amp;close=1&amp;mediaid='
							. $media->id . '\',\'newwindow\',\'width=100, height=100,menubar=no, status=no,location=no,toolbar=no,scrollbars=no\'); return false;" title="'
							. $media->malttext . ' - ' . $media->comment . ' ' . $duration . ' ' . $filesize . '" target="' . $media->special . '"><img src="'
							. $src . '" alt="' . $media->malttext . ' - ' . $media->comment . ' - ' . $duration . ' ' . $filesize . '" width="' . $width
							. '" height="' . $height . '" border="0" /></a>';

					break;

				case 1:

					if ($type == 1)
					{
						$media1_link = "<a href=\"#\" onclick=\"window.open('index.php?option=com_biblestudy&amp;player=2&amp;view=popup&amp;Itemid="
								. $Itemid . "&amp;template=" . $template . "&amp;mediaid=" . $media->id . "&amp;tmpl=component', 'newwindow','width="
								. $playerwidth . ",height=" . $playerheight . "'); return false\"\"><img src='" . $src . "' height='" . $height . "' width='"
								. $width . "' title='" . $mimetype . " " . $duration . " " . $filesize . "' alt='" . $media->malttext . "' /></a>";
					}
					else
					{
						// FIXME Looks like this is not in code need to find.
						$media1_link = $getMedia->getInternalLink($media, $width, $height, $src, $params, $image, $row_count, $path1);
					}

					break;

				case 2:
					// FIXME Looks like this is not in code need to find.
					$media1_link = $getMedia->getAVRLink($media, $width, $height, $src, $params, $image, $Itemid);
					break;

				case 3:
					echo '<div>' . JHTML::_('content.prepare', $media->mediacode) . '</div>';
					break;
			}

			if ($media->docMan_id > 0)
			{
				$media1_link = self::getDocman($media, $width, $height, $src, $duration, $filesize);
			}
			if ($media->article_id > 0)
			{
				$media1_link = self::getArticle($media, $width, $height, $src);
			}
			if ($media->virtueMart_id > 0)
			{
				$media1_link = self::getVirtuemart($media, $width, $height, $src, $params);
			}

			// Here is where we begin to build the mediatable variable
			// Here we test to see if docMan or article is used

			$link_type = $media->link_type;

			if ($link_type > 0)
			{
				$width  = $download_tmp->width;
				$height = $download_tmp->height;

				if ($compat_mode == 0)
				{
					$downloadlink = '<a href="index.php?option=com_biblestudy&amp;id=' . $media->id . '&amp;view=sermons&amp;controller=sermons&amp;task=download">';
				}
				else
				{
					$downloadlink = '<a href="http://joomlabiblestudy.org/router.php?file=' . $media->spath . $media->fpath
							. $media->filename . '&amp;size=' . $media->size . '">';
				}
				$downloadlink .= '<img src="' . $download_image . '" alt="' . JText::_('JBS_MED_DOWNLOAD') . '" height="'
						. $height . '" width="' . $width . '" title="' . JText::_('JBS_MED_DOWNLOAD') . '"   alt"' . $media->malttext . '"  /></a>';
			}
			switch ($link_type)
			{
				case 0:
					$mediatable .= $media1_link;
					break;

				case 1:
					$mediatable .= $media1_link . $downloadlink;
					break;

				case 2:
					$mediatable = '<div><table class="mediatable"><tbody><tr><td>' . $downloadlink . '</td></tr></tbody></table></div>';
					break;
			}
			$mediatable .= '</td>';

		} // End of foreach of media results

		$mediatable .= '</tr>';

		if ($params->get('show_filesize') > 0)
		{
			$mediatable .= '<tr>';

			foreach ($media1 as $media)
			{
				switch ($params->get('show_filesize'))
				{
					case 1:
						$filesize = self::getFilesize($media->size);
						break;
					case 2:
						$filesize = $media->comment;
						break;
					case 3:
						 ($media->comment ? $filesize = $media->comment : $filesize = self::getFilesize($media->size));
				        break;
				}

				$mediatable .= '<td><span class="bsfilesize">' . $filesize . '</span></td>';

			} // End second foreach
			$mediatable .= '</tr>';

		} // End of if show_filesize

		$mediatable .= '</td></tr></tbody></table></div>';

		return $mediatable;
	}

	/**
	 * Get Docman
	 *
	 * @param   object  $media     Media
	 * @param   int     $width     Width
	 * @param   int     $height    Height
	 * @param   string  $src       Sorce of Image
	 * @param   string  $duration  Duration
	 * @param   int     $filesize  File Size of Doc
	 *
	 * @return string
	 */
	public static function getDocman($media, $width, $height, $src, $duration, $filesize)
	{
		$docman = '<a href="index.php?option=com_docman&amp;task=doc_download&amp;gid=' . $media->docMan_id . '"
		 title="' . $media->malttext . ' - ' . $media->comment . '" target="' . $media->special . '"><img src="' . $src
				. '" alt="' . $media->malttext . ' ' . $duration . ' ' . $filesize . '" width="' . $width
				. '" height="' . $height . '" border="0"  alt"' . $media->malttext . '" /></a>';

		return $docman;
	}

	/**
	 * Get Article
	 *
	 * @param   object  $media   Media
	 * @param   int     $width   Width
	 * @param   int     $height  Height
	 * @param   string  $src     URL of image
	 *
	 * @return string
	 */
	public static function getArticle($media, $width, $height, $src)
	{
		$article = '<a href="index.php?option=com_content&amp;view=article&amp;id=' . $media->article_id . '"
		 alt="' . $media->malttext . ' - ' . $media->comment . '" target="' . $media->special . '"><img src="' . $src . '" width="' . $width
				. '" height="' . $height . '" border="0"  alt"' . $media->malttext . '" /></a>';

		return $article;
	}

	/**
	 * Get Virtuart
	 *
	 * @param   object  $media   Media info
	 * @param   int     $width   Width
	 * @param   int     $height  Height
	 * @param   string  $src     Source
	 * @param   object  $params  Item Params
	 *
	 * @return string
	 */
	public static function getVirtuemart($media, $width, $height, $src, $params)
	{

		$vm = '<a href="index.php?option=com_virtuemart&amp;page=shop.product_details&amp;flypage='
				. $params->get('store_page', 'flypage.tpl') . '&amp;product_id=' . $media->virtueMart_id . '"alt="'
				. $media->malttext . ' - ' . $media->comment . '" target="' . $media->special . '"><img src="' . $src . '" width="' . $width
				. '" height="' . $height . '" border="0"  alt"' . $media->malttext . '" /></a>';

		return $vm;
	}

	/**
	 * Get MediaRows
	 *
	 * @param   int  $study_id  ID
	 *
	 * @return object
	 */
	public static function getMediaRows($study_id)
	{
		$query = 'SELECT #_bsms_mediafiles.*,'
				. ' #_bsms_servers.id AS ssid, #_bsms_servers.server_path AS spath,'
				. ' #_bsms_folders.id AS fid, #_bsms_folders.folderpath AS fpath,'
				. ' #_bsms_media.id AS mid, #_bsms_media.media_image_path AS impath, #_bsms_media.media_image_name AS imname, #_bsms_media.path2 AS path2,'
				. ' #_bsms_media.media_alttext AS malttext,'
				. ' #_bsms_mimetype.id AS mtid, #_bsms_mimetype.mimetext'
				. ' FROM #_bsms_mediafiles'
				. ' LEFT JOIN #_bsms_media ON (#_bsms_media.id = #_bsms_mediafiles.media_image)'
				. ' LEFT JOIN #_bsms_servers ON (#_bsms_servers.id = #_bsms_mediafiles.server)'
				. ' LEFT JOIN #_bsms_folders ON (#_bsms_folders.id = #_bsms_mediafiles.path)'
				. ' LEFT JOIN #_bsms_mimetype ON (#_bsms_mimetype.id = #_bsms_mediafiles.mime_type)'
				. ' WHERE #_bsms_mediafiles.study_id = ' . $study_id
				. ' AND #_bsms_mediafiles.published = 1 ORDER BY ordering ASC, #_bsms_mediafiles.mime_type ASC;';

		$database = JFactory::getDBO();
		$database->setQuery($query);
		$mediaRows = $database->loadObjectList();

		return $mediaRows;
	}

	/**
	 * Get Store
	 *
	 * @param   object  $params  Params
	 * @param   object  $row     Row info
	 *
	 * @return string
	 *
	 *  Fixme look like this has missing info for the width and height
	 */
	private static function getStore($params, $row)
	{

		$mainframe = JFactory::getApplication();

		// Placing for starter of var
		$imagew = null;
		$imageh = null;

		$database  = JFactory::getDBO();
		$query     = 'SELECT m.media_image_name, m.media_alttext, m.media_image_path, m.id AS mid, s.id AS sid,'
				. ' s.image_cd, s.prod_cd, s.server_cd, sr.id AS srid, sr.server_path
                        FROM #__bsms_studies AS s
                        LEFT JOIN #__bsms_media AS m ON ( m.id = s.image_cd )
                        LEFT JOIN #__bsms_servers AS sr ON ( sr.id = s.server_cd )
                        WHERE s.id =' . $row->id;
		$database->setQuery($query);
		$cd    = $database->loadObject();
		$query = 'SELECT m.media_image_name, m.media_alttext, m.media_image_path, m.id AS mid, s.id AS sid,'
				. ' s.image_dvd, s.prod_dvd, s.server_dvd, sr.id AS srid, sr.server_path
                        FROM #__bsms_studies AS s
                        LEFT JOIN #__bsms_media AS m ON ( m.id = s.image_dvd )
                        LEFT JOIN #__bsms_servers AS sr ON ( sr.id = s.server_dvd )
                        WHERE s.id =' . $row->id;
		$database->setQuery($query);
		$dvd   = $database->loadObject();
		$store = '<table id="detailstable"><tr><td>';

		if (($cd->mid + $dvd->mid) > 0)
		{
			if ($cd->mid > 0)
			{
				$src = JURI::base() . $cd->media_image_path;

				if ($imagew)
				{
					$width = $imagew;
				}
				else
				{
					$width = 24;
				}

				if ($imageh)
				{
					$height = $imageh;
				}
				else
				{
					$height = 24;
				}
				$store .= '<a href="' . $cd->server_path . $cd->prod_cd . '" title="' . $cd->media_alttext . '"><img src="'
						. JURI::base() . $cd->media_image_path . '" width="' . $width . '" height="' . $height . '" alt="'
						. $cd->media_alttext . ' "border="0" /></a></td>';
			}

			if ($dvd->mid > 0)
			{
				$src = JURI::base() . $dvd->media_image_path;

				if ($imagew)
				{
					$width = $imagew;
				}
				else
				{
					$width = 24;
				}

				if ($imageh)
				{
					$height = $imageh;
				}
				else
				{
					$height = 24;
				}
				$store .= '<td><a href="' . $dvd->server_path . $dvd->prod_dvd
						. '" title="' . $dvd->media_alttext . '"><img src="' . JURI::base() . $dvd->media_image_path
						. '" width="' . $width . '" height="' . $height . '" alt="' . $dvd->media_alttext
						. ' "border="0" /></a></td></tr><tr><td colspan="2" align="center"><span' . $params->get('store_span')
						. $params->get('store_name') . '</span></td>';
			}
		}
		$store .= '</tr></table>';

		return $store;
	}


	/**
	 * Get File Path
	 *
	 * @param   string  $id3      ID
	 * @param   string  $idfield  ID Filed
	 * @param   string  $mime     MimeType info
	 *
	 * @return string
	 */
	public static function getFilepath($id3, $idfield, $mime)
	{

		$mainframe = JFactory::getApplication();

		$database = JFactory::getDBO();
		$query    = 'SELECT #__bsms_mediafiles.*,'
				. ' #__bsms_servers.id AS ssid, #__bsms_servers.server_path AS spath,'
				. ' #__bsms_folders.id AS fid, #__bsms_folders.folderpath AS fpath'
				. ' FROM #__bsms_mediafiles'
				. ' LEFT JOIN #__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server)'
				. ' LEFT JOIN #__bsms_folders ON (#__bsms_folders.id = #__bsms_mediafiles.path)'
				. ' WHERE ' . $idfield . ' = ' . $id3 . ' AND #__bsms_mediafiles.published = 1 ' . $mime;
		$database->setQuery($query);
		$filepathresults = $database->loadObject();

		if ($filepathresults)
		{
			$filepath = $filepathresults->spath . $filepathresults->fpath . $filepathresults->filename;

			// Check url for "http://" prefix, and add it if it doesn't exist
			if (!preg_match('@^(?:http://)?([^/]+)@i', $filepath))
			{
				$filepath = 'http://' . $filepath;
			}
		}
		elseif (isset($filepathresults->docMan_id))
		{
			$filepath = '<a href="index.php?option=com_docman&task=doc_download&gid=' . $filepathresults->docMan_id . '"';
		}
		else
		{
			$filepath = '';
		}

		return $filepath;
	}
}
