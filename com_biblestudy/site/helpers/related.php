<?php

/*
 * @desc helper to get related studies to the current one
 * @since 7.1.0
 */

defined('_JEXEC') or die;

class relatedStudies
{
    
    function getRelated($row)
    {
        $keygo = true;
        $topicgo = true;
        $registry = new JRegistry();
        $registry->loadJSON($row->params);
        $params = $registry;
        $keywords = $params->get('metakey');
        $topics = $row->topics_id;
        if (!$keywords){$keygo = false;}
        if (!$topics){$topicsgo = false;}
        if (!$keygo && !$topicsgo){return false;}
        $keyword = '';
        $topicword = '';
        $keyarray = array();
        $topicsarray = array();
        if (substr_count($keywords, ','))
        {$keyarray = explode(',', $keywords);} 
        else {$keyword = $keywords;}
        
        if (substr_count($topics, ','))
        {$topicsarray = explode(',', $topics);}
        else {$topicword = $topics;}
        
        $related = array();
        $score = array();
        $studies = $this->getStudies();
        foreach ($studies AS $study)
        {
            $registry->loadJSON($study->params);
            $studyparams = $registry;
            $studykeywords = $studyparams->get('metakey');
            if (!is_array($studykeywords)&& $studykeywords )
            {
                if (strcmp($keyword,$studykeywords)){$score[] = $study->id;}
            }
            elseif (!empty($keyarray)) 
            {
                $studykeyarray = explode(',', $studykeywords);
                foreach ($keyarray as $key)
                {
                    if (in_array($key, $studykeyarray))
                    {
                        $score[] = $study->id;
                    }
                }
            }
            if (!is_array($topicsarray)&& $study->topics_id )
            {
                if (strcmp($topicword,$study->topics_id)){$score[] = $study->id;}
            }
            elseif (!empty($topicsarray))
            {
                $studytopicsarray = explode(',',$study->topics_id);
                foreach ($topicsarray AS $topic)
                {
                    if (in_array($topic, $studytopicsarray))
                    {
                        $score[] = $study->id;
                    }
                }
            }
        }
      //  print_r($score);
        $scored = array_count_values($score);
        
        $related = $this->getRelatedLinks($scored);
       // return $related;
    }
    
    function getStudies()
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery('true');
        $query->select('id, params, topics_id, access');
        $query->from('#__bsms_studies');
        $query->where('published = 1');
        $db->setQuery($query);
        $db->query();
        $studies = $db->loadObjectList();
        //check permissions for this view by running through the records and removing those the user doesn't have permission to see
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        $count = count($studies);

        for ($i = 0; $i < $count; $i++) {

            if ($studies[$i]->access > 1) {
                if (!in_array($studies[$i]->access, $groups)) {
                    unset($studies[$i]);
                }
            }
        }
        
        return $studies;
    }
    
    function getRelatedLinks($scored)
    {
        $sorted = arsort($scored);
        print_r($scored);
    }
}
?>
