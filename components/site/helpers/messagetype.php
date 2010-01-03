<?php defined('_JEXEC') or die('Restriced Access');

function getMessageTypes($params, $id, $admin_params)
{
	global $mainframe, $option;
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'image.php');
	include_once($path1.'helper.php');
	$addItemid = '';
	$addItemid = getItemidLink($isplugin=0, $admin_params); //dump ($addItemid, 'AddItemid: ');
	$messagetype = null;
	$teacherid = null;
	$templatemenuid = $params->get('templatemenuid');
	//$templatemenuid = $params->get('teachertemplateid');
	if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}
	$limit = $params->get('landingmessagetypelimit');
	if (!$limit) {$limit = 10000;}
		$messagetype = '<table id="landing_table" width="100%"><tr>';
		$db	=& JFactory::getDBO();
		$query = 'select distinct a.* from #__bsms_message_type a inner join #__bsms_studies b on a.id = b.messagetype';
		
		$db->setQuery($query);
		$t = 0;
		$i = 0;
        $tresult = $db->loadObjectList();
        $messagetype .= '<tr>';
        $showdiv = 0;
        foreach ($tresult as &$b) {
            
            $messagetype .= '<td id="landing_td">';
            if ($t >= $limit)
		{
			if ($showdiv < 1)
			{
			
			$messagetype .= "</td></tr></table>";
			$messagetype .= '<div id="showhidemessagetype" style="display:none;">';
			$messagetype .= '<table width = "100%" id="landing_table"><tr><td>';
		
			$showdiv = 1;
			}
		}   
		    $messagetype .= '<a href="index.php?option=com_biblestudy&view=studieslist&filter_messagetype='.$b->id.'&filter_book=0&filter_teacher=0&filter_series=0&filter_topic=0&filter_location=0&filter_year=0&templatemenuid='.$templatemenuid.$addItemid.'">';
		    
		    $messagetype .= $b->message_type;
    		
            $messagetype .='</a>';
            
            $messagetype .= '</td>';
            
            $i++;
            $t++; //dump ($t, 't: ');
            if ($i == 3) {
                $messagetype .= '</tr><tr>';
                $i = 0;
            }
        }
        if ($i == 1) {
            $messagetype .= '<td id="landing_td"></td><td id="landing_td"></td>';
        };
        if ($i == 2) {
            $messagetype .= '<td id="landing_td"></td>';
        };
        if ($showdiv == 1)
			{	
        	$messagetype .= '</td></tr></table>';
			$messagetype .= '</div>';
			$showdiv = 2;
			}
        $messagetype .= '</tr>';
		$messagetype .= '</table>';
        
	return $messagetype;
}
?>