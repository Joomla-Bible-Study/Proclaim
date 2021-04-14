<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

defined('_JEXEC') or die;
jimport('joomla.html.parameter');

use Joomla\Registry\Registry;

/* Put in do to this file is used in a plugin. */

// Always load JBSM API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_biblestudy/api.php';

if (file_exists($api))
{
	require_once $api;
}

/**
 * BibleStudy Podcast Class
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class JBSMPodcast
{
	private $templateid = 0;

	private $template = null;
	/**
	 * @var string
	 * @since version
	 */
	private $filename;

	/**
	 * Make Podcasts
	 *
	 * @return boolean|string
	 *
	 * @throws Exception
	 * @since 8.0.0
	 */
	public function makePodcasts()
	{
		$msg = array();
		$db  = JFactory::getDbo();
		jimport('joomla.utilities.date');
		$year = '(' . date('Y') . ')';
		$date = date('r');

		// Get english language file as fallback
		$language = JFactory::getLanguage();
		$language->load('com_biblestudy', BIBLESTUDY_PATH_ADMIN, 'en-GB', true);

		// First get all of the podcast that are published
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__bsms_podcast')
			->where('#__bsms_podcast.published = ' . 1);
		$db->setQuery($query);
		$podids      = $db->loadObjectList();
		$custom      = new JBSMCustom;
		$JBSMlisting = new JBSMListing;
		$title       = null;

		// Now iterate through the podcasts, and pick up the mediafiles
		if ($podids)
		{
			foreach ($podids as $podinfo)
			{
				// Work Around all language
				if ($podinfo->language === '*')
				{
					$podlanguage = JFactory::getConfig()->get('language');
				}
				else
				{
					$podlanguage = $podinfo->language;
				}

				// Load language file
				$language->load('com_biblestudy', BIBLESTUDY_PATH_ADMIN, $podlanguage, true);

				// Check to see if there is a media file associated - if not, don't continue
				$query = $db->getQuery(true);
				$query->select('id')->from('#__bsms_mediafiles')->where('podcast_id LIKE  ' . $db->q('%' . $podinfo->id . '%'))->where('published = ' . 1);
				$db->setQuery($query);
				$checkresult = $db->loadObjectList();

				if ($checkresult)
				{

					// Now let's get the podcast episodes
					$limit = $podinfo->podcastlimit;

					if ($limit > 0)
					{
						$limit = 'LIMIT ' . $limit;
					}
					else
					{
						$limit = '';
					}

					$episodes = $this->getEpisodes((int) $podinfo->id, $limit);
					$registry = new Registry;
					$registry->loadString(JBSMParams::getAdmin()->params);
					$registry->merge(JBSMParams::getTemplateparams()->params);
					$params = $registry;
					$params->set('show_verses', '1');
					$protocol = $params->get('protocol', 'http://');

					$description       = $this->escapeHTML($podinfo->description);
					$detailstemplateid = $podinfo->detailstemplateid;

					if (!$detailstemplateid)
					{
						$detailstemplateid = 1;
					}

					if (empty($podinfo->podcastlink))
					{
						$podinfo->podcastlink = $podinfo->website;
					}

					$detailstemplateid = '&amp;t=' . $detailstemplateid;
					$podhead           = '<?xml version="1.0" encoding="utf-8"?>
                <rss xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/"
                 xmlns:atom="http://www.w3.org/2005/Atom" version="2.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd">
                <channel>
                	<title>' . $this->escapeHTML($podinfo->title) . '</title>
                	<link>' . $protocol . $podinfo->podcastlink . '</link>
                	<language>' . $podlanguage . '</language>
                	<copyright>© ' . $year . ' All rights reserved.</copyright>
                	<itunes:subtitle>' . $this->escapeHTML($podinfo->title) . '</itunes:subtitle>
                	<itunes:author>' . $this->escapeHTML($podinfo->editor_name) . '</itunes:author>
                	<itunes:summary>' . $this->escapeHTML($podinfo->description) . '</itunes:summary>
                	<description>' . $this->escapeHTML($description) . '</description>
                	<itunes:owner>
                		<itunes:name>' . $this->escapeHTML($podinfo->editor_name) . '</itunes:name>
                		<itunes:email>' . $podinfo->editor_email . '</itunes:email>
                	</itunes:owner>
                	<itunes:image href="' . $protocol . $podinfo->website . '/' . $podinfo->podcastimage . '" />
                	<category>Religion &amp;amp; Spirituality</category>
                	<itunes:category text="Religion &amp; Spirituality">
                		<itunes:category text="Christianity" />
                	</itunes:category>
                	<itunes:explicit>no</itunes:explicit>
                	<pubDate>' . $date . '</pubDate>
                	<lastBuildDate>' . $date . '</lastBuildDate>
                	<generator>Proclaim</generator>
                	<managingEditor>' . $podinfo->editor_email . ' (' . $this->escapeHTML($podinfo->editor_name) . ')</managingEditor>
                	<webMaster>' . $podinfo->editor_email . ' (' . $this->escapeHTML($podinfo->editor_name) . ')</webMaster>

                	<itunes:explicit>no</itunes:explicit>
                        <itunes:keywords>' . $podinfo->podcastsearch . '</itunes:keywords>
                        <itunes:type>episodic</itunes:type>
                	<ttl>1</ttl>
                	<atom:link href="' . $protocol . $podinfo->website . '/' . $podinfo->filename . '" rel="self" type="application/rss+xml" />';


					if (!$episodes)
					{
						return false;
					}

					$episodedetail = '';

					foreach ($episodes as $episode)
					{
						$episodedate = date("r", strtotime($episode->createdate));

						$esv          = 0;
						$scripturerow = 1;
						$episode->id  = $episode->study_id;
						$scripture    = $JBSMlisting->getScripture($params, $episode, $esv, $scripturerow);
						$pod_title    = $podinfo->episodetitle;
						$pod_subtitle = $podinfo->episodesubtitle;

						if ($episode->params->get('size'))
						{
							$episode->size = $episode->params->get('size');
						}
						else
						{
							$episode->size = '30000000';
						}

						switch ($pod_title)
						{
							case 0:
								if ($scripture && $episode->studytitle)
								{
									$title = $scripture . ' - ' . $episode->studytitle;
								}
								elseif (!$scripture)
								{
									$title = $episode->studytitle;
								}
								elseif (!$episode->studytitle)
								{
									$title = $scripture;
								}

								break;
							case 1:
								$title = $episode->studytitle;
								break;
							case 2:
								$title = $scripture;
								break;
							case 3:
								if ($scripture && $episode->studytitle)
								{
									$title = $episode->studytitle . ' - ' . $scripture;
								}
								elseif (!$scripture)
								{
									$title = $episode->studytitle;
								}
								elseif (!$episode->studytitle)
								{
									$title = $scripture;
								}
								break;
							case 4:
								$title = $episodedate;

								if (!$episodedate)
								{
									$title = $scripture;
								}
								else
								{
									$title .= ' - ' . $scripture;
								}

								if ($episode->studytitle)
								{
									$title .= ' - ' . $episode->studytitle;
								}
								break;
							case 5:
								if ($this->templateid !== $detailstemplateid || is_null($this->template))
								{
									$this->template   = JBSMParams::getTemplateparams($detailstemplateid);
									$this->templateid = $detailstemplateid;
								}

								$element = $JBSMlisting->getFluidCustom(
									$podinfo->custom,
									$episode,
									$params,
									$this->template,
									$rowid = '24'
								);

								$title = $element;
								break;
							case 6:
								$query = $db->getQuery('true');
								$query->select('*');
								$query->from('#__bsms_books');
								$query->where('booknumber = ' . $episode->booknumber);
								$db->setQuery($query);
								$book     = $db->loadObject();
								$bookname = JText::_($book->bookname);
								$title    = $bookname . ' ' . $episode->chapter_begin;
								break;
						}

						$subtitle = null;

						switch ($pod_subtitle)
						{
							case 0:
								$subtitle = $episode->teachername;
								break;
							case 1:
								if ($scripture && $episode->teachername)
								{
									$subtitle = $scripture . ' - ' . $episode->teachername;
								}
								elseif (!$scripture)
								{
									$subtitle = $episode->teachername;
								}
								elseif (!$episode->teachername)
								{
									$subtitle = $scripture;
								}
								break;
							case 2:
								$subtitle = $scripture;
								break;
							case 3:
								$subtitle = $episode->studytitle;
								break;
							case 4:
								$subtitle = $episodedate;

								if (!$episodedate)
								{
									$subtitle = $scripture;
								}
								else
								{
									$subtitle .= ' - ' . $scripture;
								}
								break;
							case 5:
								$subtitle = $scripture . ' - ' . $episode->studytitle;
								break;
							case 6:
								$query = $db->getQuery('true');
								$query->select('*');
								$query->from('#__bsms_books');
								$query->where('booknumber = ' . $episode->booknumber);
								$db->setQuery($query);
								$book     = $db->loadObject();
								$bookname = JText::_($book->bookname);
								$subtitle = $bookname . ' ' . $episode->chapter_begin;
								break;
							case 7:
								if ($this->templateid !== $detailstemplateid || is_null($this->template))
								{
									$this->template   = JBSMParams::getTemplateparams($detailstemplateid);
									$this->templateid = $detailstemplateid;
								}

								$element  = $JBSMlisting->getFluidCustom(
									$podinfo->custom,
									$episode,
									$params,
									$this->template,
									$rowid = '24'

								);
								$subtitle = $element;
								break;
						}

						$title       = $this->escapeHTML($title);
						$description = $this->escapeHTML($episode->studyintro);

						$episodedetailtemp = '
                        	   <item>
                        	   <itunes:episodeType>full</itunes:episodeType>
                        		<title>' . $title . '</title>';
						$file              = str_replace(' ', "%20", $episode->params->get('filename'));
						$path              = JBSMHelper::MediaBuildUrl($episode->srparams->get('path'), $file, $params, false, false, true);

						/*
						 * Default is to episode
						 * 1 = Direct Link.
						 * 2 = Popup Player Window with default player as internal.
						 */
						if ($podinfo->linktype == '1')
						{
							$episodedetailtemp .= '<link>' . $protocol . $path . '</link>';
						}
						elseif ($podinfo->linktype == '2')
						{
							$episodedetailtemp .= '<link>' . $protocol . $podinfo->website . '/index.php?option=com_biblestudy&amp;view=popup&amp;player=1&amp;id=' .
								$episode->sid . $detailstemplateid . '</link>';
						}
						else
						{
							$episodedetailtemp .= '<link>' . $protocol . $podinfo->website . '/index.php?option=com_biblestudy&amp;view=sermon&amp;id='
								. $episode->sid . $detailstemplateid . '</link>';
						}

						// Make a duration build from Params of media.
						$prefix  = JUri::root();
						$FullUrl = $protocol . $path;

						if (strpos($FullUrl, $prefix) === 0)
						{
							$this->filename = substr($FullUrl, strlen($prefix));
							$this->filename = JPATH_SITE . '/' . $this->filename;
							$duration       = $this->formatTime($this->getDuration());
						}
						else
						{
							$duration = $episode->params->get('media_hours', '00') . ':' .
								$episode->params->get('media_minutes', '00') .
								':' . $episode->params->get('media_seconds', '00');
						}

						$episodedetailtemp .= '<comments>' . $protocol . $podinfo->website . '/index.php?option=com_biblestudy&amp;view=sermon&amp;id='
							. $episode->sid . $detailstemplateid . '</comments>
                        		<itunes:author>' . $this->escapeHTML($episode->teachername) . '</itunes:author>
                        		<dc:creator>' . $this->escapeHTML($episode->teachername) . '</dc:creator>
                        		<description>
                        		<content:encoded>' . $this->escapeHTML($description) . '</content:encoded>
                        		</description>
                        		<pubDate>' . $episodedate . '</pubDate>
                        		<itunes:subtitle>' . $this->escapeHTML($subtitle) . '</itunes:subtitle>
                        		<itunes:summary>' . $this->escapeHTML($description) . '</itunes:summary>
                        		<itunes:keywords>' . $podinfo->podcastsearch . '</itunes:keywords>
                        		<itunes:duration>' . $duration . '</itunes:duration>';

						// Here is where we test to see if the link should be an article or docMan link, otherwise it is a mediafile
						if ($episode->params->get('article_id') > 1)
						{
							$episodedetailtemp .=
								'
								<enclosure url="' . $protocol . $episode->srparams->get('path') .
								'/index.php?option=com_content&amp;view=article&amp;id=' .
								$episode->params->get('article_id') . '" length="' . $episode->params->get('size', '100') . '" type="' .
								$episode->params->get('mime_type', 'application/octet-stream') . '" />
                        			<guid>' . $protocol . $episode->srparams->get('path') .
								'/index.php?option=com_content&amp;view=article&amp;id=' .
								$episode->params->get('article_id') . '</guid>';
						}

						if ($episode->params->get('docMan_id') > 1)
						{
							$episodedetailtemp .=
								'
								<enclosure url="' . $protocol . $episode->srparams->get('path') .
								'/index.php?option=com_docman&amp;task=doc_download&amp;gid=' .
								$episode->params->get('docMan_id') . '" length="' . $episode->params->get('size') . '" type="' .
								$episode->params->get('mime_type') . '" />
                        			<guid>' . $protocol . $episode->srparams->get('path') .
								'/index.php?option=com_docman&amp;task=doc_download&amp;gid=' .
								$episode->params->get('docMan_id') . '</guid>';
						}
						else
						{
							$episodedetailtemp .=
								'
								<enclosure url="' . $protocol . $path .
								'" length="' . $episode->params->get('size', '100') . '" type="'
								. $episode->params->get('mime_type', 'audio/mpeg3') . '" />
                        			<guid>' . $protocol . $path . '</guid>';
						}

						$episodedetailtemp .= '
                        		<itunes:explicit>no</itunes:explicit>
                        	       </item>';
						$episodedetail     .= $episodedetailtemp;
					}

					// End of foreach for episode details
					$podfoot     = '
                        </channel>
                        </rss>';
					$input       = new JInput;
					$client      = JApplicationHelper::getClientInfo($input->get('client', '0', 'int'));
					$file_path   = $client->path . '/' . $podinfo->filename;
					$filecontent = $podhead . $episodedetail . $podfoot;
					$filewritten = $this->writeFile($file_path, $filecontent);

					$file = JUri::root() . $podinfo->filename;

					if (!$filewritten)
					{
						$msg[] = $file . ' - ' . JText::_('JBS_PDC_XML_FILES_ERROR');
					}
					else
					{
						$msg[] = $file . ' - ' . JText::_('JBS_PDC_XML_FILES_WRITTEN');
					}
				} // End if $checkresult if positive
				else
				{
					$msg[] = JText::_('JBS_CMN_NO_MEDIA_FILES');
				}
			}
			// End foreach podids as podinfo
		}

		$message = '';

		foreach ($msg as $m)
		{
			$message .= $m . '<br />';
		}

		if (!$message)
		{
			$message = 'No message';
		}

		return $message;
	}

	/**
	 * Escape Html to XML
	 *
	 * @param   null|string  $string  HTML string to make safe
	 *
	 * @return mixed|string
	 *
	 * @since 9.0.0
	 */
	protected function escapeHTML(?string $string)
	{

		if (empty($string))
		{
			return $string;
		}

		$string = mb_convert_encoding($string, "UTF-8", "HTML-ENTITIES");
		$string = strip_tags($string);
		$string = htmlspecialchars($string, ENT_XML1 | ENT_QUOTES, "UTF-8");

		return $string;
	}

	/**
	 * Get Episodes
	 *
	 * @param   int     $id     Id for Episode
	 * @param   string  $limit  Limit of records
	 *
	 * @return array
	 *
	 * @since 8.0.0
	 */
	public function getEpisodes(int $id, string $limit)
	{
		preg_match_all('!\d+!', $limit, $set_limit);
		$set_limit = implode(' ', $set_limit[0]);

		// Here's where we look at each mediafile to see if they are connected to this podcast
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('p.id AS pid, p.podcastlimit,'
			. ' mf.id AS mfid, mf.study_id, mf.server_id, mf.podcast_id,'
			. ' mf.published AS mfpub, mf.createdate, mf.params,'
			. ' s.id AS sid, s.studydate, s.teacher_id, s.booknumber, s.chapter_begin, s.verse_begin,'
			. ' s.chapter_end, s.verse_end, s.studytitle, s.studyintro, s.published AS spub,'
			. ' se.series_text,'
			. ' sr.id AS srid, sr.params as srparams,'
			. ' t.id AS tid, t.teachername,'
			. ' b.id AS bid, b.booknumber AS bnumber, b.bookname')
			->from('#__bsms_mediafiles AS mf')
			->leftJoin('#__bsms_studies AS s ON (s.id = mf.study_id)')
			->leftJoin('#__bsms_series AS se ON (se.id = s.series_id)')
			->leftJoin('#__bsms_servers AS sr ON (sr.id = mf.server_id)')
			->leftJoin('#__bsms_books AS b ON (b.booknumber = s.booknumber)')
			->leftJoin('#__bsms_teachers AS t ON (t.id = s.teacher_id)')
			->leftJoin('#__bsms_podcast AS p ON (p.id = mf.podcast_id)')
			->where('mf.podcast_id LIKE ' . $db->q('%' . $id . '%'))
			->where('mf.published = ' . 1)
			->order($db->q('createdate desc'));

		$db->setQuery($query, 0, $set_limit);
		$episodes = $db->loadObjectList();

		// Go through each and remove the -1 strings and retest
		$epis = array();

		foreach ($episodes as $e)
		{
			$registry = new Registry;
			$registry->loadString($e->params);
			$e->params = $registry;

			$registry = new Registry;
			$registry->loadString($e->srparams);
			$e->srparams = $registry;

			$e->podcast_id = str_replace('-1', '', $e->podcast_id);

			if (substr_count($e->podcast_id, $id))
			{
				$epis[] = $e;
			}
		}

		return $epis;
	}

	/**
	 * Write the File
	 *
	 * @param   string  $file         File Name
	 * @param   string  $filecontent  File Content
	 *
	 * @return boolean|string
	 *
	 * @throws Exception
	 * @since 7.0.0
	 */
	public function writeFile($file, $filecontent)
	{
		// Set FTP credentials, if given
		$files[]        = $file;
		$podcastresults = '';
		jimport('joomla.client.helper');
		jimport('joomla.filesystem.file');
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');

		// Try to make the template file writable
		if (JFile::exists($file) && !$ftp['enabled'] && !JPath::setPermissions($file, '0755'))
		{
			JFactory::getApplication()->enqueueMessage('Could not make the file writable', 'notice');
		}

		$fileit = JFile::write($file, $filecontent);

		if ($fileit)
		{
			return true;
		}

		if (!$fileit)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage('Could not make the file unwritable', 'notice');

			return false;
		}
		// Try to make the template file unwriteable
		if (!$ftp['enabled'] && !JPath::setPermissions($file, '0555'))
		{
			JFactory::getApplication()
				->enqueueMessage('Could not make the file unwritable', 'notice');

			return false;
		}

		return $podcastresults;
	}

	/**
	 * @param $duration
	 *
	 * @return string
	 *
	 * @since version
	 */
	public function formatTime($duration) //as hh:mm:ss
	{
		$hours   = floor($duration / 3600);
		$minutes = floor(($duration - ($hours * 3600)) / 60);
		$seconds = $duration - ($hours * 3600) - ($minutes * 60);

		return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
	}

	/**
	 * Read entire file, frame by frame... ie: Variable Bit Rate (VBR)
	 *
	 * @param   false  $use_cbr_estimate  True: Use CBR to Estimate, False: Will ignore,
	 *
	 * @return float|int
	 *
	 * @since 9.2.4
	 */
	public function getDuration($use_cbr_estimate = false)
	{
		$fd = fopen($this->filename, "rb");

		$duration = 0;
		$block    = fread($fd, 100);
		$offset   = $this->skipID3v2Tag($block);
		fseek($fd, $offset, SEEK_SET);
		while (!feof($fd))
		{
			$block = fread($fd, 10);
			if (strlen($block) < 10)
			{
				break;
			}
			//looking for 1111 1111 111 (frame synchronization bits)

			if ($block[0] === "\xff" && (ord($block[1]) & 0xe0))
			{
				$info = $this->parseFrameHeader(substr($block, 0, 4));
				if (empty($info['Framesize']))
				{
					return $duration;
				} //some corrupt mp3 files
				fseek($fd, $info['Framesize'] - 10, SEEK_CUR);
				$duration += ($info['Samples'] / $info['Sampling Rate']);
			}
			else if (substr($block, 0, 3) === 'TAG')
			{
				fseek($fd, 128 - 10, SEEK_CUR);//skip over id3v1 tag size
			}
			else
			{
				fseek($fd, -9, SEEK_CUR);
			}

			if ($use_cbr_estimate && !empty($info))
			{
				return $this->estimateDuration($info['Bitrate'], $offset);
			}
		}

		return $duration;
	}

	/**
	 * Estimate Duration
	 *
	 * @param   integer  $bitrate  Bitrate
	 * @param   integer  $offset   Offset
	 *
	 * @return float
	 *
	 * @since 9.2.4
	 */
	public function estimateDuration($bitrate, $offset)
	{
		$kbps     = ($bitrate * 1000) / 8;
		$datasize = filesize($this->filename) - $offset;

		return round($datasize / $kbps);
	}

	/**
	 * Skip ID3v2 Tags
	 *
	 * @param   array  $block  ID3 info block
	 *
	 * @return float|int
	 *
	 * @since 9.2.4
	 */
	public function skipID3v2Tag(&$block)
	{
		if (substr($block, 0, 3) === "ID3")
		{
			$id3v2_flags         = ord($block[5]);
			$flag_footer_present = ($id3v2_flags & 0x10) ? 1 : 0;
			$z0                  = ord($block[6]);
			$z1                  = ord($block[7]);
			$z2                  = ord($block[8]);
			$z3                  = ord($block[9]);
			if ((($z0 & 0x80) === 0) && (($z1 & 0x80) === 0) && (($z2 & 0x80) === 0) && (($z3 & 0x80) === 0))
			{
				$header_size = 10;
				$tag_size    = (($z0 & 0x7f) * 2097152) + (($z1 & 0x7f) * 16384) + (($z2 & 0x7f) * 128) + ($z3 & 0x7f);
				$footer_size = $flag_footer_present ? 10 : 0;

				return $header_size + $tag_size + $footer_size;//bytes to skip
			}
		}

		return 0;
	}

	/**
	 * @param   array  $fourbytes  array with four bytes
	 *
	 * @return array
	 *
	 * @since 9.2.4
	 */
	public function parseFrameHeader($fourbytes)
	{
		static $versions = array(
			0x0 => '2.5', 0x1 => 'x', 0x2 => '2', 0x3 => '1', // x=>'reserved'
		);
		static $layers = array(
			0x0 => 'x', 0x1 => '3', 0x2 => '2', 0x3 => '1', // x=>'reserved'
		);
		static $bitrates = array(
			'V1L1' => array(0, 32, 64, 96, 128, 160, 192, 224, 256, 288, 320, 352, 384, 416, 448),
			'V1L2' => array(0, 32, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320, 384),
			'V1L3' => array(0, 32, 40, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320),
			'V2L1' => array(0, 32, 48, 56, 64, 80, 96, 112, 128, 144, 160, 176, 192, 224, 256),
			'V2L2' => array(0, 8, 16, 24, 32, 40, 48, 56, 64, 80, 96, 112, 128, 144, 160),
			'V2L3' => array(0, 8, 16, 24, 32, 40, 48, 56, 64, 80, 96, 112, 128, 144, 160),
		);
		static $sample_rates = array(
			'1'   => array(44100, 48000, 32000),
			'2'   => array(22050, 24000, 16000),
			'2.5' => array(11025, 12000, 8000),
		);
		static $samples = array(
			1 => array(1 => 384, 2 => 1152, 3 => 1152,), //MPEGv1,     Layers 1,2,3
			2 => array(1 => 384, 2 => 1152, 3 => 576,), //MPEGv2/2.5, Layers 1,2,3
		);

		$b1 = ord($fourbytes[1]);
		$b2 = ord($fourbytes[2]);
		$b3 = ord($fourbytes[3]);

		$version_bits   = ($b1 & 0x18) >> 3;
		$version        = $versions[$version_bits];
		$simple_version = ($version === '2.5' ? 2 : $version);

		$layer_bits = ($b1 & 0x06) >> 1;
		$layer      = $layers[$layer_bits];

		$protection_bit = ($b1 & 0x01);
		$bitrate_key    = sprintf('V%dL%d', $simple_version, $layer);
		$bitrate_idx    = ($b2 & 0xf0) >> 4;
		$bitrate        = isset($bitrates[$bitrate_key][$bitrate_idx]) ? $bitrates[$bitrate_key][$bitrate_idx] : 0;

		$sample_rate_idx     = ($b2 & 0x0c) >> 2;//0xc => b1100
		$sample_rate         = isset($sample_rates[$version][$sample_rate_idx]) ? $sample_rates[$version][$sample_rate_idx] : 0;
		$padding_bit         = ($b2 & 0x02) >> 1;
		$private_bit         = ($b2 & 0x01);
		$channel_mode_bits   = ($b3 & 0xc0) >> 6;
		$mode_extension_bits = ($b3 & 0x30) >> 4;
		$copyright_bit       = ($b3 & 0x08) >> 3;
		$original_bit        = ($b3 & 0x04) >> 2;
		$emphasis            = ($b3 & 0x03);

		$info                  = array();
		$info['Version']       = $version;//MPEGVersion
		$info['Layer']         = $layer;
		$info['Bitrate']       = $bitrate;
		$info['Sampling Rate'] = $sample_rate;
		$info['Framesize']     = $this->framesize($layer, $bitrate, $sample_rate, $padding_bit);
		$info['Samples']       = $samples[$simple_version][$layer];

		return $info;
	}

	/**
	 * Frame size setup
	 *
	 * @param   string   $layer        Layer
	 * @param   integer  $bitrate      Bit Rate
	 * @param   integer  $sample_rate  Sample rate
	 * @param   integer  $padding_bit  Padding
	 *
	 * @return int
	 *
	 * @since 9.2.4
	 */
	public function framesize($layer, $bitrate, $sample_rate, $padding_bit)
	{
		if ($layer === 1)
		{
			return (int) (((12 * $bitrate * 1000 / $sample_rate) + $padding_bit) * 4);
		}

		//layer 2, 3
		return (int) (((144 * $bitrate * 1000) / $sample_rate) + $padding_bit);
	}
}
