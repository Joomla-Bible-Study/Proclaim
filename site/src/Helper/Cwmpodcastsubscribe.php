<?php

/**
 * Part of Proclaim Package
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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;

/**
 * A helper to return buttons for podcast subscriptions
 *
 * @package  Proclaim.Site
 * @since    7.1.0
 */
class Cwmpodcastsubscribe
{
    private string $baseUri;

    /**
     * Map link URLs to FontAwesome icons and labels.
     * Matched in order — first match wins.  The RSS fallback is used
     * when no other pattern matches.
     *
     * @var  array<int, array{pattern: string, icon: string, label: string}>
     *
     * @since 10.1.0
     */
    private static array $linkIconMap = [
        ['pattern' => 'itpc://',             'icon' => 'fa-brands fa-itunes-note', 'label' => 'iTunes'],
        ['pattern' => 'apple.com/podcast',   'icon' => 'fa-brands fa-apple',       'label' => 'Apple Podcasts'],
        ['pattern' => 'podcasts.apple.com',  'icon' => 'fa-brands fa-apple',       'label' => 'Apple Podcasts'],
        ['pattern' => 'spotify.com',         'icon' => 'fa-brands fa-spotify',     'label' => 'Spotify'],
        ['pattern' => 'youtube.com',         'icon' => 'fa-brands fa-youtube',     'label' => 'YouTube'],
        ['pattern' => 'google.com/podcasts', 'icon' => 'fa-brands fa-google',      'label' => 'Google Podcasts'],
        ['pattern' => 'overcast.fm',         'icon' => 'fa-solid fa-podcast',      'label' => 'Overcast'],
        ['pattern' => 'pocketcasts.com',     'icon' => 'fa-solid fa-podcast',      'label' => 'Pocket Casts'],
        ['pattern' => 'stitcher.com',        'icon' => 'fa-solid fa-podcast',      'label' => 'Stitcher'],
        ['pattern' => 'amazon.com',          'icon' => 'fa-brands fa-amazon',      'label' => 'Amazon Music'],
        ['pattern' => 'music.amazon',        'icon' => 'fa-brands fa-amazon',      'label' => 'Amazon Music'],
    ];

    /**
     * Build Subscribe Table
     *
     * @param   ?string  $introtext  Intro Text
     *
     * @return string
     *
     * @throws \Exception
     * @since    7.1
     */
    public function buildSubscribeTable(?string $introtext = 'Our Podcasts'): string
    {
        $podcasts = $this->getPodcasts();

        if (empty($podcasts)) {
            return '';
        }

        $this->baseUri = Uri::base();
        $cards         = '';

        foreach ($podcasts as $podcast) {
            $podcastshow = (int) ($podcast->podcast_subscribe_show ?: 2);

            $links = match ($podcastshow) {
                3 => $this->buildAlternatePodcast($podcast),
                4 => $this->buildStandardPodcast($podcast)
                    . $this->buildAlternatePodcast($podcast),
                default => $this->buildStandardPodcast($podcast),
            };

            $title = htmlspecialchars($podcast->title, ENT_QUOTES, 'UTF-8');

            $cards .= '<div class="pcell">'
                . '<h5><i class="fa-solid fa-podcast" aria-hidden="true"></i> ' . $title . '</h5>'
                . '<div class="podcast-subscribe-links">' . $links . '</div>'
                . '</div>';
        }

        $heading = htmlspecialchars($introtext ?? 'Our Podcasts', ENT_QUOTES, 'UTF-8');

        return '<div class="podcastsubscribe">'
            . '<div class="podcastheader"><h4>' . $heading . '</h4></div>'
            . '<div class="prow">' . $cards . '</div>'
            . '</div>';
    }

    /**
     * Get Podcasts (excludes hidden podcasts with podcast_subscribe_show = 1)
     *
     * @return array Object List of Podcasts
     *
     * @throws \Exception
     * @since    7.1
     */
    public function getPodcasts(): array
    {
        $user  = Factory::getApplication()->getIdentity();
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('#__bsms_podcast', 'p'))
            ->where($db->quoteName('p.published') . ' = 1')
            ->where('(' . $db->quoteName('p.podcast_subscribe_show') . ' IS NULL OR '
                . $db->quoteName('p.podcast_subscribe_show') . ' != 1)')
            ->whereIn($db->quoteName('p.access'), $user->getAuthorisedViewLevels());

