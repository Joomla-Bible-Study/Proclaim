<?php defined('_JEXEC') or die('Restriced Access');

function getTopics($params, $id, $admin_params)
{
	global $mainframe, $option;
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'image.php');
	include_once($path1.'helper.php');
	$addItemid = '';
	$addItemid = getItemidLink($isplugin=0, $admin_params); //dump ($addItemid, 'AddItemid: ');
	$topic = null;
	$teacherid = null;
	$templatemenuid = $params->get('templatemenuid');
	//$templatemenuid = $params->get('teachertemplateid');
	$limit = $params->get('landingtopicslimit');
	if (!$limit) {$limit = 10000;}
	if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}

		$topic = '<table id="bsm_books" width=100%><tr>';
		$db	=& JFactory::getDBO();
		$query = 'select distinct a.* from #__bsms_topics a inner join #__bsms_studytopics b on a.id = b.topic_id';
		
		$db->setQuery($query);
		
        $tresult = $db->loadObjectList();
        $t = 0;
        $i = 0;
        
        $topic .= '<tr>';
        $showdiv = 0;
        foreach ($tresult as &$b) {
            
            $topic .= '<td width="33%">';
            if ($t >= $limit)
		{
			if ($showdiv < 1)
			{
			
			$topic .= "</td></tr></table>";
			$topic .= '<div id="showhidetopics" style="display:none;">';
			$topic .= '<table width = "100%"><tr><td>';
		
			$showdiv = 1;
			}
		}   
		    $topic .= '<a href="index.php?option=com_biblestudy&view=studieslist&filter_topic='.$b->id.'&filter_teacher=0&filter_series=0&filter_location=0&filter_book=0&filter_year=0&filter_messagetype=0&templatemenuid='.$templatemenuid.$addItemid.'">';
		    
		    $topic .= $b->topic_text;
    		
            $topic .='</a>';
            
            $topic .= '</td>';
            $i++;
            $t++; //dump ($t, 't: ');
            if ($i == 3) {
                $topic .= '</tr><tr>';
                $i = 0;
            }
        }
        if ($i == 1) {
            $topic .= '<td width="33%"></td><td width="33%"></td>';
        };
        if ($i == 2) {
            $topic .= '<td width="33%"></td>';
        };
         if ($showdiv == 1)
			{	
        	$topic .= '</td></tr></table>';
			$topic .= '</div>';
			$showdiv = 2;
			}
        $topic .= '</tr>';
		$topic .= '</table>';
        
	return $topic;
}
?>