<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

use CWM\Component\Proclaim\Administrator\Addons\Servers\Youtube\CWMAddonYoutube;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\Input\Input;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller for Server
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmserverController extends FormController
{
    /**
     * Method to add a new record.
     *
     * @return  bool  True if the record can be added, a error object if not.
     *
     * @throws \Exception
     * @since   12.2
     */
    public function add(): bool
    {
        $app = Factory::getApplication();

        if (parent::add()) {
            $app->setUserState('com_proclaim.edit.cwmserver.server_name', null);
            $app->setUserState('com_proclaim.edit.cwmserver.type', null);

            return true;
        }

        return false;
    }

    /**
     * Resets the User state for the server type. Needed to allow the value from the DB to be used
     *
     * @param   int     $key     ?
     * @param   string  $urlVar  ?
     *
     * @return  bool
     *
     * @throws \Exception
     * @since   9.0.0
     */
    public function edit($key = null, $urlVar = null): bool
    {
        $app    = Factory::getApplication();
        $result = parent::edit();

        if ($result) {
            $app->setUserState('com_proclaim.edit.cwmserver.server_name', null);
            $app->setUserState('com_proclaim.edit.cwmserver.type', null);
        }

        return $result;
    }

    /**
     * Sets the type of endpoint currently being configured.
     *
     * @return  void
     *
     * @throws \Exception
     * @since   9.0.0
     */
    public function setType(): void
    {
        $app   = Factory::getApplication();
        $input = $app->input;

        $data  = $input->get('jform', [], 'post');
        $sname = $data['server_name'] ?? '';
        $type  = json_decode(base64_decode($data['type'] ?? ''), true, 512, JSON_THROW_ON_ERROR);

        $recordId = $type['id'] ?? 0;

        // Save the endpoint in the session
        $app->setUserState('com_proclaim.edit.cwmserver.type', $type['name'] ?? '');
        $app->setUserState('com_proclaim.edit.cwmserver.server_name', $sname);

        $this->setRedirect(
            Route::_(
                'index.php?option=' . $this->option . '&view=' . $this->view_item .
                $this->getRedirectToItemAppend((int)$recordId),
                false
            )
        );
    }

    /**
     * Fetch upcoming videos from a YouTube server (AJAX handler)
     *
     * @return  void
     *
     * @since   10.0.0
     */
    public function fetchUpcoming(): void
    {
        // Suppress any error output that might corrupt JSON
        @ini_set('display_errors', '0');
        @error_reporting(0);

        // Clear any output buffers completely
        while (@ob_get_level()) {
            @ob_end_clean();
        }

        $app = Factory::getApplication();

        try {
            $serverId = $app->input->getInt('server_id', 0);

            if (!$serverId) {
                $this->outputJson(['success' => false, 'error' => 'No server ID provided']);

                return;
            }

            // Verify this is a YouTube server
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true)
                ->select($db->quoteName('type'))
                ->from($db->quoteName('#__bsms_servers'))
                ->where($db->quoteName('id') . ' = ' . $serverId);
            $db->setQuery($query);
            $serverType = $db->loadResult();

            if (strtolower($serverType) !== 'youtube') {
                $this->outputJson(['success' => false, 'error' => 'Selected server is not a YouTube server']);

                return;
            }

            // Fetch upcoming videos using the YouTube addon
            // Start buffering to capture any deprecation warnings from vendor code
            ob_start();

            $youtube = new CWMAddonYoutube();
            $input   = new Input([
                'server_id'   => $serverId,
                'max_results' => 25,
                'event_type'  => 'upcoming',
            ]);

            $result = @$youtube->fetchLiveVideos($input);

            // Discard any output from vendor code (deprecation warnings, etc.)
            ob_end_clean();

            if (!$result['success']) {
                $this->outputJson(['success' => false, 'error' => $result['error'] ?? 'Failed to fetch videos']);

                return;
            }

            $this->outputJson([
                'success' => true,
                'videos'  => $result['videos'] ?? [],
            ]);
        } catch (\Exception $e) {
            $this->outputJson([
                'success' => false,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Test YouTube API credentials (AJAX handler)
     *
     * @return  void
     *
     * @since   10.0.0
     */
    public function testYoutubeApi(): void
    {
        // Suppress any error output that might corrupt JSON
        @ini_set('display_errors', '0');
        @error_reporting(0);

        // Clear any output buffers completely
        while (@ob_get_level()) {
            @ob_end_clean();
        }

        $app     = Factory::getApplication();
        $result  = ['success' => false, 'error' => 'Unknown error'];

        try {
            $apiKey    = $app->input->getString('api_key', '');
            $channelId = $app->input->getString('channel_id', '');

            if (empty($apiKey)) {
                $result = ['success' => false, 'error' => 'API key is required'];
                $this->outputJson($result);

                return;
            }

            if (empty($channelId)) {
                $result = ['success' => false, 'error' => 'Channel ID is required'];
                $this->outputJson($result);

                return;
            }

            // Load the YouTube addon autoloader
            $autoloader = JPATH_ADMINISTRATOR . '/components/com_proclaim/src/Addons/Servers/Youtube/vendor/autoload.php';

            if (!file_exists($autoloader)) {
                $result = ['success' => false, 'error' => 'YouTube addon not properly installed'];
                $this->outputJson($result);

                return;
            }

            require_once $autoloader;

            // Test the API by fetching channel info
            // Start buffering to capture any deprecation warnings from vendor code
            ob_start();

            $client = new \Google\Client();
            $client->setApplicationName('Proclaim');
            $client->setDeveloperKey($apiKey);

            $youtube = new \Google\Service\YouTube($client);

            // Try to get channel details (suppress warnings from cURL deprecation in PHP 8.5)
            $response = @$youtube->channels->listChannels('snippet,statistics', [
                'id' => $channelId,
            ]);

            // Discard any output from vendor code (deprecation warnings, etc.)
            ob_end_clean();

            if (empty($response->items)) {
                $result = ['success' => false, 'error' => 'Channel not found. Please verify the Channel ID.'];
                $this->outputJson($result);

                return;
            }

            $channel = $response->items[0];

            $result = [
                'success' => true,
                'message' => 'API connection successful!',
                'channel' => [
                    'title'           => $channel->snippet->title,
                    'description'     => substr($channel->snippet->description ?? '', 0, 100),
                    'subscriberCount' => $channel->statistics->subscriberCount ?? null,
                    'videoCount'      => $channel->statistics->videoCount ?? null,
                ],
            ];
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            // Try to parse Google API error
            $decoded = json_decode($errorMessage, true);

            if (isset($decoded['error']['message'])) {
                $errorMessage = 'YouTube API Error: ' . $decoded['error']['message'];
            }

            $result = ['success' => false, 'error' => $errorMessage];
        }

        $this->outputJson($result);
    }

    /**
     * Output JSON and terminate
     *
     * @param   array  $data  The data to encode as JSON
     *
     * @return  void
     *
     * @since   10.0.0
     */
    private function outputJson(array $data): void
    {
        // Clear all output buffers
        while (@ob_get_level()) {
            @ob_end_clean();
        }

        // Send headers before any output
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
        }

        // Encode and output JSON
        $json = json_encode($data);

        if ($json === false) {
            $json = '{"success":false,"error":"JSON encoding failed"}';
        }

        echo $json;

        // Force flush and terminate
        if (\function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        exit;
    }
}
