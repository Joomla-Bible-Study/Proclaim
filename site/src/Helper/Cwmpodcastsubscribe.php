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
                . '<h5><i class="fa fa-podcast" aria-hidden="true"></i> ' . $title . '</h5>'
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
     * Build Standard Podcast
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

        return '<a href="' . $link . '"><i class="fa fa-rss" aria-hidden="true"></i> ' . $name . '</a>';
    }

    /**
     * Build Podcast Image
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
                'width'   => $image->width,
                'height'  => $image->height,
                'title'   => $words,
                'loading' => 'lazy',
            ]
        );
    }

    /**
     * Build Alternate Podcast
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

        return '<a href="' . $link . '"><i class="fa fa-headphones" aria-hidden="true"></i> ' . $words . '</a>';
    }
}
