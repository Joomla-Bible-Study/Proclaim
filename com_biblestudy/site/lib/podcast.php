<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */

defined('_JEXEC') or die;
jimport('joomla.html.parameter');

use Joomla\Registry\Registry;

/* Put in do to this file is used in a plugin. */
require_once JPATH_ADMINISTRATOR . '/components/com_biblestudy/lib/defines.php';

JLoader::register('JBSMParams', BIBLESTUDY_PATH_ADMIN_HELPERS . '/params.php');
JLoader::register('JBSMDbHelper', JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/dbhelper.php');
JLoader::register('JBSMCustom', JPATH_SITE . '/components/com_biblestudy/helpers/custom.php');
JLoader::register('JBSMListing', JPATH_SITE . '/components/com_biblestudy/lib/listing.php');

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
	 */
	public function makePodcasts()
	{
		$admin_params = JBSMParams::getAdmin();
		$msg          = array();
		$db           = JFactory::getDBO();

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
<<<<<<< HEAD:com_biblestudy/site/lib/podcast.php
					$podlanguage = JFactory::getConfig()->get('language');
				}
				else
				{
					$podlanguage = $podinfo->language;
				}

				// Load language file
				$language->load('com_biblestudy', BIBLESTUDY_PATH_ADMIN, $podlanguage, true);

=======
					$language = JFactory::getConfig()->get('language');
				}
				else
				{
					$language = $podinfo->language;
				}

