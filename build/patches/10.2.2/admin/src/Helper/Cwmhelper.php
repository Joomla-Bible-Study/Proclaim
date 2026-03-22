<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmyoutubeFileCache;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Core Bible Study Helper
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 * */
class Cwmhelper
{
    /**
     * Extension Name
     *
     * @var string
     *
     * @since 8.0.0
     */
    public static string $extension = 'com_proclaim';

    /**
     * Streaming platform hosts where file size is irrelevant (no downloadable file).
     *
     * @var string[]
     * @since 10.2.0
     */
    private static array $streamingHosts = [
        'youtu.be', 'youtube.com', 'vimeo.com', 'dailymotion.com',
        'facebook.com', 'fb.watch', 'rumble.com', 'soundcloud.com',
        'wistia.com', 'wistia.net', 'rfrm.io', 'rfrm.net',
        'drive.google.com', 'docs.google.com',
    ];

    /**
     * Per-request cache of remote file sizes to avoid duplicate HEAD requests.
     *
     * @var array<string, int>
     * @since 10.2.0
     */
    private static array $fileSizeCache = [];

    /**
     * Method to get file size via HTTP HEAD request.
     *
     * Skips streaming platforms (YouTube, Vimeo, etc.) where file size is
     * irrelevant. Uses a 5-second timeout and caches results per request.
     *
     * @param   string  $url  URL
     *
     * @return  int  Return size or 0 on failure.
     *
     * @since 9.0.0
     */
    public static function getRemoteFileSize(string $url): int
    {
        if ($url === '') {
            return 0;
        }

        // Skip streaming platforms — file size is meaningless for embedded players
        $host = (string) parse_url($url, PHP_URL_HOST);

        foreach (self::$streamingHosts as $streamHost) {
            if (str_contains($host, $streamHost)) {
                return 0;
            }
        }

        // Removes a bad url problem in some DB's
        if (substr_count($url, '/http')) {
            $url = ltrim($url, '/');
        }

        if (!substr_count($url, 'http://') && !substr_count($url, 'https://')) {
            if (substr_count($url, '//')) {
                $url = 'https:' . $url;
            } else {
                $url = 'https://' . $url;
            }
        }

        // Return cached result if we already fetched this URL in this request
        if (isset(self::$fileSizeCache[$url])) {
            return self::$fileSizeCache[$url];
        }

        $size = 0;

        try {
            // Use stream context with 5-second timeout instead of unbounded get_headers()
            $context = stream_context_create([
                'http' => [
                    'method'  => 'HEAD',
                    'timeout' => 5,
                ],
            ]);
            $headers = @get_headers($url, true, $context);
        } catch (\Exception $e) {
            self::$fileSizeCache[$url] = 0;

            return 0;
        }

        if (\is_array($headers)) {
            $head = array_change_key_case($headers);
        } else {
            self::$fileSizeCache[$url] = 0;

            return 0;
        }

        if (isset($head['content-length']) && \is_array($head['content-length'])) {
            if (\count($head['content-length']) >= 1) {
                $dif  = \count($head['content-length']) - 1;
                $size = $head['content-length'][$dif];
            } else {
                $size = $head['content-length'][0];
            }
        } elseif (isset($head['content-length'])) {
            $size = $head['content-length'];
        }

        $size                      = (int) $size;
        self::$fileSizeCache[$url] = $size;

        return $size;
    }

    /**
     * Set File Size for MediaFile
     *
     * @param   int  $id    ID of MediaFile
     * @param   int  $size  Size of file in bits
     *
     * @return void
     *
     * @since 9.0.14
     */
    public static function setFileSize(int $id, int $size): void
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->select($db->quoteName(['id', 'params']))
            ->from($db->quoteName('#__bsms_mediafiles'))
            ->where($db->quoteName('id') . ' = ' . (int) $id);

        $db->setQuery($query);
        $media = $db->loadObject();

        $reg = new Registry();
        $reg->loadString($media->params);
        $reg->set('size', $size);

        $update         = new \stdClass();
        $update->id     = $id;
        $update->params = $reg->toString();

