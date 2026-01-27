<?php

/**
 * Proclaim Download Class
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
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

/**
 * Proclaim Download Class
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class Cwmdownload
{
    /**
     * Method to send a file to the browser
     *
     * @param   int  $mid  ID of media
     *
     * @return void
     * @throws \Exception
     * @since 6.1.2
     */
    public function download($mid): void
    {
        // Clears file status cache
        clearstatcache();

        $app        = Factory::getApplication();
        $input      = new Input();
        $templateId = $input->get('t', '1', 'int');
        $db         = Factory::getContainer()->get('DatabaseDriver');
        $mid        = (int) $mid;

        // Get the template so we can find a protocol
        $query = $db->getQuery(true);
        $query->select('id, params')
            ->from('#__bsms_templates')
            ->where('id = ' . $templateId);
        $db->setQuery($query);
        $template = $db->loadObject();

        if (!$template) {
            $this->sendError($app, 404, 'Template not found');
        }

        // Convert parameter fields to objects.
        $registry = new Registry();
        $registry->loadString($template->params);
        $params = $registry;

        $query = $db->getQuery(true);
        $query->select(
            '#__bsms_mediafiles.*,'
            . ' #__bsms_servers.id AS ssid, #__bsms_servers.params AS sparams'
        )
            ->from('#__bsms_mediafiles')
            ->leftJoin('#__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server_id)')
            ->where('#__bsms_mediafiles.id = ' . $mid);
        $db->setQuery($query, 0, 1);

        $media = $db->loadObject();

        if (!$media) {
            $this->sendError($app, 404, 'Media not found');
        }

        // Increment download count after validation
        $this->hitDownloads($mid);

        $reg = new Registry();
        $reg->loadString($media->sparams);
        $sparams = $reg->toObject();

        $media->spath = $sparams->path ?? '';

        $registry = new Registry();
        $registry->loadString($media->params);
        $params->merge($registry);

        $download_file = Cwmhelper::mediaBuildUrl($media->spath, $params->get('filename'), $params, true);

        if ((int) $params->get('size', 0) === 0) {
            $getSize = Cwmhelper::getRemoteFileSize($download_file);
        } else {
            $getSize = $params->get('size', 0);
        }

        @set_time_limit(0);
        ignore_user_abort(false);
        ini_set('output_buffering', 0);
        ini_set('zlib.output_compression', 0);

        // Bytes per chunk (10 MB)
        $chunk = 10 * 1024 * 1024;

        $fh = @fopen($download_file, 'rb');

        if (!$fh) {
            if (JBSMDEBUG) {
                echo '<pre>' . $download_file . '</pre>';
            }

            $this->sendError($app, 500, 'Unable to open file');
        }

        // Clean the output buffer, Added to fix ZIP file corruption
        @ob_end_clean();
        @ob_start();

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($params->get('filename')) . '"');
        header('Expires: 0');
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header('Pragma: public');
        header("Content-Transfer-Encoding: binary");
        header('Content-Length: ' . $getSize);

        // Repeat reading until EOF
        while (!feof($fh)) {
            echo fread($fh, $chunk);
            @ob_flush();
            @flush();
        }

        fclose($fh);
        $app->close();
    }

    /**
     * Send an HTTP error response and terminate
     *
     * @param   CMSApplication  $app      The application
     * @param   int                                     $code     HTTP status code
     * @param   string                                  $message  Error message
     *
     * @return  never
     *
     * @since   10.0.0
     */
    protected function sendError($app, int $code, string $message): never
    {
        $statusText = match ($code) {
            400     => 'Bad Request',
            404     => 'Not Found',
            500     => 'Internal Server Error',
            default => 'Error',
        };

        $app->setHeader('status', $code . ' ' . $statusText);
        $app->sendHeaders();
        echo $message;
        $app->close();
        exit;
    }

    /**
     * Method to track Downloads
     *
     * @param   int  $mid  Media ID
     *
     * @return  bool True if hit makes it or False if failed to query
     *
     * @since   7.0.0
     */
    protected function hitDownloads(int $mid): bool
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->update('#__bsms_mediafiles')
            ->set('downloads = downloads + 1 ')
            ->where('id = ' . $mid);
        $db->setQuery($query);

        if (!$db->execute()) {
            return false;
        }

        return true;
    }
}
