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
use CWM\Component\Proclaim\Administrator\Helper\CwmmediaStreamer;
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
        $query = $db->createQuery();
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

        // The study and series levels come along because a media file is only
        // as reachable as the message it belongs to — see the access check below.
        $query = $db->createQuery();
        $query->select(
            $db->quoteName('#__bsms_mediafiles') . '.*,'
            . $db->quoteName('#__bsms_servers.id', 'ssid') . ', ' . $db->quoteName('#__bsms_servers.params', 'sparams')
            . ', ' . $db->quoteName('study.access', 'study_access')
            . ', ' . $db->quoteName('series.access', 'series_access')
        )
            ->from($db->quoteName('#__bsms_mediafiles'))
            ->leftJoin(
                $db->quoteName('#__bsms_servers') . ' ON ('
                . $db->quoteName('#__bsms_servers.id') . ' = ' . $db->quoteName('#__bsms_mediafiles.server_id') . ')'
            )
            ->leftJoin(
                $db->quoteName('#__bsms_studies', 'study') . ' ON ('
                . $db->quoteName('study.id') . ' = ' . $db->quoteName('#__bsms_mediafiles.study_id') . ')'
            )
            ->leftJoin(
                $db->quoteName('#__bsms_series', 'series') . ' ON ('
                . $db->quoteName('series.id') . ' = ' . $db->quoteName('study.series_id') . ')'
            )
            ->where($db->quoteName('#__bsms_mediafiles.id') . ' = ' . $mid)
            ->where($db->quoteName('#__bsms_mediafiles.published') . ' = 1');
        $db->setQuery($query, 0, 1);

        $media = $db->loadObject();

        if (!$media) {
            $this->sendError($app, 404, 'Media not found');
        }

        // A media file is only as reachable as the message it belongs to, and
        // the series that message is in. Checking the file's own level alone
        // let a Public file hang off a Registered message and be downloaded by
        // anyone — j6-dev had seven of those.
        //
        // Every level in the chain must be satisfied, rather than picking the
        // "most restrictive" one: Joomla view levels are arbitrary sets of user
        // groups with no ordering between them, so there is no such thing as
        // the strictest of Registered, Special and Guest. Requiring all of them
        // is the same rule the listing queries already apply to study + series
        // (CwmsermonsModel::getListQuery()).
        //
        // ⚠️ This gates Proclaim's download route only. A file stored under the
        // web root is still served directly by the web server, which never
        // enters PHP.
        $user = $app->getIdentity();

        if (!self::isAccessible($media, $user->getAuthorisedViewLevels())) {
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

        // Streaming is CwmmediaStreamer's job — it resolves the target to local
        // disk or proxies it, and it is the same implementation the podcast
        // redirect uses, so there is one Range/HEAD/If-Modified-Since handler
        // rather than two.
        //
        // What this replaces was weaker in two ways: it sent Content-Length and
        // readfile() with no Range support, so a browser could not seek in
        // gated audio or video; and its remote branch fopen()ed an
        // admin-configured URL and relayed the response to the caller with no
        // SSRF guard — the shape that was patched for the podcast endpoint in
        // 10.5.5 but never here.
        //
        // Access was already checked above. The streamer answers how to send a
        // file, never whether to.

        // Long downloads should not hold the session lock.
        if (session_id()) {
            session_write_close();
        }

        // Headers the streamer does not set. It owns Content-Type,
        // Content-Length, Accept-Ranges, Content-Range and Last-Modified —
        // Content-Length especially, which has to reflect the range served
        // rather than the whole file.
        $safeFilename = preg_replace('/[^\w.\-]/', '_', basename((string) $params->get('filename')));
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename="' . $safeFilename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Pragma: public');
        header('Content-Transfer-Encoding: binary');

        // Uri::root(), not the incoming Host header: that is client-supplied
        // and proxy-rewritable, so comparing against it misroutes local media
        // down the remote path.
        CwmmediaStreamer::serve(
            $download_file,
            'application/octet-stream',
            (string) (parse_url(Uri::root(), PHP_URL_HOST) ?: ''),
            (string) $input->server->getString('HTTP_RANGE', ''),
            (string) $input->server->getString('HTTP_IF_MODIFIED_SINCE', ''),
            (string) $input->server->getString('HTTP_IF_NONE_MATCH', ''),
            strtoupper((string) $input->getMethod()) === 'HEAD'
        );
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
    /**
     * Whether a user holding these view levels may reach this media file.
     *
     * A media file is only as reachable as the message it belongs to, and the
     * series that message is in. Checking the file's own level alone let a
     * Public file hang off a Registered message and be downloaded by anyone.
     *
     * Every level in the chain has to be satisfied, rather than reducing them
     * to whichever is "most restrictive": Joomla view levels are arbitrary sets
     * of user groups with no ordering between them, so there is no strictest of
     * Registered, Special and Guest to pick. Requiring all of them is the rule
     * the listing queries already apply to study + series — see
     * `CwmsermonsModel::getListQuery()`.
     *
     * A null level means that link in the chain is absent (a media file with no
     * study, or a study in no series) and constrains nothing.
     *
     * ⚠️ This governs Proclaim's download route only. A file stored under the
     * web root is served by the web server without ever entering PHP, so no
     * check here can protect it.
     *
     * @param   object        $media   Media row, joined to its study and series levels.
     * @param   array<int>    $levels  The user's authorised view levels.
     *
     * @return  bool
     *
     * @since   10.5.8
     */
    public static function isAccessible(object $media, array $levels): bool
    {
        $required = [
            $media->access ?? null,
            $media->study_access ?? null,
            $media->series_access ?? null,
        ];

        foreach ($required as $level) {
            if ($level !== null && !\in_array((int) $level, $levels, true)) {
                return false;
            }
        }

        return true;
    }

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
        $query = $db->createQuery();
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
