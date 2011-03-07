<?php

/**
 * @author Tom Fuller
 * @copyright 2010
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
require_once ( JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
require_once ( JPATH_ROOT .DS.'libraries'.DS.'joomla'.DS.'html'.DS.'parameter.php' );

class JBSAdmin
{
    
    function getMediaPlayer()
{
    $db = JFactory::getDBO();
   $query = "Select #__components.name FROM #__components WHERE #__components.name LIKE '%AvReloaded%'";
   $db->setQuery($query);
   $db->query();
   $num_rows = $db->getNumRows(); 
    if ($num_rows)
    {
        $player = 'avr';
    }
    else
    {
        $player = false;
    }
    $query = 'SELECT element, published FROM #__plugins WHERE #__plugins.element LIKE "%jw_allvideos%"';
    $db->setQuery($query);
    $db->query();
    $num_rows = $db->getNumRows();
    $isav = $db->loadObject($query);
    if ($num_rows && $isav->published == 1){$player = 'av';}
    return $player;
 }
 
function getAdminsettings()
	{
		if (JOOMLA_VERSION == '5')
        {
        $db =& JFactory::getDBO();
		$db->setQuery ("SELECT params FROM #__bsms_admin WHERE id = 1");
		$db->query();
		$compat = $db->loadObject();
		$admin_params = new JParameter($compat->params);
		}
        else
        {
        include_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'params.php');
        $admin_params = BsmHelper::getAdmin(true);
        }			
		return $admin_params;
	}
 
 function getPermission()
{
    
    $results = array();
    //Get the level at which users can enter studies
    $params = $this->getAdminsettings();
    $entry_access = $params->get('entry_access');
    
    $allow_entry = $params->get('allow_entry_study', 0);
        if (!$allow_entry){return false;}
    
    $database = JFactory::getDBO();
    $query = "SELECT id, title FROM #__usergroups";
    $database->setQuery($query);
    $database->query();
    $groupids = $database->loadObjectList();
    $user =& JFactory::getUser();
    
        $usrid = $user->get('id');
        $getGroups = JAccess::getGroupsByUser($usrid);
        
        $sum2 = count($getGroups);    
   
    if (!is_array($entry_access))
        {
            $entry_access = $params->get('entry_access');
           
           foreach ($getGroups AS $newgrpid)
                {
                 
                      if ($newgrpid == $entry_access)
                      {
                        $results[] = 2;
                      }
                      else
                      {
                        $results[] = 3;
                      }

                } //end of for group ids
            
        }
        else
        {
            foreach($entry_access AS $entry)
            {
                
                
           foreach ($getGroups AS $newgrpid)
                {
                                     
                      if ($newgrpid == $entry)
                      {
                        $results[] = 2;
                      }
                      else
                      {
                        $results[] = 3;
                      }
                } //end of for group ids
            } //end of foreach $entry_access as $entry
        } //end of else if not array $entry_access

    //Check $results to see if any are true
    if (in_array(2,$results))
    {
        return true;
    }
    else
    {
        return false;
    } 
     // end of if Joomla 1.6
} // End of Permission function

function commentsPermission($params)
{
    $results = array();
    $comments = 0;
    $show_comments = $params->get('show_comments');
    $enter_comments = $params->get('comments_access');
    //$comments 10 is view only, 11 is view and edit, 0 is no view or edit
    
    $database = JFactory::getDBO();
    $query = "SELECT id, title FROM #__usergroups";
    $database->setQuery($query);
    $database->query();
    $groupids = $database->loadObjectList();
    $user =& JFactory::getUser();
    if (JOOMLA_VERSION == '5')
    {
        $comments_user    = $user->get('gid');
       	if (!$comments_user) { $comments_user = 0; }
        if ($comments_user < $show_comments ){return FALSE;}
        $usertype = $user->get('gid');
        if ($usertype >= $show_comments)
        {
            $results[] = 1;
        }
        if ($usertype >= $enter_comments)
        {
            $results[] = 2;
        }
        if (in_array('2',$results))
        {
            $comments = 11;
        }
        else
        {
            $comments = 10;
        }
        return $comments;
    }
    else
    {
        $usrid = $user->get('id');
        $getGroups = JAccess::getGroupsByUser($usrid);
        $sum2 = count($getGroups);    
        foreach($show_comments AS $entry)
            {
                
                foreach ($getGroups AS $newgrpid) 
                {
                    
                    
                      if ($newgrpid == $entry)
                      {
                        $results[] = 2;
                      }
                      else
                      {
                        $results[] = 3;
                      }
                } //end of for group ids
            } //end of foreach $entry_access as $entry

    //Check $results to see if any are true. A 2 means they are found in the list, a 3 means they are not
    if (in_array(2,$results))
    {
        $comments = 10;
    }
    else
    {
        $comments = 0;
    }
    if (!$comments) {return false;}
    //Now we check to see if they can add comments
    foreach($enter_comments AS $entry)
            {
                
                foreach ($getGroups AS $newgrpid) 
                {
                   
                    
                      if ($newgrpid == $entry)
                      {
                        $results[] = 2;
                      }
                      else
                      {
                        $results[] = 3;
                      }
                } //end of for group ids
            } //end of foreach $entry_access as $entry 
     if (in_array(2,$results))
    {
        $comments = 11;
    }
    else
    {
        $comments = 10;
    }
    return $comments;
    } // end of if Joomla 1.6
} 

function getShowLevel($row)
{
    $show = null;
  /*
    $database = JFactory::getDBO();
    $query = "SELECT id, title FROM #__usergroups";
    $database->setQuery($query);
    $database->query();
    $groupids = $database->loadObjectList();
  */  
    $user =& JFactory::getUser();
    $usrid = $user->get('id');
    $getGroups = JAccess::getGroupsByUser($usrid);
    $sum2 = count($getGroups); 
    if (substr_count($row->show_level,','))
    {$showvar = explode(',',$row->show_level);}
    else {$showvar = $row->show_level;}
    $sum3 = count($showvar);
    for ($i = 0; $i<$sum3; $i++)
    {

    foreach ($getGroups AS $newgrpid) 
        {
              if ($newgrpid == $showvar[$i])
              {
                $show = true; return $show;
              }
        }

    }
   
    
    return $show;
}

function showRows($results)
{
    $count = count($results);
    
        for ($i=0; $i<$count; $i++)
        {
            $show_level = $this->getShowLevel($results[$i]);
            if (!$show_level)
            {
                unset($results[$i]);
            }
        }
       
        return $results;
}

    function getUserGroups()
    {
        $database = JFactory::getDBO();
        $query = "SELECT id, title FROM #__usergroups";
        $database->setQuery($query);
        $database->query();
        $groupids = $database->loadObjectList();
        return $groupids;
    }
} // End of class

?>