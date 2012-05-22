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
        
        $subscribe = '';
        if ($podcasts)
        {
            $subscribe = '<div class="podcastsubscribe">';
            $subscribe .= '<table id="podcastsubscribetable" cellpadding="0" cellspacing="0"><caption id="podcastsubscribetable">'.$introtext.'</caption>';
            $subscribe .= '<tr>';
            foreach ($podcasts AS $podcast)
            {
                $podcastshow = $podcast->podcast_subscribe_show;
                if (!$podcastshow){$podcastshow = 2;}
                switch ($podcastshow)
                {
                    case 1:
                        break;
                    case 2:
                        $subscribe .= '<td>';
                        $image = $this->buildPodcastImage($podcast->podcast_image_subscribe, $podcast->podcast_subscribe_desc);
                        $link = '<a href="'.JURI::base().$podcast->filename.'">'.$image.'</a>';
                        $subscribe .= '<table id="podcasttable" cellpadding="0" cellspacing="0"><tr>';
                        $subscribe .= '<td>';
                        $subscribe .= $link;
                        $subscribe .= '</td></tr>';
                        $subscribe .= '<tr><td>';
                        $subscribe .= '<a href="'.JURI::base().$podcast->filename.'"><p id="podcasttable">'.$podcast->podcast_subscribe_desc.'</p></a>';
                        $subscribe .= '</td></tr>';
                        $subscribe .= '</table>';
                        $subscribe .= '</td>';
                        break;
                    case 3:
                        $subscribe .= '<td>';
                        $image = $this->buildPodcastImage($podcast->alternateimage, $podcast->alternatewords);
                        $link1 = '<a href="'.$podcast->alternatelink.'">'.$image.'</a>';
                        $subscribe .= '<table class="podcasttable" cellpadding="0" cellspacing="0"><tr>';
                        $subscribe .= '<td>';
                        $subscribe .= $link;
                        $subscribe .= '</td></tr>';
                        $subscribe .= '<tr><td>';
                        $subscribe .= '<a href="'.JURI::base().$podcast->filename.'"><p id="podcasttable">'.$podcast->alternatewords.'</p></a>';
                        $subscribe .= '</td></tr>';
                        $subscribe .= '</table>';
                        $subscribe .= '</td>';
                        break;
                    case 4:
                        $subscribe .= '<td>';
                        $image1 = $this->buildPodcastImage($podcast->podcast_image_subscribe, $podcast->podcast_subscribe_desc);
                        $link1 = '<a href="'.JURI::base().$podcast->filename.'">'.$image1.'</a>';
                        $image2 = $this->buildPodcastImage($podcast->alternateimage, $podcast->alternatewords);
                        $link2 = '<a href="'.$podcast->alternatelink.'">'.$image2.'</a>';
                        $subscribe .= '<table class="podcasttable" cellpadding="0" cellspacing="0"><tr>';
                        $subscribe .= '<td>';
                        $subscribe .= $link1;
                        $subscribe .= '</td>';
                        $subscribe .= '<td>';
                        $subscribe .= $link2;
                        $subscribe .= '</td></tr>';
                        $subscribe .= '<tr><td>';
                        $subscribe .= '<a href="'.JURI::base().$podcast->filename.'"><p id="podcasttable">'.$words.'</p></a>';
                        $subscribe .= '</td>';
                        $subscribe .= '<td>';
                        $subscribe .= '<a href="'.JURI::base().$podcast->filename.'"><p id="podcasttable">'.$podcast->alternatewords.'</p></a>';
                        $subscribe .= '</td></tr>';
                        $subscribe .= '</table>';
                        $subscribe .= '</td>';
                        break;
                }
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
    
    function buildPodcastImage($podcastimagefromdb = 'null', $words = 'null')
    {
        $images = new jbsImages();
        $image = $images->getMediaImage($podcastimagefromdb);
        $podcastimage = '<img src="' . JURI::base() . $image->path . '" width="' . $image->width . '" height="' . $image->height . '" alt="' . $words . '" title="'.$words.'">';
        return $podcastimage;
    }
    
    
}

?>
