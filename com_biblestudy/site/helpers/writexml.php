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

/**
 * Write XML
 *
 * @return boolean
 *
 * @todo need to redo this right to MVC Standers. BCC TOM
 */
function writeXML()
{
	$return         = true;
	$podcastresults = array();
	$files          = array();
	$path1          = JPATH_SITE . '/components/com_biblestudy/helpers/';
	include_once $path1 . 'custom.php';
	$JBSMCustom = new JBSMCustom;
	include_once JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components/com_biblestudy/helpers/helper.php';
	$admin_params = JBSMParams::getAdmin();
	$config       = JFactory::getConfig();
	$lb_abspath   = JPATH_SITE;
	$lb_mailfrom  = $config->getValue('config.mailfrom');
	$lb_fromname  = $config->getValue('config.fromname');
	$lb_livesite  = JURI::root();

	$Body   = '<strong>Podcast Publishing Update confirmation.</strong><br><br> The following podcasts have been published:<br> ' . $lb_fromname;
	$params = JFactory::getApplication('site')->getParams();
	jimport('joomla.utilities.date');
	$year  = '(' . date('Y') . ')';
	$date  = date('r');
	$db    = JFactory::getDBO();
	$query = $db->getQuery(true);
	$query->select('id, title')->from('#__bsms_podcast')->where('#__bsms_podcast.published = ' . 1);
	$db->setQuery($query);
	$podid = $db->loadObjectList();

	if (count($podid))
	{
		$podcastresult = array();

		foreach ($podid as $podids2)
		{
			$Body = $Body . '<br> ' . $podids2->title;
		}
		foreach ($podid as $podids)
		{
			// Let's get the data from the podcast
			$query = $db->getQuery(true);
			$query->select('*')->from('#__bsms_podcast')->where('#__bsms_podcast.id = ' . $podids->id);
			$db->setQuery($query);
			$podinfo           = $db->loadObject();
			$description       = str_replace("&", "and", $podinfo->description);
			$detailstemplateid = $podinfo->detailstemplateid;

			if (!$detailstemplateid)
			{
				$detailstemplateid = 1;
			}
			$detailstemplateid = '&amp;t=' . $detailstemplateid;
			$podhead           = '<?xml version="1.0" encoding="utf-8"?>
<rss xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" version="2.0">
<channel>
	<title>' . $podinfo->title . '</title>
	<link>//' . $podinfo->website . '</link>
	<description>' . $description . '</description>
	<itunes:summary>' . $description . '</itunes:summary>
	<itunes:subtitle>' . $podinfo->title . '</itunes:subtitle>
	<image>
		<link>//' . $podinfo->website . '</link>
		<url>//' . $podinfo->image . '</url>
		<title>' . $podinfo->title . '</title>
		<height>' . $podinfo->imageh . '</height>
		<width>' . $podinfo->imagew . '</width>
	</image>
	<itunes:image href="//' . $podinfo->podcastimage . '" />
	<category>Religion &amp; Spirituality</category>
	<itunes:category text="Religion &amp; Spirituality">
		<itunes:category text="Christianity" />
	</itunes:category>
	<language>' . $podinfo->language . '</language>
	<copyright>' . $year . ' All rights reserved.</copyright>
	<pubDate>' . $date . '</pubDate>
	<lastBuildDate>' . $date . '</lastBuildDate>
	<generator>Joomla Bible Study</generator>
	<managingEditor>' . $podinfo->editor_email . ' (' . $podinfo->editor_name . ')</managingEditor>
	<webMaster>' . $podinfo->editor_email . ' (' . $podinfo->editor_name . ')</webMaster>
	<itunes:owner>
		<itunes:name>' . $podinfo->editor_name . '</itunes:name>
		<itunes:email>' . $podinfo->editor_email . '</itunes:email>
	</itunes:owner>
	<itunes:author>' . $podinfo->editor_name . '</itunes:author>
	<itunes:explicit>no</itunes:explicit>
	<ttl>1</ttl>
	<atom:link href="//' . $podinfo->website . '/' . $podinfo->filename . '" rel="self" type="application/rss+xml" />';

			// Now let's get the podcast episodes
			$limit = $podinfo->podcastlimit;

			if ($limit <= 0)
			{
				$limit = null;
			}

			// Here's where we look at each mediafile to see if they are connected to this podcast
			$query = $db->getQuery(true);
			$query->select('id, params, podcast_id, published')->from('#__bsms_mediafiles')->where('published = ' . 1);
			$db->setQuery($query);
			$results = $db->loadObjectList();
			$where   = array();

			foreach ($results as $result)
			{

				// Convert parameter fields to objects.
				$registry = new JRegistry;
				$registry->loadString($result->params);
				$params   = $registry;
				$podcasts = explode(',', $result->podcast_id);

				switch ($podcasts)
				{
					case is_array($podcasts) :
						foreach ($podcasts as $podcast)
						{
							if ($podids->id == $podcast)
							{
								$where[] = 'mf.id = ' . $result->id;
							}
						}
						break;
					case -1 :
						break;
					case 0 :
						break;

					default :
						if ($podcasts == $podids->id)
						{
							$where[] = 'mf.id = ' . $result->id;
							break;
						}
				}
			}
			$where = (count($where) ? ' ' . implode(' OR ', $where) : '');

			if (!$where)
			{
				return $msg = ' No media files were associated with a podcast. ';
			}

			$query = $db->getQuery(true);
			$query->select('p.id AS pid, p.podcastlimit,'
			. ' mf.id AS mfid, mf.study_id, mf.server, mf.path, mf.filename, mf.size, mf.mime_type, mf.podcast_id,'
			. ' mf.published AS mfpub, mf.createdate, mf.params,'
			. ' mf.docMan_id, mf.article_id,'
			. ' s.id AS sid, s.studydate, s.teacher_id, s.booknumber, s.chapter_begin, s.verse_begin, s.chapter_end,'
			. ' s.verse_end, s.studytitle, s.studyintro, s.published AS spub,'
			. ' s.media_hours, s.media_minutes, s.media_seconds,'
			. ' sr.id AS srid, sr.server_path,'
			. ' f.id AS fid, f.folderpath,'
			. ' t.id AS tid, t.teachername,'
			. ' b.id AS bid, b.booknumber AS bnumber, b.bookname,'
			. ' mt.id AS mtid, mt.mimetype')
				->from('#__bsms_mediafiles AS mf')
				->leftJoin('#__bsms_studies AS s ON (s.id = mf.study_id)')
				->leftJoin('#__bsms_servers AS sr ON (sr.id = mf.server)')
				->leftJoin('#__bsms_folders AS f ON (f.id = mf.path)')
				->leftJoin('#__bsms_books AS b ON (b.booknumber = s.booknumber)')
				->leftJoin('#__bsms_teachers AS t ON (t.id = s.teacher_id)')
				->leftJoin('#__bsms_mimetype AS mt ON (mt.id = mf.mime_type)')
				->leftJoin('#__bsms_podcast AS p ON (p.id = mf.podcast_id)')
				->where($where)->where('s.published = ' . 1)->where('mf.published = ' . 1)
				->order('createdate desc');
			$db->setQuery($query, 0, $limit);
			$episodes      = $db->loadObjectList();
			$episodedetail = '';

			if (count($episodes))
			{
				foreach ($episodes as $episode)
				{
					$episodedate = date("r", strtotime($episode->createdate));
					$hours       = $episode->media_hours;

					if (!$hours)
					{
						$hours = '00';
					}
					if ($hours < 1)
					{
						$hours = '00';
					}

					if (!$episode->media_seconds)
					{
						$episode->media_seconds = 1;
					}
					$params->set('show_verses', '1');
					$esv          = 0;
					$scripturerow = 1;
					$episode->id  = $episode->study_id;
					$scripture    = $JBSMCustom->getScripture($params, $episode, $esv, $scripturerow);
					$pod_title    = $podinfo->episodetitle;

					if (!$episode->size)
					{
						$episode->size = '1024';
					}
					switch ($pod_title)
					{
						case 0:
							$title = $scripture . ' - ' . $episode->studytitle;
							break;
						case 1:
							$title = $episode->studytitle;
							break;
						case 2:
							$title = $scripture;
							break;
						case 3:
							$title = $episode->studytitle . ' - ' . $scripture;
							break;
						case 4:
							$title = $episodedate . ' - ' . $scripture . ' - ' . $episode->studytitle;
							break;
						case 5:
							$custom  = new JBSMCustom;
							$element = $custom->getCustom($rowid = 'row1col1', $podinfo->custom, $episode, $params, $admin_params, $detailstemplateid);

							$title = $element->element;
							break;
					}

					$title             = str_replace('&', "and", $title);
					$description       = str_replace('&', "and", $episode->studyintro);
					$episodedetailtemp = '';
					$episodedetailtemp = '
	<item>
		<title>' . $title . '</title>
		<link>//' . $podinfo->website . '/index.php?option=com_biblestudy&view=sermon&id='
						. $episode->sid . $detailstemplateid . '</link>
		<comments>//' . $podinfo->website . '/index.php?option=com_biblestudy&view=sermon&id='
						. $episode->sid . $detailstemplateid . '</comments>
		<itunes:author>' . $episode->teachername . '</itunes:author>
		<dc:creator>' . $episode->teachername . '</dc:creator>
		<description>' . $description . '</description>
		<content:encoded>' . $description . '</content:encoded>
		<pubDate>' . $episodedate . '</pubDate>
		<itunes:subtitle>' . $title . '</itunes:subtitle>
		<itunes:summary>' . $description . '</itunes:summary>
		<itunes:keywords>' . $podinfo->podcastsearch . '</itunes:keywords>';
					$episodedetailtemp .= '<itunes:duration>' . $hours . ':' . sprintf("%02d", $episode->media_minutes)
						. ':' . sprintf("%02d", $episode->media_seconds) . '</itunes:duration>';

					// Here is where we test to see if the link should be an article or docMan link, otherwise it is a mediafile
					if ($episode->article_id > 1)
					{
						$episodedetailtemp .=
							'<enclosure url="//' . $episode->server_path . '/index.php?option=com_content&amp;view=article&amp;id='
							. $episode->article_id . '" length="' . $episode->size . '" type="'
							. $episode->mimetype . '" />
			<guid>//' . $episode->server_path . '/index.php?option=com_content&amp;view=article&amp;id=' . $episode->article_id . '</guid>';
					}
					if ($episode->docMan_id > 1)
					{
						$episodedetailtemp .=
							'<enclosure url="//' . $episode->server_path . '/index.php?option=com_docman&amp;task=doc_download&amp;gid='
							. $episode->docMan_id . '" length="' . $episode->size . '" type="'
							. $episode->mimetype . '" />
			<guid>//' . $episode->server_path . '/index.php?option=com_docman&amp;task=doc_download&amp;gid=' . $episode->docMan_id . '</guid>';
					}
					else
					{
						$episodedetailtemp .=
							'<enclosure url="//' . $episode->server_path . $episode->folderpath . str_replace(' ', "%20", $episode->filename)
							. '" length="' . $episode->size . '" type="'
							. $episode->mimetype . '" />
			<guid>//' . $episode->server_path . $episode->folderpath . str_replace(' ', "%20", $episode->filename) . '</guid>';
					}
					$episodedetailtemp .= '
		<itunes:explicit>no</itunes:explicit>
	</item>';
					$episodedetail = $episodedetail . $episodedetailtemp;

				} // End of foreach for episode details

			} // End for if count($episodes)
			$podfoot     = '
</channel>
</rss>';
			$filecontent = $podhead . $episodedetail . $podfoot;

			// Set FTP credentials, if given
			jimport('joomla.client.helper');
			jimport('joomla.filesystem.file');
			JClientHelper::setCredentialsFromRequest('ftp');
			$ftp     = JClientHelper::getCredentials('ftp');
			$input   = new JInput;
			$client  = JApplicationHelper::getClientInfo($input->get('client', '0', 'int'));
			$file    = $client->path . DIRECTORY_SEPARATOR . $podinfo->filename;
			$files[] = $file;

			// Try to make the template file writeable
			if (JFile::exists($file) && !$ftp['enabled'] && !JPath::setPermissions($file, '0755'))
			{
				JFactory::getApplication()->enqueueMessage('Could not make the file writable', 'notice');
			}

			$fileit = JFile::write($file, $filecontent);

			if ($fileit)
			{
				$podcastresults[] = true;
			}
			// Try to make the template file unwriteable
			if (!$ftp['enabled'] && !JPath::setPermissions($file, '0555'))
			{
				JFactory::getApplication()->enqueueMessage('Could not make the file unwritable', 'notece');
			}
		} // End of foreach $podid
	} // End if (count($podid))

	foreach ($podcastresults AS $podcastresult)
	{
		if (!$podcastresult)
		{
			$return = false;
		}
	}

	return $return;
}