        $db->updateObject('#__bsms_mediafiles', $update, 'id');
    }

    /**
     * Media Build URL Fix up for '/' and protocol.
     *
     * @param   string    $spath        Server Path
     * @param   string    $path         File
     * @param   Registry  $params       Parameters.
     * @param   bool      $setProtocol  True add protocol els no
     * @param   bool      $local        Local server
     * @param   bool      $podcast      True if from a precast
     *
     * @return string Completed path.
     *
     * @since 9.0.3
     */
    public static function mediaBuildUrl(
        $spath,
        $path,
        Registry $params,
        bool $setProtocol = false,
        bool $local = false,
        bool $podcast = false
    ): string {
        if (empty($path)) {
            return false;
        }

        if ($spath) {
            $spath = rtrim($spath, '/');
        } else {
            $spath = '';
        }

        $path     = ltrim($path, '/');
        $host     = $_SERVER['HTTP_HOST'];
        $protocol = Uri::root();

        // To see if the server is local
        if (str_contains($spath, $host)) {
            $local = true;
        }

        if (substr_count($path, 'http://') && $podcast) {
            return str_replace('http://', "", $path);
        }

        if (substr_count($path, 'https://') && $podcast) {
            return str_replace('https://', "", $path);
        }

        if (!empty($spath) && $podcast) {
            return str_replace('//', "", $spath) . '/' . $path;
        }

        if (!substr_count($path, '://') && !substr_count($path, '//') && $setProtocol) {
            if (empty($spath)) {
                return $protocol . $path;
            }

            $protocol = $params->get('protocol', 'http://');

            if ((substr_count($spath, '://') || substr_count($spath, '//')) && !empty($spath)) {
                if (substr_count($spath, '//')) {
                    $spath = substr($spath, 2);
                }

                return $protocol . $spath . '/' . $path;
            }

            // Set Protocol based on server status
            $path = $protocol . $spath . '/' . $path;
        } elseif ((!substr_count($spath, '://') || !substr_count($spath, '//')) && !empty($spath)) {
            $path = $spath . '/' . $path;
        }

        return $path;
    }

    /**
     * Clear Cache of Proclaim
     *
     * @return void
     * @since 9.0.4
     */
    public static function clearCache(): void
    {
        try {
            $app = Factory::getApplication();
        } catch (\Exception $e) {
            return;
        }
        $options = [];

        $options[1] = [
            'defaultgroup' => 'com_proclaim',
            'cachebase'    => $app->get('cache_path', JPATH_CACHE),
            'result'       => true,
        ];
        $options[2] = [
            'defaultgroup' => 'mod_proclaim',
            'cachebase'    => $app->get('cache_path', JPATH_CACHE),
            'result'       => true,
        ];

        foreach ($options as $option) {
            $cache = Factory::getContainer()->get(CacheControllerFactoryInterface::class)->createCacheController(
                'callback',
                $option
            );
            $cache->clean();
        }

        // Also clear the file-based YouTube video cache
        CwmyoutubeFileCache::clearVideoCache();
    }

    /**
     * Remove Http
     *
     * @param   string  $url  Url
     *
     * @return array|string|string[]
     *
     * @since 9.0.18
     */
    public static function removeHttp(string $url): array|string
    {
        $disallowed = ['http://', 'https://'];

        foreach ($disallowed as $d) {
            if (str_starts_with($url, $d)) {
                return str_replace($d, '', $url);
            }
        }

        return $url;
    }

    /**
     * Get a Simple View Sate
     *
     * @param   ?Registry  $params  AdminTable + parameters
     *
     * @return  \stdClass
     *
     * @since 9.1.6
     */
    public static function getSimpleView(?Registry $params = null): \stdClass
    {
        $simple = new \stdClass();

        if ($params === null) {
            $params = Cwmparams::getAdmin()->params;
        }

        $simple->mode    = (int)$params->get('simple_mode');
        $simple->display = (int)$params->get('simple_mode_display');

        return $simple;
    }
}
