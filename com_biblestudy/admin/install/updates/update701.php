<?php

/**
 * @author Joomla Bible Study
 * @copyright 2011
 * @desc This class performs an update to the database from version 7.0.0 to 7.0.1
 */
class updatejbs701
{
    
    function do701update()
    
        {
            
            $db = JFactory::getDBO();    
            $tables = $db->getTableFields('#__bsms_topics');
            $languagetag = 0;
            $paramstag = 0;
            //print_r($tables);
            foreach ($tables as $table)
            { 
                foreach ($table as $key=>$value)
               { 
                    if (substr_count($key,'languages'))
                    {
                       $languagetag = 1;
                       $query = 'ALTER TABLE #__bsms_topics CHANGE `languages` `params` varchar(511) null';
                       $db->setQuery($query);
                       $db->query();
                       $error = $db->getErrorNum();
                       if ($error){return false;}
                           
                    }
                    elseif(substr_count($key,'params'))
                    {
                       $paramstag = 1;
                                              
                    }
                
                }
                 if (!$languagetag && !$paramstag)
                 {
                    $query = 'ALTER #__bsms_topics ADD `params` varchar(511)';
                    $db->setQuery($query);
                    $db->query();
                    $error = $db->getErrorNum();
                    if ($error){return false;}
                 }   
            }
            return true;
        }
}


?>