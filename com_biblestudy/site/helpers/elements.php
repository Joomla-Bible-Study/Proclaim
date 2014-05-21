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

JLoader::register('JBSMImage', BIBLESTUDY_PATH_ADMIN_HELPERS . '/image.php');
JLoader::register('JBSMParams', BIBLESTUDY_PATH_ADMIN_HELPERS . '/params.php');
JLoader::register('JBSAdmin', BIBLESTUDY_PATH_ADMIN_LIB . '/biblestudy.admin.class.php');
JLoader::register('jbsMedia', BIBLESTUDY_PATH_LIB . '/biblestudy.media.class.php');

/**
 * Class for Elements
 *
 * @package  BibleStudy.Site
 * @since    8.0.0
 */
class JBSMElements
{
	/**
	 * Extension Name
	 *
	 * @var string
	 */
	public static $extension = 'com_biblestudy';

	/**
	 * Get Elementid
	 *
	 * @param   int       $rowid         ID
	 * @param   JTable    $row           Table info
	 * @param   JRegistry $params        Component / System Params
	 * @param   JRegistry $admin_params  Admin Settings
	 * @param   int       $template      Template ID
	 *
	 * @todo Redo to MVC Standers under a class
	 * @return object
	 */
	public function getElementid($rowid, $row, $params, $admin_params, $template)
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

				if (isset($row->teachername))
				{
					$elementid->element = $row->teachername;
				}
				else
				{
					$elementid->element = '';
				}
				break;
			case 8:
				$elementid->id         = 'teacher';
				$elementid->headertext = JText::_('JBS_CMN_TEACHER');

				if (isset($row->teachertitle) && isset($row->teachername))
				{
					$elementid->element = $row->teachertitle . ' ' . $row->teachername;
				}
				else
				{
					$elementid->element = '';
				}
				break;
			case 9:
				$elementid->id         = 'series';
				$elementid->headertext = JText::_('JBS_CMN_SERIES');

				if (isset($row->series_text))
				{
					$elementid->element = $row->series_text;
				}
				else
				{
					$elementid->element = '';
				}
				break;
			case 10:
				$elementid->id         = 'date';
				$elementid->headertext = JText::_('JBS_CMN_STUDY_DATE');

				if (isset($row->studydate))
				{
					$elementid->element = self::getstudyDate($params, $row->studydate);
				}
				else
				{
					$elementid->element = '';
				}
				break;
			case 11:
				$elementid->id         = 'submitted';
				$elementid->headertext = JText::_('JBS_CMN_SUBMITTED_BY');

				if (isset($row->submitted))
				{
					$elementid->element = $row->submitted;
				}
				else
				{
					$elementid->element = '';
				}
				break;
			case 12:
				$elementid->id         = 'hits';
				$elementid->headertext = JText::_('JBS_CMN_VIEWS');

				if (isset($row->hits))
				{
					$elementid->element = JText::_('JBS_CMN_HITS') . ' ' . $row->hits;
				}
				else
				{
					$elementid->element = '';
				}
				break;
			case 13:
				$elementid->id         = 'studynumber';
				$elementid->headertext = JText::_('JBS_CMN_STUDYNUMBER');

				if (isset($row->studynumber))
				{
					$elementid->element = $row->studynumber;
				}
				else
				{
					$elementid->element = '';
				}
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

				if (isset($row->location_text))
				{
					$elementid->element = $row->location_text;
				}
				else
				{
					$elementid->element = '';
				}
				break;
			case 16:
				$elementid->id         = 'messagetype';
				$elementid->headertext = JText::_('JBS_CMN_MESSAGE_TYPE');

