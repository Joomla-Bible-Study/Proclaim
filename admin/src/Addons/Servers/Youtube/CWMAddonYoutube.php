<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Youtube;

// phpcs:disable PSR1.Files.SideEffects
require_once __DIR__ . '/vendor/autoload.php';

\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Administrator\Helper\CwmserverMigrationHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmtopicSuggestionHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmyoutubeLogHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmyoutubeQuota;
use CWM\Component\Proclaim\Site\Helper\Cwmmedia;
use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use Google;
use Google\Service\Exception;
use Google\Service\YouTube;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

/**
 * Class CWMAddonYoutube
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class CWMAddonYoutube extends CWMAddon
{
    /**
     * Name of Add-on
     *
     * @var     string
     * @since   9.0.0
     */
    protected $name = 'Youtube';

    /**
     * Description of add-on
     *
     * @var     string
     * @since   9.0.0
     */
    protected $description = 'Used for YouTube server access';

    /**
     * Upload
     *
     * @param ?array $data  Data to upload
     *
     * @return array
     *
     * @since 9.0.0
     */
    public function upload(?array $data): mixed
    {
        // Holds for nothing
        return $data;
    }

    /**
     * Render Fields for a general view.
     *
     * @param   object  $media_form  Medea files form
     * @param bool      $new         If media is new
     *
     * @return string
     *
     * @since 9.1.3
     */
    public function renderGeneral(object $media_form, bool $new): string
    {
        // Load YouTube browser JavaScript
        HTMLHelper::_('script', 'media/com_proclaim/js/addon-youtube-browser.js', ['version' => 'auto']);

        $html   = '';
        $fields = $media_form->getFieldset('general');

        if ($fields) {
            foreach ($fields as $field) {
                if ($new) {
                    $s_name = $field->fieldname;

                    if (isset($media_form->s_params[$s_name])) {
                        $field->setValue($media_form->s_params[$s_name]);
                    }
                }

                $html .= $field->renderField();
            }
        }

        return $html;
    }

    /**
     * Render Layout and fields
     *
     * @param   object  $media_form  Media files form
     * @param bool      $new         If media is new
     *
     * @return string
     *
     * @since 9.1.3
     */
    public function render(object $media_form, bool $new): string
    {
        $html = HTMLHelper::_('uitab.addTab', 'myTab', 'options', Text::_('JBS_ADDON_MEDIA_OPTIONS_LABEL'));
        $html .= $this->renderOptionsFields($media_form, $new);
        $html .= HTMLHelper::_('uitab.endTab');

        return $html;
    }

    /**
     * URL patterns that identify YouTube content.
     *
     * @return  string[]
     *
     * @since   10.1.0
     */
    public function getUrlPatterns(): array
    {
        return ['/(youtube\.com|youtu\.be)/i'];
    }

    /**
     * {@inheritdoc}
     *
     * @since   10.1.0
     */
    public function getMigrationPatterns(): array
    {
        return [
            'type'     => 'youtube',
            'label'    => 'YouTube',
            'patterns' => [
                '/youtu(be\.com|\.be)\//i',
                '/youtube\.com\/embed\//i',
            ],
            'allVideosTags' => ['youtube', 'youtubewide', 'youtubehd'],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since   10.1.0
     */
    public function transformMigrationParams(
        array $params,
        string $mediacode,
        string $filename,
        string $avContent,
        string $combined,
        array $legacyServerParams = []
    ): array {
        $result  = [];
        $videoId = self::extractYoutubeVideoId($combined);

        // Fall back to AllVideos bare ID: {youtube}dQw4w9WgXcQ{/youtube}
        if ($videoId === null && !empty($avContent) && preg_match('/^[a-zA-Z0-9_-]+$/', $avContent)) {
            $videoId = $avContent;
        }

        if ($videoId) {
            $result['filename'] = '//www.youtube.com/embed/' . $videoId . '?enablejsapi=1';
        } else {
            $result['filename'] = $filename;
        }

        // Map extracted URL params to embed option form fields
        $sourceQuery = CwmserverMigrationHelper::extractSourceUrlParams($combined, 'youtube');

        $ytParamMap = [
            'mute'           => 'yt_mute',
            'start'          => 'yt_start',
            'end'            => 'yt_end',
            'loop'           => 'yt_loop',
            'controls'       => 'yt_controls',
            'rel'            => 'yt_rel',
            'cc_load_policy' => 'yt_cc',
            'playsinline'    => 'yt_playsinline',
        ];

        foreach ($ytParamMap as $urlParam => $formField) {
            if (isset($sourceQuery[$urlParam]) && $sourceQuery[$urlParam] !== '') {
                $result[$formField] = $sourceQuery[$urlParam];
            }
        }

        if (isset($sourceQuery['autoplay']) && $sourceQuery['autoplay'] === '1') {
            $result['autostart'] = 'true';
        }

        $result['player']    = '1';
        $result['mediacode'] = '';

        return $result;
    }

    /**
     * Build a YouTube embed URL with all form field params applied.
     *
     * @param   string    $filename     The raw YouTube URL
     * @param   Registry  $mediaParams  Merged template + media params
     *
     * @return  string  The embed-ready URL with query params
     *
     * @since   10.1.0
     */
    public function buildEmbedUrl(string $filename, Registry $mediaParams): string
    {
        $baseUrl = $this->convertYoutube($filename);
        $parts   = parse_url($baseUrl);
        $query   = [];

        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        // Map existing autostart → YouTube autoplay
        $autostart = $mediaParams->get('autostart', '');

        if ($autostart === 'true') {
            $query['autoplay'] = '1';
        } elseif ($autostart === 'false') {
            $query['autoplay'] = '0';
        }

        // Platform-specific form fields → URL params
        $fieldMap = [
            'yt_mute'        => 'mute',
            'yt_start'       => 'start',
            'yt_end'         => 'end',
            'yt_loop'        => 'loop',
            'yt_controls'    => 'controls',
            'yt_rel'         => 'rel',
            'yt_cc'          => 'cc_load_policy',
            'yt_playsinline' => 'playsinline',
        ];

        foreach ($fieldMap as $formField => $urlParam) {
            $val = $mediaParams->get($formField, '');

            if ($val !== '') {
                $query[$urlParam] = $val;
            }
        }

        $query['enablejsapi'] = '1';

        return strtok($baseUrl, '?') . '?' . http_build_query($query);
    }

    /**
     * Render inline YouTube player (responsive 16:9 iframe).
     *
     * @param   string    $url          The raw YouTube URL
     * @param   Registry  $mediaParams  Merged template + media params
     * @param   int       $mediaId      The media file ID
     *
     * @return  string  Complete player HTML
     *
     * @since   10.1.0
     */
    public function renderInlinePlayer(string $url, Registry $mediaParams, int $mediaId): string
    {
        $embedUrl = $this->buildEmbedUrl($url, $mediaParams);

        return '<div class="proclaim-video-wrap" style="position:relative;padding-bottom:56.25%;overflow:hidden;max-width:100%;">'
            . '<iframe class="playhit" data-id="' . $mediaId . '" src="' . htmlspecialchars($embedUrl, ENT_QUOTES, 'UTF-8') . '"'
            . ' allow="autoplay; encrypted-media" allowfullscreen'
            . ' style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;"></iframe>'
            . '</div>';
    }

    /**
     * YouTube URL to embed.
     *
     * @param  string  $url  YouTube URL to transform.
     *
     * @return string
     *
     * @since 10.1.0
     */
    public function convertYoutube(string $url = ''): string
    {
        $string = $url;

        if (preg_match('/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i', $url)) {
            $string = preg_replace(
                "/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
                "//www.youtube.com/embed/$2?enablejsapi=1",
                $url
            );
        }

        if (preg_match('/https?:\/\/www\.youtube\.com\/live\//', $url)) {
            // Find the position of the last "/"
            $lastSlashPosition = strrpos($url, '/');

            // Extract the part after the last "/"
            if ($lastSlashPosition !== false) {
                $videoID = substr($url, $lastSlashPosition + 1);
                $string  = "//www.youtube.com/embed/$videoID?enablejsapi=1";
            }
        }

        $string = (new Cwmmedia())->ensureHttpJoomla($string);

        return (string) $string;
    }

    /**
     * @param $key
     * @param   string  $playlistID
     * @param   string  $pageToken
     * @param   int  $maxResults
     *
     *
     * @throws Exception
     * @since 9.0.0
     */
    public function buildPlaylistList($key, string $playlistID, string $pageToken, int $maxResults = 50): void
    {
        $client = new Google\Client();
        $client->setApplicationName("Client_Library_Examples");
        $client->setDeveloperKey("$key");

        $service = new YouTube($client);
        try {
            $response = $service->playlistItems->listPlaylistItems(
                'snippet',
                [
                    'playlistId' => $playlistID,
                    'pageToken'  => $pageToken,
                    'maxResults' => $maxResults,
                ]
            );
        } catch (Exception $e) {
            throw new Exception($e);
        }

        # Extract data we need from the response
        $prevPageToken = $response->prevPageToken ?? null;
        $nextPageToken = $response->nextPageToken ?? null;
        $totalResults  = $response->pageInfo->totalResults;
        $videos        = $response->items;
        if ($prevPageToken) {
            echo "<a href='/?pageToken=<?php echo $prevPageToken?>'>Previous page</a>";
        }

        if ($nextPageToken) {
            echo "<a href='/?pageToken=<?php echo $nextPageToken?>'>Next page</a>";
        }
        foreach ($videos as $video) {
            echo "<div style='margin:10px 0'>
        <img
            style='width:150px'
            src='" . $video->snippet->thumbnails->high->url . "'
            alt='Thumbnail for the video " . $video->snippet->title . "'><br>

        <strong>Title:</strong>
        " . $video->snippet->title . '
        <br>
        <strong>Video ID:</strong>
        ' . $video->snippet->resourceId->videoId . '
    </div>';
        }
    }

    /**
     * YouTube supports platform stats via the Data API v3 statistics endpoint.
     *
     * @return  bool
     *
     * @since   10.1.0
     */
    public function supportsStats(): bool
    {
        return true;
    }

    /**
     * Fetch video statistics from YouTube Data API for media linked to this server.
     * Batches up to 50 video IDs per API call. When $batchLimit > 0, only the
     * least-recently-synced videos are processed (never-synced first).
     *
     * @param   int  $serverId    The server record ID
     * @param   int  $batchLimit  Max unique videos to sync (0 = unlimited)
     *
     * @return  array{success: bool, synced: int, remaining: int, errors: string[]}
     *
     * @since   10.1.0
     */
    public function fetchPlatformStats(int $serverId, int $batchLimit = 0): array
    {
        $config = $this->getServerConfig($serverId);
        $apiKey = $config['api_key'] ?? '';

        if (empty($apiKey)) {
            return ['success' => false, 'synced' => 0, 'remaining' => 0, 'errors' => ['YouTube: No API key configured']];
        }

        $totalMedia = static::getMediaVideoCount($serverId);
        $mediaRows  = static::getMediaVideoIds($serverId, 'filename', $batchLimit, 'youtube');

        if (empty($mediaRows)) {
            return ['success' => true, 'synced' => 0, 'remaining' => 0, 'errors' => []];
        }

        // Extract YouTube video IDs from embed URLs stored in params.filename
        $videoMap = []; // videoId => [media_id, ...]

        foreach ($mediaRows as $row) {
            $videoId = $this->extractYoutubeVideoId($row['video_id']);

            if ($videoId !== null) {
                $videoMap[$videoId][] = $row['media_id'];
            }
        }

        // Apply batch limit to unique videos (not media rows, since multiple media can share one video)
        if ($batchLimit > 0 && \count($videoMap) > $batchLimit) {
            $videoMap = \array_slice($videoMap, 0, $batchLimit, true);
        }

        if (empty($videoMap)) {
            return ['success' => true, 'synced' => 0, 'remaining' => 0, 'errors' => []];
        }

        // Estimate quota cost: 1 unit per videos.list call (50 IDs each)
        $chunks        = array_chunk(array_keys($videoMap), 50);
        $estimatedCost = \count($chunks) * CwmyoutubeQuota::COST_VIDEOS;

        if (!CwmyoutubeQuota::hasQuota($serverId, $estimatedCost)) {
            $remaining = CwmyoutubeQuota::getRemaining($serverId);

            return [
                'success'   => true,
                'synced'    => 0,
                'remaining' => $totalMedia,
                'errors'    => ['YouTube: daily quota budget exhausted (' . $remaining . ' units remaining, need ' . $estimatedCost . ')'],
            ];
        }

        $synced = 0;
        $errors = [];

        try {
            $client = new Google\Client();
            $client->setApplicationName('Proclaim');
            $client->setDeveloperKey($apiKey);

            $youtube = new YouTube($client);

            foreach ($chunks as $idBatch) {
                // Re-check quota before each batch in case frontend used some
                if (!CwmyoutubeQuota::hasQuota($serverId, CwmyoutubeQuota::COST_VIDEOS)) {
                    $errors[] = 'YouTube: quota budget reached mid-sync, stopping';
                    break;
                }

                try {
                    $response = @$youtube->videos->listVideos('statistics', [
                        'id' => implode(',', $idBatch),
                    ]);
                    CwmyoutubeQuota::recordUsage($serverId, CwmyoutubeQuota::COST_VIDEOS);

                    foreach ($response->items as $item) {
                        $vid   = $item->id;
                        $stats = $item->statistics;

                        foreach ($videoMap[$vid] ?? [] as $mediaId) {
                            static::upsertPlatformStats(
                                $mediaId,
                                $serverId,
                                'youtube',
                                $vid,
                                [
                                    'view_count'    => (int) ($stats->viewCount ?? 0),
                                    'play_count'    => (int) ($stats->viewCount ?? 0),
                                    'like_count'    => (int) ($stats->likeCount ?? 0),
                                    'comment_count' => (int) ($stats->commentCount ?? 0),
                                ]
                            );
                            $synced++;
                        }
                    }
                } catch (Exception $e) {
                    $errors[] = 'YouTube batch: ' . $e->getMessage();
                }
            }

            static::updateServerSyncTimestamp($serverId);
        } catch (\Exception $e) {
            $errors[] = 'YouTube: ' . $e->getMessage();
        }

        $remaining = max(0, $totalMedia - $synced);

        return ['success' => empty($errors), 'synced' => $synced, 'remaining' => $remaining, 'errors' => $errors];
    }

    /**
     * Extract YouTube video ID from any YouTube URL format.
     *
     * @param   string  $url  URL or embed URL
     *
     * @return  string|null  The video ID or null
     *
     * @since   10.1.0
     */
    /**
     * {@inheritdoc}
     *
     * @since   10.1.0
     */
    #[\Override]
    public static function extractMediaId(string $text): ?string
    {
        $patterns = [
            '/(?:youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/',
            '/(?:youtube\.com\/watch\?v=)([a-zA-Z0-9_-]+)/',
            '/(?:youtu\.be\/)([a-zA-Z0-9_-]+)/',
            '/(?:youtube\.com\/live\/)([a-zA-Z0-9_-]+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return $matches[1];
            }
        }

        // If it looks like a bare video ID (11 chars, alphanumeric + - _)
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $text)) {
            return $text;
        }

        return null;
    }

    /**
     * @deprecated Use extractMediaId() instead
     */
    public static function extractYoutubeVideoId(string $url): ?string
    {
        return static::extractMediaId($url);
    }

    /**
     * Get available AJAX actions for this addon
     *
     * @return  array  List of available action names
     *
     * @since   10.1.0
     */
    public function getAjaxActions(): array
    {
        return [
            'testApi',
            'fetchUpcoming',
            'fetchChannelVideos',
            'searchChannelVideos',
            'fetchChannelPlaylists',
            'fetchPlaylistVideos',
            'fetchLiveVideos',
            'getVideoStatus',
        ];
    }

    /**
     * Handle testApi AJAX action
     *
     * @return  array  Response data
     *
     * @throws \Exception
     * @since   10.1.0
     */
    protected function handleTestApiAction(): array
    {
        $app       = Factory::getApplication();
        $apiKey    = $app->getInput()->getString('api_key', '');
        $channelId = $app->getInput()->getString('channel_id', '');

        return $this->testApiConnection($apiKey, $channelId);
    }

    /**
     * Handle fetchUpcoming AJAX action
     *
     * @return  array  Response data
     *
     * @throws \Exception
     * @since   10.1.0
     */
    protected function handleFetchUpcomingAction(): array
    {
        $app      = Factory::getApplication();
        $serverId = $app->getInput()->getInt('server_id', 0);

        // Verify this is a YouTube server
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('type'))
            ->from($db->quoteName('#__bsms_servers'))
            ->where($db->quoteName('id') . ' = ' . $serverId);
        $db->setQuery($query);
        $serverType = $db->loadResult();

        if (strtolower($serverType) !== 'youtube') {
            return ['success' => false, 'error' => 'Selected server is not a YouTube server'];
        }

        return $this->fetchUpcomingVideos($serverId);
    }

    /**
     * Handle fetchChannelVideos AJAX action
     *
     * @return  array  Response data
     *
     * @throws \Exception
     * @since   10.1.0
     */
    protected function handleFetchChannelVideosAction(): array
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        return $this->fetchChannelVideos($input);
    }

    /**
     * Handle searchChannelVideos AJAX action
     *
     * @return  array  Response data
     *
     * @throws \Exception
     * @since   10.1.0
     */
    protected function handleSearchChannelVideosAction(): array
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        return $this->searchChannelVideos($input);
    }

    /**
     * Handle fetchChannelPlaylists AJAX action
     *
     * @return  array  Response data
     *
     * @throws \Exception
     * @since   10.1.0
     */
    protected function handleFetchChannelPlaylistsAction(): array
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        return $this->fetchChannelPlaylists($input);
    }

    /**
     * Handle fetchPlaylistVideos AJAX action
     *
     * @return  array  Response data
     *
     * @throws \Exception
     * @since   10.1.0
     */
    protected function handleFetchPlaylistVideosAction(): array
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        return $this->fetchPlaylistVideos($input);
    }

    /**
     * Handle fetchLiveVideos AJAX action
     *
     * @return  array  Response data
     *
     * @throws \Exception
     * @since   10.1.0
     */
    protected function handleFetchLiveVideosAction(): array
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        return $this->fetchLiveVideos($input);
    }

    /**
     * Get server configuration by ID
     *
     * @param   int  $serverId  Server ID
     *
     * @return  array  Server params
     *
     * @since   10.1.0
     */
    protected function getServerConfig(int $serverId): array
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__bsms_servers'))
            ->where($db->quoteName('id') . ' = ' . (int) $serverId);

        $db->setQuery($query);
        $result = $db->loadResult();

        if ($result) {
            return (new Registry($result))->toArray();
        }

        return [];
    }

    /**
     * Fetch videos from YouTube channel (XHR handler)
     *
     * @param   Input  $input  Request input
     *
     * @return  array  Response data
     *
     * @throws \Exception
     * @since   10.1.0
     */
    public function fetchChannelVideos(Input $input): array
    {
        $this->loadLanguage();

        $serverId   = $input->getInt('server_id', 0);
        $pageToken  = $input->getString('page_token', '');
        $maxResults = $input->getInt('max_results', 12);

        if (!$serverId) {
            return ['success' => false, 'error' => Text::_('JBS_ADDON_YOUTUBE_NO_SERVER_ID')];
        }

        $config    = $this->getServerConfig($serverId);
        $apiKey    = $config['api_key'] ?? '';
        $channelId = $config['channel_id'] ?? '';

        if (empty($apiKey)) {
            return ['success' => false, 'error' => Text::_('JBS_ADDON_YOUTUBE_NO_API_KEY')];
        }

        if (empty($channelId)) {
            return ['success' => false, 'error' => Text::_('JBS_ADDON_YOUTUBE_NO_CHANNEL_ID')];
        }

        try {
            $client = new Google\Client();
            $client->setApplicationName('Proclaim');
            $client->setDeveloperKey($apiKey);

            $youtube = new YouTube($client);

            // Get channel's uploads playlist ID
            $channelResponse = $youtube->channels->listChannels('contentDetails', [
                'id' => $channelId,
            ]);

            if (empty($channelResponse->items)) {
                return ['success' => false, 'error' => Text::_('JBS_ADDON_YOUTUBE_CHANNEL_NOT_FOUND')];
            }

            $uploadsPlaylistId = $channelResponse->items[0]->contentDetails->relatedPlaylists->uploads;

            // Get videos from uploads playlist
            $params = [
                'playlistId' => $uploadsPlaylistId,
                'maxResults' => $maxResults,
            ];

            if (!empty($pageToken)) {
                $params['pageToken'] = $pageToken;
            }

            $response = $youtube->playlistItems->listPlaylistItems('snippet', $params);

            $videos = [];

            foreach ($response->items as $item) {
                $videos[] = [
                    'videoId'     => $item->snippet->resourceId->videoId,
                    'title'       => $item->snippet->title,
                    'description' => $item->snippet->description,
                    'thumbnail'   => $item->snippet->thumbnails->medium->url ?? $item->snippet->thumbnails->default->url,
                    'publishedAt' => $item->snippet->publishedAt,
                ];
            }

            return [
                'success'       => true,
                'videos'        => $videos,
                'nextPageToken' => $response->nextPageToken ?? null,
                'prevPageToken' => $response->prevPageToken ?? null,
                'totalResults'  => $response->pageInfo->totalResults ?? 0,
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Search videos in YouTube channel (XHR handler)
     *
     * @param   Input  $input  Request input
     *
     * @return  array  Response data
     *
     * @throws \Exception
     * @since   10.1.0
     */
    public function searchChannelVideos(Input $input): array
    {
        $this->loadLanguage();

        $serverId     = $input->getInt('server_id', 0);
        $query        = $input->getString('query', '');
        $pageToken    = $input->getString('page_token', '');
        $maxResults   = $input->getInt('max_results', 12);
        $scopeChannel = $input->getBool('scope_channel', true);
        $phraseMatch  = $input->getBool('phrase_match', false);

        if (!$serverId) {
            return ['success' => false, 'error' => Text::_('JBS_ADDON_YOUTUBE_NO_SERVER_ID')];
        }

        if (empty($query)) {
            return $this->fetchChannelVideos($input);
        }

        $config    = $this->getServerConfig($serverId);
        $apiKey    = $config['api_key'] ?? '';
        $channelId = $config['channel_id'] ?? '';

        if (empty($apiKey)) {
            return ['success' => false, 'error' => Text::_('JBS_ADDON_YOUTUBE_NO_API_KEY')];
        }

        try {
            $client = new Google\Client();
            $client->setApplicationName('Proclaim');
            $client->setDeveloperKey($apiKey);

            $youtube = new YouTube($client);

            $searchQuery = $phraseMatch ? '"' . $query . '"' : $query;

            $params = [
                'q'          => $searchQuery,
                'type'       => 'video',
                'maxResults' => $maxResults,
            ];

            if ($scopeChannel && !empty($channelId)) {
                $params['channelId'] = $channelId;
            }

            if (!empty($pageToken)) {
                $params['pageToken'] = $pageToken;
            }

            $response = $youtube->search->listSearch('snippet', $params);

            $videos = [];

            foreach ($response->items as $item) {
                $videos[] = [
                    'videoId'     => $item->id->videoId,
                    'title'       => $item->snippet->title,
                    'description' => $item->snippet->description,
                    'thumbnail'   => $item->snippet->thumbnails->medium->url ?? $item->snippet->thumbnails->default->url,
                    'publishedAt' => $item->snippet->publishedAt,
                ];
            }

            return [
                'success'       => true,
                'videos'        => $videos,
                'nextPageToken' => $response->nextPageToken ?? null,
                'prevPageToken' => $response->prevPageToken ?? null,
                'totalResults'  => $response->pageInfo->totalResults ?? 0,
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Fetch channel playlists (XHR handler)
     *
     * @param   Input  $input  Request input
     *
     * @return  array  Response data
     *
     * @throws \Exception
     * @since   10.1.0
     */
    public function fetchChannelPlaylists(Input $input): array
    {
        $this->loadLanguage();

        $serverId = $input->getInt('server_id', 0);

        if (!$serverId) {
            return ['success' => false, 'error' => Text::_('JBS_ADDON_YOUTUBE_NO_SERVER_ID')];
        }

        $config    = $this->getServerConfig($serverId);
        $apiKey    = $config['api_key'] ?? '';
        $channelId = $config['channel_id'] ?? '';

        if (empty($apiKey)) {
            return ['success' => false, 'error' => Text::_('JBS_ADDON_YOUTUBE_NO_API_KEY')];
        }

        if (empty($channelId)) {
            return ['success' => false, 'error' => Text::_('JBS_ADDON_YOUTUBE_NO_CHANNEL_ID')];
        }

        try {
            $client = new Google\Client();
            $client->setApplicationName('Proclaim');
            $client->setDeveloperKey($apiKey);

            $youtube = new YouTube($client);

            $response = $youtube->playlists->listPlaylists('snippet', [
                'channelId'  => $channelId,
                'maxResults' => 50,
            ]);

            $playlists = [];

            foreach ($response->items as $item) {
                $playlists[] = [
                    'playlistId' => $item->id,
                    'title'      => $item->snippet->title,
                    'thumbnail'  => $item->snippet->thumbnails->medium->url ?? $item->snippet->thumbnails->default->url,
                ];
            }

            return [
                'success'   => true,
                'playlists' => $playlists,
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Fetch videos from a specific playlist (XHR handler)
     *
     * @param   Input  $input  Request input
     *
     * @return  array  Response data
     *
     * @throws \Exception
     * @since   10.1.0
     */
    public function fetchPlaylistVideos(Input $input): array
    {
        $this->loadLanguage();

        $serverId   = $input->getInt('server_id', 0);
        $playlistId = $input->getString('playlist_id', '');
        $pageToken  = $input->getString('page_token', '');
        $maxResults = $input->getInt('max_results', 12);

        if (!$serverId) {
            return ['success' => false, 'error' => Text::_('JBS_ADDON_YOUTUBE_NO_SERVER_ID')];
        }

        if (empty($playlistId)) {
            return ['success' => false, 'error' => Text::_('JBS_ADDON_YOUTUBE_NO_PLAYLIST_ID')];
        }

        $config = $this->getServerConfig($serverId);
        $apiKey = $config['api_key'] ?? '';

        if (empty($apiKey)) {
            return ['success' => false, 'error' => Text::_('JBS_ADDON_YOUTUBE_NO_API_KEY')];
        }

        try {
            $client = new Google\Client();
            $client->setApplicationName('Proclaim');
            $client->setDeveloperKey($apiKey);

            $youtube = new YouTube($client);

            $params = [
                'playlistId' => $playlistId,
                'maxResults' => $maxResults,
            ];

            if (!empty($pageToken)) {
                $params['pageToken'] = $pageToken;
            }

            $response = $youtube->playlistItems->listPlaylistItems('snippet', $params);

            $videos = [];

            foreach ($response->items as $item) {
                $videos[] = [
                    'videoId'     => $item->snippet->resourceId->videoId,
                    'title'       => $item->snippet->title,
                    'description' => $item->snippet->description,
                    'thumbnail'   => $item->snippet->thumbnails->medium->url ?? $item->snippet->thumbnails->default->url,
                    'publishedAt' => $item->snippet->publishedAt,
                ];
            }

            return [
                'success'       => true,
                'videos'        => $videos,
                'nextPageToken' => $response->nextPageToken ?? null,
                'prevPageToken' => $response->prevPageToken ?? null,
                'totalResults'  => $response->pageInfo->totalResults ?? 0,
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Fetch live videos from channel (XHR handler)
     *
     * @param   Input  $input  Request input
     *
     * @return  array  Response data
     *
     * @throws \Exception
     * @since   10.1.0
     */
    public function fetchLiveVideos(Input $input): array
    {
        $this->loadLanguage();

        $serverId   = $input->getInt('server_id', 0);
        $pageToken  = $input->getString('page_token', '');
        $maxResults = $input->getInt('max_results', 12);
        $eventType  = $input->getString('event_type', 'completed'); // live, upcoming, completed

        if (!$serverId) {
            return ['success' => false, 'error' => Text::_('JBS_ADDON_YOUTUBE_NO_SERVER_ID')];
        }

        $config    = $this->getServerConfig($serverId);
        $apiKey    = $config['api_key'] ?? '';
        $channelId = $config['channel_id'] ?? '';

        if (empty($apiKey)) {
            return ['success' => false, 'error' => Text::_('JBS_ADDON_YOUTUBE_NO_API_KEY')];
        }

        try {
            $client = new Google\Client();
            $client->setApplicationName('Proclaim');
            $client->setDeveloperKey($apiKey);

            $youtube = new YouTube($client);

            $params = [
                'type'       => 'video',
                'eventType'  => $eventType,
                'maxResults' => $maxResults,
            ];

            if (!empty($channelId)) {
                $params['channelId'] = $channelId;
            }

            if (!empty($pageToken)) {
                $params['pageToken'] = $pageToken;
            }

            $response = $youtube->search->listSearch('snippet', $params);

            $videos = [];

            foreach ($response->items as $item) {
                $videos[] = [
                    'videoId'     => $item->id->videoId,
                    'title'       => $item->snippet->title,
                    'description' => $item->snippet->description,
                    'thumbnail'   => $item->snippet->thumbnails->medium->url ?? $item->snippet->thumbnails->default->url,
                    'publishedAt' => $item->snippet->publishedAt,
                    'liveBadge'   => $eventType === 'live' ? 'LIVE' : ($eventType === 'upcoming' ? 'UPCOMING' : ''),
                ];
            }

            return [
                'success'       => true,
                'videos'        => $videos,
                'nextPageToken' => $response->nextPageToken ?? null,
                'prevPageToken' => $response->prevPageToken ?? null,
                'totalResults'  => $response->pageInfo->totalResults ?? 0,
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Test YouTube API credentials
     *
     * @param   string  $apiKey     The YouTube API key
     * @param   string  $channelId  The YouTube channel ID
     *
     * @return  array  Response data with success status
     *
     * @throws \Exception
     * @since   10.1.0
     */
    public function testApiConnection(string $apiKey, string $channelId): array
    {
        $this->loadLanguage();

        if (empty($apiKey)) {
            return ['success' => false, 'error' => Text::_('JBS_ADDON_YOUTUBE_NO_API_KEY')];
        }

        if (empty($channelId)) {
            return ['success' => false, 'error' => Text::_('JBS_ADDON_YOUTUBE_NO_CHANNEL_ID')];
        }

        try {
            $client = new Google\Client();
            $client->setApplicationName('Proclaim');
            $client->setDeveloperKey($apiKey);

            $youtube = new YouTube($client);

            // Try to get channel details (suppress warnings from cURL deprecation in PHP 8.5)
            $response = @$youtube->channels->listChannels('snippet,statistics', [
                'id' => $channelId,
            ]);

            if (empty($response->items)) {
                return ['success' => false, 'error' => Text::_('JBS_ADDON_YOUTUBE_CHANNEL_NOT_FOUND')];
            }

            $channel = $response->items[0];

            return [
                'success' => true,
                'message' => Text::_('JBS_ADDON_YOUTUBE_API_SUCCESS'),
                'channel' => [
                    'title'           => $channel->snippet->title,
                    'description'     => substr($channel->snippet->description ?? '', 0, 100),
                    'subscriberCount' => $channel->statistics->subscriberCount ?? null,
                    'videoCount'      => $channel->statistics->videoCount ?? null,
                ],
            ];
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();

            // Try to parse Google API error
            $decoded = json_decode($errorMessage, true, 512, JSON_THROW_ON_ERROR);

            if (isset($decoded['error']['message'])) {
                $errorMessage = Text::_('JBS_ADDON_YOUTUBE_API_ERROR') . ': ' . $decoded['error']['message'];
            }

            return ['success' => false, 'error' => $errorMessage];
        }
    }

    /**
     * Fetch upcoming videos for an exclusion list (AJAX handler)
     *
     * @param   int  $serverId  The server ID
     *
     * @return  array  Response data with videos
     *
     * @throws \Exception
     * @since   10.1.0
     */
    public function fetchUpcomingVideos(int $serverId): array
    {
        $this->loadLanguage();

        if (!$serverId) {
            return ['success' => false, 'error' => Text::_('JBS_ADDON_YOUTUBE_NO_SERVER_ID')];
        }

        $input = new Input([
            'server_id'   => $serverId,
            'max_results' => 25,
            'event_type'  => 'upcoming',
        ]);

        return $this->fetchLiveVideos($input);
    }

    /**
     * Handle getVideoStatus AJAX action
     *
     * @return  array  Response data
     *
     * @throws \Exception
     * @since   10.1.0
     */
    protected function handleGetVideoStatusAction(): array
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        return $this->getVideoStatus($input);
    }

    /**
     * Get the live streaming status of a specific video
     *
     * Uses the Videos API with liveStreamingDetails to get the actual
     * broadcast status, which is more reliable than search API for
     * detecting status transitions.
     *
     * @param   Input  $input  Request input with server_id and video_id
     *
     * @return  array  Response with isLive, isUpcoming, liveBroadcastContent
     *
     * @throws \Exception
     * @since   10.1.0
     */
    public function getVideoStatus(Input $input): array
    {
        $this->loadLanguage();

        $serverId = $input->getInt('server_id', 0);
        $videoId  = $input->getString('video_id', '');

        if (!$serverId) {
            return ['success' => false, 'error' => Text::_('JBS_ADDON_YOUTUBE_NO_SERVER_ID')];
        }

        if (empty($videoId)) {
            return ['success' => false, 'error' => 'No video ID provided'];
        }

        $config = $this->getServerConfig($serverId);
        $apiKey = $config['api_key'] ?? '';

        if (empty($apiKey)) {
            return ['success' => false, 'error' => Text::_('JBS_ADDON_YOUTUBE_NO_API_KEY')];
        }

        try {
            $client = new Google\Client();
            $client->setApplicationName('Proclaim');
            $client->setDeveloperKey($apiKey);

            $youtube = new YouTube($client);

            // Get video details with liveStreamingDetails
            $response = $youtube->videos->listVideos('snippet,liveStreamingDetails', [
                'id' => $videoId,
            ]);

            if (empty($response->items)) {
                return [
                    'success'    => true,
                    'isLive'     => false,
                    'isUpcoming' => false,
                    'videoId'    => $videoId,
                    'status'     => 'not_found',
                ];
            }

            $video                 = $response->items[0];
            $liveBroadcastContent  = $video->snippet->liveBroadcastContent ?? 'none';
            $liveStreamingDetails  = $video->liveStreamingDetails;

            // Determine status based on liveBroadcastContent
            // Values: 'live', 'upcoming', 'none'
            $isLive     = ($liveBroadcastContent === 'live');
            $isUpcoming = ($liveBroadcastContent === 'upcoming');

            $result = [
                'success'              => true,
                'isLive'               => $isLive,
                'isUpcoming'           => $isUpcoming,
                'videoId'              => $videoId,
                'liveBroadcastContent' => $liveBroadcastContent,
            ];

            // Include scheduled start time if available
            if ($liveStreamingDetails && $liveStreamingDetails->scheduledStartTime) {
                $result['scheduledStartTime'] = $liveStreamingDetails->scheduledStartTime;
            }

            // Include actual start time if live or completed
            if ($liveStreamingDetails && $liveStreamingDetails->actualStartTime) {
                $result['actualStartTime'] = $liveStreamingDetails->actualStartTime;
            }

            return $result;
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Detect metadata for a YouTube video.
     *
     * @param   Registry    $params      Media params (modified in place)
     * @param   object      $server      Server object
     * @param   string      $set_path    Server path prefix
     * @param   Registry    $path        Server params
     * @param   Cwmpodcast  $jbspodcast  Podcast helper
     *
     * @return  void
     *
     * @since   10.1.0
     */
    #[\Override]
    public function detectMetadata(Registry $params, object $server, string $set_path, Registry $path, Cwmpodcast $jbspodcast): void
    {
        $filename = $params->get('filename');

        if (empty($filename)) {
            return;
        }

        ['needsMime' => $needsMime, 'needsDuration' => $needsDuration] = $this->needsDetection($params);

        // Set MIME type (video/mp4 is the standard enclosure type for podcast feeds)
        if ($needsMime) {
            $params->set('mime_type', 'video/mp4');
        }

        // Only detect duration and title for YouTube (size is irrelevant for streaming)
        if (!$needsDuration && !empty($params->get('title'))) {
            return;
        }

        $serverParams = new Registry($server->params);
        $apiKey       = $serverParams->get('api_key');

        if (empty($apiKey)) {
            return;
        }

        $videoId = $this->extractYoutubeVideoId($filename);

        if (empty($videoId)) {
            return;
        }

        // Check YouTube API daily quota before making the call (videos.list = 1 unit)
        if (!CwmyoutubeQuota::hasQuota((int) $server->id, CwmyoutubeQuota::COST_VIDEOS)) {
            $remaining = CwmyoutubeQuota::getRemaining((int) $server->id);
            $budget    = CwmyoutubeQuota::getDailyBudget((int) $server->id);

            CwmyoutubeLogHelper::log(
                CwmyoutubeLogHelper::LEVEL_WARNING,
                'Admin: Quota exhausted — skipped metadata detection',
                ['server_id' => (int) $server->id, 'video_id' => $videoId, 'remaining' => $remaining, 'budget' => $budget]
            );

            try {
                Factory::getApplication()->enqueueMessage(
                    Text::sprintf('JBS_CMN_YT_QUOTA_EXHAUSTED', $remaining, $budget),
                    'warning'
                );
            } catch (\Exception $e) {
                // Ignore if application not available
            }

            return;
        }

        try {
            $client = new Google\Client();
            $client->setApplicationName('Proclaim');
            $client->setDeveloperKey($apiKey);

            $youtube  = new YouTube($client);
            $response = $youtube->videos->listVideos('snippet,contentDetails', [
                'id' => $videoId,
            ]);

            // Record quota usage (YouTube counts the call regardless of result)
            CwmyoutubeQuota::recordUsage((int) $server->id, CwmyoutubeQuota::COST_VIDEOS);

            if (!empty($response->items)) {
                $item = $response->items[0];

                // Set Title if empty
                if (empty($params->get('title'))) {
                    $params->set('title', $item->snippet->title);
                }

                // Set Duration
                if ($needsDuration) {
                    $durationISO = $item->contentDetails->duration;
                    try {
                        $interval = new \DateInterval($durationISO);
                        $params->set('media_hours', str_pad((string) $interval->h, 2, '0', STR_PAD_LEFT));
                        $params->set('media_minutes', str_pad((string) $interval->i, 2, '0', STR_PAD_LEFT));
                        $params->set('media_seconds', str_pad((string) $interval->s, 2, '0', STR_PAD_LEFT));
                    } catch (\Exception $e) {
                        // Ignore invalid duration format
                    }
                }

                // Auto-import YouTube tags as topics when setting is enabled
                $adminParams = Cwmparams::getAdmin()->params;

                if ($adminParams->get('yt_auto_import_topics', '0') === '1') {
                    $tags = $item->snippet->tags ?? [];

                    if (!empty($tags)) {
                        $this->importTagsAsTopics($tags);
                    }
                }
            }
        } catch (\Exception $e) {
            // Log or ignore error
        }
    }

    /**
     * Import YouTube video tags as Proclaim topics
     *
     * Matches tags against existing topics and creates new topics for unmatched tags.
     *
     * @param   array  $tags  YouTube video tags
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private function importTagsAsTopics(array $tags): void
    {
        $tagText      = implode(', ', $tags);
        $matched      = CwmtopicSuggestionHelper::matchExistingTopics($tagText);
        $matchedTexts = array_map(
            fn ($m) => mb_strtolower(trim($m['text'])),
            $matched
        );

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        foreach ($tags as $tag) {
            $tag = trim($tag);

            if (empty($tag)) {
                continue;
            }

            // Skip if already matched to an existing topic
            if (\in_array(mb_strtolower($tag), $matchedTexts, true)) {
                continue;
            }

            // Case-insensitive de-dup check
            $query = $db->getQuery(true);
            $query->select('COUNT(*)')
                ->from($db->quoteName('#__bsms_topics'))
                ->where('LOWER(' . $db->quoteName('topic_text') . ') = LOWER(:tag)')
                ->bind(':tag', $tag);
            $db->setQuery($query);

            if ((int) $db->loadResult() > 0) {
                continue;
            }

            // Insert new topic
            $insert = $db->getQuery(true);
            $insert->insert($db->quoteName('#__bsms_topics'))
                ->columns($db->quoteName(['topic_text', 'published', 'language']))
                ->values(
                    $db->quote($tag) . ', 1, ' . $db->quote('*')
                );
            $db->setQuery($insert);

            try {
                $db->execute();
            } catch (\Exception $e) {
                // Skip duplicate or failed insert
            }
        }
    }

}
