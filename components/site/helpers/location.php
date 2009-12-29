<?php defined('_JEXEC') or die('Restriced Access');

function getLocations($params, $id, $admin_params)
{
	global $mainframe, $option;
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'image.php');
	$location = null;
	$teacherid = null;
	$templatemenuid = $params->get('templatemenuid');
	//$templatemenuid = $params->get('teachertemplateid');
	if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}

		$location = '<table id="bsm_books" width=100%><tr>';
		$db	=& JFactory::getDBO();
		$query = 'select distinct a.* from #__bsms_locations a inner join #__bsms_studies b on a.id = b.location_id';
		
		$db->setQuery($query);
		
        $tresult = $db->loadObjectList();
        
        $i = 0;
        
        $location .= '<tr>';
        foreach ($tresult as &$b) {
            
            $location .= '<td width="33%">';
		    $location .= '<a href="index.php?option=com_biblestudy&view=studieslist&filter_location='.$b->id.'&filter_teacher=0&filter_series=0&filter_topic=0&filter_book=0&filter_year=0&filter_messagetype=0&templatemenuid='.$templatemenuid.'">';
		    
		    $location .= $b->location_text;
    		
            $location .='</a>';
            
            $location .= '</td>';
            $i++;
            if ($i == 3) {
                $location .= '</tr><tr>';
                $i = 0;
            }
        }
        if ($i == 1) {
            $location .= '<td width="33%"></td><td width="33%"></td>';
        };
        if ($i == 2) {
            $location .= '<td width="33%"></td>';
        };
        $location .= '</tr>';
		$location .= '</table>';
        
	return $location;
}
?>