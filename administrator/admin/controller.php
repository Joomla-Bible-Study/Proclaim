<?php
/**
 * Bible Study default controller
 * 
 * @license		GNU/GPL
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');

/**
 * Bible Study Component Controller
 *
 * 
 */
class biblestudyController extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function display()
	{
				$type = JRequest::getWord('view');
				if (!$type){
				JRequest::setVar( 'view'  , 'cpanel');
			//	$model = $this->getModel('studieslist');
				}
		
		if(JRequest::getCmd('view') == 'studydetails')
		{
			$model =& $this->getModel('studydetails');
			$model->hit();
		}
		
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
 
            $serverId=JRequest::getVar('server');
            $folderId=JRequest::getVar('path');
            
            $path1 = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
            include_once($path1.'server.php');
            
            $server = getServer($serverId);
            $folder = getFolder($folderId);
            
            $type = $server->server_type;
            
            switch ($type) {
                case 'ftp':
                
                    //ToDo - 
                    $ftp_server = $server->server_path;
                    $conn_id = ftp_connect($ftp_server);

                    // login with username and password
                    $ftp_user_name = $server->ftp_username;
                    $ftp_user_pass = $server->ftp_password;
                    $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

                    // get contents of the current directory
                    $files = ftp_nlist($conn_id, $folder->folderpath);
                    
                    //ftp_quit();

                break;
                case 'local':
                    $searchpath = JPATH_ROOT . $folder->folderpath;
                    $files = JFolder::files($searchpath);
                break;
            }
            
            // output $contents
            echo json_encode($files);
            
        }
}

?>
