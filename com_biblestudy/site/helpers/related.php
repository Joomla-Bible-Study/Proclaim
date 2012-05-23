<?php

/*
 * @desc helper to get related studies to the current one
 * @since 7.1.0
 */

defined('_JEXEC') or die;

class relatedStudies
{
    
    function getRelated($row, $params)
    {
        $keygo = true;
        $topicsgo = true;
        $registry = new JRegistry();
        $registry->loadJSON($row->params);
        $params = $registry;
        $keywords = $params->get('metakey');
        $topics = $row->topics_id;
        if (!$keywords){$keygo = false;}
        if (!$topics){$topicsgo = false;}
        if (!$keygo && !$topicsgo){return false;}
        $studies = $this->getStudies();
        foreach ($studies as $study)
        {
            $registry = new JRegistry();
            $registry->loadJSON($study->params);
            $sparams = $registry;
            $compare = $sparams->get('metakey');
            
            if ($compare) {$keywordsresults = $this->parseKeys($keywords, $compare, $study->id);}
            if ($study->topics_id) {$topicsresults = $this->parseKeys($topics,$study->topics_id,$study->id);}
        }
        
       // print_r($this->score);
        $scored =  ($this->score);
        
        $related = $this->getRelatedLinks($scored, $params);
       // return $related;
    }
    
    function parseKeys($source, $compare, $id)
    {
        $sourceisarray = false;
        $compareisarray = false;
        $sourcearray = array();
        $comparearray = array();
        if (substr_count($source,','))
            {
                $sourcearray = explode(',',$source);
                $sourceisarray = true;
            }
        if (substr_count($compare,','))
            {
                $comparearray = explode(',',$compare);
                $compareisarray = true;
            }
        if ($sourceisarray && $compareisarray)
        {
            foreach ($sourcearray as $sarray)
            {
                if (in_array($sarray, $comparearray)){$this->score[] = $id;}
            }
        }
        if ($sourceisarray && !$compareisarray)
        {
            if (in_array($compare,$sourcearray)){$this->score[] = $id;}
        }
        
        if (!$sourceisarray && $compareisarray)
        {
            if (in_array($source,$comparearray)){$this->score[] = $id;}
        }
        
        if (!$sourceisarray && !$compareisarray)
        { 
            if (strcmp($source, $compare)){$this->score[] = $id;}
        }
        return true;
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
    
    function getRelatedLinks($scored, $params)
    {
        $db = JFactory::getDBO();
        $scored = array_count_values($scored);
        $sorted = arsort($scored);
        $output = array_slice($scored, 0, 20, true);
        $links = array();
        $studyrecords = array();
        $studyrecord = '';
        foreach ($output as $key=>$value)
        {
            $links[] = $key;
        }
        foreach ($links as $link)
        {
            $query = $db->getQuery('true');
            $query->select('studytitle, alias, id');
            $query->from('#__bsms_studies');
            $query->where('id = '.$link);
            $db->setQuery($query);
            $db->query();
            $study = $db->loadObject();
            $study->slug = $study->alias ? ($study->id . ':' . $study->alias) : str_replace(' ', '-', htmlspecialchars_decode($study->studytitle, ENT_QUOTES)) . ':' . $study->id;
            $studyrecords[] = array('text'=>$study->studytitle,'value'=>JRoute::_('index.php?option=com_biblestudy&view=sermon&id='.$study->slug.'&t='.$params->get('detailstemplateid',1)));
        }
        
        //print_r($studyrecords);
        $go = 'onchange="this.form.submit()"';
        $related[] = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_RELATED_STUDY'));
        $related = array_merge($related, $studyrecords);
        $relatedstudies = JHTML::_('select.genericlist', $related, '', 'class="inputbox" size="1" ' . 'onchange="goTo()"', 'value', 'text', "");
       // $relatedlinks = '<form action="index.php?option=com_biblestudy&view=sermon&t=' . JRequest::getInt('t', '1').'" method="post">'.$relatedstudies.'</form>';
        $relatedlinks = '<form >'.$relatedstudies.'</form>';
        //$relatedlinks = $relatedstudies;
        echo $relatedlinks;

    }
}
?>
