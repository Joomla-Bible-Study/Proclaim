<?php defined('_JEXEC') or die('Restriced Access');

function getLocations($params, $id, $admin_params)
{
	global $mainframe, $option;
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'image.php');
	include_once($path1.'helper.php');
	$addItemid = '';
	$addItemid = getItemidLink($isplugin=0, $admin_params); //dump ($addItemid, 'AddItemid: ');
	$location = null;
	$teacherid = null;
	$templatemenuid = $params->get('templatemenuid');
	//$templatemenuid = $params->get('teachertemplateid');
	if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}
	$limit = $params->get('landinglocationslimit');
	if (!$limit) {$limit = 10000;}

		$location = '<table id="landing_table" width=100%><tr>';
		$db	=& JFactory::getDBO();
		$query = 'select distinct a.* from #__bsms_locations a inner join #__bsms_studies b on a.id = b.location_id';
		
		$db->setQuery($query);
		
        $tresult = $db->loadObjectList();
        $t = 0;
        $i = 0;
        
        $location .= '<tr>';
        $showdiv = 0;
        foreach ($tresult as &$b) {
            
            $location .= '<td id="landing_td">';
            if ($t >= $limit)
		{
			if ($showdiv < 1)
			{
			
			$location .= "</td></tr></table>";
			$location .= '<div id="showhidelocations" style="display:none;">';
			$location .= '<table width = "100%" id="landing_table"><tr><td>';
		
			$showdiv = 1;
			}
		}   
		    $location .= '<a href="index.php?option=com_biblestudy&view=studieslist&filter_location='.$b->id.'&filter_teacher=0&filter_series=0&filter_topic=0&filter_book=0&filter_year=0&filter_messagetype=0&templatemenuid='.$templatemenuid.$addItemid.'">';
		    
		    $location .= $b->location_text;
    		
            $location .='</a>';
            
            $location .= '</td>';
            $i++;
            $t++; //dump ($t, 't: ');
            if ($i == 3) {
                $location .= '</tr><tr>';
                $i = 0;
            }
        }
        if ($i == 1) {
            $location .= '<td id="landing_td"></td><td id="landing_td"></td>';
        };
        if ($i == 2) {
            $location .= '<td id="landing_td"></td>';
        };
        if ($showdiv == 1)
			{	
        	$location .= '</td></tr></table>';
			$location .= '</div>';
			$showdiv = 2;
			}
        $location .= '</tr>';
		$location .= '</table>';
        
	return $location;
}
?>