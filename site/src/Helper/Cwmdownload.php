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

use CWM\Component\Proclaim\Administrator\Helper\CwmDebug;
use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
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
     * @throws \Exception If the template or media is not found.
     * @since 6.1.2
     */
    public function download(int $mid): void
    {
        // Clears file status cache
        clearstatcache();

        $app        = Factory::getApplication();
        $input      = Factory::getApplication()->getInput();
        $templateId = $input->get('t', '1', 'int');
        $db         = Factory::getContainer()->get(DatabaseInterface::class);

        // Get the template so we can find a protocol
        $query = $db->getQuery(true);
        $query->select($db->quoteName(['id', 'params']))
            ->from($db->quoteName('#__bsms_templates'))
            ->where($db->quoteName('id') . ' = ' . $templateId);
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
            $db->quoteName('#__bsms_mediafiles') . '.*,'
            . $db->quoteName('#__bsms_servers.id', 'ssid') . ', ' . $db->quoteName('#__bsms_servers.params', 'sparams')
        )
            ->from($db->quoteName('#__bsms_mediafiles'))
            ->leftJoin(
                $db->quoteName('#__bsms_servers') . ' ON ('
                . $db->quoteName('#__bsms_servers.id') . ' = ' . $db->quoteName('#__bsms_mediafiles.server_id') . ')'
            )
            ->where($db->quoteName('#__bsms_mediafiles.id') . ' = ' . $mid)
            ->where($db->quoteName('#__bsms_mediafiles.published') . ' = 1');
        $db->setQuery($query, 0, 1);

        $media = $db->loadObject();

        if (!$media) {
            $this->sendError($app, 404, 'Media not found');
        }

        // Verify the current user has the required access level
        $user         = $app->getIdentity();
        $accessLevels = $user->getAuthorisedViewLevels();

        if (isset($media->access) && !\in_array((int) $media->access, $accessLevels, true)) {
            $this->sendError($app, 403, 'Access denied');
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
        $isLocal       = false;

        CwmDebug::log(
            'mid=' . $mid . ' file=' . ($params->get('filename') ?: '(none)') . ' template=' . $templateId,
            'download'
        );

        // Optimization: Check if a file is local to avoid HTTP loopback and get an accurate size
        if ($download_file) {
            $root = Uri::root();
            if (str_starts_with($download_file, $root)) {
                $relativePath = substr($download_file, \strlen($root));
                $localPath    = JPATH_ROOT . '/' . $relativePath;
                // Clean up path
                $localPath = str_replace('//', '/', $localPath);

                if (file_exists($localPath)) {
                    $download_file = $localPath;
                    $isLocal       = true;
                }
            }
        }

        if ((int) $params->get('size', 0) === 0) {
            if ($isLocal) {
                $getSize = filesize($download_file);
            } else {
                $getSize = Cwmhelper::getRemoteFileSize($download_file);
            }
        } else {
            $getSize = $params->get('size', 0);
        }

        // Disable the time limit and close the session to prevent locking
        @set_time_limit(0);
        ignore_user_abort(false);
        if (session_id()) {
            session_write_close();
        }

        ini_set('output_buffering', 0);
        ini_set('zlib.output_compression', 0);

        // Clean the output buffer, Added to fix ZIP file corruption
        while (ob_get_level()) {
            @ob_end_clean();
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        $safeFilename = preg_replace('/[^\w.\-]/', '_', basename($params->get('filename')));
        header('Content-Disposition: attachment; filename="' . $safeFilename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Pragma: public');
        header('Content-Transfer-Encoding: binary');

        if ($getSize > 0) {
            header('Content-Length: ' . $getSize);
        }

        // Flush headers before streaming
        flush();

        if ($isLocal) {
            readfile($download_file);
        } else {
            // Bytes per chunk (8 KB) - Reduced from 10MB to save memory
            $chunk = 8 * 1024;

            $fh = @fopen($download_file, 'rb');

            if (!$fh) {
                CwmDebug::log('download fopen failed path=' . $download_file, 'download');

                // We cannot send a 500 error cleanly if headers are already sent, but we can try
                exit;
            }

            // Repeat reading until EOF
            while (!feof($fh)) {
                echo fread($fh, $chunk);
                flush();
            }

            fclose($fh);
        }

        $app->close();
    }

    /**
     * Send an HTTP error response and terminate
     *
     * @param   CMSApplication  $app      The application
     * @param   int             $code     HTTP status code
     * @param   string          $message  Error message
     *
     * @return  never
     *
     * @since   10.0.0
     */
    protected function sendError(CMSApplication $app, int $code, string $message): never
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
     * @return  bool True if hit makes it, or False if failed to query
     *
     * @since   7.0.0
     */
    protected function hitDownloads(int $mid): bool
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__bsms_mediafiles'))
            ->set($db->quoteName('downloads') . ' = ' . $db->quoteName('downloads') . ' + 1')
            ->where($db->quoteName('id') . ' = ' . $mid);
        $db->setQuery($query);

        if (!$db->execute()) {
            return false;
        }

        return true;
    }
}
