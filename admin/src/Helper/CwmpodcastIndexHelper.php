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

use Joomla\Http\HttpFactory;

/**
 * Podcast Index API Helper — submit and search podcast feeds.
 *
 * Podcast Index is the only major directory with a free, open API for
 * programmatic feed submission.  All other directories (Apple, Spotify,
 * YouTube, Amazon) require manual portal submission.
 *
 * API docs: https://podcastindex-org.github.io/docs-api/
 *
 * GDPR note: This sends the podcast RSS feed URL to podcastindex.org.
 * No visitor personal data is transmitted — only the church's public feed URL.
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class CwmpodcastIndexHelper
{
    /**
     * API base URL
     *
     * @var string
     */
    private string $baseUrl = 'https://api.podcastindex.org/api/1.0';

    /**
     * API key from component config
     *
     * @var string
     */
    private string $apiKey;

    /**
     * API secret from component config
     *
     * @var string
     */
    private string $apiSecret;

    /**
     * Constructor
     *
     * @param   string  $apiKey     Podcast Index API key
     * @param   string  $apiSecret  Podcast Index API secret
     *
     * @since   10.1.0
     */
    public function __construct(string $apiKey, string $apiSecret)
    {
        $this->apiKey    = $apiKey;
        $this->apiSecret = $apiSecret;
    }

    /**
     * Build the four required authentication headers.
     *
     * Podcast Index uses SHA-1 hash of (key + secret + epoch) for auth.
     *
     * @return  array<string, string>
     *
     * @since   10.1.0
     */
    private function getAuthHeaders(): array
    {
        $now = time();

        return [
            'X-Auth-Key'    => $this->apiKey,
            'X-Auth-Date'   => (string) $now,
            'Authorization' => sha1($this->apiKey . $this->apiSecret . $now),
            'User-Agent'    => 'Proclaim/10.1.0',
        ];
    }

    /**
     * Submit a feed URL to Podcast Index for indexing.
     *
     * GET /add/byfeedurl?url={feedUrl}
     *
     * @param   string  $feedUrl  The full RSS feed URL to submit
     *
     * @return  object  API response decoded from JSON
     *
     * @throws  \RuntimeException  On HTTP or API error
     *
     * @since   10.1.0
     */
    public function submitFeed(string $feedUrl): object
    {
        $url      = $this->baseUrl . '/add/byfeedurl?url=' . urlencode($feedUrl);
        $http     = HttpFactory::getHttp();
        $response = $http->get($url, $this->getAuthHeaders());

        $status = $response->getStatusCode();
        $body   = (string) $response->getBody();

        if ($status < 200 || $status >= 300) {
            throw new \RuntimeException(
                'Podcast Index API returned HTTP ' . $status . ': ' . $body
            );
        }

        try {
            $data = json_decode($body, false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Invalid JSON from Podcast Index API: ' . $e->getMessage());
        }

        return $data;
    }

    /**
     * Search Podcast Index for a podcast by its feed URL.
     *
     * GET /podcasts/byfeedurl?url={feedUrl}
     *
     * @param   string  $feedUrl  The RSS feed URL to look up
     *
     * @return  object|null  API response or null if not found (404)
     *
     * @throws  \RuntimeException  On HTTP or API error (non-404)
     *
     * @since   10.1.0
     */
    public function searchByFeedUrl(string $feedUrl): ?object
    {
        $url      = $this->baseUrl . '/podcasts/byfeedurl?url=' . urlencode($feedUrl);
        $http     = HttpFactory::getHttp();
        $response = $http->get($url, $this->getAuthHeaders());

        $status = $response->getStatusCode();
        $body   = (string) $response->getBody();

        if ($status === 404) {
            return null;
        }

        if ($status < 200 || $status >= 300) {
            throw new \RuntimeException(
                'Podcast Index API returned HTTP ' . $status . ': ' . $body
            );
        }

        try {
            $data = json_decode($body, false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Invalid JSON from Podcast Index API: ' . $e->getMessage());
        }

        return $data;
    }
}
