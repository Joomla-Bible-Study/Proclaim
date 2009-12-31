<?php defined('_JEXEC') or die('Restriced Access');

function getBooks($params, $id, $admin_params)
{
	global $mainframe, $option;
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'image.php');
	include_once($path1.'helper.php');
	$addItemid = '';
	$addItemid = getItemidLink($isplugin=0, $admin_params); //dump ($addItemid, 'AddItemid: ');
	$book = null;
	$teacherid = null;
	$templatemenuid = $params->get('templatemenuid');
	//$templatemenuid = $params->get('teachertemplateid');
	$limit = $params->get('landingbooklimit');
	if (!$limit) {$limit = 10000;}
	if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}

		$book = '<table id="bsm_books" width=100%><tr>';
		$db	=& JFactory::getDBO();
		$query = 'select distinct a.* from #__bsms_books a inner join #__bsms_studies b on a.booknumber = b.booknumber';
		
		$db->setQuery($query);
		
        $tresult = $db->loadObjectList();
        $t = 0;
        $i = 0;
        
        $book .= '
		<tr>';
		$showdiv = 0;
        foreach ($tresult as &$b) {
            
            $book .= '<td width="33%">';
            if ($t >= $limit)
		{
			if ($showdiv < 1)
			{
			
			$book .= "</td></tr></table>";
			$book .= '<div id="showhidebook" style="display:none;">';
			$book .= '<table width = "100%"><tr><td>';
		
			$showdiv = 1;
			}
		}   
		    $book .= '<a href="index.php?option=com_biblestudy&view=studieslist&filter_book='.$b->booknumber.'&filter_teacher=0&filter_series=0&filter_topic=0&filter_location=0&filter_year=0&filter_messagetype=0&templatemenuid='.$templatemenuid.$addItemid.'">';
		    
		    $book .= $numRows;
		    $book .= $b->bookname;
    		
            $book .='</a>';
            
            $book .= '</td>';
            $i++;
            $t++; //dump ($t, 't: ');
            if ($i == 3) {
                $book .= '</tr><tr>';
                $i = 0;
            }
        }
        if ($i == 1) {
            $book .= '<td width="33%"></td><td width="33%"></td>';
        };
        if ($i == 2) {
            $book .= '<td width="33%"></td>';
        };
        if ($showdiv == 1)
			{	
        	$book .= '</td></tr></table>';
			$book .= '</div>';
			$showdiv = 2;
			}
        $book .= '</tr>';
		$book .= '</table>';
        
	return $book;
}
?>