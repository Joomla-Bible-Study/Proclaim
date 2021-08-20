<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2021 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
defined('_JEXEC') or die;

/**
 * Convert Class
 * PreachIT Converter system
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 */
class JBSMPIconvert
{
	/**
	 * Array of Comments Id's
	 *
	 * @var array
	 *
	 * @since 9.0.0
	 */
	public $commentsids;

	/**
	 * Array of Servers Id's
	 *
	 * @var array
	 *
	 * @since 9.0.0
	 */
	public $serversids;

	/**
	 * Array of Folders Id's
	 *
	 * @var array
	 *
	 * @since 9.0.0
	 */
	public $foldersids;

	/**
	 * Array of Studies Id's
	 *
	 * @var array
	 *
	 * @since 9.0.0
	 */
	public $studiesids;

	/**
	 * Array of Media-Files Id's
	 *
	 * @var array
	 *
	 * @since 9.0.0
	 */
	public $mediafilesids;

	/**
	 * Array of Teachers Id's
	 *
	 * @var array
	 *
	 * @since 9.0.0
	 */
	public $teachersids;

	/**
	 * Array of Series Id's
	 *
	 * @var array
	 *
	 * @since 9.0.0
	 */
	public $seriesids;

	/**
	 * Array of Podcasts Id's
	 *
	 * @var array
	 *
	 * @since 9.0.0
	 */
	public $podcastids;

	/**
	 * Array of Locations
	 *
	 * @var array
	 *
	 * @since 9.0.0
	 */
	public $locations;

	/**
	 * ???
	 *
	 * @var int
	 *
	 * @since 9.0.0
	 */
	public $cnoadd;

	/**
	 * Can Add switch
	 *
	 * @var int
	 *
	 * @since 9.0.0
	 */
	public $cadd;

	/**
	 * Comment object
	 *
	 * @var object
	 *
	 * @since 9.0.0
	 */
	public $picomments;

	/**
	 * Number of Podcasts
	 *
	 * @var int
	 *
	 * @since 9.0.0
	 */
	public $podcasts;

