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
use Joomla\CMS\Response\JsonResponse;
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
     * @throws  \Exception
     * @since   10.0.0
     */
    public function fetchUpcoming(): void
    {
        $app = Factory::getApplication();

        try {
            $serverId = $app->input->getInt('server_id', 0);

            if (!$serverId) {
                throw new \RuntimeException('No server ID provided');
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
                throw new \RuntimeException('Selected server is not a YouTube server');
            }

            // Fetch upcoming videos using the YouTube addon
            $youtube = new CWMAddonYoutube();
            $input   = new Input([
                'server_id'   => $serverId,
                'max_results' => 25,
                'event_type'  => 'upcoming',
            ]);

            $result = $youtube->fetchLiveVideos($input);

            if (!$result['success']) {
                throw new \RuntimeException($result['error'] ?? 'Failed to fetch videos');
            }

            echo new JsonResponse([
                'success' => true,
                'videos'  => $result['videos'] ?? [],
            ]);
        } catch (\Exception $e) {
            echo new JsonResponse([
                'success' => false,
                'error'   => $e->getMessage(),
            ]);
        }

        $app->close();
    }
}
