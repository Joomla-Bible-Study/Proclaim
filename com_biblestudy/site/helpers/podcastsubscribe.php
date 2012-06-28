<?php

/**
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.images.class.php');

/**
 * @package BibleStudy.Site
 * @since 7.1.0
 * a helper to return buttons for podcast subscriptions
 *
 */
class podcastSubscribe {

    /**
     *
     * @param type $introtext
     * @return string
     */
    function buildSubscribeTable($introtext = 'Our Podcasts') {
        $podcasts = $this->getPodcasts();

        $subscribe = '';
        if ($podcasts) {

            $subscribe .= '<div class="podcastheader" ><h3>' . $introtext . '</h3></div>';
            // $subscribe .= '<div class="podcastlinks">';
            //    $subscribe .= '<table class="podcasttable"><tr><td>';
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

                        $image = $this->buildPodcastImage($podcast->podcast_image_subscribe, $podcast->podcast_subscribe_desc);
                        $link = '<div class="image"><a href="' . JURI::base() . $podcast->filename . '">' . $image . '</a>';
                        $subscribe .= '<div class="pcell">';
                        $subscribe .= $link;
                        $subscribe .= '<div class="text"><a href="' . JURI::base() . $podcast->filename . '">' . $podcast->podcast_subscribe_desc . '</a></div></div>';
                        //end of cell
                        $subscribe .= '</div>';
                        break;

                    case 3:

                        $image = $this->buildPodcastImage($podcast->alternateimage, $podcast->alternatewords);
                        $link1 = '<div class="image"><a href="' . $podcast->alternatelink . '">' . $image . '</a>';
                        $subscribe .= '<div class="pcell">';
                        $subscribe .= $link1;
                        $subscribe .= '<div class="text"><a href="' . JURI::base() . $podcast->filename . '">' . $podcast->alternatewords . '</a></div></div>';
                        //end of cell
                        $subscribe .= '</div>';
                        break;

                    case 4:

                        $image1 = $this->buildPodcastImage($podcast->podcast_image_subscribe, $podcast->podcast_subscribe_desc);
                        $link1 = '<div class="image"><a href="' . JURI::base() . $podcast->filename . '">' . $image1 . '</a>';
                        $subscribe .= '<div class="pcell">';
                        $subscribe .= $link1;
                        $subscribe .= '<div class="text"><a href="' . JURI::base() . $podcast->filename . '">' . $podcast->podcast_subscribe_desc . '</a></div></div>';

                        $image2 = $this->buildPodcastImage($podcast->alternateimage, $podcast->alternatewords);
                        $link2 = '<div class="image"><a href="' . $podcast->alternatelink . '">' . $image2 . '</a>';
                        $subscribe .= $link2;
                        $subscribe .= '<div class="text"><a href="' . JURI::base() . $podcast->filename . '">' . $podcast->alternatewords . '</a></div></div>';
                        // end of cell
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
     *
     * @return type
     */
    function getPodcasts() {
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
     *
     * @param type $podcastimagefromdb
     * @param type $words
     * @return string
     */
    function buildPodcastImage($podcastimagefromdb = 'null', $words = 'null') {
        $images = new jbsImages();
        $image = $images->getMediaImage($podcastimagefromdb);
        $podcastimage = '<img class="image" src="' . JURI::base() . $image->path . '" width="' . $image->width . '" height="' . $image->height . '" alt="' . $words . '" title="' . $words . '">';

        return $podcastimage;
    }

}