	/**
	 * Convert PreachIT
	 *
	 * @return string
	 *
	 * @since 7.1.0
	 */
	public function convertPI()
	{
		// Check for request forgeries.
		JSession::checkToken('get') or JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->commentsids   = array();
		$this->serversids    = array();
		$this->foldersids    = array();
		$this->mediafilesids = array();
		$this->studiesids    = array();
		$this->teachersids   = array();
		$this->seriesids     = array();
		$this->podcastids    = array();
		$this->locations     = array();
		$this->cnoadd        = 0;
		$this->cadd          = 0;
		$svadd               = 0;
		$svnoadd             = 0;
		$fnoadd              = 0;
		$fadd                = 0;
		$tnoadd              = 0;
		$tadd                = 0;
		$srnoadd             = 0;
		$sradd               = 0;
		$pnoadd              = 0;
		$padd                = 0;
		$lnoadd              = 0;
		$ladd                = 0;
		$snoadd              = 0;
		$sadd                = 0;
		$mnoadd              = 0;
		$madd                = 0;
		$newid               = 0;
		$oldid               = 0;

		//Convert comments
		$db                  = JFactory::getDbo();
		$query               = $db->getQuery(true);
		$query->select('*')->from('#__picomments');
		$db->setQuery($query);
		$this->picomments = $db->loadObjectList();
		/** @var $piconversion string */
		$piconversion = null;

		// Create servers
		$query = $db->getQuery(true);
		$query->select('*')->from('#__pifilepath');
		$db->setQuery($query);
		$piservers = $db->loadObjectList();

		if (!$piservers)
		{
			$svnoadd++;
		}
		else
		{
			foreach ($piservers as $pi)
			{
				$data         = new stdClass;
				$data->id     = null;
				$data->type   = 'legacy';
				$data->params = '{"path":"\/\/' . $pi->server . $pi->folder . '\/","protocol":"http:\/\/"}';
				$data->media  = '{"link_type":"1","player":"7","popup":"3","mediacode":"","media_image":"images\/biblestudy\/mp3.png","mime_type":"audio\/mp3","autostart":"1"}';
				//$data->server_path = $pi->server;
				$data->server_name = $pi->name;
				$data->published   = $pi->published;

				if (!$db->insertObject('#__bsms_servers', $data, 'id'))
				{
					$svnoadd++;
				}
				else
				{
					$svadd++;
					$query = $db->getQuery(true);
					$query->select('id')->from('#__bsms_servers')->order('id desc');
					$db->setQuery($query, 0, 1);
					$newid              = $db->loadResult();
					$oldid              = $pi->id;
					$this->serversids[] = array('newid' => $newid, 'oldid' => $oldid);
					//xdebug_var_dump($this->serversids);

				}
/*
				$datafolders             = new stdClass;
				$datafolders->id         = null;
				$datafolders->foldername = $pi->name;
				$datafolders->folderpath = $pi->server . $pi->folder;
				$datafolders->published  = $pi->published;
*/
			}
		}

		// Teachers
		$query = $db->getQuery(true);
		$query->select('*')->from('#__piteachers');
		$db->setQuery($query);
		$piteachers = $db->loadObjectList();

		if (!$piteachers)
		{
			$tnoadd++;
		}
		else
		{
			foreach ($piteachers as $pi)
			{

				$datateachers              = new stdClass;
				$datateachers->id          = null;
				$datateachers->teachername = $pi->name;
				$datateachers->alias       = $pi->alias;
				$datateachers->title       = $pi->teacher_title;
				$datateachers->image       = $pi->image_folderlrg . $pi->teacher_image_lrg;
				$datateachers->thumb       = $pi->image_folderlrg . $pi->teacher_image_lrg;
				$datateachers->email       = $pi->email;
				$datateachers->website     = $pi->website;
				$datateachers->short       = $db->escape($pi->description);
				$datateachers->list_show   = $pi->teacher_view;
				$datateachers->published   = $pi->published;

				if (!$db->insertObject('#__bsms_teachers', $datateachers, 'id'))
				{
					$tnoadd++;
				}
				else
				{
					$tadd++;

					// Get the new teacherid so we can later connect it to a study
					$query = $db->getQuery(true);
					$query->select('id')->from('#__bsms_teachers')->order('id desc');
					$db->setQuery($query, 0, 1);
					$newid               = $db->loadResult();
					$oldid               = $pi->id;
					$this->teachersids[] = array('newid' => $newid, 'oldid' => $oldid);
				}
			}
		}

		// Convert Ministries
		$query = $db->getQuery(true);
		$query->select('*')->from('#__piministry');
		$db->setQuery($query);
		$ministries = $db->loadObjectList();

		if (!$ministries)
		{
			$piconversion .= '<tr><td>' . JText::_('JBS_IBM_NO_MINISTRIES') . '</td></tr>';
		}
		else
		{
			foreach ($ministries as $pi)
			{
				$locations                = new stdClass;
				$locations->id            = null;
				$locations->published     = $pi->published;
				$locations->location_text = $pi->name;
				$locations->access        = $pi->access;
				$locations->ordering      = $pi->ordering;
				$locations->misc   = $pi->description;
				$locations->image         = $pi->image_folderlrg . $pi->ministry_image_lrg;

                if (!$db->insertObject('#__bsms_locations', $locations, 'id'))
                {
                    $lnoadd++;
                }
                else
                {
                    $ladd++;

                    // Get the new teacherid so we can later connect it to a study
                    $query = $db->getQuery(true);
                    $query->select('id')->from('#__bsms_locations')->order('id desc');
                    $db->setQuery($query, 0, 1);
                    $newid             = $db->loadResult();
                    $oldid             = $pi->id;
                    $this->locations[] = array('newid' => $newid, 'oldid' => $oldid);
                }

			}

		}


		// Convert Series
		$query = $db->getQuery(true);
		$query->select('*')->from('#__piseries');
		$db->setQuery($query);
		$series = $db->loadObjectList();

		if (!$series)
		{
			$piconversion .= '<tr><td>' . JText::_('JBS_IBM_NO_SERIES') . '</td></tr>';
		}
		else
		{
			foreach ($series as $pi)
			{

				$dataseries                   = new stdClass;
				$dataseries->id               = null;
				$dataseries->series_text      = $pi->name;
				$dataseries->alias            = $pi->alias;
				$dataseries->description      = $pi->description;
				$dataseries->series_thumbnail = $pi->image_folderlrg . $pi->series_image_lrg;
				$dataseries->published        = $pi->published;

				if (!$db->insertObject('#__bsms_series', $dataseries, 'id'))
				{
					$srnoadd++;
				}
				else
				{
					$sradd++;

					// Get the new teacherid so we can later connect it to a study
					$query = $db->getQuery(true);
					$query->select('id')->from('#__bsms_series')->order('id desc');
					$db->setQuery($query, 0, 1);
					$newid             = $db->loadResult();
					$oldid             = $pi->id;
					$this->seriesids[] = array('newid' => $newid, 'oldid' => $oldid);
				}
			}
		}

		// Convert the podcacst
		$query = $db->getQuery(true);
		$query->select('*')->from('#__pipodcast');
		$db->setQuery($query);
		$podcasts = $db->loadObjectList();

		if (!$podcasts)
		{
			$piconversion .= '<tr><td>' . JText::_('JBS_IBM_NO_PODCASTS') . '</td></tr>';
		}
		else
		{
			foreach ($podcasts as $pi)
			{
				$podcast                    = new stdClass;
				$podcast->id                = null;
				$podcast->title             = $pi->name;
				$podcast->website           = $pi->website;
				$podcast->description       = $pi->description;
				$podcast->image             = $pi->image;
				$podcast->imageh            = $pi->imagehgt;
				$podcast->imagew            = $pi->imagewth;
				$podcast->author            = $pi->author;
				$podcast->filename          = $pi->filename;
				$podcast->language          = $pi->language;
				$podcast->editor_name       = $pi->editor;
				$podcast->editor_email      = $pi->email;
				$podcast->podcastlimit      = $pi->records;
				$podcast->episodetitle      = $pi->itunestitle;
				$podcast->detailstemplateid = 1;
				$podcast->published         = $pi->published;
				$podcast->podcastsearch     = $pi->search;

				if (!$db->insertObject('#__bsms_podcast', $podcast, 'id'))
				{
					$pnoadd++;
				}
				else
				{
					$padd++;

					// Get the new podcast id so we can later connect it to a study
					$query = $db->getQuery(true);
					$query->select('id')->from('#__bsms_podcast')->order('id desc');
					$db->setQuery($query, 0, 1);
					$newid            = $db->loadResult();
					$oldid            = $pi->id;
					$this->podcasts[] = array('newid' => $newid, 'oldid' => $oldid);
				}
			}
		}

		// Convert studies and media files
		$books = $this->getBooks();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__pistudies');
		$db->setQuery($query);
		$studies = $db->loadObjectList();

		if (!$studies)
		{
			$piconversion .= '<tr><td>' . JText::_('JBS_IBM_NO_STUDIES') . '</td></tr>';
		}
		else
		{
			foreach ($studies as $pi)
			{
				$studydate  = $pi->date;
				$studytitle = $pi->name;
				$teacher_id = null;

				foreach ($this->teachersids as $teacher)
				{
					if ($teacher['oldid'] == $pi->teacher)
					{
						$teacher_id = $teacher['newid'];
					}
					else
					{
						$teacher_id = '1';
					}
				}

				$studynumber = $pi->id;
				$booknumber  = null;
				$booknumber2 = null;

				foreach ($books as $book)
				{
					if ($book['id'] == $pi->study_book)
					{
						$booknumber = $book['jbs'];
					}
					else
					{
						$booknumber = '101';
					}

					if ($book['id'] == $pi->study_book2)
					{
						$booknumber2 = $book['jbs'];
					}
				}

				$chapter_begin  = $pi->ref_ch_beg;
				$chapter_end    = $pi->ref_ch_end;
				$verse_begin    = $pi->ref_vs_beg;
				$verse_end      = $pi->ref_vs_end;
				$chapter_begin2 = $pi->ref_ch_beg2;
				$chapter_end2   = $pi->ref_ch_end2;
				$verse_begin2   = $pi->ref_vs_beg2;
				$verse_end2     = $pi->ref_vs_end2;
				$comments       = $pi->comments;
				$hits           = $pi->hits;
				$user_id        = $pi->user;
				$show_level     = $pi->access;
				$location_id    = null;

				foreach ($this->locations as $location)
				{
					if ($location['oldid'] == $pi->ministry)
					{
						$location_id = $location['newid'];
					}
				}

				$alias      = $pi->alias;
				$studyintro = $pi->description;
				$series_id  = null;

				foreach ($this->seriesids as $series)
				{
					if ($series['oldid'] == $pi->series)
					{
						$series_id = $series['newid'];
					}
				}

				$studytext   = $db->escape($pi->study_text);
				$imagefolder = 0;
				$newfolder   = 0;
				$thumbnailm  = '';
				$image       = null;

				foreach ($this->foldersids as $folder)
				{
					if ($folder['oldid'] == $pi->image_folderlrg)
					{
						$imagefolder = $folder['newid'];
						$image       = $pi->imagelrg;
					}
				}

				if ($imagefolder)
				{

					$thumbnailm = $newfolder . $image;
				}

				$published = $pi->published;
				$params    = '{"metakey":"' . $pi->metakey . '","metadesc":""}';
				$params    = $db->escape($params);
				$access    = $pi->saccess;

				// Create the study then get the id to create the media file and comments
				$datastudies                 = new stdClass;
				$datastudies->id             = null;
				$datastudies->published      = $published;
				$datastudies->studydate      = $studydate;
				$datastudies->studytitle     = $studytitle;
				$datastudies->teacher_id     = $teacher_id;
				$datastudies->studynumber    = $studynumber;
				$datastudies->booknumber     = $booknumber;
				$datastudies->booknumber2    = $booknumber2;
				$datastudies->chapter_begin  = $chapter_begin;
				$datastudies->chapter_end    = $chapter_end;
				$datastudies->verse_begin    = $verse_begin;
				$datastudies->verse_end      = $verse_end;
				$datastudies->chapter_begin2 = $chapter_begin2;
				$datastudies->chapter_end2   = $chapter_end2;
				$datastudies->verse_begin2   = $verse_begin2;
				$datastudies->verse_end2     = $verse_end2;
				$datastudies->comments       = $comments;
				$datastudies->hits           = $hits;
				$datastudies->user_id        = $user_id;
				$datastudies->show_level     = $show_level;
				$datastudies->location_id    = $location_id;
				$datastudies->alias          = $alias;
				$datastudies->studyintro     = $studyintro;
				$datastudies->series_id      = $series_id;
				$datastudies->studytext      = $studytext;
				$datastudies->thumbnailm     = $thumbnailm;
				$datastudies->params         = $params;
				$datastudies->access         = $access;

				if (!$db->insertObject('#__bsms_studies', $datastudies, 'id'))
				{
					$snoadd++;
				}
				else
				{
					$sadd++;

					// Get the new studiesid so we can later connect it to a study
					$query = $db->getQuery(true);
					$query->select('id')->from('#__bsms_studies')->order('id desc');
					$db->setQuery($query, 0, 1);
					$newid              = $db->loadResult();
					$oldid              = $pi->id;
					$this->studiesids[] = array('newid' => $newid, 'oldid' => $oldid);
				}

				// Create the mediafiles
				if ($pi->audio_link)
				{
					if (!$audio = $this->insertMedia($pi, $type = 'audio', $newid, $oldid))
					{
						$mnoadd++;
					}
					else
					{
						$madd++;
					}
				}

				if ($pi->video_link)
				{
					if (!$video = $this->insertMedia($pi, $type = 'video', $newid, $oldid))
					{
						$mnoadd++;
					}
					else
					{
						$madd++;
					}
				}

				if ($pi->slides_link)
				{
					if (!$slides = $this->insertMedia($pi, $type = 'slides', $newid, $oldid))
					{
						$mnoadd++;
					}
					else
					{
						$madd++;
					}
				}

				if ($pi->notes_link)
				{
					if (!$notes = $this->insertMedia($pi, $type = 'notes', $newid, $oldid))
					{
						$mnoadd++;
					}
					else
					{
						$madd++;
					}
				}

				$comments = $this->insertComments($oldid, $newid);
			}
			// Endforeach study
		}

		$piconversion = '<table><tr><td><h3>' . JText::_('JBS_IBM_PREACHIT_RESULTS') . '</h3></td></tr>'
			. '<tr><td>' . JText::_('JBS_IBM_PI_SERVERS') . '<strong>' . $svadd . '</strong> - ' . JText::_('JBS_IBM_NOT_CONVERTED') . $svnoadd . '</td></tr>'
			. '<tr><td>' . JText::_('JBS_IBM_PI_FOLDERS') . '<strong>' . $fadd . '</strong> - ' . JText::_('JBS_IBM_NOT_CONVERTED') . $fnoadd . '</td></tr>'
			. '<tr><td>' . JText::_('JBS_IBM_PI_TEACHERS') . '<strong>' . $tadd . '</strong> - ' . JText::_('JBS_IBM_NOT_CONVERTED') . $tnoadd . '</td></tr>'
			. '<tr><td>' . JText::_('JBS_IBM_PI_SERIES') . '<strong>' . $sradd . '</strong> - ' . JText::_('JBS_IBM_NOT_CONVERTED') . $srnoadd . '</td></tr>'
			. '<tr><td>' . JText::_('JBS_IBM_PI_PODCAST') . '<strong>' . $padd . '</strong> - ' . JText::_('JBS_IBM_NOT_CONVERTED') . $pnoadd . '</td></tr>'
			. '<tr><td>' . JText::_('JBS_IBM_PI_STUDIES') . '<strong>' . $sadd . '</strong> - ' . JText::_('JBS_IBM_NOT_CONVERTED') . $snoadd . '</td></tr>'
			. '<tr><td>' . JText::_('JBS_IBM_PI_MEDIA') . '<strong>' . $madd . '</strong> - ' . JText::_('JBS_IBM_NOT_CONVERTED') . $mnoadd . '</td></tr>'
			. '<tr><td>' . JText::_('JBS_IBM_PI_COMMENTS') . '<strong>' . $this->cadd . '</strong> - ' . JText::_('JBS_IBM_NOT_CONVERTED')
			. $this->cnoadd . '</td></tr>'
			. '</table>';

		return $piconversion;
	}

