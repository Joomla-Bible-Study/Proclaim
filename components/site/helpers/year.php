<?php defined('_JEXEC') or die('Restriced Access');

function getYears($params, $id, $admin_params)
{
	global $mainframe, $option;
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'image.php');
	$year = null;
	$teacherid = null;
	$templatemenuid = $params->get('templatemenuid');
	//$templatemenuid = $params->get('teachertemplateid');
	if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}

		$year = '<table id="bsm_years" width=100%><tr>';
		$db	=& JFactory::getDBO();
		$query = 'select distinct year(studydate) as theYear from #__bsms_studies order by year(studydate) desc';
		
		$db->setQuery($query);
		
        $tresult = $db->loadObjectList();
        
        $i = 0;
        
        $year .= '<tr>';
        foreach ($tresult as &$b) {
            
            $year .= '<td width="33%">';
		    $year .= '<a href="index.php?option=com_biblestudy&view=studieslist&filter_year='.$b->theYear.'&filter_teacher=0&filter_series=0&filter_topic=0&filter_location=0&filter_book=0&filter_messagetype=0&templatemenuid='.$templatemenuid.'">';
		    
		    $year .= $numRows;
		    $year .= $b->theYear;
    		
            $year .='</a>';
            
            $year .= '</td>';
            $i++;
            if ($i == 3) {
                $year .= '</tr><tr>';
                $i = 0;
            }
        }
        if ($i == 1) {
            $year .= '<td width="33%"></td><td width="33%"></td>';
        };
        if ($i == 2) {
            $year .= '<td width="33%"></td>';
        };
        $year .= '</tr>';
		$year .= '</table>';
  
	return $year;
}
?>