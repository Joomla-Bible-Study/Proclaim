<?php

/*
 * @since 7.1.0
 * @desc a helper to return buttons for podcast subscriptions
 * 
 */
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.images.class.php');
class podcastSubscribe
{
    
    function buildSubscribeTable()
    {
        $podcasts = $this->getPodcasts();
        $subscribe = '<div class="podcastsubscribe">';
        if ($podcasts)
        {
            foreach ($podcasts AS $podcast)
            {
                
                $image = $this->buildPodcastImage($podcast);
                $link = '<a href="'.JURI::base().$podcast->filename.'">'.$image.'</a>';
                $words = $podcast->podcast_subscribe_desc;
                if ($words)
                {
                    $subscribe .= '<table class="podcast_small_table" style="display: inline"><tr><td>';
                    $subscribe .= $link;
                    $subscribe .= '</td></tr><tr align="center"><td>';
                    $subscribe .= $words.'</td></tr></table>';
                    $subscribe .= '</td>';
                }
                else
                {
                    $subscribe .= '<td>';
                    $subscribe .= $link;
                    $subscribe .= '</td>';
                }
            }
        }
        $subscribe .= '</div>'; 
        return $subscribe;
    }
    
    function getPodcasts()
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery('true');
        $query->select('*');
        $query->from('#__bsms_podcast as p');
        $query->where('p.published = 1');
        $db->setQuery($query);
        $podcasts = $db->loadObjectList();
        return $podcasts;
    }
    
    function buildPodcastImage($podcast)
    {
        $images = new jbsImages();
        $image = $images->getMediaImage($podcast->podcast_image_subscribe);
        $podcastimage = '<img src="' . JURI::base() . $image->path . '" width="' . $image->width . '" height="' . $image->height . '" alt="' . $podcast->title . '">';
        return $podcastimage;
    }
}

?>
