<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmcountHelper;
use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;

/**
 * Controller for Podcasts
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmpodcastsController extends AdminController
{
    /**
     * Write the XML file Called from admin podcast list page.
     * Used for the Podcasts Page to create xml files.
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.0.0
     */
    public function writeXMLFile(): void
    {
        // Check for request forgeries (toolbar uses POST, validation page links use GET)
        if (!Session::checkToken('get') && !Session::checkToken('post')) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmpodcasts', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        $podcasts = new Cwmpodcast();
        $result   = $podcasts->makePodcasts();
        $this->setRedirect('index.php?option=com_proclaim&view=cwmpodcasts', $result);
    }

    /**
     * Validate all podcasts before building XML files.
     * Checks for required fields, valid images, associated media files, etc.
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.2.0
     */
    public function validate(): void
    {
        // Check for request forgeries (toolbar uses POST)
        if (!Session::checkToken('post')) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmpodcasts', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        $podcasts = new Cwmpodcast();
        $results  = $podcasts->validateAllPodcasts();

        // Store validation results in session for display
        $this->app->setUserState('com_proclaim.podcasts.validation', $results);

        // Count ready vs not ready
        $ready    = 0;
        $notReady = 0;

        foreach ($results as $result) {
            if ($result['ready']) {
                $ready++;
            } else {
                $notReady++;
            }
        }

        $message = Text::sprintf('JBS_PDC_VALIDATION_COMPLETE', $ready, $notReady);
        $type    = $notReady > 0 ? 'warning' : 'success';

        $this->setRedirect('index.php?option=com_proclaim&view=cwmpodcasts&layout=validation', $message, $type);
    }

    /**
     * Fix missing durations for media files associated with podcasts.
     * Reads MP3 files to calculate duration.
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.2.0
     */
    public function fixDurations(): void
    {
        // Check for request forgeries (check 'get' since this is a link click)
        if (!Session::checkToken('get')) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmpodcasts', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        $podcasts = new Cwmpodcast();
        $results  = $podcasts->fixMediaDurations();

        // Build message
        $message = Text::sprintf(
            'JBS_PDC_FIX_DURATION_COMPLETE',
            $results['fixed'],
            $results['failed'],
            $results['skipped']
        );

        $type = $results['failed'] > 0 ? 'warning' : 'success';

        // Store details in session for display on validation page
        $this->app->setUserState('com_proclaim.podcasts.duration_fix', $results);

        // Redirect back to validation page to show details
        $this->setRedirect('index.php?option=com_proclaim&view=cwmpodcasts&layout=validation', $message, $type);
    }

    /**
     * AJAX: Get list of media files needing duration fix.
     * Returns JSON with file IDs and titles.
     *
     * @return void
     *
     * @since 10.2.0
     */
    public function getMediaFilesForDuration(): void
    {
        // Check token
        if (!Session::checkToken('get') && !Session::checkToken('post')) {
            echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
            $this->app->close();

            return;
        }

        try {
            $podcasts = new Cwmpodcast();
            $files    = $podcasts->getMediaFilesNeedingDuration();

            echo new JsonResponse([
                'files'   => $files,
                'total'   => \count($files),
                'ffprobe' => $podcasts->getAvailableDurationMethods()['ffprobe'],
            ]);
        } catch (\Exception $e) {
            echo new JsonResponse(null, $e->getMessage(), true);
        }

        $this->app->close();
    }

    /**
     * AJAX: Fix duration for a single media file.
     * Returns JSON with result.
     *
     * @return void
     *
     * @since 10.2.0
     */
    public function fixSingleDuration(): void
    {
        // Check token
        if (!Session::checkToken('get') && !Session::checkToken('post')) {
            echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
            $this->app->close();

            return;
        }

        $mediaId = $this->getInput()->getInt('media_id', 0);

        if ($mediaId <= 0) {
            echo new JsonResponse(null, Text::_('JBS_PDC_INVALID_MEDIA_ID'), true);
            $this->app->close();

            return;
        }

        try {
            $podcasts = new Cwmpodcast();
            $result   = $podcasts->fixSingleMediaDuration($mediaId);

            echo new JsonResponse($result);
        } catch (\Exception $e) {
            echo new JsonResponse(null, $e->getMessage(), true);
        }

        $this->app->close();
    }

    /**
     * AJAX: Get list of media files needing metadata fix.
     * Returns JSON with file IDs, titles, and missing fields.
     *
     * @return void
     *
     * @since 10.2.0
     */
    public function getMediaFilesForMetadata(): void
    {
        // Check token
        if (!Session::checkToken('get') && !Session::checkToken('post')) {
            echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
            $this->app->close();

            return;
        }

        try {
            $podcasts = new Cwmpodcast();
            $files    = $podcasts->getMediaFilesNeedingMetadata();
            $methods  = $podcasts->getAvailableDurationMethods();

            echo new JsonResponse([
                'files'       => $files,
                'total'       => \count($files),
                'ffprobe'     => $methods['ffprobe'],
                'youtube_api' => $methods['youtube_api'],
            ]);
        } catch (\Exception $e) {
            echo new JsonResponse(null, $e->getMessage(), true);
        }

        $this->app->close();
    }

    /**
     * AJAX: Fix all metadata for a single media file.
     * Handles size, mime_type, and duration in one pass.
     *
     * @return void
     *
     * @since 10.2.0
     */
    public function fixSingleMetadata(): void
    {
        // Check token
        if (!Session::checkToken('get') && !Session::checkToken('post')) {
            echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
            $this->app->close();

            return;
        }

        $mediaId = $this->getInput()->getInt('media_id', 0);

        if ($mediaId <= 0) {
            echo new JsonResponse(null, Text::_('JBS_PDC_INVALID_MEDIA_ID'), true);
            $this->app->close();

            return;
        }

        try {
            $podcasts = new Cwmpodcast();
            $result   = $podcasts->fixSingleMediaMetadata($mediaId);

            echo new JsonResponse($result);
        } catch (\Exception $e) {
            echo new JsonResponse(null, $e->getMessage(), true);
        }

        $this->app->close();
    }

    /**
     * AJAX: Store fix results in session for display after validation.
     *
     * @return void
     *
     * @since 10.2.0
     */
    public function storeFixResults(): void
    {
        // Check token
        if (!Session::checkToken('get') && !Session::checkToken('post')) {
            echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
            $this->app->close();

            return;
        }

        try {
            // Get JSON body
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            if ($data) {
                $results = [
                    'fixed'      => (int) ($data['fixed'] ?? 0),
                    'failed'     => (int) ($data['failed'] ?? 0),
                    'skipped'    => (int) ($data['skipped'] ?? 0),
                    'fixedItems' => $data['fixedItems'] ?? [],
                    'errors'     => $data['errors'] ?? [],
                ];

                $this->app->setUserState('com_proclaim.podcasts.metadata_fix', $results);
            }

            echo new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            echo new JsonResponse(null, $e->getMessage(), true);
        }

        $this->app->close();
    }

    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  The array of possible config values. Optional.
     *
     * @return  BaseDatabaseModel
     *
     * @since   1.6
     */
    public function getModel($name = 'Cwmpodcast', $prefix = 'Administrator', $config = ['ignore_request' => true]): BaseDatabaseModel
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Method to get the JSON-encoded counts for Podcasts
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function getQuickIconPodcasts(): void
    {
        CwmcountHelper::sendQuickIconResponse('#__bsms_podcast', 'COM_PROCLAIM_N_QUICKICON_PODCASTS');
    }
}
