<?php

/**
 * @author Joomla Bible Study
 * @copyright 2010
 * @desc Updates the CSS .media to .jbsmedia because some templates had media as a tag
 */
defined( '_JEXEC' ) or die('Restricted access');

class jbs622Install{  
    
  function upgrade622()
  {
 
    $db = JFactory::getDBO();
    $query = "SELECT count(`id`) FROM  #__bsms_mediafiles WHERE `params` LIKE '%podcast1%' GROUP BY `id`";
    $db->setQuery($query);
    $db->query();
    $rows = $db->loadResult();
    $query = "SELECT `id`, `params` FROM #__bsms_mediafiles WHERE `params` LIKE '%podcast1%'";
    $db->setQuery($query);
    $db->query();
    $results = $db->loadObjectList();
    
    $count = 0;
    foreach ($results AS $result)
    {
        $oldparams = $result->params;
        $newparams = str_replace('podcast1', 'podcasts',$oldparams);
        $query = "UPDATE #__bsms_mediafiles SET `params` = '".$newparams."' WHERE `id` = ".$result->id;
        $db->setQuery($query);
        $db->query();
    }             
    $query = "SELECT `id`, `params` FROM #__bsms_mediafiles WHERE `params` LIKE '%podcast1%'";
    $db->setQuery($query);
    $db->query();
    if ($db->getErrorNum() != 0)
    {$msg = false;}
    else {$msg = true;}
    return $msg;
 }
} 
?>