	/**
	 * Get Books
	 *
	 * @return array
	 *
	 * @since 9.0.0
	 */
	private function getBooks()
	{
		$books = array(
			array('id' => '1', 'book_name' => 'Genesis', 'published' => '1', 'jbs' => '101'),
			array('id' => '2', 'book_name' => 'Exodus', 'published' => '1', 'jbs' => '102'),
			array('id' => '3', 'book_name' => 'Leviticus', 'published' => '1', 'jbs' => '103'),
			array('id' => '4', 'book_name' => 'Numbers', 'published' => '1', 'jbs' => '104'),
			array('id' => '5', 'book_name' => 'Deuteronomy', 'published' => '1', 'jbs' => '105'),
			array('id' => '6', 'book_name' => 'Joshua', 'published' => '1', 'jbs' => '106'),
			array('id' => '7', 'book_name' => 'Judges', 'published' => '1', 'jbs' => '107'),
			array('id' => '8', 'book_name' => 'Ruth', 'published' => '1', 'jbs' => '108'),
			array('id' => '9', 'book_name' => '1 Samuel', 'published' => '1', 'jbs' => '109'),
			array('id' => '10', 'book_name' => '2 Samuel', 'published' => '1', 'jbs' => '110'),
			array('id' => '11', 'book_name' => '1 Kings', 'published' => '1', 'jbs' => '111'),
			array('id' => '12', 'book_name' => '2 Kings', 'published' => '1', 'jbs' => '112'),
			array('id' => '13', 'book_name' => '1 Chronicles', 'published' => '1', 'jbs' => '113'),
			array('id' => '14', 'book_name' => '2 Chronicles', 'published' => '1', 'jbs' => '114'),
			array('id' => '15', 'book_name' => 'Ezra', 'published' => '1', 'jbs' => '115'),
			array('id' => '16', 'book_name' => 'Nehemiah', 'published' => '1', 'jbs' => '116'),
			array('id' => '17', 'book_name' => 'Esther', 'published' => '1', 'jbs' => '117'),
			array('id' => '18', 'book_name' => 'Job', 'published' => '1', 'jbs' => '118'),
			array('id' => '19', 'book_name' => 'Psalm', 'published' => '1', 'jbs' => '119'),
			array('id' => '20', 'book_name' => 'Proverbs', 'published' => '1', 'jbs' => '120'),
			array('id' => '21', 'book_name' => 'Ecclesiastes', 'published' => '1', 'jbs' => '121'),
			array('id' => '22', 'book_name' => 'Song of Songs', 'published' => '1', 'jbs' => '122'),
			array('id' => '23', 'book_name' => 'Isaiah', 'published' => '1', 'jbs' => '123'),
			array('id' => '24', 'book_name' => 'Jeremiah', 'published' => '1', 'jbs' => '124'),
			array('id' => '25', 'book_name' => 'Lamentations', 'published' => '1', 'jbs' => '125'),
			array('id' => '26', 'book_name' => 'Ezekiel', 'published' => '1', 'jbs' => '126'),
			array('id' => '27', 'book_name' => 'Daniel', 'published' => '1', 'jbs' => '127'),
			array('id' => '28', 'book_name' => 'Hosea', 'published' => '1', 'jbs' => '129'),
			array('id' => '29', 'book_name' => 'Joel', 'published' => '1', 'jbs' => '129'),
			array('id' => '30', 'book_name' => 'Amos', 'published' => '1', 'jbs' => '130'),
			array('id' => '31', 'book_name' => 'Obadiah', 'published' => '1', 'jbs' => '131'),
			array('id' => '32', 'book_name' => 'Jonah', 'published' => '1', 'jbs' => '132'),
			array('id' => '33', 'book_name' => 'Micah', 'published' => '1', 'jbs' => '133'),
			array('id' => '34', 'book_name' => 'Nahum', 'published' => '1', 'jbs' => '134'),
			array('id' => '35', 'book_name' => 'Habakkuk', 'published' => '1', 'jbs' => '135'),
			array('id' => '36', 'book_name' => 'Zephaniah', 'published' => '1', 'jbs' => '136'),
			array('id' => '37', 'book_name' => 'Haggai', 'published' => '1', 'jbs' => '137'),
			array('id' => '38', 'book_name' => 'Zechariah', 'published' => '1', 'jbs' => '138'),
			array('id' => '39', 'book_name' => 'Malachi', 'published' => '1', 'jbs' => '139'),
			array('id' => '40', 'book_name' => 'Matthew', 'published' => '1', 'jbs' => '140'),
			array('id' => '41', 'book_name' => 'Mark', 'published' => '1', 'jbs' => '141'),
			array('id' => '42', 'book_name' => 'Luke', 'published' => '1', 'jbs' => '142'),
			array('id' => '43', 'book_name' => 'John', 'published' => '1', 'jbs' => '143'),
			array('id' => '44', 'book_name' => 'Acts', 'published' => '1', 'jbs' => '144'),
			array('id' => '45', 'book_name' => 'Romans', 'published' => '1', 'jbs' => '145'),
			array('id' => '46', 'book_name' => '1 Corinthians', 'published' => '1', 'jbs' => '146'),
			array('id' => '47', 'book_name' => '2 Corinthians', 'published' => '1', 'jbs' => '147'),
			array('id' => '48', 'book_name' => 'Galatians', 'published' => '1', 'jbs' => '148'),
			array('id' => '49', 'book_name' => 'Ephesians', 'published' => '1', 'jbs' => '149'),
			array('id' => '50', 'book_name' => 'Philippians', 'published' => '1', 'jbs' => '150'),
			array('id' => '51', 'book_name' => 'Colossians', 'published' => '1', 'jbs' => '151'),
			array('id' => '52', 'book_name' => '1 Thessalonians', 'published' => '1', 'jbs' => '152'),
			array('id' => '53', 'book_name' => '2 Thessalonians', 'published' => '1', 'jbs' => '153'),
			array('id' => '54', 'book_name' => '1 Timothy', 'published' => '1', 'jbs' => '154'),
			array('id' => '55', 'book_name' => '2 Timothy', 'published' => '1', 'jbs' => '155'),
			array('id' => '56', 'book_name' => 'Titus', 'published' => '1', 'jbs' => '156'),
			array('id' => '57', 'book_name' => 'Philemon', 'published' => '1', 'jbs' => '157'),
			array('id' => '58', 'book_name' => 'Hebrews', 'published' => '1', 'jbs' => '158'),
			array('id' => '59', 'book_name' => 'James', 'published' => '1', 'jbs' => '159'),
			array('id' => '60', 'book_name' => '1 Peter', 'published' => '1', 'jbs' => '160'),
			array('id' => '61', 'book_name' => '2 Peter', 'published' => '1', 'jbs' => '161'),
			array('id' => '62', 'book_name' => '1 John', 'published' => '1', 'jbs' => '162'),
			array('id' => '63', 'book_name' => '2 John', 'published' => '1', 'jbs' => '163'),
			array('id' => '64', 'book_name' => '3 John', 'published' => '1', 'jbs' => '164'),
			array('id' => '65', 'book_name' => 'Jude', 'published' => '1', 'jbs' => '165'),
			array('id' => '66', 'book_name' => 'Revelation', 'published' => '1', 'jbs' => '166')
		);

		return $books;
	}

