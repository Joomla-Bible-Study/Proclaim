<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Youtube;

require_once __DIR__ . '/vendor/autoload.php';

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
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
        $html .= HtmlHelper::_('uitab.addTab', 'myTab', 'options', Text::_('Options'));

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
        $html .= HtmlHelper::_('uitab.endTab');

        return $html;
    }

    /**
     * YouTube URL to embed.
     *
     * @param  string  $url  YouTube URL to transform.
     *
     * @return string
     *
     * @since 10.0.0
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
    public function buildPlaylistList($key, $playlistID = 'UULyz8iEvzxyhKEBzOTs6bJQ', $pageToken = 'EAAaI1BUOkNESWlFREUzUVRSQ09EQkdSVUV6UWprM09EVW9BVkFC', $maxResults = 50)
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
     * Get server configuration by ID
     *
     * @param   int  $serverId  Server ID
     *
     * @return  array  Server params
     *
     * @since   10.0.0
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
     * Load addon language file
     *
     * @return  void
     *
     * @since   10.0.0
     */
    protected function loadLanguage(): void
    {
        $lang = Factory::getApplication()->getLanguage();
        $path = JPATH_ADMINISTRATOR . '/components/com_proclaim/src/Addons/Servers/Youtube';
        $lang->load('jbs_addon_youtube', $path);
    }

    /**
     * Fetch videos from YouTube channel (XHR handler)
     *
     * @param   Input  $input  Request input
     *
     * @return  array  Response data
     *
     * @since   10.0.0
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
     * @since   10.0.0
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
}
