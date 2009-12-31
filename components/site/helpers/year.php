<?php defined('_JEXEC') or die('Restriced Access');

function getYears($params, $id, $admin_params)
{
	global $mainframe, $option;
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'image.php');
	include_once($path1.'helper.php');
	$addItemid = '';
	$addItemid = getItemidLink($isplugin=0, $admin_params); //dump ($addItemid, 'AddItemid: ');
	$year = null;
	$teacherid = null;
	$templatemenuid = $params->get('templatemenuid');
	//$templatemenuid = $params->get('teachertemplateid');
	if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}
	$limit = $params->get('landingyearslimit');
	if (!$limit) {$limit = 10000;}
		
		$year = '<table id="bsm_years" width=100%>';
		$db	=& JFactory::getDBO();
		$query = 'select distinct year(studydate) as theYear from #__bsms_studies order by year(studydate) desc';
		
		$db->setQuery($query);
		
        $tresult = $db->loadObjectList();
        $t = 0;
        $i = 0;
        
        $year .= '
		<tr>';
        $showdiv = 0;
        foreach ($tresult as &$b) {
        	
            $year .= '<td width="33%">';
            if ($t >= $limit)
		{
			if ($showdiv < 1)
			{
			
			$year .= "</td></tr></table>";
			$year .= '<div id="showhideyears" style="display:none;">';
			$year .= '<table width = "100%"><tr><td>';
		
			$showdiv = 1;
			}
		}   
		    $year .= '<a href="index.php?option=com_biblestudy&view=studieslist&filter_year='.$b->theYear.'&filter_teacher=0&filter_series=0&filter_topic=0&filter_location=0&filter_book=0&filter_messagetype=0&templatemenuid='.$templatemenuid.$addItemid.'">';
		    
		    $year .= $numRows;
		    $year .= $b->theYear;
    		
            $year .='</a>';
            
            $year .= '</td>';
            $i++;
            $t++; //dump ($t, 't: ');
            if ($i == 3) {
                $year .= '</tr>';
 				$year .= '<tr>';
 				
	
 				
                $i = 0;
            }
            
        }
        if ($i == 1) {
            $year .= '<td width="33%"></td><td width="33%"></td>';
        };
        if ($i == 2) {
            $year .= '<td width="33%"></td>';
        };
        if ($showdiv == 1)
			{	
        	$year .= '</td></tr></table>';
			$year .= '</div>';
			$showdiv = 2;
			}
        $year .= '</tr>';
		$year .= '</table>';
	
		
		
  
	return $year;
}
?>