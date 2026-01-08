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

            // Verify this is a YouTube server
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true)
                ->select($db->quoteName('type'))
                ->from($db->quoteName('#__bsms_servers'))
                ->where($db->quoteName('id') . ' = ' . $serverId);
            $db->setQuery($query);
            $serverType = $db->loadResult();

            if (strtolower($serverType) !== 'youtube') {
                CWMAddonYoutube::outputJson(['success' => false, 'error' => 'Selected server is not a YouTube server']);

                return;
            }

            // Start buffering to capture any deprecation warnings from vendor code
            ob_start();

            $youtube = new CWMAddonYoutube();
            $result  = @$youtube->fetchUpcomingVideos($serverId);

            // Discard any output from vendor code (deprecation warnings, etc.)
            ob_end_clean();

            CWMAddonYoutube::outputJson($result);
        } catch (\Exception $e) {
            CWMAddonYoutube::outputJson([
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

        $app = Factory::getApplication();

        try {
            $apiKey    = $app->input->getString('api_key', '');
            $channelId = $app->input->getString('channel_id', '');

            // Start buffering to capture any deprecation warnings from vendor code
            ob_start();

            $youtube = new CWMAddonYoutube();
            $result  = @$youtube->testApiConnection($apiKey, $channelId);

            // Discard any output from vendor code (deprecation warnings, etc.)
            ob_end_clean();

            CWMAddonYoutube::outputJson($result);
        } catch (\Exception $e) {
            CWMAddonYoutube::outputJson([
                'success' => false,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