        $db->setQuery($query);

        return $db->loadObjectList();
    }

    /**
     * Build Standard Podcast (RSS feed link)
     *
     * Uses a custom image badge when configured; otherwise renders a
     * FontAwesome RSS icon button.
     *
     * @param   object  $podcast  Podcast Info
     *
     * @return string
     *
     * @since    7.1
     */
    public function buildStandardPodcast(object $podcast): string
    {
        $link = htmlspecialchars($this->baseUri . $podcast->filename, ENT_QUOTES, 'UTF-8');
        $name = htmlspecialchars(
            $podcast->podcast_subscribe_desc ?: $podcast->title,
            ENT_QUOTES,
            'UTF-8'
        );

        if (!empty($podcast->podcast_image_subscribe)) {
            $image = $this->buildPodcastImage($podcast->podcast_image_subscribe, $name);

            if ($image) {
                return '<a href="' . $link . '" class="podcast-badge">' . $image . '</a>';
            }
        }

        return '<a href="' . $link . '">'
            . '<i class="fa-solid fa-rss" aria-hidden="true"></i> ' . $name
            . '</a>';
    }

    /**
     * Build Podcast Image
     *
     * Renders with CSS-controlled sizing (no inline width/height constraints)
     * so the badge scales to match the podcast-badge img max-height rule.
     *
     * @param   ?string  $podcastimagefromdb  Podcast image
     * @param   ?string  $words               Alt podcast image text
     *
     * @return string|null
     *
     * @since    7.1
     */
    public function buildPodcastImage(?string $podcastimagefromdb = null, ?string $words = null): ?string
    {
        $image = Cwmimages::getMediaImage($podcastimagefromdb);

        if (!$image->path) {
            return null;
        }

        return HTMLHelper::image(
            $this->baseUri . $image->path,
            $words,
            [
                'title'   => $words,
                'loading' => 'lazy',
                'class'   => 'podcast-badge-img',
            ]
        );
    }

    /**
     * Build Alternate Podcast link
     *
     * Uses a custom image badge when configured; otherwise detects the
     * service from the URL and renders the appropriate FontAwesome icon.
     *
     * @param   object  $podcast  Podcast info
     *
     * @return string
     *
     * @since    7.1
     */
    public function buildAlternatePodcast(object $podcast): string
    {
        $link  = htmlspecialchars($podcast->alternatelink ?? '', ENT_QUOTES, 'UTF-8');
        $words = htmlspecialchars($podcast->alternatewords ?? '', ENT_QUOTES, 'UTF-8');

        if (!empty($podcast->alternateimage)) {
            $image = $this->buildPodcastImage($podcast->alternateimage, $words);

            if ($image) {
                return '<a href="' . $link . '" class="podcast-badge">' . $image . '</a>';
            }
        }

        $iconInfo = $this->detectLinkIcon($podcast->alternatelink ?? '');

        return '<a href="' . $link . '">'
            . '<i class="' . $iconInfo['icon'] . '" aria-hidden="true"></i> '
            . ($words ?: $iconInfo['label'])
            . '</a>';
    }

    /**
     * Detect the appropriate FontAwesome icon for a podcast link URL.
     *
     * @param   string  $url  The subscribe URL
     *
     * @return  array{icon: string, label: string}
     *
     * @since   10.1.0
     */
    private function detectLinkIcon(string $url): array
    {
        $lower = strtolower($url);

        foreach (self::$linkIconMap as $entry) {
            if (str_contains($lower, $entry['pattern'])) {
                return ['icon' => $entry['icon'], 'label' => $entry['label']];
            }
        }

        // Default fallback: generic headphones icon
        return ['icon' => 'fa-solid fa-headphones', 'label' => 'Subscribe'];
    }
}
