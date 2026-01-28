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

require_once __DIR__ . '/vendor/autoload.php';

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Site\Helper\Cwmmedia;
use Google;
use Google\Service\Exception;
use Google\Service\YouTube;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
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
     * Render Fields for general view.
     *
     * @param object  $media_form  Medea files form
     * @param bool    $new         If media is new
     *
     * @return string
     *
     * @since 9.1.3
     */
    public function renderGeneral($media_form, bool $new): string
    {
        // Load YouTube browser JavaScript
        HTMLHelper::_('script', 'media/com_proclaim/js/addon-youtube-browser.js', ['version' => 'auto']);

        $html   = '';
        $fields = $media_form->getFieldset('general');

        if ($fields) {
            foreach ($fields as $field) :
                $html .= '<div class="control-group">';
                $html .= '<div class="control-label">';
                $html .= $field->label;
                $html .= '</div>';
                $html .= '<div class="controls">';

                // Way to set defaults on new media
                if ($new) {
                    $s_name = $field->fieldname;

                    if (isset($media_form->s_params[$s_name])) {
                        $field->setValue($media_form->s_params[$s_name]);
                    }
                }

                $html .= $field->input;
                $html .= '</div>';
                $html .= '</div>';
            endforeach;
        }

        return $html;
    }

    /**
     * Render Layout and fields
     *
     * @param object  $media_form  Medea files form
     * @param bool    $new         If media is new
     *
     * @return string
     *
     * @since 9.1.3
     */
    public function render($media_form, bool $new): string
    {
        $html = '';
        $html .= HTMLHelper::_('uitab.addTab', 'myTab', 'options', Text::_('Options'));

        $html .= '<div class="row-fluid">';

        foreach ($media_form->getFieldsets('params') as $name => $fieldset) {
            if ($name !== 'general') {
                $html .= '<div class="col-6">';

                foreach ($media_form->getFieldset($name) as $field) :
                    $html .= '<div class="control-group">';
                    $html .= '<div class="control-label">';
                    $html .= $field->label;
                    $html .= '</div>';
                    $html .= '<div class="controls">';

                    // Way to set defaults on new media
                    if ($new) {
                        $s_name = $field->fieldname;

                        if (isset($media_form->s_params[$s_name])) {
                            $field->setValue($media_form->s_params[$s_name]);
                        }
                    }

                    $html .= $field->input;
                    $html .= '</div>';
                    $html .= '</div>';
                endforeach;

                $html .= '</div>';
            }
        }

        $html .= '</div>';
        $html .= HTMLHelper::_('uitab.endTab');

        return $html;
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
                "//www.youtube.com/embed/$2",
                $url
            );
        }

        if (preg_match('/https?:\/\/www\.youtube\.com\/live\//', $url)) {
            // Find the position of the last "/"
            $lastSlashPosition = strrpos($url, '/');

            // Extract the part after the last "/"
            if ($lastSlashPosition !== false) {
                $videoID = substr($url, $lastSlashPosition + 1);
                $string  = "//www.youtube.com/embed/$videoID";
            }
        }

        $string = (new Cwmmedia())->ensureHttpJoomla($string);

        return (string) $string;
    }

    /**
     * @param $key
     * @param $playlistID
     * @param $pageToken
     * @param $maxResults
     *
     *
     * @throws Exception
     * @since version
     */
    public function buildPlaylistList($key, $playlistID = 'UULyz8iEvzxyhKEBzOTs6bJQ', $pageToken = 'EAAaI1BUOkNESWlFREUzUVRSQ09EQkdSVUV6UWprM09EVW9BVkFC', $maxResults = 50): void
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
        " . $video->snippet->title . "
        <br>
        <strong>Video ID:</strong>
        " . $video->snippet->resourceId->videoId . "
    </div>";
        }
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
     * @since   10.1.0
     */
    protected function handleTestApiAction(): array
    {
        $app       = Factory::getApplication();
        $apiKey    = $app->input->getString('api_key', '');
        $channelId = $app->input->getString('channel_id', '');

        return $this->testApiConnection($apiKey, $channelId);
    }

    /**
     * Handle fetchUpcoming AJAX action
     *
     * @return  array  Response data
     *
     * @since   10.1.0
     */
    protected function handleFetchUpcomingAction(): array
    {
        $app      = Factory::getApplication();
        $serverId = $app->input->getInt('server_id', 0);

        // Verify this is a YouTube server
        $db    = Factory::getContainer()->get('DatabaseDriver');
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
     * @since   10.1.0
     */
    protected function handleFetchChannelVideosAction(): array
    {
        $app   = Factory::getApplication();
        $input = $app->input;

        return $this->fetchChannelVideos($input);
    }

    /**
     * Handle searchChannelVideos AJAX action
     *
     * @return  array  Response data
     *
     * @since   10.1.0
     */
    protected function handleSearchChannelVideosAction(): array
    {
        $app   = Factory::getApplication();
        $input = $app->input;

        return $this->searchChannelVideos($input);
    }

    /**
     * Handle fetchChannelPlaylists AJAX action
     *
     * @return  array  Response data
     *
     * @since   10.1.0
     */
    protected function handleFetchChannelPlaylistsAction(): array
    {
        $app   = Factory::getApplication();
        $input = $app->input;

        return $this->fetchChannelPlaylists($input);
    }

    /**
     * Handle fetchPlaylistVideos AJAX action
     *
     * @return  array  Response data
     *
     * @since   10.1.0
     */
    protected function handleFetchPlaylistVideosAction(): array
    {
        $app   = Factory::getApplication();
        $input = $app->input;

        return $this->fetchPlaylistVideos($input);
    }

    /**
     * Handle fetchLiveVideos AJAX action
     *
     * @return  array  Response data
     *
     * @since   10.1.0
     */
    protected function handleFetchLiveVideosAction(): array
    {
        $app   = Factory::getApplication();
        $input = $app->input;

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
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__bsms_servers'))
            ->where($db->quoteName('id') . ' = ' . (int) $serverId);

        $db->setQuery($query);
        $result = $db->loadResult();

        if ($result) {
            $registry = new Registry($result);

            return $registry->toArray();
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
     * @since   10.1.0
     */
    public function searchChannelVideos(Input $input): array
    {
        $this->loadLanguage();

        $serverId   = $input->getInt('server_id', 0);
        $query      = $input->getString('query', '');
        $pageToken  = $input->getString('page_token', '');
        $maxResults = $input->getInt('max_results', 12);

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

            $params = [
                'q'          => $query,
                'type'       => 'video',
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
            $decoded = json_decode($errorMessage, true);

            if (isset($decoded['error']['message'])) {
                $errorMessage = Text::_('JBS_ADDON_YOUTUBE_API_ERROR') . ': ' . $decoded['error']['message'];
            }

            return ['success' => false, 'error' => $errorMessage];
        }
    }

    /**
     * Fetch upcoming videos for exclusion list (AJAX handler)
     *
     * @param   int  $serverId  The server ID
     *
     * @return  array  Response data with videos
     *
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
     * @since   10.1.0
     */
    protected function handleGetVideoStatusAction(): array
    {
        $app   = Factory::getApplication();
        $input = $app->input;

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
}