>>>>>>> Joomla-Bible-Study/master:com_biblestudy/site/lib/biblestudy.podcast.class.php
				// Check to see if there is a media file associated - if not, don't continue
				$query = $db->getQuery(true);
				$query->select('id')->from('#__bsms_mediafiles')->where('podcast_id LIKE  ' . $db->q('%' . $podinfo->id . '%'))->where('published = ' . 1);
				$db->setQuery($query);
				$checkresult = $db->loadObjectList();

				if ($checkresult)
				{
<<<<<<< HEAD:com_biblestudy/site/lib/podcast.php
					$description = $this->escapeHTML($podinfo->description);
=======
					$description       = $this->escapeHTML($podinfo->description);
>>>>>>> Joomla-Bible-Study/master:com_biblestudy/site/lib/biblestudy.podcast.class.php
					$detailstemplateid = $podinfo->detailstemplateid;
					$podcastimage      = $this->jimage($podinfo->image);
					if (!$detailstemplateid)
					{
						$detailstemplateid = 1;
					}
					$detailstemplateid = '&amp;t=' . $detailstemplateid;
					$podhead           = '<?xml version="1.0" encoding="utf-8"?>
                <rss xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/"
                 xmlns:atom="http://www.w3.org/2005/Atom" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" version="2.0">
                <channel>
                	<title>' . $this->escapeHTML($podinfo->title) . '</title>
                	<link>http://' . $podinfo->website . '</link>
                	<description>' . $description . '</description>
                	<itunes:summary>' . $description . '</itunes:summary>
                	<itunes:subtitle>' . $this->escapeHTML($podinfo->title) . '</itunes:subtitle>
                	<itunes:author>' . $this->escapeHTML($podinfo->editor_name) . '</itunes:author>
                	<image>
                		<link>http://' . $podinfo->website . '</link>
                		<url>http://' . $podinfo->website . '/' . $podinfo->image . '</url>
                		<title>' . $this->escapeHTML($podinfo->title) . '</title>
<<<<<<< HEAD:com_biblestudy/site/lib/podcast.php
                		<height>' . $podcastimage[1] . '</height>
                		<width>' . $podcastimage[0] . '</width>
                	</image>
                	<itunes:image href="http://' . $podinfo->website . '/' . $podinfo->podcastimage . '" />
=======
                		<height>' . $podinfo->imageh . '</height>
                		<width>' . $podinfo->imagew . '</width>
                	</image>
                	<itunes:image href="http://' . $podinfo->website  . '/' . $podinfo->podcastimage . '" />
>>>>>>> Joomla-Bible-Study/master:com_biblestudy/site/lib/biblestudy.podcast.class.php
                	<category>Religion &amp; Spirituality</category>
                	<itunes:category text="Religion &amp; Spirituality">
                		<itunes:category text="Christianity" />
                	</itunes:category>
<<<<<<< HEAD:com_biblestudy/site/lib/podcast.php
                	<language>' . $podlanguage . '</language>
=======
                	<language>' . $language . '</language>
>>>>>>> Joomla-Bible-Study/master:com_biblestudy/site/lib/biblestudy.podcast.class.php
                	<copyright>' . $year . ' All rights reserved.</copyright>
                	<pubDate>' . $date . '</pubDate>
                	<lastBuildDate>' . $date . '</lastBuildDate>
                	<generator>Joomla Bible Study</generator>
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
						$hours       = $episode->media_hours;

						if (!$hours || $hours < 1)
						{
							$hours = '00';
						}
<<<<<<< HEAD:com_biblestudy/site/lib/podcast.php
						// If there is no length set, we default to 35 mints
=======
						// If there is no length set, we default to 35 minuets
>>>>>>> Joomla-Bible-Study/master:com_biblestudy/site/lib/biblestudy.podcast.class.php
						if (!$episode->media_minutes && !$episode->media_seconds)
						{
							$episode->media_minutes = 35;
							$episode->media_seconds = 00;
						}
						$esv          = 0;
						$scripturerow = 1;
						$episode->id  = $episode->study_id;
						$scripture    = $JBSMlisting->getScripture($params, $episode, $esv, $scripturerow);
						$pod_title    = $podinfo->episodetitle;
						$pod_subtitle = $podinfo->episodesubtitle;

						if ($episode->params->get('size'))
						{
<<<<<<< HEAD:com_biblestudy/site/lib/podcast.php
							$episode->size = $episode->params->get('size');
						}
						else
						{
=======
>>>>>>> Joomla-Bible-Study/master:com_biblestudy/site/lib/biblestudy.podcast.class.php
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
								$element = $custom->getCustom(
									$rowid = '24',
									$podinfo->custom,
									$episode,
									$params,
									$this->template
								);

								$title = $element->element;
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
								if ($scripture && $episode->studytitle)
								{
									$subtitle = $scripture . ' - ' . $episode->studytitle;
								}
								elseif (!$scripture)
								{
									$subtitle = $episode->studytitle;
								}
								elseif (!$episode->studytitle)
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

								if (!$episode->studytitle)
								{
									$subtitle = $scripture;
								}
								else
								{
									$subtitle .= ' - ' . $scripture;
								}
								break;
							case 7:
							case 5:
								if ($this->templateid !== $detailstemplateid || is_null($this->template))
								{
									$this->template   = JBSMParams::getTemplateparams($detailstemplateid);
									$this->templateid = $detailstemplateid;
								}
								$element = $custom->getCustom(
									$rowid = '24',
									$podinfo->episodesubtitle,
									$episode,
									$params,
									$this->template
								);

								$subtitle = $element->element;
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
						}
						$title       = $this->escapeHTML($title);
						$description = $this->escapeHTML($episode->studyintro);

						$episodedetailtemp = '
                        	   <item>
<<<<<<< HEAD:com_biblestudy/site/lib/podcast.php
                        		<title>' . $title . '</title>';
						/*
						 * Default is to episode
						 * 1 = Direct Link.
						 * 2 = Popup Player Window with default player as internal.
						 */
						if ($podinfo->linktype == '1')
						{
							$episodedetailtemp .= '<link>http://' . $episode->server_path . $episode->folderpath . str_replace(' ', "%20", $episode->filename) . '</link>';
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

						$episodedetailtemp .= '<comments>http://' . $podinfo->website . '/index.php?option=com_biblestudy&amp;view=sermon&amp;id='
=======
                        		<title>' . $title . '</title>
                        		<link>http://' . $podinfo->website . '/index.php?option=com_biblestudy&amp;view=sermon&amp;id='
							. $episode->sid . $detailstemplateid . '</link>
                        		<comments>http://' . $podinfo->website . '/index.php?option=com_biblestudy&amp;view=sermon&amp;id='
>>>>>>> Joomla-Bible-Study/master:com_biblestudy/site/lib/biblestudy.podcast.class.php
							. $episode->sid . $detailstemplateid . '</comments>
                        		<itunes:author>' . $this->escapeHTML($episode->teachername) . '</itunes:author>
                        		<dc:creator>' . $this->escapeHTML($episode->teachername) . '</dc:creator>
                        		<description>' . $description . '</description>
                        		<content:encoded>' . $description . '</content:encoded>
                        		<pubDate>' . $episodedate . '</pubDate>
                        		<itunes:subtitle>' . $this->escapeHTML($subtitle) . '</itunes:subtitle>
                        		<itunes:summary>' . $description . '</itunes:summary>
                        		<itunes:keywords>' . $podinfo->podcastsearch . '</itunes:keywords>
                        		<itunes:duration>' . $hours . ':' . sprintf(
								"%02d",
								$episode->media_minutes
							) . ':' . sprintf("%02d", $episode->media_seconds) . '</itunes:duration>';

						// Here is where we test to see if the link should be an article or docMan link, otherwise it is a mediafile
						if ($episode->params->get('article_id') > 1)
						{
<<<<<<< HEAD:com_biblestudy/site/lib/podcast.php
							$episodedetailtemp .=
								'<enclosure url="http://' . $episode->srparams->get('path') .
=======
							$episodedetailtemp .= '
								<enclosure url="http://' . $episode->server_path .
>>>>>>> Joomla-Bible-Study/master:com_biblestudy/site/lib/biblestudy.podcast.class.php
								'/index.php?option=com_content&amp;view=article&amp;id=' .
								$episode->params->get('article_id') . '" length="' . $episode->params->get('size') . '" type="' .
								$episode->params->get('mimetype') . '" />
                        			<guid>http://' . $episode->srparams->get('path') .
								'/index.php?option=com_content&amp;view=article&amp;id=' .
								$episode->params->get('article_id') . '</guid>';
						}
						if ($episode->params->get('docMan_id') > 1)
						{
<<<<<<< HEAD:com_biblestudy/site/lib/podcast.php
							$episodedetailtemp .=
								'<enclosure url="http://' . $episode->srparams->get('path') .
=======
							$episodedetailtemp .= '
							<enclosure url="http://' . $episode->server_path .
>>>>>>> Joomla-Bible-Study/master:com_biblestudy/site/lib/biblestudy.podcast.class.php
								'/index.php?option=com_docman&amp;task=doc_download&amp;gid=' .
								$episode->params->get('docMan_id') . '" length="' . $episode->params->get('size') . '" type="' .
								$episode->params->get('mimetype') . '" />
                        			<guid>http://' . $episode->srparams->get('path') .
								'/index.php?option=com_docman&amp;task=doc_download&amp;gid=' .
								$episode->params->get('docMan_id') . '</guid>';
						}
						else
						{
<<<<<<< HEAD:com_biblestudy/site/lib/podcast.php
							$episodedetailtemp .=
								'<enclosure url="http://' . $episode->srparams->get('path') . str_replace(
=======
							$episodedetailtemp .= '
							<enclosure url="http://' . $episode->server_path . $episode->folderpath . str_replace(
>>>>>>> Joomla-Bible-Study/master:com_biblestudy/site/lib/biblestudy.podcast.class.php
									' ',
									"%20",
									$episode->params->get('filename')
								) . '" length="' . $episode->params->get('size') . '" type="'
								. $episode->params->get('mimetype') . '" />
                        			<guid>http://' . str_replace(
									' ',
									"%20",
									$episode->params->get('filename')
								) . '</guid>';
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
	 */
	public function jimage($path)
	{
		if (!$path)
		{
			return false;
		}
		$return = @getimagesize(JURI::root() . $path);

		return $return;
	}

	/**
	 * Get Episodes
	 *
	 * @param   int     $id     Id for Episode
	 * @param   string  $limit  Limit of records
	 *
	 * @return object
	 */
	public function getEpisodes($id, $limit)
	{
		preg_match_all('!\d+!', $limit, $set_limit);
		$set_limit = implode(' ', $set_limit[0]);

		// Here's where we look at each mediafile to see if they are connected to this podcast
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('p.id AS pid, p.podcastlimit,'
<<<<<<< HEAD:com_biblestudy/site/lib/podcast.php
			. ' mf.id AS mfid, mf.study_id, mf.server_id, mf.podcast_id,'
		. ' mf.published AS mfpub, mf.createdate, mf.params,'
		. ' s.id AS sid, s.studydate, s.teacher_id, s.booknumber, s.chapter_begin, s.verse_begin,'
		. ' s.chapter_end, s.verse_end, s.studytitle, s.studyintro, s.published AS spub,'
		. ' s.media_hours, s.media_minutes, s.media_seconds,'
		. ' se.series_text,'
		. ' sr.id AS srid, sr.params as srparams,'
		. ' t.id AS tid, t.teachername,'
		. ' b.id AS bid, b.booknumber AS bnumber, b.bookname')
=======
			. ' mf.id AS mfid, mf.study_id, mf.server, mf.path, mf.filename, mf.size, mf.mime_type, mf.podcast_id,'
			. ' mf.published AS mfpub, mf.createdate, mf.params,'
			. ' mf.docMan_id, mf.article_id,'
			. ' s.id AS sid, s.studydate, s.teacher_id, s.booknumber, s.chapter_begin, s.verse_begin,'
			. ' s.chapter_end, s.verse_end, s.studytitle, s.studyintro, s.published AS spub,'
			. ' s.media_hours, s.media_minutes, s.media_seconds,'
			. ' se.series_text,'
			. ' sr.id AS srid, sr.server_path,'
			. ' f.id AS fid, f.folderpath,'
			. ' t.id AS tid, t.teachername,'
			. ' b.id AS bid, b.booknumber AS bnumber, b.bookname,'
			. ' mt.id AS mtid, mt.mimetype')
>>>>>>> Joomla-Bible-Study/master:com_biblestudy/site/lib/biblestudy.podcast.class.php
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
			$e->podcast_id = str_replace('-1','',$e->podcast_id);
			if (substr_count($e->podcast_id,$id)){$epis[]= $e;}
		}

		return $epis;

