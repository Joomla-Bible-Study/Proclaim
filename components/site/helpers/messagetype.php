<?php defined('_JEXEC') or die('Restriced Access');

function getMessageTypesLandingPage($params, $id, $admin_params)
{
	$mainframe =& JFactory::getApplication();, $option;
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'image.php');
	include_once($path1.'helper.php');
	$addItemid = '';
	$addItemid = getItemidLink($isplugin=0, $admin_params); //dump ($addItemid, 'AddItemid: ');
	$messagetype = null;
	$teacherid = null;
	$templatemenuid = $params->get('templatemenuid');
	//$templatemenuid = $params->get('teachertemplateid');
	$limit = $params->get('landingmessagetypelimit');
	if (!$limit) {$limit = 10000;}
	
	if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}

		$messagetype = "\n" . '<table id="landing_table" width="100%">';
		$db	=& JFactory::getDBO();
		$query = 'select distinct a.* from #__bsms_message_type a inner join #__bsms_studies b on a.id = b.messagetype';
		
		$db->setQuery($query);

        $tresult = $db->loadObjectList();
         $t = 0;
         $i = 0;
         
        $messagetype .= "\n\t" . '<tr>';
        $showdiv = 0;
        foreach ($tresult as &$b) {
            
            if ($t >= $limit)
		{
			if ($showdiv < 1)
			{
				if ($i == 1) {
    	      		$messagetype .= "\n\t\t" . '<td  id="landing_td"></td>' . "\n\t\t" . '<td id="landing_td"></td>';
    	      		$messagetype .= "\n\t" . '</tr>';
    	    	};
    	    	if ($i == 2) {
    	        	$messagetype .= "\n\t\t" . '<td  id="landing_td"></td>';
    	      		$messagetype .= "\n\t" . '</tr>';
	        	};
			
			$messagetype .= "\n" .'</table>';
			$messagetype .= "\n\t" . '<div id="showhidemessagetype" style="display:none;"> <!-- start show/hide messagetype div-->';
			$messagetype .= "\n" .'<table width = "100%" id="landing_table">';
		
            $i = 0;
			$showdiv = 1;
			}
		}   
		
            if ($i == 0) {
                $messagetype .= "\n\t" . '<tr>';
            }
            $messagetype .= "\n\t\t" . '<td id="landing_td">';

		    $messagetype .= '<a href="index.php?option=com_biblestudy&view=studieslist&filter_messagetype='.$b->id.'&filter_book=0&filter_teacher=0&filter_series=0&filter_topic=0&filter_location=0&filter_year=0&templatemenuid='.$templatemenuid.$addItemid.'">';
		    
		    $messagetype .= $b->message_type;
    		
            $messagetype .='</a>';
            
            $messagetype .= '</td>';
            
            $i++;
            $t++; //dump ($t, 't: ');
            if ($i == 3) {
                $messagetype .= "\n\t" . '</tr>';
                $i = 0;
            }
        }
        if ($i == 1) {
            $messagetype .= "\n\t\t" . '<td  id="landing_td"></td>' . "\n\t\t" . '<td id="landing_td"></td>';
        };
        if ($i == 2) {
            $messagetype .= "\n\t\t" . '<td  id="landing_td"></td>';
        };
        
        $messagetype .= "\n". '</table>' ."\n";

        if ($showdiv == 1)
			{	

			$messagetype .= "\n\t". '</div> <!-- close show/hide messagetype div-->';
			$showdiv = 2;
			}
  $messagetype .= '<div id="landing_separator"></div>';
        
	return $messagetype;
}
?>