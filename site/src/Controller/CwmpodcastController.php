<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Site\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Administrator\Helper\CwmpodcastTrackHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * Podcast controller — serves JSON endpoints for Podcasting 2.0.
 *
 * @since  10.3.0
 */
class CwmpodcastController extends BaseController
{
    /**
     * Serve JSON chapters for a media file.
     *
     * URL: index.php?option=com_proclaim&task=cwmpodcast.chapters&media_id={id}
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function chapters(): void
    {
        $mediaId = $this->input->getInt('media_id', 0);

        if ($mediaId <= 0) {
            $this->sendJson(['version' => '1.2.0', 'chapters' => []], 400);

            return;
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__bsms_mediafiles'))
            ->where($db->quoteName('id') . ' = ' . $mediaId)
            ->where($db->quoteName('published') . ' = 1');

        $db->setQuery($query);
        $rawParams = $db->loadResult();

        if (empty($rawParams)) {
            $this->sendJson(['version' => '1.2.0', 'chapters' => []], 404);

            return;
        }

        $params   = new Registry($rawParams);
        $chapters = $params->get('chapters', []);

        $output = ['version' => '1.2.0', 'chapters' => []];

        foreach ($chapters as $chapter) {
            $chapter = (object) $chapter;
            $time    = $chapter->time ?? '';
            $label   = $chapter->label ?? '';

            if (empty($time) || empty($label)) {
                continue;
            }

            $output['chapters'][] = [
                'startTime' => self::timeToSeconds($time),
                'title'     => $label,
            ];
        }

        $this->sendJson($output);
    }

    /**
     * Download tracking redirect (#1281).
     *
     * The podcast feed points <enclosure> URLs here (when the podcast has
     * track_downloads enabled) instead of at the live media. This counts the
     * download IAB-style (one per client per 24h, bots excluded), then 302-
     * redirects to the live media URL — which may be on this server OR an
     * external host (the common case, given storage limits). Counting is
     * best-effort and never blocks the redirect.
     *
     * URL: index.php?option=com_proclaim&task=cwmpodcast.track&media_id={id}
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function track(): void
    {
        $app     = Factory::getApplication();
        $mediaId = $this->input->getInt('media_id', 0);

        if ($mediaId <= 0) {
            $this->fail($app, 400);
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('mf.params'))
            ->select($db->quoteName('sr.params', 'sparams'))
            ->from($db->quoteName('#__bsms_mediafiles', 'mf'))
            ->leftJoin(
                $db->quoteName('#__bsms_servers', 'sr')
                . ' ON ' . $db->quoteName('sr.id') . ' = ' . $db->quoteName('mf.server_id')
            )
            ->where($db->quoteName('mf.id') . ' = :mid')
            ->where($db->quoteName('mf.published') . ' = 1')
            ->bind(':mid', $mediaId, ParameterType::INTEGER);

        $media = $db->setQuery($query)->loadObject();

        if (!$media) {
            $this->fail($app, 404);
        }

        // Reconstruct the exact live URL the enclosure would have used, so the
        // redirect target matches (local OR external host). Derived server-side
        // from the media id only — the target URL is never taken from the request
        // (that would be an open redirect).
        $config = new Registry();
        $config->loadString(Cwmparams::getAdmin()->params);
        $config->merge(Cwmparams::getTemplateparams()->params);
        $protocol = (string) $config->get('protocol', 'http://');

        $sreg = new Registry($media->sparams);
        $mreg = new Registry($media->params);
        $file = str_replace(' ', '%20', (string) $mreg->get('filename'));
        $path = Cwmhelper::mediaBuildUrl($sreg->get('path'), $file, $config, false, false, true);

        if (empty($path)) {
            $this->fail($app, 404);
        }

        $target = $protocol . $path;

        // Count the download — best-effort; a failure must never block playback.
        try {
            $userAgent = (string) $this->input->server->getString('HTTP_USER_AGENT', '');

            if (!CwmpodcastTrackHelper::isBot($userAgent)) {
                $ip   = (string) $this->input->server->getString('REMOTE_ADDR', '');
                $hash = CwmpodcastTrackHelper::clientHash($ip, $userAgent);

                CwmpodcastTrackHelper::record(
                    $db,
                    $mediaId,
                    $hash,
                    Factory::getDate()->toSql(),
                    Factory::getDate('-24 hours')->toSql()
                );
            }
        } catch (\Exception $e) {
            // Swallow — counting is non-critical.
        }

        $app->redirect($target, 302);
    }

    /**
     * Send a bare HTTP error status and terminate.
     *
     * @param   \Joomla\CMS\Application\CMSApplication  $app   Application.
     * @param   int                                     $code  HTTP status code.
     *
     * @return  never
     *
     * @since   __DEPLOY_VERSION__
     */
    private function fail($app, int $code): never
    {
        $app->setHeader('Status', (string) $code, true);
        $app->sendHeaders();
        $app->close();
        exit;
    }

    /**
     * Convert a time string (M:SS or H:MM:SS) to seconds.
     *
     * @param   string  $time  Time string
     *
     * @return  float  Seconds
     *
     * @since   10.3.0
     */
    private static function timeToSeconds(string $time): float
    {
        $parts = array_reverse(explode(':', $time));

        $seconds = (float) ($parts[0] ?? 0);
        $seconds += ((int) ($parts[1] ?? 0)) * 60;
        $seconds += ((int) ($parts[2] ?? 0)) * 3600;

        return $seconds;
    }

    /**
     * Send a JSON response and terminate.
     *
     * @param   array  $data    Data to encode
     * @param   int    $status  HTTP status code
     *
     * @return  void
     *
     * @since   10.3.0
     */
    private function sendJson(array $data, int $status = 200): void
    {
        $app = Factory::getApplication();
        $app->setHeader('Content-Type', 'application/json; charset=utf-8');
        $app->setHeader('Status', (string) $status);

        try {
            echo json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        } catch (\JsonException) {
            echo '{"version":"1.2.0","chapters":[]}';
        }

        $app->close();
    }
}