				if (isset($row->message_type))
				{
					$elementid->element = $row->message_type;
				}
				else
				{
					$elementid->element = '';
				}
				break;
			case 17:
				$elementid->id         = 'details';
				$elementid->headertext = JText::_('JBS_CMN_DETAILS');
				$textorpdf             = 'text';
				$elementid->element    = $this->getTextlink($params, $row, $textorpdf, $admin_params, $template);
				break;
			case 18:
				$elementid->id         = 'details';
				$elementid->headertext = JText::_('JBS_CMN_DETAILS');
				$textorpdf             = 'text';
				$elementid->element    = '<table class="table detailstable"><tbody><tr><td>';
				$elementid->element .= $this->getTextlink($params, $row, $textorpdf, $admin_params, $template) . '</td><td>';
				$textorpdf = 'pdf';
				$elementid->element .= $this->getTextlink(
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
				$mediaclass            = new jbsMedia;
				$elementid->id         = 'jbsmedia';
				$elementid->headertext = JText::_('JBS_CMN_MEDIA');
				$elementid->element    = $mediaclass->getMediaTable($row, $params, $admin_params);
				break;
				break;
			case 22:
				$elementid->id         = 'store';
				$elementid->headertext = JText::_('JBS_CMN_STORE');
				$elementid->element    = self::getStore($params, $row);
				break;
			case 23:
				$elementid->id         = 'filesize';
				$elementid->headertext = JText::_('JBS_CMN_FILESIZE');
				$query_media1          = $db->getQuery(true);
				$query_media1->select('#__bsms_mediafiles.id AS mid, #__bsms_mediafiles.size, #__bsms_mediafiles.published, #__bsms_mediafiles.study_id')
					->from('#__bsms_mediafiles')
					->where('#__bsms_mediafiles.study_id = ' . $row->id)
					->where('#__bsms_mediafiles.published = ' . 1)
					->order('ordering, #__bsms_mediafiles.id asc');
				$db->setQuery($query_media1, 0, 1);
				$media1             = $db->loadObject();
				$elementid->element = $this->getFilesize($media1->size);
				break;
			case 25:
				$elementid->id         = 'thumbnail';
				$elementid->headertext = JText::_('JBS_CMN_THUMBNAIL');

				if ($row->thumbnailm)
				{
					$images             = new JBSMImages;
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
					$images             = new JBSMImages;
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

				if (isset($row->sdescription))
				{
					$elementid->element = $row->sdescription;
				}
				else
				{
					$elementid->element = '';
				}
				break;
			case 28:
				$elementid->id         = 'plays';
				$elementid->headertext = JText::_('JBS_CMN_PLAYS');

				if (isset($row->totalplays))
				{
					$elementid->element = $row->totalplays;
				}
				else
				{
					$elementid->element = '';
				}
				break;
			case 29:
				$elementid->id         = 'downloads';
				$elementid->headertext = JText::_('JBS_CMN_DOWNLOADS');

				if (isset($row->totaldownloads))
				{
					$elementid->element = $row->totaldownloads;
				}
				else
				{
					$elementid->element = '';
				}
				break;
			case 30:
				$timages              = new JBSMImages;
				$elementid->id        = 'teacher-image';
				$elementid->headetext = JText::_('JBS_CMN_TEACHER_IMAGE');
				$query                = $db->getQuery(true);
				$query->select('thumb, teacher_thumbnail')->from('#__bsms_teachers')->where('id =' . $row->teacher_id);
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
	 * @param   object $params        Item Params
	 * @param   object $row           Row Info
	 * @param   string $esv           ESV String
	 * @param   string $scripturerow  Scripture Row
	 *
	 * @return string
	 */
	public function getScripture($params, $row, $esv, $scripturerow)
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
		$query       = $db->getQuery(true);
		$query->select('#__bsms_studies.*, #__bsms_books.bookname, #__bsms_books.id as bid')
			->from('#__bsms_studies')
			->leftJoin('#__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)')
			->where('#__bsms_studies.id = ' . $row->id);
		$db->setQuery($query);
		$bookresults  = $db->loadObject();
		$affectedrows = count($bookresults);

		if ($affectedrows < 1)
		{
			return null;
		}
		$query = $db->getQuery(true);
		$query->select('bookname, booknumber')->from('#__bsms_books')->where('booknumber = ' . $booknumber);
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
	 * @param   object $params  Item Params
	 * @param   object $row     Row info
	 *
	 * @return  null|string
	 */
	public function getDuration($params, $row)
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
	 * @param   object $params     Item Params
	 * @param   string $studydate  Study Date
	 *
	 * @return string
	 */
	public function getstudyDate($params, $studydate)
	{
        if (!$this->MyCheckDate($studydate))
            {
                $date = $studydate; return $date;
            }

		switch ($params->get('date_format'))
		{
			case 0:
				$date = JHTML::date($studydate, "M j, Y");
				break;
			case 1:
				$date = JHTML::date($studydate, "M J");
				break;
			case 2:
				$date = JHTML::date($studydate, "n/j/Y");
				break;
			case 3:
				$date = JHTML::date($studydate, "n/j");
				break;
			case 4:
				$date = JHTML::date($studydate, "l, F j, Y");
				break;
			case 5:
				$date = JHTML::date($studydate, "F j, Y");
				break;
			case 6:
				$date = JHTML::date($studydate, "j F Y");
				break;
			case 7:
				$date = JHTML::date($studydate, "j/n/Y");
				break;
			case 8:
				$date = JHTML::date($studydate, JText::_('DATE_FORMAT_LC'));
				break;
			case 9:
				$date = JHTML::date($studydate, "Y/M/D");
				break;
			default:
				$date = JHTML::date($studydate, "n/j");
				break;
		}

		$customDate = $params->get('custom_date_format');

		if ($customDate != '')
		{
			$date = JHTML::date($studydate, $customDate);
		}

		return $date;
	}
    /**
     * Check whether date is valid YYYY-MM-DD format
     *
     * @param   string $datein  Study Date
     *
     * @return boolean
     */
    function MyCheckDate( $datein ) {
        if (preg_match ("/([0-9]{4})-([0-9]{2})-([0-9]{2})/", $datein))
        {
            return true;
        }else{
            return false;
        }
    }
    /**
	 * Function to get File Size
	 *
	 * @param   string $file_size  Size in bytes
	 *
	 * @return null|string
	 */
	public function getFilesize($file_size)
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
	 * @param   JRegistry $params        Item Params
	 * @param   object    $row           JTable
	 * @param   string    $textorpdf     Text Or PDF location
	 * @param   JRegistry $admin_params  Admin info
	 * @param   int       $template      Template ID
	 *
	 * @return string
	 */
	public function getTextlink($params, $row, $textorpdf, $admin_params, $template)
	{
		$linktext = null;

		$images = new JBSMImages;
		$input  = new JInput;
		$t      = null;

		if (!$template)
		{
			$t        = $input->get('t', 1, 'int');
			$template = JBSMParams::getTemplateparams();
		}
		else
		{
			$t = (int) $template->id;
		}

		if (!$template->text || !substr_count($template->text, '/'))
		{
			$i_path    = 'media/com_biblestudy/images/textfile24.png';
			$textimage = $images->getImagePath($i_path);
			$src       = JURI::base() . $textimage->path;
			$height    = $textimage->height;
			$width     = $textimage->width;
		}
		elseif (substr_count($template->text, '//'))
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
	 * Get Docman
	 *
	 * @param   object $media     Media
	 * @param   int    $width     Width
	 * @param   int    $height    Height
	 * @param   string $src       Sorce of Image
	 * @param   string $duration  Duration
	 * @param   int    $filesize  File Size of Doc
	 *
	 * @return string
	 */
	public function getDocman($media, $width, $height, $src, $duration, $filesize)
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
	 * @param   object $media   Media
	 * @param   int    $width   Width
	 * @param   int    $height  Height
	 * @param   string $src     URL of image
	 *
	 * @return string
	 */
	public function getArticle($media, $width, $height, $src)
	{
		$article = '<a href="index.php?option=com_content&amp;view=article&amp;id=' . $media->article_id . '"
		 alt="' . $media->malttext . ' - ' . $media->comment . '" target="' . $media->special . '"><img src="' . $src . '" width="' . $width
			. '" height="' . $height . '" border="0"  alt"' . $media->malttext . '" /></a>';

		return $article;
	}

	/**
	 * Get Virtuart
	 *
	 * @param   object $media   Media info
	 * @param   int    $width   Width
	 * @param   int    $height  Height
	 * @param   string $src     Source
	 * @param   object $params  Item Params
	 *
	 * @return string
	 */
	public function getVirtuemart($media, $width, $height, $src, $params)
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
	 * @param   int $study_id  ID
	 *
	 * @return object
	 */
	public function getMediaRows($study_id)
	{
		$database = JFactory::getDBO();
		$query    = $database->getQuery(true);
		$query->select('SELECT #_bsms_mediafiles.*,'
		. ' #_bsms_servers.id AS ssid, #_bsms_servers.server_path AS spath,'
		. ' #_bsms_folders.id AS fid, #_bsms_folders.folderpath AS fpath,'
		. ' #_bsms_media.id AS mid, #_bsms_media.media_image_path AS impath, #_bsms_media.media_image_name AS imname, #_bsms_media.path2 AS path2,'
		. ' #_bsms_media.media_alttext AS malttext,'
		. ' #_bsms_mimetype.id AS mtid, #_bsms_mimetype.mimetext')
			->from('#_bsms_mediafiles')
			->leftJoin('#_bsms_media ON (#_bsms_media.id = #_bsms_mediafiles.media_image)')
			->leftJoin('#_bsms_servers ON (#_bsms_servers.id = #_bsms_mediafiles.server)')
			->leftJoin('#_bsms_folders ON (#_bsms_folders.id = #_bsms_mediafiles.path)')
			->leftJoin('#_bsms_mimetype ON (#_bsms_mimetype.id = #_bsms_mediafiles.mime_type)')
			->where('#_bsms_mediafiles.study_id = ' . $study_id)
			->where('#_bsms_mediafiles.published = ' . 1)
			->order('ordering asc, #_bsms_mediafiles.mime_type asc');
		$database->setQuery($query);
		$mediaRows = $database->loadObjectList();

		return $mediaRows;
	}

	/**
	 * Get Store
	 *
	 * @param   object $params  Params
	 * @param   object $row     Row info
	 *
	 * @return string
	 *
	 *  Fixme look like this has missing info for the width and height. TOM
	 */
	private function getStore($params, $row)
	{

		$mainframe = JFactory::getApplication();

		// Placing for starter of var
		$imagew = null;
		$imageh = null;

		$database = JFactory::getDBO();
		$query    = $database->getQuery(true);
		$query->select('m.media_image_name, m.media_alttext, m.media_image_path, m.id AS mid, s.id AS sid,'
		. ' s.image_cd, s.prod_cd, s.server_cd, sr.id AS srid, sr.server_path')
			->from('#__bsms_studies AS s')
			->leftJoin('#__bsms_media AS m ON ( m.id = s.image_cd )')
			->leftJoin('#__bsms_servers AS sr ON ( sr.id = s.server_cd )')
			->where('s.id =' . $row->id);
		$database->setQuery($query);
		$cd    = $database->loadObject();
		$query = $database->getQuery(true);
		$query->select('m.media_image_name, m.media_alttext, m.media_image_path, m.id AS mid, s.id AS sid,'
		. ' s.image_dvd, s.prod_dvd, s.server_dvd, sr.id AS srid, sr.server_path')
			->from('#__bsms_studies AS s')
			->leftJoin('#__bsms_media AS m ON ( m.id = s.image_dvd )')
			->leftJoin('#__bsms_servers AS sr ON ( sr.id = s.server_dvd )')
			->where('s.id =' . $row->id);
		$database->setQuery($query);
		$dvd   = $database->loadObject();
		$store = '<table class="table" id="detailstable"><tr><td>';

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
	 * @param   string $id3      ID
	 * @param   string $idfield  ID Filed
	 * @param   string $mime     MimeType info
	 *
	 * @return string
	 *
	 * @FIXME look like the last where is not right, TOM
	 */
	public function getFilepath($id3, $idfield, $mime)
	{
		$mainframe = JFactory::getApplication();

		$database = JFactory::getDBO();
		$query    = $database->getQuery(true);
		$query->select('#__bsms_mediafiles.*,'
		. ' #__bsms_servers.id AS ssid, #__bsms_servers.server_path AS spath,'
		. ' #__bsms_folders.id AS fid, #__bsms_folders.folderpath AS fpath')
			->from('#__bsms_mediafiles')
			->leftJoin('#__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server)')
			->leftJoin('#__bsms_folders ON (#__bsms_folders.id = #__bsms_mediafiles.path)')
			->where($idfield . ' = ' . $id3)
			->where('#__bsms_mediafiles.published = 1 ' . $mime);
		$database->setQuery($query);
		$filepathresults = $database->loadObject();

		if ($filepathresults)
		{
			$filepath = $filepathresults->spath . $filepathresults->fpath . $filepathresults->filename;

			// Check url for "http://" prefix, and add it if it doesn't exist
			if (!preg_match('@^(?:http://)?([^/]+)@i', $filepath))
			{
				$filepath = '//' . $filepath;
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

	/**
	 * Only Return the Body of a html doc.
	 *
	 * @param   string $html  Html document
	 *
	 * @return string
	 *
	 * @since 8.0.0
	 */
	public function body_only($html)
	{
		return preg_replace("/.*<body[^>]*>|<\/body>.*/si", "", $html);
	}
}
