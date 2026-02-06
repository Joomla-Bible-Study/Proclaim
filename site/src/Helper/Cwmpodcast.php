<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Site\Helper\Cwmmedia;
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
     * @var Cwmlisting|null
     * @since 10.2.0
     */
    private ?Cwmlisting $listing = null;

    /**
     * Get or create Cwmlisting instance (lazy loading)
     *
     * @return Cwmlisting
     *
     * @since 10.2.0
     */
    private function getListing(): Cwmlisting
    {
        if ($this->listing === null) {
            $this->listing = new Cwmlisting();
        }

        return $this->listing;
    }

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
            $query->select($db->quoteName('id'))
                ->from($db->quoteName('#__bsms_mediafiles'))
                ->where('FIND_IN_SET(' . (int) $podinfo->id . ', ' . $db->quoteName('podcast_id') . ')')
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

            // Cache sanitized description to avoid processing twice
            $sanitizedDescription = $this->sanitizeHtmlForPodcast($podinfo->description);
            $escapedTitle = $this->escapeHTML($podinfo->title);

            $podhead = '<?xml version="1.0" encoding="utf-8"?>
<rss xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/"
 xmlns:atom="http://www.w3.org/2005/Atom" version="2.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd">
<channel>
	<generator>Proclaim</generator>
	<title>' . $escapedTitle . '</title>
	<link>' . $protocol . $podinfo->podcastlink . '</link>
	<image>
	    <url>' . $imagePath . '</url>
        <title>' . $escapedTitle . '</title>
        <link>' . $protocol . $podinfo->podcastlink . '</link>
	</image>
	<description><![CDATA[' . $sanitizedDescription . ']]></description>
	<language>' . $podlanguage . '</language>
	<itunes:type>episodic</itunes:type>
	<copyright>© ' . $year . ' All rights reserved.</copyright>
	<atom:link href="' . $protocol . $podinfo->website . '/' . $podinfo->filename . '" rel="self" type="application/rss+xml" />
	<lastBuildDate>' . $date . '</lastBuildDate>
	<itunes:summary><![CDATA[' . $sanitizedDescription . ']]></itunes:summary>
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

            foreach ($episodes as $episode) {
                $episodedate   = date('r', strtotime($episode->createdate));
                $scripture     = $this->getListing()->getScripture($params, $episode, 0, 1);
                $episode->size = $episode->params->get('size', '30000000');

                $title    = $this->getEpisodeTitle($podinfo, $episode, $scripture, $params, $detailstemplateid, $episodedate);
                $subtitle = $this->getEpisodeSubtitle($podinfo, $episode, $scripture, $params, $detailstemplateid, $episodedate);

                $title    = $this->escapeHTML($title);
                $subtitle = $this->escapeHTML($subtitle);

                $file          = str_replace(' ', '%20', $episode->params->get('filename'));
                $path          = Cwmhelper::mediaBuildUrl($episode->srparams->get('path'), $file, $params, false, false, true);
                $episode->slug = $episode->alias ? ($episode->sid . ':' . $episode->alias) : $episode->sid;

                $link     = $this->getEpisodeLink($podinfo, $episode, $path, $protocol, $detailstemplateid);
                $duration = $this->getEpisodeDuration($episode);

                // Cache sanitized content to avoid processing three times
                $sanitizedIntro = $this->sanitizeHtmlForPodcast($episode->studyintro);
                $escapedTeacher = $this->escapeHTML($episode->teachername);

                $episodedetail .= '
	<item>
		<itunes:episodeType>full</itunes:episodeType>
		<title>' . $title . '</title>
		<link>' . $link . '</link>
		<comments>' . $link . '</comments>
		<itunes:author>' . $escapedTeacher . '</itunes:author>
		<dc:creator>' . $escapedTeacher . '</dc:creator>
		<description><![CDATA[' . $sanitizedIntro . ']]></description>
		<content:encoded><![CDATA[' . $sanitizedIntro . ']]></content:encoded>
		<pubDate>' . $episodedate . '</pubDate>
		<itunes:subtitle>' . $subtitle . '</itunes:subtitle>
		<itunes:summary><![CDATA[' . $sanitizedIntro . ']]></itunes:summary>
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
                return (string) $this->getListing()->getFluidCustom($podinfo->custom, $episode, $params, $this->template, '24');
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
                return (string) $this->getListing()->getFluidCustom($podinfo->custom, $episode, $params, $this->template, 'podcast');
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
        if ($podinfo->linktype === '1' || (int) $podinfo->linktype === 1) {
            return $protocol . $path;
        }

        $view = ($podinfo->linktype === '2' || (int) $podinfo->linktype === 2) ? 'cwmpopup&amp;player=1' : 'cwmsermon';
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
            ->leftJoin($db->quoteName('#__bsms_podcast', 'p') . ' ON FIND_IN_SET(' . $db->quoteName('p.id') . ', ' . $db->quoteName('mf.podcast_id') . ')')
            ->where('FIND_IN_SET(' . $id . ', ' . $db->quoteName('mf.podcast_id') . ')')
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
     * Escape HTML to XML (strips all HTML tags)
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
            return $string ?? '';
        }

        $string = strip_tags($string);

        return htmlspecialchars($string, ENT_DISALLOWED, "UTF-8");
    }

    /**
     * Sanitize HTML for podcast feeds - allows only limited HTML tags
     *
     * Most podcast apps only support: <a>, <p>, <ul>, <ol>, <li>, <b>, <i>, <strong>, <em>
     * Apple Podcasts has a 4,000 character limit including HTML tags.
     *
     * @param   null|string  $string     HTML string to sanitize
     * @param   int          $maxLength  Maximum length (default 4000 for Apple Podcasts)
     *
     * @return string
     *
     * @since 10.2.0
     */
    protected function sanitizeHtmlForPodcast(?string $string, int $maxLength = 4000): string
    {
        if (empty($string)) {
            return '';
        }

        // Allow only podcast-safe HTML tags
        $allowedTags = '<a><p><ul><ol><li><b><i><strong><em><br>';
        $string = strip_tags($string, $allowedTags);

        // Clean anchor tags - keep only href attribute
        $string = preg_replace_callback(
            '/<a\s+[^>]*>/i',
            function ($matches) {
                if (preg_match('/href\s*=\s*(["\'])([^"\']*)\1/i', $matches[0], $href)) {
                    return '<a href="' . htmlspecialchars($href[2], ENT_QUOTES, 'UTF-8') . '">';
                }
                return '<a>';
            },
            $string
        );

        // Remove all attributes from other tags (not anchors)
        $string = preg_replace('/<(p|ul|ol|li|b|i|strong|em)(\s+[^>]*)>/i', '<$1>', $string);

        // Normalize <br> tags to self-closing format
        $string = preg_replace('/<br\s*\/?>/i', '<br/>', $string);

        // Escape CDATA end sequence to prevent breaking CDATA sections
        $string = str_replace(']]>', ']]&gt;', $string);

        // Trim to max length if needed (Apple Podcasts limit is 4000 chars)
        if (mb_strlen($string, 'UTF-8') > $maxLength) {
            // Find a safe point to cut - avoid cutting inside a tag
            $string = mb_substr($string, 0, $maxLength, 'UTF-8');

            // If we're inside a tag, back up to before it
            $lastOpenBracket = strrpos($string, '<');
            $lastCloseBracket = strrpos($string, '>');
            if ($lastOpenBracket !== false && ($lastCloseBracket === false || $lastOpenBracket > $lastCloseBracket)) {
                $string = substr($string, 0, $lastOpenBracket);
            }

            // Try to end at a complete word
            $lastSpace = strrpos($string, ' ');
            if ($lastSpace !== false && $lastSpace > mb_strlen($string, 'UTF-8') - 50) {
                $string = substr($string, 0, $lastSpace);
            }

            // Close any unclosed tags
            $string = $this->closeUnclosedTags($string);
        }

        return trim($string);
    }

    /**
     * Close unclosed HTML tags in a string
     *
     * @param   string  $html  HTML string that may have unclosed tags
     *
     * @return string
     *
     * @since 10.2.0
     */
    protected function closeUnclosedTags(string $html): string
    {
        // Void elements that don't need closing tags
        $voidElements = ['br', 'hr', 'img', 'input', 'meta', 'link'];

        // Track open tags in order
        $openStack = [];

        // Find all tags
        preg_match_all('/<\/?([a-z]+)[^>]*>/i', $html, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $tag = strtolower($match[1]);

            // Skip void elements
            if (in_array($tag, $voidElements, true)) {
                continue;
            }

            if (str_starts_with($match[0], '</')) {
                // Closing tag - pop from stack if it matches
                $lastIndex = array_search($tag, array_reverse($openStack, true), true);
                if ($lastIndex !== false) {
                    unset($openStack[$lastIndex]);
                    $openStack = array_values($openStack);
                }
            } else {
                // Opening tag - push to stack
                $openStack[] = $tag;
            }
        }

        // Close remaining open tags in reverse order
        $closingTags = '';
        foreach (array_reverse($openStack) as $tag) {
            $closingTags .= '</' . $tag . '>';
        }

        return $html . $closingTags;
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
     * Validate all podcasts and return a comprehensive report
     *
     * @return array  Array of validation results per podcast
     *
     * @throws \Exception
     * @since 10.2.0
     */
    public function validateAllPodcasts(): array
    {
        $results = [];
        $db = Factory::getContainer()->get('DatabaseDriver');

        // Get all published podcasts
        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->quoteName('#__bsms_podcast'))
            ->where($db->quoteName('published') . ' = 1');
        $db->setQuery($query);
        $podcasts = $db->loadObjectList() ?: [];

        foreach ($podcasts as $podcast) {
            $results[$podcast->id] = $this->validatePodcast($podcast);
        }

        return $results;
    }

    /**
     * Validate a single podcast (pre-build check)
     *
     * @param   object  $podcast  The podcast object to validate
     *
     * @return array  Validation result with 'valid', 'errors', 'warnings' keys
     *
     * @throws \Exception
     * @since 10.2.0
     */
    public function validatePodcast(object $podcast): array
    {
        $errors = [];
        $warnings = [];
        $info = [];

        // Required fields
        if (empty($podcast->title)) {
            $errors[] = Text::_('JBS_PDC_VALIDATE_TITLE_REQUIRED');
        }

        if (empty($podcast->description)) {
            $errors[] = Text::_('JBS_PDC_VALIDATE_DESCRIPTION_REQUIRED');
        } elseif (mb_strlen(strip_tags($podcast->description), 'UTF-8') > 4000) {
            $warnings[] = Text::sprintf('JBS_PDC_VALIDATE_DESCRIPTION_TOO_LONG', mb_strlen(strip_tags($podcast->description), 'UTF-8'));
        }

        if (empty($podcast->editor_name)) {
            $errors[] = Text::_('JBS_PDC_VALIDATE_AUTHOR_REQUIRED');
        }

        if (empty($podcast->editor_email)) {
            $errors[] = Text::_('JBS_PDC_VALIDATE_EMAIL_REQUIRED');
        } elseif (!filter_var($podcast->editor_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = Text::_('JBS_PDC_VALIDATE_EMAIL_INVALID');
        }

        if (empty($podcast->website)) {
            $errors[] = Text::_('JBS_PDC_VALIDATE_WEBSITE_REQUIRED');
        }

        if (empty($podcast->filename)) {
            $errors[] = Text::_('JBS_PDC_VALIDATE_FILENAME_REQUIRED');
        }

        // Podcast image validation
        if (empty($podcast->podcastimage)) {
            $errors[] = Text::_('JBS_PDC_VALIDATE_IMAGE_REQUIRED');
        } else {
            $imageValidation = $this->validatePodcastImage($podcast->podcastimage);
            if (!$imageValidation['valid']) {
                $errors = array_merge($errors, $imageValidation['errors']);
            }
            if (!empty($imageValidation['warnings'])) {
                $warnings = array_merge($warnings, $imageValidation['warnings']);
            }
        }

        // Check for associated media files
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('COUNT(*)')
            ->from($db->quoteName('#__bsms_mediafiles'))
            ->where('FIND_IN_SET(' . (int) $podcast->id . ', ' . $db->quoteName('podcast_id') . ')')
            ->where($db->quoteName('published') . ' = 1');
        $db->setQuery($query);
        $mediaCount = (int) $db->loadResult();

        if ($mediaCount === 0) {
            $errors[] = Text::_('JBS_PDC_VALIDATE_NO_MEDIA_FILES');
        } else {
            $info[] = Text::sprintf('JBS_PDC_VALIDATE_MEDIA_COUNT', $mediaCount);

            // Validate media files
            $mediaValidation = $this->validatePodcastMedia((int) $podcast->id);
            $warnings = array_merge($warnings, $mediaValidation['warnings']);
        }

        // Check if XML file exists
        $input = Factory::getApplication()->getInput();
        $client = ApplicationHelper::getClientInfo($input->get('client', '0', 'int'));
        $filePath = $client->path . '/' . $podcast->filename;

        if (is_file($filePath)) {
            $info[] = Text::_('JBS_PDC_VALIDATE_XML_EXISTS');

            // Validate existing XML
            $xmlValidation = $this->validatePodcastXml($filePath);
            if (!$xmlValidation['valid']) {
                $warnings = array_merge($warnings, $xmlValidation['errors']);
            }
        } else {
            $info[] = Text::_('JBS_PDC_VALIDATE_XML_NOT_EXISTS');
        }

        return [
            'podcast_id' => $podcast->id,
            'podcast_title' => $podcast->title,
            'valid' => empty($errors),
            'ready' => empty($errors) && $mediaCount > 0,
            'errors' => $errors,
            'warnings' => $warnings,
            'info' => $info,
            'media_count' => $mediaCount,
        ];
    }

    /**
     * Validate podcast image meets iTunes requirements
     *
     * iTunes requires: JPEG or PNG, 1400x1400 to 3000x3000 pixels, RGB color space
     *
     * @param   string  $imagePath  Path to the image
     *
     * @return array  Validation result
     *
     * @since 10.2.0
     */
    protected function validatePodcastImage(string $imagePath): array
    {
        $errors = [];
        $warnings = [];

        // Build full path
        $fullPath = JPATH_ROOT . '/' . ltrim($imagePath, '/');

        if (!is_file($fullPath)) {
            $errors[] = Text::sprintf('JBS_PDC_VALIDATE_IMAGE_NOT_FOUND', $imagePath);
            return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
        }

        // Get image info
        $imageInfo = @getimagesize($fullPath);

        if ($imageInfo === false) {
            $errors[] = Text::_('JBS_PDC_VALIDATE_IMAGE_INVALID');
            return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
        }

        [$width, $height, $type] = $imageInfo;

        // Check format (JPEG or PNG required)
        if ($type !== IMAGETYPE_JPEG && $type !== IMAGETYPE_PNG) {
            $errors[] = Text::_('JBS_PDC_VALIDATE_IMAGE_FORMAT');
        }

        // Check dimensions (iTunes requires 1400x1400 to 3000x3000)
        if ($width < 1400 || $height < 1400) {
            $errors[] = Text::sprintf('JBS_PDC_VALIDATE_IMAGE_TOO_SMALL', $width, $height);
        } elseif ($width > 3000 || $height > 3000) {
            $warnings[] = Text::sprintf('JBS_PDC_VALIDATE_IMAGE_TOO_LARGE', $width, $height);
        }

        // Check if square
        if ($width !== $height) {
            $warnings[] = Text::sprintf('JBS_PDC_VALIDATE_IMAGE_NOT_SQUARE', $width, $height);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'dimensions' => ['width' => $width, 'height' => $height],
        ];
    }

    /**
     * Validate media files associated with a podcast
     *
     * @param   int  $podcastId  The podcast ID
     *
     * @return array  Validation result with warnings
     *
     * @since 10.2.0
     */
    protected function validatePodcastMedia(int $podcastId): array
    {
        $warnings = [];
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true);
        $query->select($db->quoteName(['mf.id', 'mf.params', 's.studytitle', 's.studyintro']))
            ->from($db->quoteName('#__bsms_mediafiles', 'mf'))
            ->leftJoin($db->quoteName('#__bsms_studies', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('mf.study_id'))
            ->where('FIND_IN_SET(' . $podcastId . ', ' . $db->quoteName('mf.podcast_id') . ')')
            ->where($db->quoteName('mf.published') . ' = 1');
        $db->setQuery($query);
        $mediaFiles = $db->loadObjectList() ?: [];

        foreach ($mediaFiles as $media) {
            $params = new Registry($media->params);
            $filename = $params->get('filename');
            $isYouTube = $filename && $this->isYouTubeUrl($filename);

            // Check for missing file size (skip for YouTube - no file size available)
            if (!$isYouTube) {
                $size = $params->get('size', 0);
                if (empty($size) || $size < 1000) {
                    $warnings[] = Text::sprintf('JBS_PDC_VALIDATE_MEDIA_NO_SIZE', $media->studytitle ?: 'ID: ' . $media->id);
                }
            }

            // Check for missing MIME type
            $mimeType = $params->get('mime_type');
            if (empty($mimeType)) {
                $warnings[] = Text::sprintf('JBS_PDC_VALIDATE_MEDIA_NO_MIME', $media->studytitle ?: 'ID: ' . $media->id);
            }

            // Check for missing duration
            $hours = $params->get('media_hours', '00');
            $minutes = $params->get('media_minutes', '00');
            $seconds = $params->get('media_seconds', '00');
            if ($hours === '00' && $minutes === '00' && $seconds === '00') {
                $warnings[] = Text::sprintf('JBS_PDC_VALIDATE_MEDIA_NO_DURATION', $media->studytitle ?: 'ID: ' . $media->id);
            }

            // Check for missing filename
            if (empty($filename)) {
                $warnings[] = Text::sprintf('JBS_PDC_VALIDATE_MEDIA_NO_FILENAME', $media->studytitle ?: 'ID: ' . $media->id);
            }
        }

        return ['warnings' => $warnings];
    }

    /**
     * Validate an existing podcast XML file
     *
     * @param   string  $filePath  Path to the XML file
     *
     * @return array  Validation result
     *
     * @since 10.2.0
     */
    public function validatePodcastXml(string $filePath): array
    {
        $errors = [];
        $warnings = [];

        if (!is_file($filePath)) {
            $errors[] = Text::_('JBS_PDC_VALIDATE_XML_FILE_NOT_FOUND');
            return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
        }

        // Load and parse XML
        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($filePath);

        if ($xml === false) {
            $xmlErrors = libxml_get_errors();
            foreach ($xmlErrors as $error) {
                $errors[] = Text::sprintf('JBS_PDC_VALIDATE_XML_PARSE_ERROR', trim($error->message), $error->line);
            }
            libxml_clear_errors();
            return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
        }

        // Register iTunes namespace
        $namespaces = $xml->getNamespaces(true);
        $itunes = isset($namespaces['itunes']) ? $xml->channel->children($namespaces['itunes']) : null;

        // Check required RSS elements
        if (empty($xml->channel->title)) {
            $errors[] = Text::_('JBS_PDC_VALIDATE_XML_NO_TITLE');
        }

        if (empty($xml->channel->link)) {
            $errors[] = Text::_('JBS_PDC_VALIDATE_XML_NO_LINK');
        }

        if (empty($xml->channel->description)) {
            $errors[] = Text::_('JBS_PDC_VALIDATE_XML_NO_DESCRIPTION');
        }

        // Check required iTunes elements
        if ($itunes !== null) {
            if (empty($itunes->author)) {
                $warnings[] = Text::_('JBS_PDC_VALIDATE_XML_NO_ITUNES_AUTHOR');
            }

            if (empty($itunes->image)) {
                $warnings[] = Text::_('JBS_PDC_VALIDATE_XML_NO_ITUNES_IMAGE');
            }

            if (empty($itunes->category)) {
                $warnings[] = Text::_('JBS_PDC_VALIDATE_XML_NO_ITUNES_CATEGORY');
            }

            if (empty($itunes->explicit)) {
                $warnings[] = Text::_('JBS_PDC_VALIDATE_XML_NO_ITUNES_EXPLICIT');
            }
        } else {
            $warnings[] = Text::_('JBS_PDC_VALIDATE_XML_NO_ITUNES_NAMESPACE');
        }

        // Count episodes
        $episodeCount = count($xml->channel->item ?? []);
        if ($episodeCount === 0) {
            $errors[] = Text::_('JBS_PDC_VALIDATE_XML_NO_EPISODES');
        }

        // Check episodes for required elements
        $episodeWarnings = 0;
        foreach ($xml->channel->item ?? [] as $item) {
            if (empty($item->enclosure)) {
                $episodeWarnings++;
            }
        }

        if ($episodeWarnings > 0) {
            $warnings[] = Text::sprintf('JBS_PDC_VALIDATE_XML_EPISODES_MISSING_ENCLOSURE', $episodeWarnings);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'episode_count' => $episodeCount,
        ];
    }

    /**
     * Get a formatted validation report as HTML
     *
     * @param   array  $validationResults  Results from validateAllPodcasts()
     *
     * @return string  HTML formatted report
     *
     * @since 10.2.0
     */
    public function getValidationReport(array $validationResults): string
    {
        $html = '<div class="podcast-validation-report">';

        foreach ($validationResults as $result) {
            $statusClass = $result['ready'] ? 'success' : ($result['valid'] ? 'warning' : 'danger');
            $statusIcon = $result['ready'] ? '✓' : ($result['valid'] ? '⚠' : '✗');

            $html .= '<div class="card mb-3 border-' . $statusClass . '">';
            $html .= '<div class="card-header bg-' . $statusClass . ' text-white">';
            $html .= '<strong>' . $statusIcon . ' ' . htmlspecialchars($result['podcast_title']) . '</strong>';
            $html .= ' <span class="badge bg-light text-dark">' . $result['media_count'] . ' ' . Text::_('JBS_PDC_EPISODES') . '</span>';
            $html .= '</div>';
            $html .= '<div class="card-body">';

            if (!empty($result['errors'])) {
                $html .= '<div class="alert alert-danger mb-2"><strong>' . Text::_('JBS_PDC_ERRORS') . ':</strong><ul class="mb-0">';
                foreach ($result['errors'] as $error) {
                    $html .= '<li>' . htmlspecialchars($error) . '</li>';
                }
                $html .= '</ul></div>';
            }

            if (!empty($result['warnings'])) {
                $html .= '<div class="alert alert-warning mb-2"><strong>' . Text::_('JBS_PDC_WARNINGS') . ':</strong><ul class="mb-0">';
                foreach ($result['warnings'] as $warning) {
                    $html .= '<li>' . htmlspecialchars($warning) . '</li>';
                }
                $html .= '</ul></div>';
            }

            if (!empty($result['info'])) {
                $html .= '<div class="alert alert-info mb-0"><ul class="mb-0">';
                foreach ($result['info'] as $info) {
                    $html .= '<li>' . htmlspecialchars($info) . '</li>';
                }
                $html .= '</ul></div>';
            }

            if ($result['ready']) {
                $html .= '<div class="alert alert-success mb-0">' . Text::_('JBS_PDC_READY_TO_BUILD') . '</div>';
            }

            $html .= '</div></div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Fix missing durations for media files associated with podcasts
     *
     * @param   int|null  $podcastId  Optional podcast ID to limit to, or null for all podcasts
     *
     * @return array  Results with 'fixed', 'failed', 'skipped' counts and details
     *
     * @throws \Exception
     * @since 10.2.0
     */
    public function fixMediaDurations(?int $podcastId = null): array
    {
        $fixed = 0;
        $failed = 0;
        $skipped = 0;
        $details = [];

        $db = Factory::getContainer()->get('DatabaseDriver');

        // Get media files with missing duration
        $query = $db->getQuery(true);
        $query->select($db->quoteName(['mf.id', 'mf.params', 'mf.server_id', 's.studytitle']))
            ->from($db->quoteName('#__bsms_mediafiles', 'mf'))
            ->leftJoin($db->quoteName('#__bsms_studies', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('mf.study_id'))
            ->where($db->quoteName('mf.published') . ' = 1');

        if ($podcastId !== null) {
            $query->where('FIND_IN_SET(' . $podcastId . ', ' . $db->quoteName('mf.podcast_id') . ')');
        } else {
            $query->where($db->quoteName('mf.podcast_id') . ' != ' . $db->q(''));
        }

        $db->setQuery($query);
        $mediaFiles = $db->loadObjectList() ?: [];

        foreach ($mediaFiles as $media) {
            $params = new Registry($media->params);
            $title = $media->studytitle ?: 'ID: ' . $media->id;

            // Check if duration is already set
            $hours = $params->get('media_hours', '00');
            $minutes = $params->get('media_minutes', '00');
            $seconds = $params->get('media_seconds', '00');

            if ($hours !== '00' || $minutes !== '00' || $seconds !== '00') {
                $skipped++;
                continue;
            }

            // Get server path
            $serverQuery = $db->getQuery(true);
            $serverQuery->select($db->quoteName('params'))
                ->from($db->quoteName('#__bsms_servers'))
                ->where($db->quoteName('id') . ' = ' . (int) $media->server_id);
            $db->setQuery($serverQuery);
            $serverParams = new Registry($db->loadResult());
            $serverPath = $serverParams->get('path', '');

            // Build file path
            $filename = $params->get('filename');
            if (empty($filename)) {
                $failed++;
                $details[] = Text::sprintf('JBS_PDC_FIX_DURATION_NO_FILENAME', $title);
                continue;
            }

            // Check if this is a YouTube URL - try API first
            if ($this->isYouTubeUrl($filename)) {
                $videoId = $this->extractYouTubeVideoId($filename);
                $apiKey = $this->getYouTubeApiKey();

                if ($videoId && $apiKey) {
                    $durationSeconds = $this->getYouTubeDuration($videoId, $apiKey);

                    if ($durationSeconds > 0) {
                        // Success - save the duration
                        $duration = $this->formatTime($durationSeconds);
                        $params->set('media_hours', str_pad((string) $duration->hours, 2, '0', STR_PAD_LEFT));
                        $params->set('media_minutes', str_pad((string) $duration->minutes, 2, '0', STR_PAD_LEFT));
                        $params->set('media_seconds', str_pad((string) $duration->seconds, 2, '0', STR_PAD_LEFT));

                        $updateQuery = $db->getQuery(true);
                        $updateQuery->update($db->quoteName('#__bsms_mediafiles'))
                            ->set($db->quoteName('params') . ' = ' . $db->q($params->toString()))
                            ->where($db->quoteName('id') . ' = ' . (int) $media->id);
                        $db->setQuery($updateQuery);

                        try {
                            $db->execute();
                            $fixed++;
                            $durationStr = sprintf('%02d:%02d:%02d', $duration->hours, $duration->minutes, $duration->seconds);
                            $details[] = Text::sprintf('JBS_PDC_FIX_DURATION_YOUTUBE_SUCCESS', $title, $durationStr);
                        } catch (\Exception $e) {
                            $failed++;
                            $details[] = Text::sprintf('JBS_PDC_FIX_DURATION_DB_ERROR', $title, $e->getMessage());
                        }
                        continue;
                    } else {
                        $failed++;
                        $details[] = Text::sprintf('JBS_PDC_FIX_DURATION_YOUTUBE_API_FAILED', $title);
                        continue;
                    }
                } else {
                    // No API key configured
                    $skipped++;
                    $details[] = Text::sprintf('JBS_PDC_FIX_DURATION_YOUTUBE_NO_API', $title);
                    continue;
                }
            }

            // First check if filename is clearly a local path (skip external detection)
            // Local paths: /path/file.mp3, images/file.mp3, file.mp3 (no domain pattern)
            // External: www.example.com/file.mp3, example.com/path/file.mp3
            $isLocalFilename = str_starts_with($filename, '/')
                || preg_match('/^[a-z]:\\\\/i', $filename) // Windows path
                || preg_match('/^(images|media|files|modules|components|administrator|tmp|cache|logs)\//i', $filename)
                || !preg_match('/^(www\.)?[a-z0-9-]+\.[a-z]{2,}/i', $filename); // No domain pattern at start

            // Check if it's an external file - try FFprobe for remote URLs
            if (!$isLocalFilename && Cwmmedia::isExternal($filename)) {
                // Build full URL for remote file
                $remoteUrl = $filename;
                if (!str_contains($remoteUrl, '://')) {
                    $remoteUrl = 'https://' . ltrim($remoteUrl, '/');
                }

                // Try FFprobe for remote URL
                $durationSeconds = $this->getDurationWithFFprobe($remoteUrl);

                if ($durationSeconds > 0) {
                    // Success - save the duration
                    $duration = $this->formatTime($durationSeconds);
                    $params->set('media_hours', str_pad((string) $duration->hours, 2, '0', STR_PAD_LEFT));
                    $params->set('media_minutes', str_pad((string) $duration->minutes, 2, '0', STR_PAD_LEFT));
                    $params->set('media_seconds', str_pad((string) $duration->seconds, 2, '0', STR_PAD_LEFT));

                    $updateQuery = $db->getQuery(true);
                    $updateQuery->update($db->quoteName('#__bsms_mediafiles'))
                        ->set($db->quoteName('params') . ' = ' . $db->q($params->toString()))
                        ->where($db->quoteName('id') . ' = ' . (int) $media->id);
                    $db->setQuery($updateQuery);

                    try {
                        $db->execute();
                        $fixed++;
                        $durationStr = sprintf('%02d:%02d:%02d', $duration->hours, $duration->minutes, $duration->seconds);
                        $details[] = Text::sprintf('JBS_PDC_FIX_DURATION_SUCCESS_REMOTE', $title, $durationStr);
                    } catch (\Exception $e) {
                        $failed++;
                        $details[] = Text::sprintf('JBS_PDC_FIX_DURATION_DB_ERROR', $title, $e->getMessage());
                    }
                } else {
                    // FFprobe failed or not available
                    $ffprobe = $this->findFFprobe();
                    if ($ffprobe === null) {
                        $skipped++;
                        $details[] = Text::sprintf('JBS_PDC_FIX_DURATION_EXTERNAL_NO_FFPROBE', $title);
                    } else {
                        $failed++;
                        $details[] = Text::sprintf('JBS_PDC_FIX_DURATION_EXTERNAL_FAILED', $title, $filename);
                    }
                }
                continue;
            }

            // Build local file path
            $prefix = Uri::root();
            $nohttp = $this->removeHttp($prefix);
            $pathServer = Cwmhelper::mediaBuildUrl($serverPath, $filename, $params, false, false, true);
            $siteinfo = strpos($pathServer, $nohttp);

            if ($siteinfo !== false) {
                $localPath = JPATH_SITE . '/' . substr($pathServer, strlen($nohttp));
            } else {
                $localPath = $pathServer;
            }

            // Check if path looks like an external URL without protocol (e.g., www.example.org/...)
            // Skip if it looks like a local path (starts with common directories or is an absolute path)
            $isLocalPath = preg_match('/^(images|media|files|modules|components|administrator|tmp|cache|logs)\//i', $localPath)
                || str_starts_with($localPath, '/')
                || str_starts_with($localPath, JPATH_SITE)
                || preg_match('/^[a-z]:\\\\/i', $localPath); // Windows path

            if (!$isLocalPath && preg_match('/^(www\.)?[a-z0-9]([a-z0-9-]*[a-z0-9])?\.[a-z]{2,}(\/|$)/i', $localPath)) {
                // Build full URL for remote file
                $remoteUrl = 'https://' . ltrim($localPath, '/');

                // Try FFprobe for remote URL
                $durationSeconds = $this->getDurationWithFFprobe($remoteUrl);

                if ($durationSeconds > 0) {
                    // Success - save the duration
                    $duration = $this->formatTime($durationSeconds);
                    $params->set('media_hours', str_pad((string) $duration->hours, 2, '0', STR_PAD_LEFT));
                    $params->set('media_minutes', str_pad((string) $duration->minutes, 2, '0', STR_PAD_LEFT));
                    $params->set('media_seconds', str_pad((string) $duration->seconds, 2, '0', STR_PAD_LEFT));

                    $updateQuery = $db->getQuery(true);
                    $updateQuery->update($db->quoteName('#__bsms_mediafiles'))
                        ->set($db->quoteName('params') . ' = ' . $db->q($params->toString()))
                        ->where($db->quoteName('id') . ' = ' . (int) $media->id);
                    $db->setQuery($updateQuery);

                    try {
                        $db->execute();
                        $fixed++;
                        $durationStr = sprintf('%02d:%02d:%02d', $duration->hours, $duration->minutes, $duration->seconds);
                        $details[] = Text::sprintf('JBS_PDC_FIX_DURATION_SUCCESS_REMOTE', $title, $durationStr);
                    } catch (\Exception $e) {
                        $failed++;
                        $details[] = Text::sprintf('JBS_PDC_FIX_DURATION_DB_ERROR', $title, $e->getMessage());
                    }
                } else {
                    // FFprobe failed or not available
                    $ffprobe = $this->findFFprobe();
                    if ($ffprobe === null) {
                        $skipped++;
                        $details[] = Text::sprintf('JBS_PDC_FIX_DURATION_EXTERNAL_NO_FFPROBE', $title);
                    } else {
                        $failed++;
                        $details[] = Text::sprintf('JBS_PDC_FIX_DURATION_EXTERNAL_FAILED', $title, $localPath);
                    }
                }
                continue;
            }

            // Check if file exists
            if (!is_file($localPath)) {
                $failed++;
                $details[] = Text::sprintf('JBS_PDC_FIX_DURATION_FILE_NOT_FOUND', $title, $localPath);
                continue;
            }

            // Get duration using multi-format support
            $durationSeconds = $this->getMediaDuration($localPath);

            if ($durationSeconds <= 0) {
                $failed++;
                $details[] = Text::sprintf('JBS_PDC_FIX_DURATION_COULD_NOT_READ', $title);
                continue;
            }

            // Format and save
            $duration = $this->formatTime($durationSeconds);

            $params->set('media_hours', str_pad((string) $duration->hours, 2, '0', STR_PAD_LEFT));
            $params->set('media_minutes', str_pad((string) $duration->minutes, 2, '0', STR_PAD_LEFT));
            $params->set('media_seconds', str_pad((string) $duration->seconds, 2, '0', STR_PAD_LEFT));

            // Update the database
            $updateQuery = $db->getQuery(true);
            $updateQuery->update($db->quoteName('#__bsms_mediafiles'))
                ->set($db->quoteName('params') . ' = ' . $db->q($params->toString()))
                ->where($db->quoteName('id') . ' = ' . (int) $media->id);
            $db->setQuery($updateQuery);

            try {
                $db->execute();
                $fixed++;
                $durationStr = sprintf('%02d:%02d:%02d', $duration->hours, $duration->minutes, $duration->seconds);
                $details[] = Text::sprintf('JBS_PDC_FIX_DURATION_SUCCESS', $title, $durationStr);
            } catch (\Exception $e) {
                $failed++;
                $details[] = Text::sprintf('JBS_PDC_FIX_DURATION_DB_ERROR', $title, $e->getMessage());
            }
        }

        return [
            'fixed' => $fixed,
            'failed' => $failed,
            'skipped' => $skipped,
            'total' => count($mediaFiles),
            'details' => $details,
        ];
    }

    /**
     * Get list of media files that need duration fixing.
     * Used for AJAX batch processing.
     *
     * @param   int|null  $podcastId  Optional podcast ID to limit to
     *
     * @return array  Array of media file IDs and titles
     *
     * @since 10.2.0
     */
    public function getMediaFilesNeedingDuration(?int $podcastId = null): array
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true);
        $query->select($db->quoteName(['mf.id', 'mf.params', 's.studytitle']))
            ->from($db->quoteName('#__bsms_mediafiles', 'mf'))
            ->leftJoin($db->quoteName('#__bsms_studies', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('mf.study_id'))
            ->where($db->quoteName('mf.published') . ' = 1');

        if ($podcastId !== null) {
            $query->where('FIND_IN_SET(' . $podcastId . ', ' . $db->quoteName('mf.podcast_id') . ')');
        } else {
            $query->where($db->quoteName('mf.podcast_id') . ' != ' . $db->q(''));
        }

        $db->setQuery($query);
        $mediaFiles = $db->loadObjectList() ?: [];

        $result = [];

        foreach ($mediaFiles as $media) {
            $params = new Registry($media->params);

            // Check if duration is already set
            $hours = $params->get('media_hours', '00');
            $minutes = $params->get('media_minutes', '00');
            $seconds = $params->get('media_seconds', '00');

            if ($hours === '00' && $minutes === '00' && $seconds === '00') {
                $result[] = [
                    'id' => (int) $media->id,
                    'title' => $media->studytitle ?: 'ID: ' . $media->id,
                ];
            }
        }

        return $result;
    }

    /**
     * Fix duration for a single media file.
     * Used for AJAX batch processing.
     *
     * @param   int  $mediaId  The media file ID to process
     *
     * @return array  Result with 'status', 'message', 'duration'
     *
     * @since 10.2.0
     */
    public function fixSingleMediaDuration(int $mediaId): array
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        // Get the media file
        $query = $db->getQuery(true);
        $query->select($db->quoteName(['mf.id', 'mf.params', 'mf.server_id', 's.studytitle']))
            ->from($db->quoteName('#__bsms_mediafiles', 'mf'))
            ->leftJoin($db->quoteName('#__bsms_studies', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('mf.study_id'))
            ->where($db->quoteName('mf.id') . ' = ' . $mediaId);

        $db->setQuery($query);
        $media = $db->loadObject();

        if (!$media) {
            return [
                'status' => 'error',
                'message' => Text::sprintf('JBS_PDC_FIX_DURATION_FILE_NOT_FOUND', 'ID: ' . $mediaId, 'Database'),
                'type' => 'failed',
            ];
        }

        $params = new Registry($media->params);
        $title = $media->studytitle ?: 'ID: ' . $media->id;

        // Get server path
        $serverQuery = $db->getQuery(true);
        $serverQuery->select($db->quoteName('params'))
            ->from($db->quoteName('#__bsms_servers'))
            ->where($db->quoteName('id') . ' = ' . (int) $media->server_id);
        $db->setQuery($serverQuery);
        $serverParams = new Registry($db->loadResult());
        $serverPath = $serverParams->get('path', '');

        // Build file path
        $filename = $params->get('filename');
        if (empty($filename)) {
            return [
                'status' => 'error',
                'message' => Text::sprintf('JBS_PDC_FIX_DURATION_NO_FILENAME', $title),
                'type' => 'failed',
            ];
        }

        // Check if this is a YouTube URL - try API first
        if ($this->isYouTubeUrl($filename)) {
            $videoId = $this->extractYouTubeVideoId($filename);
            $apiKey = $this->getYouTubeApiKey();

            if ($videoId && $apiKey) {
                $durationSeconds = $this->getYouTubeDuration($videoId, $apiKey);

                if ($durationSeconds > 0) {
                    return $this->saveDuration($media->id, $params, $title, $durationSeconds, $db, true);
                }

                return [
                    'status' => 'error',
                    'message' => Text::sprintf('JBS_PDC_FIX_DURATION_YOUTUBE_API_FAILED', $title),
                    'type' => 'failed',
                ];
            }

            return [
                'status' => 'skipped',
                'message' => Text::sprintf('JBS_PDC_FIX_DURATION_YOUTUBE_NO_API', $title),
                'type' => 'skipped',
            ];
        }

        // First check if filename is clearly a local path (skip external detection)
        // Local paths: /path/file.mp3, images/file.mp3, file.mp3 (no domain pattern)
        // External: www.example.com/file.mp3, example.com/path/file.mp3
        $isLocalFilename = str_starts_with($filename, '/')
            || preg_match('/^[a-z]:\\\\/i', $filename) // Windows path
            || preg_match('/^(images|media|files|modules|components|administrator|tmp|cache|logs)\//i', $filename)
            || !preg_match('/^(www\.)?[a-z0-9-]+\.[a-z]{2,}/i', $filename); // No domain pattern at start

        // Check if it's an external file - try FFprobe for remote URLs
        if (!$isLocalFilename && Cwmmedia::isExternal($filename)) {
            return $this->processRemoteFile($media->id, $params, $title, $filename, $db);
        }

        // Build local file path
        $prefix = Uri::root();
        $nohttp = $this->removeHttp($prefix);
        $pathServer = Cwmhelper::mediaBuildUrl($serverPath, $filename, $params, false, false, true);
        $siteinfo = strpos($pathServer, $nohttp);

        if ($siteinfo !== false) {
            $localPath = JPATH_SITE . '/' . substr($pathServer, strlen($nohttp));
        } else {
            $localPath = $pathServer;
        }

        // Check if path looks like an external URL without protocol
        // Skip if it looks like a local path (starts with common directories or is an absolute path)
        $isLocalPath = preg_match('/^(images|media|files|modules|components|administrator|tmp|cache|logs)\//i', $localPath)
            || str_starts_with($localPath, '/')
            || str_starts_with($localPath, JPATH_SITE)
            || preg_match('/^[a-z]:\\\\/i', $localPath); // Windows path

        if (!$isLocalPath && preg_match('/^(www\.)?[a-z0-9]([a-z0-9-]*[a-z0-9])?\.[a-z]{2,}(\/|$)/i', $localPath)) {
            return $this->processRemoteFile($media->id, $params, $title, $localPath, $db);
        }

        // Check if file exists
        if (!is_file($localPath)) {
            return [
                'status' => 'error',
                'message' => Text::sprintf('JBS_PDC_FIX_DURATION_FILE_NOT_FOUND', $title, $localPath),
                'type' => 'failed',
            ];
        }

        // Get duration
        $durationSeconds = $this->getMediaDuration($localPath);

        if ($durationSeconds <= 0) {
            return [
                'status' => 'error',
                'message' => Text::sprintf('JBS_PDC_FIX_DURATION_COULD_NOT_READ', $title),
                'type' => 'failed',
            ];
        }

        // Save duration
        return $this->saveDuration($media->id, $params, $title, $durationSeconds, $db, false);
    }

    /**
     * Process a remote file for duration detection.
     *
     * @param   int       $mediaId   Media file ID
     * @param   Registry  $params    Media params
     * @param   string    $title     Media title
     * @param   string    $path      File path/URL
     * @param   object    $db        Database driver
     *
     * @return array  Result array
     *
     * @since 10.2.0
     */
    protected function processRemoteFile(int $mediaId, Registry $params, string $title, string $path, object $db): array
    {
        // Build full URL
        $remoteUrl = $path;
        if (!str_contains($remoteUrl, '://')) {
            $remoteUrl = 'https://' . ltrim($remoteUrl, '/');
        }

        // Try FFprobe for remote URL
        $durationSeconds = $this->getDurationWithFFprobe($remoteUrl);

        if ($durationSeconds > 0) {
            return $this->saveDuration($mediaId, $params, $title, $durationSeconds, $db, true);
        }

        // FFprobe failed or not available
        $ffprobe = $this->findFFprobe();
        if ($ffprobe === null) {
            return [
                'status' => 'skipped',
                'message' => Text::sprintf('JBS_PDC_FIX_DURATION_EXTERNAL_NO_FFPROBE', $title),
                'type' => 'skipped',
            ];
        }

        return [
            'status' => 'error',
            'message' => Text::sprintf('JBS_PDC_FIX_DURATION_EXTERNAL_FAILED', $title, $path),
            'type' => 'failed',
        ];
    }

    /**
     * Save duration to a media file.
     *
     * @param   int       $mediaId          Media file ID
     * @param   Registry  $params           Media params
     * @param   string    $title            Media title
     * @param   int       $durationSeconds  Duration in seconds
     * @param   object    $db               Database driver
     * @param   bool      $isRemote         Whether this is a remote file
     *
     * @return array  Result array
     *
     * @since 10.2.0
     */
    protected function saveDuration(int $mediaId, Registry $params, string $title, int $durationSeconds, object $db, bool $isRemote): array
    {
        $duration = $this->formatTime($durationSeconds);

        $params->set('media_hours', str_pad((string) $duration->hours, 2, '0', STR_PAD_LEFT));
        $params->set('media_minutes', str_pad((string) $duration->minutes, 2, '0', STR_PAD_LEFT));
        $params->set('media_seconds', str_pad((string) $duration->seconds, 2, '0', STR_PAD_LEFT));

        $updateQuery = $db->getQuery(true);
        $updateQuery->update($db->quoteName('#__bsms_mediafiles'))
            ->set($db->quoteName('params') . ' = ' . $db->q($params->toString()))
            ->where($db->quoteName('id') . ' = ' . $mediaId);
        $db->setQuery($updateQuery);

        try {
            $db->execute();
            $durationStr = sprintf('%02d:%02d:%02d', $duration->hours, $duration->minutes, $duration->seconds);
            $langKey = $isRemote ? 'JBS_PDC_FIX_DURATION_SUCCESS_REMOTE' : 'JBS_PDC_FIX_DURATION_SUCCESS';

            return [
                'status' => 'success',
                'message' => Text::sprintf($langKey, $title, $durationStr),
                'duration' => $durationStr,
                'type' => 'fixed',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => Text::sprintf('JBS_PDC_FIX_DURATION_DB_ERROR', $title, $e->getMessage()),
                'type' => 'failed',
            ];
        }
    }

    /**
     * Get media files that need any metadata fixed (size, mime_type, or duration).
     * Used for AJAX batch processing.
     *
     * @param   int|null  $podcastId  Optional podcast ID to filter by
     *
     * @return array  Array of files needing metadata fix with id, title, and missing fields
     *
     * @since 10.2.0
     */
    public function getMediaFilesNeedingMetadata(?int $podcastId = null): array
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true);
        $query->select($db->quoteName(['mf.id', 'mf.params', 's.studytitle']))
            ->from($db->quoteName('#__bsms_mediafiles', 'mf'))
            ->leftJoin($db->quoteName('#__bsms_studies', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('mf.study_id'))
            ->where($db->quoteName('mf.published') . ' = 1');

        if ($podcastId !== null) {
            $query->where('FIND_IN_SET(' . $podcastId . ', ' . $db->quoteName('mf.podcast_id') . ')');
        } else {
            $query->where($db->quoteName('mf.podcast_id') . ' != ' . $db->q(''));
        }

        $db->setQuery($query);
        $mediaFiles = $db->loadObjectList() ?: [];

        $result = [];

        foreach ($mediaFiles as $media) {
            $params = new Registry($media->params);
            $missing = [];
            $filename = $params->get('filename');
            $isYouTube = $filename && $this->isYouTubeUrl($filename);

            // Check for missing file size (skip for YouTube - no file size available)
            if (!$isYouTube) {
                $size = $params->get('size', 0);
                if (empty($size) || $size < 1000) {
                    $missing[] = 'size';
                }
            }

            // Check for missing MIME type
            $mimeType = $params->get('mime_type');
            if (empty($mimeType)) {
                $missing[] = 'mime_type';
            }

            // Check for missing duration
            $hours = $params->get('media_hours', '00');
            $minutes = $params->get('media_minutes', '00');
            $seconds = $params->get('media_seconds', '00');
            if ($hours === '00' && $minutes === '00' && $seconds === '00') {
                $missing[] = 'duration';
            }

            if (!empty($missing)) {
                $result[] = [
                    'id' => (int) $media->id,
                    'title' => $media->studytitle ?: 'ID: ' . $media->id,
                    'missing' => $missing,
                ];
            }
        }

        return $result;
    }

    /**
     * Fix all missing metadata for a single media file.
     * Handles size, mime_type, and duration in one pass.
     *
     * @param   int  $mediaId  The media file ID to process
     *
     * @return array  Result with 'status', 'message', 'fixed' array
     *
     * @since 10.2.0
     */
    public function fixSingleMediaMetadata(int $mediaId): array
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        // Get the media file
        $query = $db->getQuery(true);
        $query->select($db->quoteName(['mf.id', 'mf.params', 'mf.server_id', 's.studytitle']))
            ->from($db->quoteName('#__bsms_mediafiles', 'mf'))
            ->leftJoin($db->quoteName('#__bsms_studies', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('mf.study_id'))
            ->where($db->quoteName('mf.id') . ' = ' . $mediaId);

        $db->setQuery($query);
        $media = $db->loadObject();

        if (!$media) {
            return [
                'status' => 'error',
                'message' => Text::sprintf('JBS_PDC_FIX_METADATA_FILE_NOT_FOUND', 'ID: ' . $mediaId),
                'type' => 'failed',
            ];
        }

        $params = new Registry($media->params);
        $title = $media->studytitle ?: 'ID: ' . $media->id;

        // Determine what needs to be fixed
        $needsSize = empty($params->get('size', 0)) || $params->get('size', 0) < 1000;
        $needsMimeType = empty($params->get('mime_type'));
        $hours = $params->get('media_hours', '00');
        $minutes = $params->get('media_minutes', '00');
        $seconds = $params->get('media_seconds', '00');
        $needsDuration = ($hours === '00' && $minutes === '00' && $seconds === '00');

        if (!$needsSize && !$needsMimeType && !$needsDuration) {
            return [
                'status' => 'skipped',
                'message' => Text::sprintf('JBS_PDC_FIX_METADATA_ALREADY_SET', $title),
                'type' => 'skipped',
            ];
        }

        // Get server path
        $serverQuery = $db->getQuery(true);
        $serverQuery->select($db->quoteName('params'))
            ->from($db->quoteName('#__bsms_servers'))
            ->where($db->quoteName('id') . ' = ' . (int) $media->server_id);
        $db->setQuery($serverQuery);
        $serverParams = new Registry($db->loadResult());
        $serverPath = $serverParams->get('path', '');

        // Build file path
        $filename = $params->get('filename');
        if (empty($filename)) {
            return [
                'status' => 'error',
                'message' => Text::sprintf('JBS_PDC_FIX_METADATA_NO_FILENAME', $title),
                'type' => 'failed',
            ];
        }

        // Check if this is a YouTube URL
        if ($this->isYouTubeUrl($filename)) {
            return $this->fixYouTubeMetadata($media->id, $params, $title, $needsDuration, $db);
        }

        // Determine if local or remote
        $isLocalFilename = str_starts_with($filename, '/')
            || preg_match('/^[a-z]:\\\\/i', $filename)
            || preg_match('/^(images|media|files|modules|components|administrator|tmp|cache|logs)\//i', $filename)
            || !preg_match('/^(www\.)?[a-z0-9-]+\.[a-z]{2,}/i', $filename);

        if (!$isLocalFilename && Cwmmedia::isExternal($filename)) {
            return $this->fixRemoteMetadata($media->id, $params, $title, $filename, $needsSize, $needsMimeType, $needsDuration, $db);
        }

        // Build local file path
        $prefix = Uri::root();
        $nohttp = $this->removeHttp($prefix);
        $pathServer = Cwmhelper::mediaBuildUrl($serverPath, $filename, $params, false, false, true);
        $siteinfo = strpos($pathServer, $nohttp);

        if ($siteinfo !== false) {
            $localPath = JPATH_SITE . '/' . substr($pathServer, strlen($nohttp));
        } else {
            $localPath = $pathServer;
        }

        // Check if path looks like an external URL
        $isLocalPath = preg_match('/^(images|media|files|modules|components|administrator|tmp|cache|logs)\//i', $localPath)
            || str_starts_with($localPath, '/')
            || str_starts_with($localPath, JPATH_SITE)
            || preg_match('/^[a-z]:\\\\/i', $localPath);

        if (!$isLocalPath && preg_match('/^(www\.)?[a-z0-9]([a-z0-9-]*[a-z0-9])?\.[a-z]{2,}(\/|$)/i', $localPath)) {
            return $this->fixRemoteMetadata($media->id, $params, $title, $localPath, $needsSize, $needsMimeType, $needsDuration, $db);
        }

        // Check if file exists
        if (!is_file($localPath)) {
            return [
                'status' => 'error',
                'message' => Text::sprintf('JBS_PDC_FIX_METADATA_FILE_NOT_FOUND', $title),
                'type' => 'failed',
            ];
        }

        // Fix local file metadata
        return $this->fixLocalMetadata($media->id, $params, $title, $localPath, $needsSize, $needsMimeType, $needsDuration, $db);
    }

    /**
     * Fix metadata for YouTube videos.
     *
     * @param   int       $mediaId       Media file ID
     * @param   Registry  $params        Media params
     * @param   string    $title         Media title
     * @param   bool      $needsDuration Whether duration needs fixing
     * @param   object    $db            Database driver
     *
     * @return array  Result array
     *
     * @since 10.2.0
     */
    protected function fixYouTubeMetadata(int $mediaId, Registry $params, string $title, bool $needsDuration, object $db): array
    {
        $fixed = [];

        // YouTube videos - set mime type and size to defaults if missing
        if (empty($params->get('mime_type'))) {
            $params->set('mime_type', 'video/mp4');
            $fixed[] = 'mime_type';
        }

        if (empty($params->get('size', 0)) || $params->get('size', 0) < 1000) {
            // YouTube doesn't provide file size, use a reasonable default
            $params->set('size', 0);
        }

        // Get duration via YouTube API if needed
        if ($needsDuration) {
            $videoId = $this->extractYouTubeVideoId($params->get('filename'));
            $apiKey = $this->getYouTubeApiKey();

            if ($videoId && $apiKey) {
                $durationSeconds = $this->getYouTubeDuration($videoId, $apiKey);

                if ($durationSeconds > 0) {
                    $duration = $this->formatTime($durationSeconds);
                    $params->set('media_hours', str_pad((string) $duration->hours, 2, '0', STR_PAD_LEFT));
                    $params->set('media_minutes', str_pad((string) $duration->minutes, 2, '0', STR_PAD_LEFT));
                    $params->set('media_seconds', str_pad((string) $duration->seconds, 2, '0', STR_PAD_LEFT));
                    $fixed[] = 'duration';
                }
            }

            if (!in_array('duration', $fixed)) {
                if (!$apiKey) {
                    return [
                        'status' => 'skipped',
                        'message' => Text::sprintf('JBS_PDC_FIX_METADATA_YOUTUBE_NO_API', $title),
                        'type' => 'skipped',
                    ];
                }

                return [
                    'status' => 'error',
                    'message' => Text::sprintf('JBS_PDC_FIX_METADATA_YOUTUBE_API_FAILED', $title),
                    'type' => 'failed',
                ];
            }
        }

        // Save changes
        return $this->saveMetadata($mediaId, $params, $title, $fixed, $db, true);
    }

    /**
     * Fix metadata for remote/external files.
     *
     * @param   int       $mediaId       Media file ID
     * @param   Registry  $params        Media params
     * @param   string    $title         Media title
     * @param   string    $path          File path/URL
     * @param   bool      $needsSize     Whether size needs fixing
     * @param   bool      $needsMimeType Whether MIME type needs fixing
     * @param   bool      $needsDuration Whether duration needs fixing
     * @param   object    $db            Database driver
     *
     * @return array  Result array
     *
     * @since 10.2.0
     */
    protected function fixRemoteMetadata(int $mediaId, Registry $params, string $title, string $path, bool $needsSize, bool $needsMimeType, bool $needsDuration, object $db): array
    {
        $fixed = [];

        // Build full URL
        $remoteUrl = $path;
        if (!str_contains($remoteUrl, '://')) {
            $remoteUrl = 'https://' . ltrim($remoteUrl, '/');
        }

        // Get HTTP headers for size and mime type
        if ($needsSize || $needsMimeType) {
            $headers = $this->getRemoteFileHeaders($remoteUrl);

            if ($headers) {
                if ($needsSize && isset($headers['content-length'])) {
                    $params->set('size', (int) $headers['content-length']);
                    $fixed[] = 'size';
                }

                if ($needsMimeType && isset($headers['content-type'])) {
                    // Extract just the MIME type, removing any charset info
                    $mimeType = $headers['content-type'];
                    if (str_contains($mimeType, ';')) {
                        $mimeType = trim(explode(';', $mimeType)[0]);
                    }
                    $params->set('mime_type', $mimeType);
                    $fixed[] = 'mime_type';
                }
            }
        }

        // Get duration via FFprobe if needed
        if ($needsDuration) {
            $durationSeconds = $this->getDurationWithFFprobe($remoteUrl);

            if ($durationSeconds > 0) {
                $duration = $this->formatTime($durationSeconds);
                $params->set('media_hours', str_pad((string) $duration->hours, 2, '0', STR_PAD_LEFT));
                $params->set('media_minutes', str_pad((string) $duration->minutes, 2, '0', STR_PAD_LEFT));
                $params->set('media_seconds', str_pad((string) $duration->seconds, 2, '0', STR_PAD_LEFT));
                $fixed[] = 'duration';
            }
        }

        if (empty($fixed)) {
            $ffprobe = $this->findFFprobe();

            return [
                'status' => 'skipped',
                'message' => Text::sprintf(
                    $ffprobe ? 'JBS_PDC_FIX_METADATA_EXTERNAL_FAILED' : 'JBS_PDC_FIX_METADATA_EXTERNAL_NO_FFPROBE',
                    $title
                ),
                'type' => 'skipped',
            ];
        }

        return $this->saveMetadata($mediaId, $params, $title, $fixed, $db, true);
    }

    /**
     * Fix metadata for local files.
     *
     * @param   int       $mediaId       Media file ID
     * @param   Registry  $params        Media params
     * @param   string    $title         Media title
     * @param   string    $localPath     Local file path
     * @param   bool      $needsSize     Whether size needs fixing
     * @param   bool      $needsMimeType Whether MIME type needs fixing
     * @param   bool      $needsDuration Whether duration needs fixing
     * @param   object    $db            Database driver
     *
     * @return array  Result array
     *
     * @since 10.2.0
     */
    protected function fixLocalMetadata(int $mediaId, Registry $params, string $title, string $localPath, bool $needsSize, bool $needsMimeType, bool $needsDuration, object $db): array
    {
        $fixed = [];

        // Get file size
        if ($needsSize) {
            $size = filesize($localPath);
            if ($size !== false && $size > 0) {
                $params->set('size', $size);
                $fixed[] = 'size';
            }
        }

        // Get MIME type
        if ($needsMimeType) {
            $mimeType = $this->detectMimeType($localPath);
            if ($mimeType) {
                $params->set('mime_type', $mimeType);
                $fixed[] = 'mime_type';
            }
        }

        // Get duration
        if ($needsDuration) {
            $durationSeconds = $this->getMediaDuration($localPath);
            if ($durationSeconds > 0) {
                $duration = $this->formatTime($durationSeconds);
                $params->set('media_hours', str_pad((string) $duration->hours, 2, '0', STR_PAD_LEFT));
                $params->set('media_minutes', str_pad((string) $duration->minutes, 2, '0', STR_PAD_LEFT));
                $params->set('media_seconds', str_pad((string) $duration->seconds, 2, '0', STR_PAD_LEFT));
                $fixed[] = 'duration';
            }
        }

        if (empty($fixed)) {
            return [
                'status' => 'error',
                'message' => Text::sprintf('JBS_PDC_FIX_METADATA_COULD_NOT_READ', $title),
                'type' => 'failed',
            ];
        }

        return $this->saveMetadata($mediaId, $params, $title, $fixed, $db, false);
    }

    /**
     * Save updated metadata to database.
     *
     * @param   int       $mediaId   Media file ID
     * @param   Registry  $params    Updated params
     * @param   string    $title     Media title
     * @param   array     $fixed     Array of fixed fields
     * @param   object    $db        Database driver
     * @param   bool      $isRemote  Whether this is a remote file
     *
     * @return array  Result array
     *
     * @since 10.2.0
     */
    protected function saveMetadata(int $mediaId, Registry $params, string $title, array $fixed, object $db, bool $isRemote): array
    {
        $updateQuery = $db->getQuery(true);
        $updateQuery->update($db->quoteName('#__bsms_mediafiles'))
            ->set($db->quoteName('params') . ' = ' . $db->q($params->toString()))
            ->where($db->quoteName('id') . ' = ' . $mediaId);
        $db->setQuery($updateQuery);

        try {
            $db->execute();

            // Build success message
            $fixedLabels = [];
            if (in_array('size', $fixed)) {
                $sizeFormatted = $this->formatFileSize($params->get('size', 0));
                $fixedLabels[] = Text::sprintf('JBS_PDC_FIX_METADATA_SIZE_VALUE', $sizeFormatted);
            }
            if (in_array('mime_type', $fixed)) {
                $fixedLabels[] = Text::sprintf('JBS_PDC_FIX_METADATA_MIME_VALUE', $params->get('mime_type'));
            }
            if (in_array('duration', $fixed)) {
                $durationStr = sprintf(
                    '%s:%s:%s',
                    $params->get('media_hours', '00'),
                    $params->get('media_minutes', '00'),
                    $params->get('media_seconds', '00')
                );
                $fixedLabels[] = Text::sprintf('JBS_PDC_FIX_METADATA_DURATION_VALUE', $durationStr);
            }

            $langKey = $isRemote ? 'JBS_PDC_FIX_METADATA_SUCCESS_REMOTE' : 'JBS_PDC_FIX_METADATA_SUCCESS';

            return [
                'status' => 'success',
                'message' => Text::sprintf($langKey, $title, implode(', ', $fixedLabels)),
                'fixed' => $fixed,
                'type' => 'fixed',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => Text::sprintf('JBS_PDC_FIX_METADATA_DB_ERROR', $title, $e->getMessage()),
                'type' => 'failed',
            ];
        }
    }

    /**
     * Get HTTP headers from a remote URL.
     *
     * @param   string  $url  The URL to check
     *
     * @return array|null  Array of headers or null on failure
     *
     * @since 10.2.0
     */
    protected function getRemoteFileHeaders(string $url): ?array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Proclaim Podcast/1.0)');

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 400 || $response === false) {
            return null;
        }

        $headers = [];
        $headerLines = explode("\r\n", $response);

        foreach ($headerLines as $line) {
            if (str_contains($line, ':')) {
                [$key, $value] = explode(':', $line, 2);
                $headers[strtolower(trim($key))] = trim($value);
            }
        }

        return $headers;
    }

    /**
     * Detect MIME type of a file.
     *
     * @param   string  $filepath  Path to the file
     *
     * @return string|null  MIME type or null if unable to detect
     *
     * @since 10.2.0
     */
    protected function detectMimeType(string $filepath): ?string
    {
        // Try mime_content_type first
        if (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($filepath);
            if ($mimeType && $mimeType !== 'application/octet-stream') {
                return $mimeType;
            }
        }

        // Try finfo
        if (class_exists('finfo')) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($filepath);
            if ($mimeType && $mimeType !== 'application/octet-stream') {
                return $mimeType;
            }
        }

        // Fall back to extension-based detection
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'mp3' => 'audio/mpeg',
            'mp4' => 'video/mp4',
            'm4a' => 'audio/mp4',
            'm4v' => 'video/mp4',
            'ogg' => 'audio/ogg',
            'oga' => 'audio/ogg',
            'ogv' => 'video/ogg',
            'wav' => 'audio/wav',
            'webm' => 'video/webm',
            'flac' => 'audio/flac',
            'aac' => 'audio/aac',
            'wma' => 'audio/x-ms-wma',
            'wmv' => 'video/x-ms-wmv',
            'avi' => 'video/x-msvideo',
            'mov' => 'video/quicktime',
            'mkv' => 'video/x-matroska',
            'pdf' => 'application/pdf',
        ];

        return $mimeTypes[$extension] ?? null;
    }

    /**
     * Format file size in human-readable format.
     *
     * @param   int  $bytes  Size in bytes
     *
     * @return string  Formatted size
     *
     * @since 10.2.0
     */
    protected function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;
        $size = (float) $bytes;

        while ($size >= 1024 && $index < count($units) - 1) {
            $size /= 1024;
            $index++;
        }

        return round($size, 2) . ' ' . $units[$index];
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
     * Get duration of any supported media file
     *
     * Uses a fallback chain:
     * 1. FFprobe (if available) - supports ALL formats
     * 2. Native PHP parsers - M4A/MP4, WAV, OGG
     * 3. getID3 library (if installed)
     * 4. MP3 frame parser (original method)
     *
     * @param   string  $filename  File path to the media file
     *
     * @return int  Duration in seconds, or 0 if unable to determine
     *
     * @since 10.2.0
     */
    public function getMediaDuration(string $filename): int
    {
        if (!is_file($filename)) {
            return 0;
        }

        // Get file extension
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Try FFprobe first (most reliable, supports all formats)
        $duration = $this->getDurationWithFFprobe($filename);
        if ($duration > 0) {
            return $duration;
        }

        // Try native parsers based on file type
        switch ($extension) {
            case 'm4a':
            case 'mp4':
            case 'm4v':
            case 'm4b':
            case 'aac':
                $duration = $this->getM4aDuration($filename);
                if ($duration > 0) {
                    return $duration;
                }
                break;

            case 'wav':
                $duration = $this->getWavDuration($filename);
                if ($duration > 0) {
                    return $duration;
                }
                break;

            case 'ogg':
            case 'oga':
            case 'ogv':
                $duration = $this->getOggDuration($filename);
                if ($duration > 0) {
                    return $duration;
                }
                break;
        }

        // Try getID3 library if available
        $duration = $this->getDurationWithGetID3($filename);
        if ($duration > 0) {
            return $duration;
        }

        // Fall back to MP3 parser (works for MP3 files)
        return $this->getMp3Duration($filename);
    }

    /**
     * Check which duration detection methods are available
     *
     * @return array  Array with method names as keys and availability as boolean values
     *
     * @since 10.2.0
     */
    public function getAvailableDurationMethods(): array
    {
        // Check YouTube API availability (may fail in test environment)
        $youtubeApiAvailable = false;

        try {
            $youtubeApiAvailable = $this->getYouTubeApiKey() !== null;
        } catch (\Throwable $e) {
            // Ignore - Joomla not available (test environment)
        }

        return [
            'ffprobe' => $this->findFFprobe() !== null,
            'native_m4a' => true,  // Always available (PHP)
            'native_wav' => true,  // Always available (PHP)
            'native_ogg' => true,  // Always available (PHP)
            'getid3' => class_exists('getID3') || $this->checkGetID3Available(),
            'mp3_parser' => true,  // Always available (PHP)
            'youtube_api' => $youtubeApiAvailable,
        ];
    }

    /**
     * Check if getID3 library is available
     *
     * @return bool
     *
     * @since 10.2.0
     */
    protected function checkGetID3Available(): bool
    {
        $paths = [
            JPATH_LIBRARIES . '/getid3/getid3.php',
            JPATH_ROOT . '/libraries/getid3/getid3.php',
            JPATH_ROOT . '/vendor/james-heinrich/getid3/getid3/getid3.php',
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get supported file extensions for duration detection
     *
     * @return array  Array of supported extensions
     *
     * @since 10.2.0
     */
    public function getSupportedDurationFormats(): array
    {
        $formats = ['mp3']; // Always supported

        // Native parsers
        $formats = array_merge($formats, ['m4a', 'mp4', 'm4v', 'm4b', 'aac', 'wav', 'ogg', 'oga', 'ogv']);

        // FFprobe supports everything
        if ($this->findFFprobe() !== null) {
            $formats = array_merge($formats, ['flac', 'wma', 'webm', 'mkv', 'avi', 'mov', 'wmv', 'flv']);
        }

        return array_unique($formats);
    }

    /**
     * Get duration using FFprobe (supports all formats)
     *
     * @param   string  $filename  File path
     *
     * @return int  Duration in seconds or 0 if FFprobe not available
     *
     * @since 10.2.0
     */
    public function getDurationWithFFprobe(string $filename): int
    {
        // Check if ffprobe is available
        $ffprobe = $this->findFFprobe();
        if ($ffprobe === null) {
            return 0;
        }

        // Build command to get duration
        $cmd = sprintf(
            '%s -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 %s 2>/dev/null',
            escapeshellcmd($ffprobe),
            escapeshellarg($filename)
        );

        $output = @shell_exec($cmd);

        if ($output !== null && is_numeric(trim($output))) {
            return (int) round((float) trim($output));
        }

        return 0;
    }

    /**
     * Find FFprobe executable
     *
     * @return string|null  Path to ffprobe or null if not found
     *
     * @since 10.2.0
     */
    protected function findFFprobe(): ?string
    {
        static $ffprobe = false;

        if ($ffprobe !== false) {
            return $ffprobe;
        }

        // Common locations for ffprobe
        $locations = [
            'ffprobe',                    // In PATH
            '/usr/bin/ffprobe',
            '/usr/local/bin/ffprobe',
            '/opt/local/bin/ffprobe',
            '/opt/homebrew/bin/ffprobe',  // macOS Homebrew
        ];

        foreach ($locations as $path) {
            if ($path === 'ffprobe') {
                // Check if in PATH
                $result = @shell_exec('which ffprobe 2>/dev/null');
                if ($result !== null && trim($result) !== '') {
                    $ffprobe = trim($result);
                    return $ffprobe;
                }
            } elseif (is_executable($path)) {
                $ffprobe = $path;
                return $ffprobe;
            }
        }

        $ffprobe = null;
        return null;
    }

    /**
     * Get duration from M4A/MP4 files by reading the moov atom
     *
     * @param   string  $filename  File path
     *
     * @return int  Duration in seconds or 0 if unable to read
     *
     * @since 10.2.0
     */
    protected function getM4aDuration(string $filename): int
    {
        $fp = @fopen($filename, 'rb');
        if (!$fp) {
            return 0;
        }

        $duration = 0;
        $timescale = 0;

        // Read atoms until we find moov
        while (!feof($fp)) {
            $header = fread($fp, 8);
            if (strlen($header) < 8) {
                break;
            }

            $size = unpack('N', substr($header, 0, 4))[1];
            $type = substr($header, 4, 4);

            if ($size === 0) {
                break;
            }

            if ($size === 1) {
                // Extended size
                $extSize = fread($fp, 8);
                if (strlen($extSize) < 8) {
                    break;
                }
                $size = unpack('J', $extSize)[1];
                $size -= 16;
            } else {
                $size -= 8;
            }

            if ($type === 'moov' || $type === 'trak' || $type === 'mdia') {
                // Container atom - continue reading inside
                continue;
            }

            if ($type === 'mvhd') {
                // Movie header atom - contains duration
                $data = fread($fp, min($size, 120));
                if (strlen($data) >= 20) {
                    $version = ord($data[0]);
                    if ($version === 0) {
                        // 32-bit values
                        $timescale = unpack('N', substr($data, 12, 4))[1];
                        $durationRaw = unpack('N', substr($data, 16, 4))[1];
                    } else {
                        // 64-bit values
                        $timescale = unpack('N', substr($data, 20, 4))[1];
                        $durationRaw = unpack('J', substr($data, 24, 8))[1];
                    }

                    if ($timescale > 0) {
                        $duration = (int) round($durationRaw / $timescale);
                    }
                }
                break;
            }

            // Skip this atom
            if ($size > 0) {
                fseek($fp, $size, SEEK_CUR);
            }
        }

        fclose($fp);
        return $duration;
    }

    /**
     * Get duration from WAV files
     *
     * @param   string  $filename  File path
     *
     * @return int  Duration in seconds or 0 if unable to read
     *
     * @since 10.2.0
     */
    protected function getWavDuration(string $filename): int
    {
        $fp = @fopen($filename, 'rb');
        if (!$fp) {
            return 0;
        }

        // Read RIFF header
        $header = fread($fp, 12);
        if (strlen($header) < 12 || substr($header, 0, 4) !== 'RIFF' || substr($header, 8, 4) !== 'WAVE') {
            fclose($fp);
            return 0;
        }

        $sampleRate = 0;
        $byteRate = 0;
        $dataSize = 0;

        // Read chunks
        while (!feof($fp)) {
            $chunkHeader = fread($fp, 8);
            if (strlen($chunkHeader) < 8) {
                break;
            }

            $chunkId = substr($chunkHeader, 0, 4);
            $chunkSize = unpack('V', substr($chunkHeader, 4, 4))[1];

            if ($chunkId === 'fmt ') {
                $fmtData = fread($fp, min($chunkSize, 40));
                if (strlen($fmtData) >= 16) {
                    $sampleRate = unpack('V', substr($fmtData, 4, 4))[1];
                    $byteRate = unpack('V', substr($fmtData, 8, 4))[1];
                }
                // Seek past any remaining fmt data
                $remaining = $chunkSize - strlen($fmtData);
                if ($remaining > 0) {
                    fseek($fp, $remaining, SEEK_CUR);
                }
            } elseif ($chunkId === 'data') {
                $dataSize = $chunkSize;
                break;
            } else {
                // Skip unknown chunk
                fseek($fp, $chunkSize, SEEK_CUR);
            }
        }

        fclose($fp);

        if ($byteRate > 0 && $dataSize > 0) {
            return (int) round($dataSize / $byteRate);
        }

        return 0;
    }

    /**
     * Get duration from OGG files
     *
     * @param   string  $filename  File path
     *
     * @return int  Duration in seconds or 0 if unable to read
     *
     * @since 10.2.0
     */
    protected function getOggDuration(string $filename): int
    {
        $fp = @fopen($filename, 'rb');
        if (!$fp) {
            return 0;
        }

        // Read first page to get sample rate
        $header = fread($fp, 4);
        if ($header !== 'OggS') {
            fclose($fp);
            return 0;
        }

        // Skip to segment table
        fseek($fp, 26);
        $numSegments = ord(fread($fp, 1));
        $segments = fread($fp, $numSegments);

        // Calculate page size
        $pageSize = 0;
        for ($i = 0; $i < $numSegments; $i++) {
            $pageSize += ord($segments[$i]);
        }

        // Read first packet (Vorbis identification header)
        $packet = fread($fp, $pageSize);
        $sampleRate = 44100; // Default

        // Check for Vorbis
        if (strlen($packet) >= 30 && substr($packet, 1, 6) === 'vorbis') {
            $sampleRate = unpack('V', substr($packet, 12, 4))[1];
        } elseif (strlen($packet) >= 19 && substr($packet, 0, 8) === 'OpusHead') {
            // Opus format
            $sampleRate = 48000; // Opus always uses 48kHz internally
        }

        // Seek to end of file to find last granule position
        fseek($fp, -65536, SEEK_END); // Read last 64KB
        $endData = fread($fp, 65536);
        fclose($fp);

        // Find last OggS page
        $lastGranule = 0;
        $pos = strlen($endData) - 4;
        while ($pos >= 0) {
            if (substr($endData, $pos, 4) === 'OggS') {
                // Found a page header, get granule position
                if ($pos + 14 <= strlen($endData)) {
                    $granule = unpack('P', substr($endData, $pos + 6, 8))[1];
                    if ($granule > $lastGranule) {
                        $lastGranule = $granule;
                    }
                }
            }
            $pos--;
        }

        if ($lastGranule > 0 && $sampleRate > 0) {
            return (int) round($lastGranule / $sampleRate);
        }

        return 0;
    }

    /**
     * Get duration using getID3 library if available
     *
     * @param   string  $filename  File path
     *
     * @return int  Duration in seconds or 0 if getID3 not available
     *
     * @since 10.2.0
     */
    protected function getDurationWithGetID3(string $filename): int
    {
        // Check if getID3 is available
        if (!class_exists('getID3')) {
            // Try to load it from common locations
            $paths = [
                JPATH_LIBRARIES . '/getid3/getid3.php',
                JPATH_ROOT . '/libraries/getid3/getid3.php',
                JPATH_ROOT . '/vendor/james-heinrich/getid3/getid3/getid3.php',
            ];

            foreach ($paths as $path) {
                if (is_file($path)) {
                    require_once $path;
                    break;
                }
            }
        }

        if (!class_exists('getID3')) {
            return 0;
        }

        try {
            $getID3 = new \getID3();
            $fileInfo = $getID3->analyze($filename);

            if (isset($fileInfo['playtime_seconds'])) {
                return (int) round($fileInfo['playtime_seconds']);
            }
        } catch (\Exception $e) {
            // getID3 failed, return 0
        }

        return 0;
    }

    /**
     * Read MP3 file frame by frame for Variable Bit Rate (VBR) files
     *
     * @param   string  $filename  File name of media.
     *
     * @return int  Duration in seconds
     *
     * @since 9.2.4
     */
    public function getMp3Duration(string $filename): int
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
                        return (int) $duration;
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

            fclose($fd);
        }

        return (int) $duration;
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
    /**
     * Check if a URL is a YouTube video URL.
     *
     * Detects youtube.com, youtu.be, and various YouTube URL formats.
     * YouTube videos require API access to get duration, so they need special handling.
     *
     * @param   string  $url  URL to check
     *
     * @return  bool  True if this is a YouTube URL
     *
     * @since 10.2.0
     */
    public function isYouTubeUrl(string $url): bool
    {
        // Normalize URL for checking
        $checkUrl = strtolower($url);

        // Remove protocol if present
        $checkUrl = preg_replace('#^https?://#', '', $checkUrl);

        // Check for YouTube domains
        return str_starts_with($checkUrl, 'youtube.com/')
            || str_starts_with($checkUrl, 'www.youtube.com/')
            || str_starts_with($checkUrl, 'youtu.be/')
            || str_starts_with($checkUrl, 'm.youtube.com/')
            || str_contains($checkUrl, 'youtube.com/watch')
            || str_contains($checkUrl, 'youtube.com/embed')
            || str_contains($checkUrl, 'youtube.com/v/')
            || str_contains($checkUrl, 'youtube.com/live/');
    }

    /**
     * Extract YouTube video ID from various URL formats.
     *
     * @param   string  $url  YouTube URL
     *
     * @return  string|null  Video ID or null if not found
     *
     * @since 10.2.0
     */
    public function extractYouTubeVideoId(string $url): ?string
    {
        // youtu.be/VIDEO_ID
        if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        // youtube.com/watch?v=VIDEO_ID
        if (preg_match('/[?&]v=([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        // youtube.com/embed/VIDEO_ID
        if (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        // youtube.com/v/VIDEO_ID
        if (preg_match('/youtube\.com\/v\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        // youtube.com/live/VIDEO_ID
        if (preg_match('/youtube\.com\/live\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Get YouTube API key from a configured YouTube server.
     *
     * @return  string|null  API key or null if not found
     *
     * @since 10.2.0
     */
    public function getYouTubeApiKey(): ?string
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        // Find a YouTube server with an API key
        $query = $db->getQuery(true);
        $query->select($db->quoteName('params'))
            ->from($db->quoteName('#__bsms_servers'))
            ->where($db->quoteName('type') . ' = ' . $db->q('youtube'))
            ->where($db->quoteName('published') . ' = 1');

        $db->setQuery($query);
        $servers = $db->loadObjectList();

        foreach ($servers as $server) {
            $params = new Registry($server->params);
            $apiKey = $params->get('api_key', '');

            if (!empty($apiKey)) {
                return $apiKey;
            }
        }

        return null;
    }

    /**
     * Get video duration from YouTube API.
     *
     * @param   string  $videoId  YouTube video ID
     * @param   string  $apiKey   YouTube API key
     *
     * @return  int  Duration in seconds, or 0 if failed
     *
     * @since 10.2.0
     */
    public function getYouTubeDuration(string $videoId, string $apiKey): int
    {
        // Build API URL
        $apiUrl = 'https://www.googleapis.com/youtube/v3/videos?'
            . 'id=' . urlencode($videoId)
            . '&part=contentDetails'
            . '&key=' . urlencode($apiKey);

        // Fetch from API
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'ignore_errors' => true,
            ],
        ]);

        $response = @file_get_contents($apiUrl, false, $context);

        if ($response === false) {
            return 0;
        }

        $data = json_decode($response, true);

        if (!isset($data['items'][0]['contentDetails']['duration'])) {
            return 0;
        }

        // Parse ISO 8601 duration (e.g., PT1H30M45S)
        return $this->parseIso8601Duration($data['items'][0]['contentDetails']['duration']);
    }

    /**
     * Parse ISO 8601 duration format to seconds.
     *
     * Handles formats like PT1H30M45S, PT45M, PT30S, etc.
     *
     * @param   string  $duration  ISO 8601 duration string
     *
     * @return  int  Duration in seconds
     *
     * @since 10.2.0
     */
    public function parseIso8601Duration(string $duration): int
    {
        $seconds = 0;

        // Match hours, minutes, seconds
        if (preg_match('/(\d+)H/', $duration, $matches)) {
            $seconds += (int) $matches[1] * 3600;
        }

        if (preg_match('/(\d+)M/', $duration, $matches)) {
            $seconds += (int) $matches[1] * 60;
        }

        if (preg_match('/(\d+)S/', $duration, $matches)) {
            $seconds += (int) $matches[1];
        }

        return $seconds;
    }

    /**
     * Remove Http from link.
     *
     * @param   string  $url  URL to remove http from
     *
     * @return  string
     *
     * @since 9.0.0
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
