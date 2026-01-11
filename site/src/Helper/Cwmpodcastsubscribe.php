<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2025 CWM Team All rights reserved
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
        $rows          = '';

        foreach ($podcasts as $podcast) {
            $podcastshow = $podcast->podcast_subscribe_show ?: 2;

            // Case 1 = hidden, skip
            if ($podcastshow === 1) {
                continue;
            }

            $content = match ($podcastshow) {
                3 => $this->buildAlternatePodcast($podcast),
                4 => '<div class="row"><div class="col-6">'
                    . $this->buildStandardPodcast($podcast)
                    . '</div><div class="col-6">'
                    . $this->buildAlternatePodcast($podcast)
                    . '</div></div>',
                default => $this->buildStandardPodcast($podcast),
            };

            $rows .= '<div class="pcell col-md-6"><h5><i class="fa fa-podcast"></i> '
                . $podcast->title . '</h5>' . $content . '<hr /></div>';
        }

        if (empty($rows)) {
            return '';
        }

        return '<div class="podcastsubscribe">'
            . '<div class="podcastheader"><h4>' . $introtext . '</h4></div>'
            . '<div class="prow row">' . $rows . '</div>'
            . '</div>';
    }

    /**
     * Get Podcasts
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
        $link = $this->baseUri . $podcast->filename;
        $name = $podcast->podcast_subscribe_desc ?: $podcast->title;

        $html = '';

        if (!empty($podcast->podcast_image_subscribe)) {
            $image = $this->buildPodcastImage($podcast->podcast_image_subscribe, $podcast->podcast_subscribe_desc);
            $html .= '<div class="image"><a href="' . $link . '">' . $image . '</a></div><div class="clr"></div>';
        }

        $html .= '<div class="text"><a href="' . $link . '">' . $name . '</a></div>';

        return $html;
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
            ['width' => $image->width, 'height' => $image->height, 'title' => $words]
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
        $html = '';

        if (!empty($podcast->alternateimage)) {
            $image = $this->buildPodcastImage($podcast->alternateimage, $podcast->alternatewords);
            $html .= '<div class="image"><a href="' . $podcast->alternatelink . '">' . $image . '</a></div><div class="clr"></div>';
        }

        $html .= '<div class="text"><a href="' . $podcast->alternatelink . '">' . $podcast->alternatewords . '</a></div>';

        return $html;
    }
}
