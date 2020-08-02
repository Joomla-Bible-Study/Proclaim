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
	 * Make Podcasts
	 *
	 * @return boolean|string
	 *
	 * @since 8.0.0
	 * @throws Exception
	 */
	public function makePodcasts()
	{
		$msg          = array();
		$db           = JFactory::getDbo();
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
		$podids       = $db->loadObjectList();
		$custom       = new JBSMCustom;
		$JBSMlisting  = new JBSMListing;
		$title        = null;

		// Now iterate through the podcasts, and pick up the mediafiles
		if ($podids)
		{
			foreach ($podids AS $podinfo)
			{
				// Work Around all language
				if ($podinfo->language == '*')
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
					$description = $this->escapeHTML($podinfo->description);
					$detailstemplateid = $podinfo->detailstemplateid;
					$podcastimage      = $this->jimage($podinfo->image);

					if (!$detailstemplateid)
					{
						$detailstemplateid = 1;
					}

					if ((int) $podcastimage[0] < "144")
					{
						$podcastimage[0] = 144;
						$podcastimage[1] = (int) $podcastimage[1] - ((int) $podcastimage[0] - 144);
					}

					$detailstemplateid = '&amp;t=' . $detailstemplateid;
					$podhead           = '<?xml version="1.0" encoding="utf-8"?>
                <rss xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/"
                 xmlns:atom="http://www.w3.org/2005/Atom" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" version="2.0">
                <channel>
                	<title>' . $this->escapeHTML($podinfo->title) . '</title>
                	<link>http://' . $podinfo->website . '</link>
                	<description>' . $description . '</description>
                	<itunes:summary>' . strip_tags($podinfo->description) . '</itunes:summary>
                	<itunes:subtitle>' . $this->escapeHTML($podinfo->title) . '</itunes:subtitle>
                	<itunes:author>' . $this->escapeHTML($podinfo->editor_name) . '</itunes:author>
                	<image>
                		<link>http://' . $podinfo->website . '</link>
                		<url>http://' . $podinfo->website . '/' . $podinfo->image . '</url>
                		<title>' . $this->escapeHTML($podinfo->title) . '</title>
                	</image>
                	<itunes:image href="http://' . $podinfo->website . '/' . $podinfo->podcastimage . '" />
                	<category>Religion &amp; Spirituality</category>
                	<itunes:category text="Religion &amp; Spirituality">
                		<itunes:category text="Christianity" />
                	</itunes:category>
                	<language>' . $podlanguage . '</language>
                	<copyright>' . $year . ' All rights reserved.</copyright>
                	<pubDate>' . $date . '</pubDate>
                	<lastBuildDate>' . $date . '</lastBuildDate>
                	<generator>Proclaim</generator>
                	<managingEditor>' . $podinfo->editor_email . ' (' . $this->escapeHTML($podinfo->editor_name) . ')</managingEditor>
                	<webMaster>' . $podinfo->editor_email . ' (' . $this->escapeHTML($podinfo->editor_name) . ')</webMaster>
                	<itunes:owner>
                		<itunes:name>' . $this->escapeHTML($podinfo->editor_name) . '</itunes:name>
                		<itunes:email>' . $podinfo->editor_email . '</itunes:email>
                	</itunes:owner>
                	<itunes:explicit>no</itunes:explicit>
                        <itunes:keywords>' . $podinfo->podcastsearch . '</itunes:keywords>
                	<ttl>1</ttl>
                	<atom:link href="http://' . $podinfo->website . '/' . $podinfo->filename . '" rel="self" type="application/rss+xml" />';

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

					$episodes        = $this->getEpisodes($podinfo->id, $limit);
					$registry        = new Registry;
					$registry->loadString(JBSMParams::getAdmin()->params);
					$registry->merge(JBSMParams::getTemplateparams()->params);
					$params = $registry;
					$params->set('show_verses', '1');

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

								$element = $JBSMlisting->getFluidCustom(
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
                        		<title>' . $title . '</title>';
						$file = str_replace(' ', "%20", $episode->params->get('filename'));
						$path = JBSMHelper::MediaBuildUrl($episode->srparams->get('path'), $file, $params, false, false, true);

						/*
						 * Default is to episode
						 * 1 = Direct Link.
						 * 2 = Popup Player Window with default player as internal.
						 */
						if ($podinfo->linktype == '1')
						{
							$episodedetailtemp .= '<link>http://' . $path . '</link>';
						}
						elseif ($podinfo->linktype == '2')
						{
							$episodedetailtemp .= '<link>http://' . $podinfo->website . '/index.php?option=com_biblestudy&amp;view=popup&amp;player=1&amp;id=' .
								$episode->sid . $detailstemplateid . '</link>';
						}
						else
						{
							$episodedetailtemp .= '<link>http://' . $podinfo->website . '/index.php?option=com_biblestudy&amp;view=sermon&amp;id='
								. $episode->sid . $detailstemplateid . '</link>';
						}

						// Make a duration build from Params of media.
						$duration = '';

						if ($episode->params->get('media_hours', '00') !== '00' ||
                            $episode->params->get('media_minutes', '00') !== '00' ||
                            $episode->params->get('media_seconds', '00') !== '00')
                        {
                            $duration = $episode->params->get('media_hours', '00') . ':' .
                                $episode->params->get('media_minutes', '00') .
                                ':' . $episode->params->get('media_seconds', '00');
                        }

						$episodedetailtemp .= '<comments>http://' . $podinfo->website . '/index.php?option=com_biblestudy&amp;view=sermon&amp;id='
							. $episode->sid . $detailstemplateid . '</comments>
                        		<itunes:author>' . $this->escapeHTML($episode->teachername) . '</itunes:author>
                        		<dc:creator>' . $this->escapeHTML($episode->teachername) . '</dc:creator>
                        		<description>' . $description . '</description>
                        		<content:encoded>' . $description . '</content:encoded>
                        		<pubDate>' . $episodedate . '</pubDate>
                        		<itunes:subtitle>' . $this->escapeHTML($subtitle) . '</itunes:subtitle>
                        		<itunes:summary>' . $description . '</itunes:summary>
                        		<itunes:keywords>' . $podinfo->podcastsearch . '</itunes:keywords>
                        		<itunes:duration>' . $duration . '</itunes:duration>';

						// Here is where we test to see if the link should be an article or docMan link, otherwise it is a mediafile
						if ($episode->params->get('article_id') > 1)
						{
							$episodedetailtemp .=
								'
								<enclosure url="http://' . $episode->srparams->get('path') .
								'/index.php?option=com_content&amp;view=article&amp;id=' .
								$episode->params->get('article_id') . '" length="' . $episode->params->get('size', '100') . '" type="' .
								$episode->params->get('mimetype', 'application/octet-stream') . '" />
                        			<guid>http://' . $episode->srparams->get('path') .
								'/index.php?option=com_content&amp;view=article&amp;id=' .
								$episode->params->get('article_id') . '</guid>';
						}

						if ($episode->params->get('docMan_id') > 1)
						{
							$episodedetailtemp .=
								'
								<enclosure url="http://' . $episode->srparams->get('path') .
								'/index.php?option=com_docman&amp;task=doc_download&amp;gid=' .
								$episode->params->get('docMan_id') . '" length="' . $episode->params->get('size') . '" type="' .
								$episode->params->get('mimetype') . '" />
                        			<guid>http://' . $episode->srparams->get('path') .
								'/index.php?option=com_docman&amp;task=doc_download&amp;gid=' .
								$episode->params->get('docMan_id') . '</guid>';
						}
						else
						{
							$episodedetailtemp .=
								'
								<enclosure url="http://' . $path .
								'" length="' . $episode->params->get('size', '100') . '" type="'
								. $episode->params->get('mimetype', 'audio/mpeg3') . '" />
                        			<guid>http://' . $path . '</guid>';
						}

						$episodedetailtemp .= '
                        		<itunes:explicit>no</itunes:explicit>
                        	       </item>';
						$episodedetail = $episodedetail . $episodedetailtemp;
					}

					// End of foreach for episode details
					$podfoot     = '
                        </channel>
                        </rss>';
					$input       = new JInput;
					$client      = JApplicationHelper::getClientInfo($input->get('client', '0', 'int'));
					$file_path = $client->path . '/' . $podinfo->filename;
					$filecontent = $podhead . $episodedetail . $podfoot;
					$filewritten = $this->writeFile($file_path, $filecontent);

					$file = JUri::root() . '/' . $podinfo->filename;

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
	 * @param   string  $html  HTML string to make safe
	 *
	 * @return mixed|string
	 *
	 * @since 9.0.0
	 */
	protected function escapeHTML($html)
	{
		$string = str_replace(' & ', " and ", $html);
		$string = trim(html_entity_decode($string));

		if (!empty($string))
		{
			$string = '<![CDATA[' . $string . ']]>';
		}
		else
		{
			$string = "";
		}

		return $string;
	}

	/**
	 * JImage
	 *
	 * @param   string  $path  Path of Image
	 *
	 * @return array|bool
	 *
	 * @since 9.0.0
	 */
	public function jimage($path)
	{
		if (!$path)
		{
			return false;
		}

		$return = @getimagesize(JUri::root() . $path);

		return $return;
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
	public function getEpisodes($id, $limit)
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
			->where('mf.podcast_id LIKE ' . $db->q('%' . $id . '%'))->where('mf.published = ' . 1)->order('createdate desc');

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
	 * @since 7.0.0
	 * @throws Exception
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
}
