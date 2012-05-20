<?php

/*
 * @since 7.1.0
 * @desc a helper to return buttons for podcast subscriptions
 * 
 */
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.images.class.php');
class podcastSubscribe
{
    
    function buildSubscribeTable($introtext = 'Our Podcasts')
    {
        $podcasts = $this->getPodcasts();
        $totalpodcasts = $this->getTotal();
        $subscribe = '';
        if ($podcasts)
        {
            $subscribe = '<div class="podcastsubscribe">';
            $subscribe .= '<table id="podcastsubscribetable"><tr align="center"><td colspan="'.$totalpodcasts.'">';
            $subscribe .= '<h3 id="podcastsubscribetable">';
            $subscribe .= $introtext.'</h3></td></tr><tr>';
            foreach ($podcasts AS $podcast)
            {
                $subscribe .= '<td>';
                $image = $this->buildPodcastImage($podcast);
                if ($podcast->alternatelink)
                {
                    $link = '<a href="'.$podcast->alternatelink.'">'.$image.'</a>';
                }
                else
                {
                    $link = '<a href="'.JURI::base().$podcast->filename.'">'.$image.'</a>';
                }
                
                $words = $podcast->podcast_subscribe_desc;
                    $subscribe .= '<table id="podcasttable"><tr>';
                    $subscribe .= '<td>';
                    $subscribe .= $link;
                    $subscribe .= '</td></tr>';
                    $subscribe .= '<tr><td>';
                    if ($words)
                    {
                        $subscribe .= '<a href="'.JURI::base().$podcast->filename.'"><p id="podcasttable">'.$words.'</p></a>';
                    }
                $subscribe .= '</td></tr>';
                $subscribe .= '</table>';
                $subscribe .= '</td>';
            }
            $subscribe .= '</tr></table>';
            $subscribe .= '</div>'; 
        }
        
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
        $podcastimage = '<img src="' . JURI::base() . $image->path . '" width="' . $image->width . '" height="' . $image->height . '" alt="' . $podcast->title . '" title="'.$podcast->title.'">';
        return $podcastimage;
    }
    
    function getTotal()
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery('true');
        $query->select('COUNT(*)');
        $query->from('#__bsms_podcast as p');
        $query->where('p.published = 1');
        $db->setQuery($query);
        $total = $db->loadResult();
        return $total;
    }
}

?>
