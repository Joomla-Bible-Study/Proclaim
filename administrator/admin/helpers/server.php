<?php defined('_JEXEC') or die('Restriced Access');

    function getServer($serverid) {
        $mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
        
        //$book = null;
        
        //$templatemenuid = $params->get('templatemenuid');
        
        //if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}

        //$book = '<table id="bsm_books" width=100%><tr>';
        $db	=& JFactory::getDBO();
        $query = 'select distinct * from #__bsms_servers where id = ' . $serverid;

        $db->setQuery($query);

        $tresult = $db->loadObject();

        $i = 0;

        return $tresult;
    }
    
    function getFolder($folderId) {
        $mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
        
        $db	=& JFactory::getDBO();
        $query = 'select distinct * from #__bsms_folders where id = ' . $folderId;

        $db->setQuery($query);

        $tresult = $db->loadObject();

        $i = 0;

        return $tresult;
    }
  
?>