<<<<<<< HEAD:com_biblestudy/site/lib/podcast.php
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
=======
>>>>>>> Joomla-Bible-Study/master:com_biblestudy/site/lib/biblestudy.podcast.class.php
	}

	/**
	 * Write the File
	 *
	 * @param   string  $file         File Name
	 * @param   string  $filecontent  File Content
	 *
	 * @return boolean|string
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
<<<<<<< HEAD:com_biblestudy/site/lib/podcast.php
=======

	/**
	 * Method to get file size
	 *
	 * @param   string $url URL
	 *
	 * @return  boolean
	 */
	protected function getRemoteFileSize($url)
	{
		$parsed = parse_url($url);
		$host   = $parsed["host"];
		$fp     = null;

		if (function_exists('fsockopen'))
		{
			$fp = @fsockopen($host, 80, $errno, $errstr, 20);
		}
		if (!$fp)
		{
			return false;
		}
		else
		{
			@fputs($fp, "HEAD $url HTTP/1.1\r\n");
			@fputs($fp, "HOST: $host\r\n");
			@fputs($fp, "Connection: close\r\n\r\n");
			$headers = "";

			while (!@feof($fp))
			{
				$headers .= @fgets($fp, 128);
			}
		}
		@fclose($fp);
		$return      = false;
		$arr_headers = explode("\n", $headers);

		foreach ($arr_headers as $header)
		{
			$s = "Content-Length: ";

			if (substr(strtolower($header), 0, strlen($s)) == strtolower($s))
			{
				$return = trim(substr($header, strlen($s)));
				break;
			}
		}

		return $return;
	}

	/**
	 * Escape Html to XML
	 *
	 * @param $html
	 *
	 * @return mixed|string
	 */
	protected function escapeHTML($html)
	{
		$string = str_replace(' & ', " and ", $html);
		$string = trim(html_entity_decode($string));
		if(!empty($string))
		{
			$string = '<![CDATA[' . $string . ']]>';
		}
		else
		{
			$string = "";
		}

		return $string;
	}

>>>>>>> Joomla-Bible-Study/master:com_biblestudy/site/lib/biblestudy.podcast.class.php
}