	/**
	 * Insert Media into BibleStudy
	 *
	 * @param   object  $pi     ?
	 * @param   string  $type   Type of Media
	 * @param   int     $newid  New ID
	 * @param   int     $oldid  Old ID
	 *
	 * @return boolean
	 *
	 * @since 9.0.0
	 *
	 * @FIXME look like the $pod is missing.
	 */
	public function insertMedia($pi, $type, $newid, $oldid)
	{
		$db          = JFactory::getDbo();
		$podcast_id  = '-1';
		$study_id    = $newid;
		$media_image = '';
		$path        = '';
		$filename    = '';
		$size        = '';
		$mime_type   = '';
		$podcast_id  = '';
		$filesize    = '';
		$mediacode   = '';
		$link_type   = '';
		$player      = '';
		$pod         = array();
		$query       = $db->getQuery(true);
		$query->select('*')->from('#__pipodcast');
		$db->setQuery($query);
		$podcasts = $db->loadObjectList();

		if ($type == 'audio')
		{
			$player      = $pi->audio_type;
			$media_image = '1';
			$mime_type   = '1';
			$filesize    = $pi->audiofs;
			$player      = '1';
			$filename    = $pi->audio_link;

			if ($podcasts)
			{
				foreach ($podcasts as $podcast)
				{
					if ($podcast->id == $oldid)
					{
						$oldpodid = $podcast->id;

						//if ($pod['oldid'] == $oldpodid)
						//{
							//$podcast_id = $pod['newid'];
						//}
					}
				}
			}
		}

		if ($type == 'video')
		{
			if ($podcasts)
			{
				foreach ($podcasts as $podcast)
				{
					if ($podcast->id == $oldid)
					{
						$oldpodid = $podcast->id;

						if ($pod['oldid'] == $oldpodid)
						{
							$podcast_id = $pod['newid'];
						}
					}
				}
			}

			$filesize = $pi->videofs;

			switch ($pi->video_type)
			{
				case 4:
					// Bliptv
					$mediacode   = '<embed src="http://blip.tv/play/' . $pi->video_link
						. '" type="application/x-shockwave-flash" width="500" height="500" wmode="transparent"'
						. 'allowscriptaccess="always" allowfullscreen="true" ></embed>';
					$mediacode   = $db->escape($mediacode);
					$player      = '8';
					$media_image = '5';
					$mime_type   = '15';
					break;

				case 7:
					// Flowplayer

							$query = $db->getQuery(true);
							$query->select('folder')->from('#__pifilepath')->where('id = ' . $pi->video_link);
							$db->setQuery($query);
							$object   = $db->loadObject();
							$path     = $object->folder;
							$filename = $path . $pi->video_link;



					$player      = '1';
					$media_image = '5';
					$mime_type   = '15';
					//$server      = '-1';
					break;

				case 1:
					// JWPlayer
                    $query = $db->getQuery(true);
                    $query->select('folder')->from('#__pifilepath')->where('id = ' . $pi->video_link);
                    $db->setQuery($query);
                    $object   = $db->loadObject();
                    $path     = $object->folder;
                    $filename = $path . $pi->video_link;

					$player      = '1';
					$media_image = '5';
					$mime_type   = '15';
					//$server      = '-1';
					break;

				case 2:
					// Vimeo
					$mediacode   = '<iframe src="http://player.vimeo.com/video/' . $pi->video_link . '" width="500" height="500" frameborder="0"></iframe> ';
					$mediacode   = $db->escape($mediacode);
					$player      = '8';
					$media_image = '5';
					$mime_type   = '15';
					//$path        = '-1';
					//$server      = '-1';
					break;

				case 3:
					// Youtube
					$mediacode   = '<iframe width="500" height="500" src="http://www.youtube.com/embed/' . $pi->video_link
						. '" frameborder="0" allowfullscreen></iframe>';
					$mediacode   = $db->escape($mediacode);
					$player      = '8';
					$media_image = '13';
					$mime_type   = '15';
					//$path        = '-1';
					//$server      = '-1';
					break;
			}
		}

		$createdate = $pi->date;

		if ($type == 'audio')
		{
			$link_type = $pi->audio_download;
		}

		if ($type == 'video')
		{
			$link_type = $pi->video_download;
		}

		if ($type == 'notes')
		{
			$filesize    = $pi->notesfs;
			//$download    = '1';
			$player      = '0';
			$media_image = '12';
			$mime_type   = '6';

            $query = $db->getQuery(true);
            $query->select('folder')->from('#__pifilepath')->where('id = ' . $pi->video_link);
            $db->setQuery($query);
            $object   = $db->loadObject();
            $path     = $object->folder;
            $filename = $path . $pi->video_link;
		}



		if ($type == 'slides')
		{
			//$download = '1';
			$filesize = $pi->slidesfs;
			$player   = '0';
			//$filename = $pi->slides_link;

            $query = $db->getQuery(true);
            $query->select('folder')->from('#__pifilepath')->where('id = ' . $pi->slides_link);
            $db->setQuery($query);
            $object   = $db->loadObject();
            $path     = $object->folder;
            $filename = $path . $pi->slides_link;
		}
        $hits      = $pi->hits;
        $downloads = $pi->downloads;
        $published = $pi->published;
        $params    = '{"mime_type":'.$mime_type.'","media_image":"'.$media_image.'","playerwidth":"","playerheight":"","itempopuptitle":"","itempopupfooter":"","popupmargin":"50","filename":"'.$filename.'","size":"'.$filesize.'","mediacode:""'.$mediacode.'"}';
        $params    = $db->escape($params);
        //$popup     = '1';

		$mediafiles              = new stdClass;
		$mediafiles->id          = null;
		$mediafiles->published   = $published;
		$mediafiles->study_id    = $newid;
		//$mediafiles->path        = $path;
		//$mediafiles->filename    = $filename;
		//$mediafiles->size        = $filesize;
		//$mediafiles->mime_type   = $mime_type;
		$mediafiles->podcast_id  = $podcast_id;
		//$mediafiles->mediacode   = $mediacode;
		$mediafiles->createdate  = $createdate;
		//$mediafiles->link_type   = $link_type;
		$mediafiles->hits        = $hits;
		$mediafiles->downloads = $downloads;
		$mediafiles->params      = $params;
		//$mediafiles->player      = $player;
		//$mediafiles->popup       = 1;

		//$mediafiles->media_image = $media_image;
		$mediafiles->access = $pi->access;
		$servers = $this->serversids;
		foreach ($servers as $server)
        {
            if ($server['oldid'] == $pi->audio_folder){$mediafiles->server_id = $server->newid;}
            if ($server['oldid'] == $pi->video_folder){$mediafiles->server_id = $server->newid;}
            else {$mediafiles->server_id = 1;}
        }

		//xdebug_var_dump($servers);


		if (!$db->insertObject('#__bsms_mediafiles', $mediafiles, 'id'))
		{
			return false;
		}

		return true;
	}

	/**
	 * Insert Comments
	 *
	 * @param   int  $oldid  ?
	 * @param   int  $newid  ?
	 *
	 * @return boolean
	 *
	 * @since 9.0.0
	 */
	private function insertComments($oldid, $newid)
	{
		if (!$this->picomments)
		{
			return false;
		}

		$db = JFactory::getDbo();

		foreach ($this->picomments as $pi)
		{
			if ($pi->id == $oldid)
			{
				$comments               = new stdClass;
				$comments->id           = null;
				$comments->published    = $pi->published;
				$comments->study_id     = $newid;
				$comments->user_id      = $pi->user_id;
				$comments->full_name    = $pi->full_name;
				$comments->comment_date = $pi->comment_date;
				$comments->user_email   = $pi->email;
				$comments->comment_text = $db->escape($pi->comment_text);

				if (!$db->insertObject('#__bsms_comments', $comments, 'id'))
				{
					$this->cnoadd++;
				}
				else
				{
					$this->cadd++;
				}
			}
		}

		return true;
	}
}
