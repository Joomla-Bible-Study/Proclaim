<?php defined('_JEXEC') or die('Restriced Access');

function getTopicsLandingPage($params, $id, $admin_params)
{
	$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');;
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'image.php');
	include_once($path1.'helper.php');
	//$addItemid = '';
	//$addItemid = getItemidLink($isplugin=0, $admin_params); //dump ($addItemid, 'AddItemid: ');
	$topic = null;
	$teacherid = null;
	$templatemenuid = $params->get('templatemenuid');
	//$templatemenuid = $params->get('teachertemplateid');
	$limit = $params->get('landingtopicslimit');
	if (!$limit) {$limit = 10000;}
	if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}

		$topic = "\n" . '<table id="landing_table" width=100%>';
		$db	=& JFactory::getDBO();
		$query = 'select distinct a.* from #__bsms_topics a inner join #__bsms_studytopics b on a.id = b.topic_id';
		
		$db->setQuery($query);
		
        $tresult = $db->loadObjectList();
        $t = 0;
        $i = 0;
        
        $topic .= "\n\t" . '<tr>';
        $showdiv = 0;
        foreach ($tresult as &$b) {
            
            if ($t >= $limit)
		{
			if ($showdiv < 1)
			{
				if ($i == 1) {
    	      		$topic .= "\n\t\t" . '<td  id="landing_td"></td>' . "\n\t\t" . '<td id="landing_td"></td>';
    	      		$topic .= "\n\t" . '</tr>';
    	    	};
    	    	if ($i == 2) {
    	        	$topic .= "\n\t\t" . '<td  id="landing_td"></td>';
    	      		$topic .= "\n\t" . '</tr>';
	        	};
	        	
	        				
			$topic .= "\n" .'</table>';
			$topic .= "\n\t" . '<div id="showhidetopics" style="display:none;"> <!-- start show/hide topics div-->';
			$topic .= "\n" . '<table width = "100%" id="landing_table">';
		
			$i = 0;
			$showdiv = 1;
			}
		}   
		
            if ($i == 0) {
                $topic .= "\n\t" . '<tr>';
            }
            $topic .= "\n\t\t" . '<td id="landing_td">';
		    $topic .= '<a href="index.php?option=com_biblestudy&view=studieslist&filter_topic='.$b->id.'&filter_teacher=0&filter_series=0&filter_location=0&filter_book=0&filter_year=0&filter_messagetype=0&templatemenuid='.$templatemenuid.'">';
		    
		    $topic .= $b->topic_text;
    		
            $topic .='</a>';
            
            $topic .= '</td>';
            $i++;
            $t++; //dump ($t, 't: ');
            if ($i == 3) {
                $topic .= "\n\t" . '</tr>';
                $i = 0;
            }
        }
        if ($i == 1) {
            $topic .= "\n\t\t" . '<td  id="landing_td"></td>' . "\n\t\t" . '<td id="landing_td"></td>';
        };
        if ($i == 2) {
            $topic .= "\n\t\t" . '<td  id="landing_td"></td>';
        };

		$topic .= "\n". '</table>' ."\n";

        if ($showdiv == 1)
			{	

			$topic .= "\n\t". '</div> <!-- close show/hide topics div-->';
			$showdiv = 2;
			}
  $topic .= '<div id="landing_separator"></div>';
        
	return $topic;
}
?>