<?php defined('_JEXEC') or die('Restriced Access');

function getMessageTypes($params, $id, $admin_params)
{
	global $mainframe, $option;
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'image.php');
	$messagetype = null;
	$teacherid = null;
	$templatemenuid = $params->get('templatemenuid');
	//$templatemenuid = $params->get('teachertemplateid');
	if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}

		$messagetype = '<table id="bsm_messagetype" width="100%"><tr>';
		$db	=& JFactory::getDBO();
		$query = 'select distinct a.* from #__bsms_message_type a inner join #__bsms_studies b on a.id = b.messagetype';
		
		$db->setQuery($query);
		$i = 0;
        $tresult = $db->loadObjectList();
        $messagetype .= '<tr>';
        foreach ($tresult as &$b) {
            
            $messagetype .= '<td width="33%">';
		    $messagetype .= '<a href="index.php?option=com_biblestudy&view=studieslist&filter_messagetype='.$b->id.'&filter_book=0&filter_teacher=0&filter_series=0&filter_topic=0&filter_location=0&filter_year=0&templatemenuid='.$templatemenuid.'">';
		    
		    $messagetype .= $b->message_type;
    		
            $messagetype .='</a>';
            
            $messagetype .= '</td>';
            
            $i++;
            if ($i == 3) {
                $messagetype .= '</tr><tr>';
                $i = 0;
            }
        }
        if ($i == 1) {
            $messagetype .= '<td width="33%"></td><td width="33%"></td>';
        };
        if ($i == 2) {
            $messagetype .= '<td width="33%"></td>';
        };
        $messagetype .= '</tr>';
		$messagetype .= '</table>';
        
	return $messagetype;
}
?>