<?php defined('_JEXEC') or die('Restriced Access');

function getBooksLandingPage($params, $id, $admin_params)
{
	$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'image.php');
	include_once($path1.'helper.php');
//	$addItemid = '';
//	$addItemid = getItemidLink($isplugin=0, $admin_params); //dump ($addItemid, 'AddItemid: ');
	$book = null;
	$teacherid = null;
	$t = $params->get('t');
	//$t = $params->get('teachertemplateid');
	$limit = $params->get('landingbooklimit');
	if (!$limit) {$limit = 10000;}
	if (!$t) {$t = JRequest::getVar('t',1,'get','int');}

		$book = "\n" . '<table id="landing_table" width=100%>';
		$db	=& JFactory::getDBO();
		$query = 'select distinct a.* from #__bsms_books a inner join #__bsms_studies b on a.booknumber = b.booknumber order by a.booknumber';
		
		$db->setQuery($query);
		
        $tresult = $db->loadObjectList();
        $t = 0;
        $i = 0;
        
        $book .= "\n\t" . '<tr>';
		$showdiv = 0;
        foreach ($tresult as &$b) {
            
            if ($t >= $limit)
		{
			if ($showdiv < 1)
			{
				if ($i == 1) {
    	      		$book .= "\n\t\t" . '<td  id="landing_td"></td>' . "\n\t\t" . '<td id="landing_td"></td>';
    	      		$book .= "\n\t" . '</tr>';
    	    	};
    	    	if ($i == 2) {
    	        	$book .= "\n\t\t" . '<td  id="landing_td"></td>';
    	      		$book .= "\n\t" . '</tr>';
	        	};


			$book .= "\n" .'</table>';
			$book .= "\n\t" . '<div id="showhidebook" style="display:none;"> <!-- start show/hide book div-->';
			$book .= "\n" . '<table width = "100%" id="landing_table">';
			
			$i = 0;
			$showdiv = 1;
			}
		}   
		
            if ($i == 0) {
                $book .= "\n\t" . '<tr>';
            }
            $book .= "\n\t\t" . '<td id="landing_td">';
		    $book .= '<a href="index.php?option=com_biblestudy&view=studieslist&filter_book='.$b->booknumber.'&filter_teacher=0&filter_series=0&filter_topic=0&filter_location=0&filter_year=0&filter_messagetype=0&t='.$t.'">';
		    ##$book .= '<a href="dummy">'; ## can uncomment this line and use instead of above line when bug-fixing for simpler code
		    
		    $book .= $numRows;
		    $book .= JText::sprintf($b->bookname);
    		
            $book .='</a>';
            
            $book .= '</td>';
            $i++;
            $t++; //dump ($t, 't: ');
            if ($i == 3) {
                $book .= "\n\t" . '</tr>';
                $i = 0;
            }
        }
        if ($i == 1) {
            $book .= "\n\t\t" . '<td  id="landing_td"></td>' . "\n\t\t" . '<td id="landing_td"></td>';
        };
        if ($i == 2) {
            $book .= "\n\t\t" . '<td  id="landing_td"></td>';
        };

		$book .= "\n". '</table>' ."\n";

        if ($showdiv == 1)
			{	

			$book .= "\n\t". '</div> <!-- close show/hide books div-->';
			$showdiv = 2;
			}
  $book .= '<div id="landing_separator"></div>';
        
	return $book;
}
?>