<?php defined('_JEXEC') or die('Restriced Access');

function getTopics($params, $id, $admin_params)
{
	global $mainframe, $option;
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'image.php');
	$topic = null;
	$teacherid = null;
	$templatemenuid = $params->get('templatemenuid');
	//$templatemenuid = $params->get('teachertemplateid');
	if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}

		$topic = '<table id="bsm_books" width=100%><tr>';
		$db	=& JFactory::getDBO();
		$query = 'select distinct a.* from #__bsms_topics a inner join #__bsms_studytopics b on a.id = b.topic_id';
		
		$db->setQuery($query);
		
        $tresult = $db->loadObjectList();
        
        $i = 0;
        
        $topic .= '<tr>';
        foreach ($tresult as &$b) {
            
            $topic .= '<td width="33%">';
		    $topic .= '<a href="index.php?option=com_biblestudy&view=studieslist&filter_topic='.$b->id.'&filter_teacher=0&filter_series=0&filter_location=0&filter_book=0&filter_year=0&filter_messagetype=0&templatemenuid='.$templatemenuid.'">';
		    
		    $topic .= $b->topic_text;
    		
            $topic .='</a>';
            
            $topic .= '</td>';
            $i++;
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
        $topic .= '</tr>';
		$topic .= '</table>';
        
	return $topic;
}
?>