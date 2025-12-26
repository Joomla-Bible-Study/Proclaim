<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;
use Joomla\Registry\Registry;

/**
 * Proclaim Podcast Class
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class Cwmpodcast
{
    /**
     * @var int
     * @since version
     */
    private int $templateid = 0;

    /**
     * @var null
     * @since version
     */
    private $template = null;

    /**
     * @var string
     * @since version
     */
    private string $filename;

    /**
     * Make All Podcasts
     *
     * @return string
     *
     * @throws \Exception
     * @since 8.0.0
     */
    public function makePodcasts(): string
    {
        $msg  = [];
        $db   = Factory::getContainer()->get('DatabaseDriver');
        $year = '(' . date('Y') . ')';
        $date = date('r');

        // Get English language file as fallback
        $language = Factory::getApplication()->getLanguage();
        $language->load('com_proclaim', BIBLESTUDY_PATH_ADMIN, 'en-GB', true);

        // First, get all podcasts that are published
        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->quoteName('#__bsms_podcast'))
            ->where($db->quoteName('published') . ' = 1');
        $db->setQuery($query);
        $podcasts = $db->loadObjectList() ?: [];

        // Now iterate through the podcasts, and pick up the mediafiles
        foreach ($podcasts as $podinfo) {
            $podlanguage = $podinfo->language === '*' ? Factory::getApplication()->getConfig()->get('language') : $podinfo->language;
            $language->load('com_proclaim', BIBLESTUDY_PATH_ADMIN, $podlanguage, true);

            // Check if there's any media file associated
            $query = $db->getQuery(true);
            $query->select('id')
                ->from($db->quoteName('#__bsms_mediafiles'))
                ->where($db->quoteName('podcast_id') . ' LIKE ' . $db->q('%,' . $podinfo->id . ',%'))
                ->where($db->quoteName('published') . ' = 1');
            $db->setQuery($query, 0, 1);

            if (!$db->loadResult()) {
                $msg[] = Text::_('JBS_CMN_NO_MEDIA_FILES') . ' (' . $podinfo->title . ')';
                continue;
            }

            $limit    = (int) $podinfo->podcastlimit;
            $episodes = $this->getEpisodes((int) $podinfo->id, $limit > 0 ? 'LIMIT ' . $limit : '');

            if (!$episodes) {
                continue;
            }

            $registry = new Registry();
            $registry->loadString(Cwmparams::getAdmin()->params);
            $registry->merge(Cwmparams::getTemplateparams()->params);
            $params = $registry;
            $params->set('show_verses', '1');
            $protocol          = $params->get('protocol', 'http://');
            $detailstemplateid = (int) ($podinfo->detailstemplateid ?: 1);

            if (empty($podinfo->podcastlink)) {
                $podinfo->podcastlink = $podinfo->website;
            }

            if (!isset($podinfo->subtitle)) {
                $podinfo->subtitle = $podinfo->title;
            }

            $imagePath = $protocol . $podinfo->website . '/' . Cwmimages::getImagePath($podinfo->podcastimage)->path;

            $podhead = '<?xml version="1.0" encoding="utf-8"?>
<rss xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/"
 xmlns:atom="http://www.w3.org/2005/Atom" version="2.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd">
<channel>
	<generator>Proclaim</generator>
	<title>' . $this->escapeHTML($podinfo->title) . '</title>
	<link>' . $protocol . $podinfo->podcastlink . '</link>
	<image>
	    <url>' . $imagePath . '</url>
        <title>' . $this->escapeHTML($podinfo->title) . '</title>
        <link>' . $protocol . $podinfo->podcastlink . '</link>
	</image>
	<description>' . $this->escapeHTML($podinfo->description) . '</description>
	<language>' . $podlanguage . '</language>
	<itunes:type>episodic</itunes:type>
	<copyright>© ' . $year . ' All rights reserved.</copyright>
	<atom:link href="' . $protocol . $podinfo->website . '/' . $podinfo->filename . '" rel="self" type="application/rss+xml" />
	<lastBuildDate>' . $date . '</lastBuildDate>
	<itunes:summary>' . $this->escapeHTML($podinfo->description) . '</itunes:summary>
	<itunes:subtitle>' . $this->escapeHTML($podinfo->subtitle) . '</itunes:subtitle>
	<itunes:author>' . $this->escapeHTML($podinfo->editor_name) . '</itunes:author>
	<itunes:owner>
		<itunes:name>' . $this->escapeHTML($podinfo->editor_name) . '</itunes:name>
		<itunes:email>' . $podinfo->editor_email . '</itunes:email>
	</itunes:owner>
	<itunes:image href="' . $imagePath . '" />
	<itunes:category text="Religion &amp; Spirituality">
		<itunes:category text="Christianity" />
	</itunes:category>
	<itunes:explicit>no</itunes:explicit>
	<managingEditor>' . $podinfo->editor_email . ' (' . $this->escapeHTML($podinfo->editor_name) . ')</managingEditor>
	<webMaster>' . $podinfo->editor_email . ' (' . $this->escapeHTML($podinfo->editor_name) . ')</webMaster>
    <itunes:keywords>' . $podinfo->podcastsearch . '</itunes:keywords>';

            $episodedetail = '';
            $CWMlisting    = new Cwmlisting();

            foreach ($episodes as $episode) {
                $episodedate   = date("r", strtotime($episode->createdate));
                $scripture     = $CWMlisting->getScripture($params, $episode, 0, 1);
                $episode->size = $episode->params->get('size', '30000000');

                $title    = $this->getEpisodeTitle($podinfo, $episode, $scripture, $params, $detailstemplateid, $episodedate);
                $subtitle = $this->getEpisodeSubtitle($podinfo, $episode, $scripture, $params, $detailstemplateid, $episodedate);

                $title    = $this->escapeHTML($title);
                $subtitle = $this->escapeHTML($subtitle);

                $file          = str_replace(' ', "%20", $episode->params->get('filename'));
                $path          = Cwmhelper::mediaBuildUrl($episode->srparams->get('path'), $file, $params, false, false, true);
                $episode->slug = $episode->alias ? ($episode->sid . ':' . $episode->alias) : $episode->sid;

                $link     = $this->getEpisodeLink($podinfo, $episode, $path, $protocol, $detailstemplateid);
                $duration = $this->getEpisodeDuration($episode);

                $episodedetail .= '
	<item>
		<itunes:episodeType>full</itunes:episodeType>
		<title>' . $title . '</title>
		<link>' . $link . '</link>
		<comments>' . $link . '</comments>
		<itunes:author>' . $this->escapeHTML($episode->teachername) . '</itunes:author>
		<dc:creator>' . $this->escapeHTML($episode->teachername) . '</dc:creator>
		<description>' . $this->escapeHTML($episode->studyintro) . '</description>
		<content:encoded><![CDATA[' . $episode->studyintro . ']]></content:encoded>
		<pubDate>' . $episodedate . '</pubDate>
		<itunes:subtitle>' . $subtitle . '</itunes:subtitle>
		<itunes:summary><![CDATA[' . $episode->studyintro . ']]></itunes:summary>
		<itunes:keywords>' . $podinfo->podcastsearch . '</itunes:keywords>
		<itunes:duration>' . $duration . '</itunes:duration>';

                $episodedetail .= $this->getEnclosureXml($episode, $protocol, $path);

                $episodedetail .= '
		<itunes:explicit>no</itunes:explicit>
	</item>';
            }

            $podfoot = '
</channel>
</rss>';
            $input       = Factory::getApplication()->getInput();
            $client      = ApplicationHelper::getClientInfo($input->get('client', '0', 'int'));
            $file_path   = $client->path . '/' . $podinfo->filename;
            $filecontent = $podhead . $episodedetail . $podfoot;
            $filewritten = $this->writeFile($file_path, $filecontent);

            $file_url = Uri::root() . $podinfo->filename;
            $msg[]    = $file_url . ' - ' . ($filewritten ? Text::_('JBS_PDC_XML_FILES_WRITTEN') : Text::_('JBS_PDC_XML_FILES_ERROR'));
        }

        return implode('<br />', $msg) ?: 'No message';
    }

    /**
     * Get Episode Title
     *
     * @param   object    $podinfo            Podcast Info
     * @param   object    $episode            Episode Info
     * @param   string    $scripture          Scripture Reference
     * @param   Registry  $params             Params
     * @param   int       $detailstemplateid  Template ID
     * @param   string    $episodedate        Episode Date
     *
     * @return string
     * @since  8.0.0
     *
     * @throws \Exception
     */
    private function getEpisodeTitle($podinfo, $episode, $scripture, $params, $detailstemplateid, $episodedate): string
    {
        $CWMlisting = new Cwmlisting();
        switch ($podinfo->episodetitle) {
            case 0:
                if ($scripture && $episode->studytitle) {
                    return $scripture . ' - ' . $episode->studytitle;
                }
                return $episode->studytitle ?: $scripture;
            case 1:
                return (string) $episode->studytitle;
            case 2:
                return (string) $scripture;
            case 3:
                if ($scripture && $episode->studytitle) {
                    return $episode->studytitle . ' - ' . $scripture;
                }
                return $episode->studytitle ?: $scripture;
            case 4:
                $title = $episodedate ? $episodedate . ' - ' . $scripture : $scripture;
                return $episode->studytitle ? $title . ' - ' . $episode->studytitle : $title;
            case 5:
                if ($this->templateid !== $detailstemplateid || $this->template === null) {
                    $this->template   = Cwmparams::getTemplateparams($detailstemplateid);
                    $this->templateid = $detailstemplateid;
                }
                return (string) $CWMlisting->getFluidCustom($podinfo->custom, $episode, $params, $this->template, '24');
            case 6:
                return $episode->bookname . ' ' . $episode->chapter_begin;
        }
        return '';
    }

    /**
     * Get Episode Subtitle
     *
     * @param   object    $podinfo            Podcast Info
     * @param   object    $episode            Episode Info
     * @param   string    $scripture          Scripture Reference
     * @param   Registry  $params             Params
     * @param   int       $detailstemplateid  Template ID
     * @param   string    $episodedate        Episode Date
     *
     * @return string
     * @since  8.0.0
     *
     * @throws \Exception
     */
    private function getEpisodeSubtitle($podinfo, $episode, $scripture, $params, $detailstemplateid, $episodedate): string
    {
        $CWMlisting = new Cwmlisting();
        switch ($podinfo->episodesubtitle) {
            case 0:
                return (string) $episode->teachername;
            case 1:
                if ($scripture && $episode->teachername) {
                    return $scripture . ' - ' . $episode->teachername;
                }
                return $episode->teachername ?: $scripture;
            case 2:
                return (string) $scripture;
            case 3:
                return (string) $episode->studytitle;
            case 4:
                return $episodedate ? $episodedate . ' - ' . $scripture : $scripture;
            case 5:
                return $scripture . ' - ' . $episode->studytitle;
            case 6:
                return $episode->bookname . ' ' . $episode->chapter_begin;
            case 7:
                if ($this->templateid !== $detailstemplateid || $this->template === null) {
                    $this->template   = Cwmparams::getTemplateparams($detailstemplateid);
                    $this->templateid = $detailstemplateid;
                }
                return (string) $CWMlisting->getFluidCustom($podinfo->custom, $episode, $params, $this->template, 'podcast');
        }
        return '';
    }

    /**
     * Get Episode Link
     *
     * @param   object  $podinfo            Podcast Info
     * @param   object  $episode            Episode Info
     * @param   string  $path               Media Path
     * @param   string  $protocol           Protocol
     * @param   int     $detailstemplateid  Template ID
     *
     * @return string
     * @since  8.0.0
     */
    private function getEpisodeLink($podinfo, $episode, $path, $protocol, $detailstemplateid): string
    {
        if ($podinfo->linktype == '1') {
            return $protocol . $path;
        }

        $view = $podinfo->linktype == '2' ? 'cwmpopup&amp;player=1' : 'cwmsermon';
        return $protocol . $podinfo->website . '/index.php?option=com_proclaim&amp;view=' . $view . '&amp;id=' . $episode->slug . '&amp;t=' . $detailstemplateid;
    }

    /**
     * Get Episode Duration
     *
     * @param   object  $episode  Episode Info
     *
     * @return string
     * @since  8.0.0
     */
    private function getEpisodeDuration($episode): string
    {
        $hours   = $episode->params->get('media_hours', '00');
        $minutes = $episode->params->get('media_minutes', '00');
        $seconds = $episode->params->get('media_seconds', '00');

        if ($hours !== '00' || $minutes !== '00' || $seconds !== '00') {
            return $hours . ':' . $minutes . ':' . $seconds;
        }

        return '';
    }

    /**
     * Get Enclosure XML
     *
     * @param   object  $episode   Episode Info
     * @param   string  $protocol  Protocol
     * @param   string  $path      Media Path
     *
     * @return string
     * @since  8.0.0
     */
    private function getEnclosureXml($episode, $protocol, $path): string
    {
        $articleId = (int) $episode->params->get('article_id');
        $docmanId  = (int) $episode->params->get('docMan_id');
        $basePath  = $protocol . $episode->srparams->get('path');
        $mimeType  = $episode->params->get('mime_type');
        $size      = $episode->params->get('size', '100');

        if ($articleId > 1) {
            $url  = $basePath . '/index.php?option=com_content&amp;view=article&amp;id=' . $articleId;
            $type = $mimeType ?: 'application/octet-stream';
        } elseif ($docmanId > 1) {
            $url  = $basePath . '/index.php?option=com_docman&amp;task=doc_download&amp;gid=' . $docmanId;
            $type = $mimeType;
        } else {
            $url  = $protocol . $path;
            $type = $mimeType ?: 'audio/mpeg3';
        }

        return '
		<enclosure url="' . $url . '" length="' . $size . '" type="' . $type . '" />
		<guid>' . $url . '</guid>';
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
    public function getEpisodes(int $id, string $limit): array
    {
        preg_match('!\d+!', $limit, $matches);
        $set_limit = (int) ($matches[0] ?? 0);

        // This is set due to the hard limit of Apple's max episodes.
        if ($set_limit > 300 || $set_limit === 0) {
            $set_limit = 300;
        }

        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select(
            [
                'p.id AS pid', 'p.podcastlimit',
                'mf.id AS mfid', 'mf.study_id', 'mf.server_id', 'mf.podcast_id',
                'mf.published AS mfpub', 'mf.createdate', 'mf.params',
                's.id AS sid', 's.alias AS alias', 's.studydate', 's.teacher_id', 's.booknumber', 's.chapter_begin', 's.verse_begin',
                's.chapter_end', 's.verse_end', 's.studytitle', 's.studyintro', 's.published AS spub',
                'se.series_text', 'se.published',
                'sr.id AS srid', 'sr.params as srparams',
                't.id AS tid', 't.teachername',
                'b.id AS bid', 'b.booknumber AS bnumber', 'b.bookname',
            ]
        )
            ->from($db->quoteName('#__bsms_mediafiles', 'mf'))
            ->leftJoin($db->quoteName('#__bsms_studies', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('mf.study_id'))
            ->leftJoin($db->quoteName('#__bsms_series', 'se') . ' ON ' . $db->quoteName('se.id') . ' = ' . $db->quoteName('s.series_id'))
            ->leftJoin($db->quoteName('#__bsms_servers', 'sr') . ' ON ' . $db->quoteName('sr.id') . ' = ' . $db->quoteName('mf.server_id'))
            ->leftJoin($db->quoteName('#__bsms_books', 'b') . ' ON ' . $db->quoteName('b.booknumber') . ' = ' . $db->quoteName('s.booknumber'))
            ->leftJoin($db->quoteName('#__bsms_teachers', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('s.teacher_id'))
            ->leftJoin($db->quoteName('#__bsms_podcast', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('mf.podcast_id'))
            ->where($db->quoteName('mf.podcast_id') . ' LIKE ' . $db->q('%,' . $id . ',%'))
            ->where($db->quoteName('mf.published') . ' = 1')
            ->where($db->quoteName('s.published') . ' = 1')
            ->where('(' . $db->quoteName('se.published') . ' = 1 OR ' . $db->quoteName('s.series_id') . ' = -1)')
            ->order($db->quoteName('createdate') . ' DESC');

        $db->setQuery($query, 0, $set_limit);
        $episodes = $db->loadObjectList() ?: [];

        foreach ($episodes as $e) {
            $e->params   = new Registry($e->params);
            $e->srparams = new Registry($e->srparams);
        }

        return $episodes;
    }

    /**
     * Escape HTML to XML
     *
     * @param   null|string  $string  HTML string to make safe
     *
     * @return string
     *
     * @since 9.0.0
     */
    protected function escapeHTML(?string $string): string
    {
        if (empty($string)) {
            return $string;
        }

        $string = strip_tags($string);

        return htmlspecialchars($string, ENT_DISALLOWED, "UTF-8");
    }

    /**
     * Write the File
     *
     * @param   string  $file         File Name
     * @param   string  $filecontent  File Content
     *
     * @return bool
     *
     * @throws \Exception
     * @since 7.0.0
     */
    public function writeFile(string $file, string $filecontent): bool
    {
        // Set FTP credentials, if given
        ClientHelper::setCredentialsFromRequest('ftp');
        $ftp = ClientHelper::getCredentials('ftp');

        // Try to make the template file writable
        if (!$ftp['enabled'] && is_file($file) && !Path::setPermissions($file, '0755')) {
            Factory::getApplication()->enqueueMessage('Could not make the file writable', 'notice');
        }

        $fileIt = File::write($file, $filecontent);

        // Try to make the template file unwriteable so other applications can't update it
        if (!$fileIt || (!$ftp['enabled'] && !Path::setPermissions($file, '0555'))) {
            Factory::getApplication()
                ->enqueueMessage('Could not make the file un-writable', 'notice');

            return false;
        }

        return true;
    }

    /**
     * Break out time from seconds to (Array of hours, minutes, seconds).
     *
     * @param   int  $duration  Time in seconds as hh:mm:ss
     *
     * @return  object  Returns hours, minutes, seconds
     *
     * @since 9.2.4
     */
    public function formatTime(int $duration): object
    {
        $time = new \stdClass();

        if ($duration !== 0) {
            $time->hours   = floor($duration / 3600);
            $time->minutes = floor(($duration - ($time->hours * 3600)) / 60);
            $time->seconds = $duration - ($time->hours * 3600) - ($time->minutes * 60);
        } else {
            $time->hours   = '00';
            $time->minutes = '00';
            $time->seconds = '00';
        }

        return $time;
    }

    /**
     * Read the entire file, frame by frame... i.e., Variable Bit Rate (VBR)
     *
     * @param   string  $filename  File name of media.
     *
     * @return int
     *
     * @since 9.2.4
     */
    public function getDuration(string $filename): int
    {
        $duration = 0;

        if ($fd = fopen($filename, 'rb')) {
            $block  = fread($fd, 100);
            $offset = $this->skipID3v2Tag($block);
            @fseek($fd, $offset, SEEK_SET);

            while (!feof($fd)) {
                $block = fread($fd, 10);

                if (\strlen($block) < 10) {
                    break;
                }

                // Looking for 1111 1111 111 (frame synchronization bits)

                if ($block[0] === "\xff" && (\ord($block[1]) & 0xe0)) {
                    $info = $this->parseFrameHeader(substr($block, 0, 4));

                    if (empty($info['Framesize'])) {
                        return (int)$duration;
                        // Some corrupt mp3 files
                    }

                    fseek($fd, $info['Framesize'] - 10, SEEK_CUR);
                    $duration += ($info['Samples'] / $info['Sampling Rate']);
                } elseif (str_starts_with($block, 'TAG')) {
                    fseek($fd, 128 - 10, SEEK_CUR);
                    // Skip over id3v1 tag size
                } else {
                    @fseek($fd, -9, SEEK_CUR);
                }
            }
        }

        return (int)$duration;
    }

    /**
     * Skip ID3v2 Tags
     *
     * @param   array|string  $block  ID3 info block
     *
     * @return int
     *
     * @since 9.2.4
     */
    public function skipID3v2Tag(&$block): int
    {
        // Do not worry about string vs array work right.
        if (str_starts_with($block, "ID3")) {
            $id3v2_major_version    = \ord($block[3]);
            $id3v2_minor_version    = \ord($block[4]);
            $id3v2_flags            = \ord($block[5]);
            $flag_unsynchronisation = $id3v2_flags & 0x80 ? 1 : 0;
            $flag_extended_header   = $id3v2_flags & 0x40 ? 1 : 0;
            $flag_experimental_ind  = $id3v2_flags & 0x20 ? 1 : 0;
            $flag_footer_present    = $id3v2_flags & 0x10 ? 1 : 0;
            $z0                     = \ord($block[6]);
            $z1                     = \ord($block[7]);
            $z2                     = \ord($block[8]);
            $z3                     = \ord($block[9]);

            if ((($z0 & 0x80) === 0) && (($z1 & 0x80) === 0) && (($z2 & 0x80) === 0) && (($z3 & 0x80) === 0)) {
                $header_size = 10;
                $tag_size    = (($z0 & 0x7f) * 2097152) + (($z1 & 0x7f) * 16384) + (($z2 & 0x7f) * 128) + ($z3 & 0x7f);
                $footer_size = $flag_footer_present ? 10 : 0;

                return $header_size + $tag_size + $footer_size;
                // Bytes to skip
            }
        }

        return 0;
    }

    /**
     * Get the Frame Header of the ID3 file
     *
     * @param   string  $fourbytes  array with four bytes
     *
     * @return array
     *
     * @since 9.2.4
     */
    public function parseFrameHeader(string $fourbytes): array
    {
        static $versions = [
            0x0 => '2.5',
            0x1 => 'x',
            0x2 => '2',
            0x3 => '1',
        ];
        static $layers = [
            0x0 => 'x',
            0x1 => '3',
            0x2 => '2',
            0x3 => '1',
        ];
        static $bitrates = [
            'V1L1' => [0, 32, 64, 96, 128, 160, 192, 224, 256, 288, 320, 352, 384, 416, 448],
            'V1L2' => [0, 32, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320, 384],
            'V1L3' => [0, 32, 40, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320],
            'V2L1' => [0, 32, 48, 56, 64, 80, 96, 112, 128, 144, 160, 176, 192, 224, 256],
            'V2L2' => [0, 8, 16, 24, 32, 40, 48, 56, 64, 80, 96, 112, 128, 144, 160],
            'V2L3' => [0, 8, 16, 24, 32, 40, 48, 56, 64, 80, 96, 112, 128, 144, 160],
        ];
        static $sample_rates = [
            '1'   => [44100, 48000, 32000],
            '2'   => [22050, 24000, 16000],
            '2.5' => [11025, 12000, 8000],
        ];
        static $samples = [
            1 => [1 => 384, 2 => 1152, 3 => 1152],
            2 => [1 => 384, 2 => 1152, 3 => 576],
        ];

        $b1 = \ord($fourbytes[1]);
        $b2 = \ord($fourbytes[2]);
        $b3 = \ord($fourbytes[3]);

        $version_bits   = ($b1 & 0x18) >> 3;
        $version        = $versions[$version_bits];
        $simple_version = ($version === '2.5' ? 2 : $version);

        $layer_bits = ($b1 & 0x06) >> 1;
        $layer      = (int)$layers[$layer_bits];

        $protection_bit = ($b1 & 0x01);
        $bitrate_key    = \sprintf('V%dL%d', $simple_version, $layer);
        $bitrate_idx    = ($b2 & 0xf0) >> 4;
        $bitrate        = $bitrates[$bitrate_key][$bitrate_idx] ?? 0;

        $sample_rate_idx     = ($b2 & 0x0c) >> 2;
        $sample_rate         = $sample_rates[$version][$sample_rate_idx] ?? 0;
        $padding_bit         = ($b2 & 0x02) >> 1;
        $private_bit         = ($b2 & 0x01);
        $channel_mode_bits   = ($b3 & 0xc0) >> 6;
        $mode_extension_bits = ($b3 & 0x30) >> 4;
        $copyright_bit       = ($b3 & 0x08) >> 3;
        $original_bit        = ($b3 & 0x04) >> 2;
        $emphasis            = ($b3 & 0x03);

        $info                  = [];
        $info['Version']       = $version;
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
     * @param   int  $layer        Layer
     * @param   int  $bitrate      Bit Rate
     * @param   int  $sample_rate  Sample rate
     * @param   int  $padding_bit  Padding
     *
     * @return int
     *
     * @since 9.2.4
     */
    public function frameSize(int $layer, int $bitrate, int $sample_rate, int $padding_bit): int
    {
        if ($layer === 1) {
            return (int)(((12 * $bitrate * 1000 / $sample_rate) + $padding_bit) * 4);
        }

        // Layer 2, 3
        return (int)(((144 * $bitrate * 1000) / $sample_rate) + $padding_bit);
    }

    /**
     * Remove Http from URL
     *
     * @param   string  $url  Url of website
     *
     * @return string
     *
     * @since version
     */
    public function removeHttp(string $url): string
    {
        $disallowed = ['http://', 'https://'];

        foreach ($disallowed as $d) {
            if (str_starts_with($url, $d)) {
                return str_replace($d, '', $url);
            }
        }

        return $url;
    }
}
