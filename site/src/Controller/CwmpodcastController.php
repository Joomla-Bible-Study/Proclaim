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

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Database\DatabaseInterface;
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
