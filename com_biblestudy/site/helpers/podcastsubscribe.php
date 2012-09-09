<?php

/**
 * Podcast Subscribe Helper
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.images.class.php');

/**
 * A helper to return buttons for podcast subscriptions
 * @package BibleStudy.Site
 * @since 7.1.0
 *
 */
class podcastSubscribe {

    /**
     * Build Subscribe Table
     * @param type $introtext
     * @return string
     */
    public function buildSubscribeTable($introtext = 'Our Podcasts') {
        $podcasts = podcastSubscribe::getPodcasts();

        $subscribe = '';
        if ($podcasts) {

            $subscribe .= '<div class="podcastheader" ><h3>' . $introtext . '</h3></div>';
            $subscribe .= '<div class="prow">';
            foreach ($podcasts AS $podcast) {

                $podcastshow = $podcast->podcast_subscribe_show;
                if (!$podcastshow) {
                    $podcastshow = 2;
                }
                switch ($podcastshow) {
                    case 1:
                        break;

                    case 2:
                        $subscribe .= '<div class="pcell">';
                        $subscribe .= podcastSubscribe::buildStanderdPodcast($podcast);
                        $subscribe .= '</div>';
                        break;

                    case 3:
                        $subscribe .= '<div class="pcell">';
                        $subscribe .= podcastSubscribe::buildAlernatePodcast($podcast);
                        $subscribe .= '</div>';
                        break;

                    case 4:
                        $subscribe .= '<div class="pcell">';
                        $subscribe .= podcastSubscribe::buildStanderdPodcast($podcast);
                        $subscribe .= podcastSubscribe::buildStanderdPodcast($podcast);
                        $subscribe .= '</div>';
                        break;
                }
            }
            // end of row
            $subscribe .= '</div>';

            //add a div around it all
            $subscribe = '<div class="podcastsubscribe">' . $subscribe . '</div>';
        }

        return $subscribe;
    }

    /**
     * Build Standerd Podcast
     * @param object $podcast
     * @return string
     */
    public static function buildStanderdPodcast($podcast) {
        $subscribe = '';
        if (!empty($podcast->podcast_image_subscribe)):
            $image = podcastSubscribe::buildPodcastImage($podcast->podcast_image_subscribe, $podcast->podcast_subscribe_desc);
            $link = '<div class="image"><a href="' . JURI::base() . $podcast->filename . '">' . $image . '</a></div>';
            $subscribe .= $link;
        endif;
        if (empty($podcast->podcast_subscribe_desc)):
            $name = $podcast->title;
        else :
            $name = $podcast->podcast_subscribe_desc;
        endif;
        $subscribe .= '<div class="text"><a href="' . JURI::base() . $podcast->filename . '">' . $name . '</a></div>';
        return $subscribe;
    }

    /**
     * Build Alternet Podcast
     * @param object $podcast
     * @return string
     */
    public static function buildAlernatePodcast($podcast) {
        $subscribe = '';
        if (!empty($podcast->alternateimage)):
            $image = podcastSubscribe::buildPodcastImage($podcast->alternateimage, $podcast->alternatewords);
            $link = '<div class="image"><a href="' . $podcast->alternatelink . '">' . $image . '</a></div>';
            $subscribe .= $link;
        endif;
        $subscribe .= '<div class="text"><a href="' . JURI::base() . $podcast->filename . '">' . $podcast->alternatewords . '</a></div>';
        return $subscribe;
    }

    /**
     * Get Podcasts
     * @return object
     */
    public static function getPodcasts() {
        $db = JFactory::getDBO();
        $query = $db->getQuery('true');
        $query->select('*');
        $query->from('#__bsms_podcast as p');
        $query->where('p.published = 1');
        $db->setQuery($query);
        $podcasts = $db->loadObjectList();
        //check permissions for this view by running through the records and removing those the user doesn't have permission to see
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        $count = count($podcasts);

        for ($i = 0; $i < $count; $i++) {

            if ($podcasts[$i]->access > 1) {
                if (!in_array($podcasts[$i]->access, $groups)) {
                    unset($podcasts[$i]);
                }
            }
        }
        return $podcasts;
    }

    /**
     * Build Podcast Image
     * @param array $podcastimagefromdb
     * @param array $words
     * @return string
     */
    public static function buildPodcastImage($podcastimagefromdb = 'null', $words = 'null') {
        $images = new jbsImages();
        $image = $images->getMediaImage($podcastimagefromdb);
        $podcastimage = '<img class="image" src="' . JURI::base() . $image->path . '" width="' . $image->width . '" height="' . $image->height . '" alt="' . $words . '" title="' . $words . '">';

        return $podcastimage;
    }

}