<?php

defined('_JEXEC') or die();
jimport('joomla.application.component.controller');


class biblestudyController extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function display()
	{
//	dump ($_GET, 'get: ');
		
		parent::display();
	}
	
	
	function AjaxTags()
        {
            header('Content-type: text/javascript');
            $q=JRequest::getVar('q');
         
            $db	=& JFactory::getDBO();
		    $query = "select '0_".$q."' as id, '".$q."' as 'name' from dual union select distinct id, cast(topic_text as char) as 'name' from #__bsms_topics where topic_text like '%".$q."%' order by 'name' desc limit 10";
        	$db->setQuery($query);
    		
            $tresult = $db->loadObjectList();
            
            if (empty($tresult)) {
                
                $query = "select distinct '0_".$q."' as id, '".$q."' as 'name' from dual";
        	
		        $db->setQuery($query);
        		
                $tresult = $db->loadObjectList();
                
            }
            
            foreach ($tresult as $item)
             {
                if ($tresult[0]->name == $item->name && $tresult[0]->id != $item->id) {      
                    unset($tresult[0]);
                }
             }
            echo json_encode($tresult);
        }
    
    function getTags()
        {
            header('Content-type: text/javascript');
            $q=JRequest::getVar('q');
         
            $db	=& JFactory::getDBO();
		    $query = "select a.id, a.topic_text as name from #__bsms_topics a inner join #__bsms_studytopics b on a.id = b.topic_id where study_id = " .$q;
        	
		    $db->setQuery($query);
    		
            $tresult = $db->loadObjectList();
                        
            echo json_encode($tresult);
            
        }
        
        function getFileList() {
            $type=JRequest::getVar('type');
            $server=JRequest::getVar('server');
            $folder=JRequest::getVar('folder');
            
            switch ($type) {
                case 'ftp':
                
                    //ToDo - 
                    $ftp_server = "media.crosswaybristol.org";
                    $conn_id = ftp_connect($ftp_server);

                    // login with username and password
                    $ftp_user_name = "sermons";
                    $ftp_user_pass = "uploads";
                    $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

                    // get contents of the current directory
                    $files = ftp_nlist($conn_id, "/Sermons/SM-2009/");

                break;
                case 'local':
                    $searchpath = JPATH_BASE . DS . "images";
                    $files = JFolder::files($searchpath, '.png');
                break;
            }
            
            // output $contents
            echo json_encode($files);
            
        }
}
?>
