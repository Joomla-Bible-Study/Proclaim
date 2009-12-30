<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

function getTeacher($params, $id, $admin_params)
{
	
	global $mainframe, $option;
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'image.php');
	$teacher = null;
	$teacherid = null;
	//$templatemenuid = $params->get('templatemenuid');
	$templatemenuid = $params->get('teachertemplateid');
	if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}
	$viewtype = JRequest::getVar('view');
	//dump ($viewtype, 'view: ');
		if ($viewtype == 'studieslist')
			{
				$teacherid = $params->get('listteachers');
				$teacherids = explode(",", $params->get('listteachers'));
			}
		if ($viewtype == 'studydetails')
			{$teacherids[] = $id;}
	//if ($teacherid > 0) {$teacherids['id'] = $teacherid;}
	//if ($params->get('mult_teachers')) { $teacherids = explode(",", $params->get('mult_teachers'));}
	//if ($params->get('listteachers') && $teacherid) {$teacherids = explode(",", $params->get('mult_teachers')); $teacherids[] = $teacherid;}
	//if ($id) {$teacherids[] = $id;}	
		
		//dump ($teacherids['id'], 'tresult: ');
		$teacher = '<table id = "teacher"><tr>';
		if (!isset($teacherids)) {return $teacher;}
		foreach ($teacherids as $teachers)
		
		{
			$database	= & JFactory::getDBO();
			$query = 'SELECT * FROM #__bsms_teachers'.
					'  WHERE id = '.$teachers['id'];
			//dump ($teachers, 'teachers: ');		
			$database->setQuery($query);
			$tresult = $database->loadObject();
			$i_path = null;
			//dump ($tresult, 'tresult: ');
			//Check to see if there is a teacher image, if not, skip this step
			
			if ($tresult->teacher_thumbnail == '- Select Image -' || !$tresult->teacher_thumbnail) 
				{ 
					$image->path = $tresult->thumb; $image->height = $tresult->thumbh; $image->width = $tresult->thumbw;
				}
			else
			{
				if ($tresult->teacher_thumbnail && !$admin_params->get('teachers_imagefolder')) 
					{ 
						$i_path = 'images/stories/'.$tresult->teacher_thumbnail; 
					}
				if ($tresult->teacher_thumbnail && $admin_params->get('teachers_imagefolder')) 
					{
						$i_path = 'images/'.$admin_params->get('teachers_imagefolder').'/'.$tresult->teacher_thumbnail;
					}
			$image = getImage($i_path);
				if (!$image) 
					{
						$image->path = ''; $image->width=0; $image->height=0;
					}
			}
				$teacher .= '<td><table cellspacing ="0"><tr><td><img src="'.$image->path.'" border="1" width="'.$image->width.'" height="'.$image->height.'" ></td></tr>';
			
		$teacher .= '<tr><td>';
		if ($params->get('teacherlink') > 0)
			{
				$teacher .= '<a href="index.php?option=com_biblestudy&view=teacherdisplay&amp;id='.$tresult->id.'&templatemenuid='.$templatemenuid.'">';
			}
		$teacher .= $tresult->teachername;
		if ($params->get('teacherlink') > 0)
			{
				$teacher .='</a>';
			}
		$teacher .= '</td></tr></table></td>';
		}
	if ($params->get('intro_show') == 2 && $viewtype == 'studieslist')
		{
			$teacher .= '<td><div id="listintro"><table id="listintro"><tr><td><p>'.$params->get('list_intro').'</p></td></tr></table> </div></td>';
		}
		$teacher .= '</tr></table>';

	return $teacher;
}
function getTeacherLandingPage($params, $id, $admin_params)
{
	
	global $mainframe, $option;
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'image.php');
	include_once($path1.'helper.php');
	$addItemid = '';
	//$addItemid = getItemidLink($isplugin=0, $admin_params); 
	$teacher = null;
	$teacherid = null;
	$templatemenuid = $params->get('templatemenuid');
	//$templatemenuid = $params->get('teachertemplateid');
	
	$menu =& JSite::getMenu();
	
	if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}

		$teacher = '<table id="bsm_teacher" width="100%"><tr>';
		$db	=& JFactory::getDBO();
		$query = 'select distinct a.* from #__bsms_teachers a inner join #__bsms_studies b on a.id = b.teacher_id where list_show = 1';
		
		$db->setQuery($query);
		
        $tresult = $db->loadObjectList();
        $teacher .= '<tr>';        
        foreach ($tresult as &$b) {
            
            $teacher .= '<td width="33%">';
            if ($params->get('linkto') == 0) {
		        $teacher .= '<a href="index.php?option=com_biblestudy&view=studieslist&filter_teacher='.$b->id.'&filter_book=0&filter_series=0&filter_topic=0&filter_location=0&filter_year=0&filter_messagetype=0&templatemenuid='.$templatemenuid.'&Itemid='.$itemid.'">';
            } else {
		    
		        $teacher .= '<a href="index.php?option=com_biblestudy&view=teacherdisplay&id='.$b->id.'&templatemenuid='.$templatemenuid.'&Itemid='.$itemid.'">';
		    };
		    $teacher .= $b->teachername;
    		
            $teacher .='</a>';
            
            $teacher .= '</td>';
            
            $i++;
            if ($i == 3) {
                $teacher .= '</tr><tr>';
                $i = 0;
            }
        }
        if ($i == 1) {
            $teacher .= '<td width="33%"></td><td width="33%"></td>';
        };
        if ($i == 2) {
            $teacher .= '<td width="33%"></td>';
        };
        $teacher .= '</tr>';
		$teacher .= '</table>';
        
	return $teacher;
}

function getTeacherListExp($row, $params, $oddeven, $admin_params, $template)
{
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'elements.php');
	include_once($path1.'scripture.php');
	include_once($path1.'custom.php');
		
	$label = $params->get('teacher_templatecode');
    $label = str_replace('{{teacher}}', $row->teachername, $label);
	$label = str_replace('{{title}}', $row->title, $label);
	$label = str_replace('{{phone}}', $row->phone, $label);
	$label = str_replace('{{website}}', '<A href="' .$row->website .'">Website</a>', $label);
	$label = str_replace('{{information}}', $row->information, $label);
	$label = str_replace('{{image}}', '<img src="'. $row->image .'" width="'.$row->imagew.'" height="'.$row->imageh.'" />', $label);
	$label = str_replace('{{short}}', $row->short, $label);
	$label = str_replace('{{thumbnail}}', '<img src="'. $row->thumb .'" width="'.$row->thumbw.'" height="'.$row->thumbh.'" />', $label);
    $label = str_replace('{{url}}', 'index.php?component=com_biblestudy&view=teacherdisplay&id='.$row->id .'&templatemenuid='.$template, $label);
	return $label;

}

function getTeacherDetailsExp($row, $params, $template)
{
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'elements.php');
	include_once($path1.'scripture.php');
	include_once($path1.'custom.php');
	//dump ($row);
    
    $label = $params->get('teacher_detailtemplate');
    $label = str_replace('{{teacher}}', $row->teachername, $label);
	$label = str_replace('{{title}}', $row->title, $label);
	$label = str_replace('{{phone}}', $row->phone, $label);
	$label = str_replace('{{website}}', '<A href="' .$row->website .'">Website</a>', $label);
	$label = str_replace('{{information}}', $row->information, $label);
	$label = str_replace('{{image}}', '<img src="'. $row->image .'" width="'.$row->imagew.'" height="'.$row->imageh.'" id="bsms_teacherImage" />', $label);
	$label = str_replace('{{short}}', $row->short, $label);
	$label = str_replace('{{thumbnail}}', '<img src="'. $row->thumb .'" width="'.$row->thumbw.'" height="'.$row->thumbh.'" id="bsms_teacherThumbnail" />', $label);
    $label = str_replace('{{information}}', $row->information, $label);
    $label = str_replace('{{shortinformation}}', $row->short, $label);
    
	return $label;
}

function getTeacherStudiesExp($id, $params, $admin_params, $template)
{
    $path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
    include_once($path1.'listing.php');
    $path2 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'models'.DS;
    include_once($path2.'studieslist.php');
    
	$limit = '';
	$nolimit = JRequest::getVar('nolimit', 'int', 0);
	if ($params->get('series_detail_limit')) {$limit = ' LIMIT '.$params->get('series_detail_limit');}
	if ($nolimit == 1) {$limit = '';}
	$db	= & JFactory::getDBO();
	$query = 'SELECT s.series_id FROM #__bsms_studies AS s WHERE s.published = 1 AND s.series_id = '.$id;
	$db->setQuery($query);
	$allrows = $db->loadObjectList();
	$rows = $db->getAffectedRows();
    /*
	$query = 'SELECT #__bsms_studies.*, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername, #__bsms_teachers.title AS teachertitle,'
		. ' #__bsms_series.id AS sid, #__bsms_series.series_text, #__bsms_series.description AS sdescription, #__bsms_series.series_thumbnail, #__bsms_message_type.id AS mid,'
		. ' #__bsms_message_type.message_type AS message_type, #__bsms_books.bookname,'
		. ' #__bsms_topics.id AS tp_id, #__bsms_topics.topic_text, #__bsms_locations.id AS lid, #__bsms_locations.location_text'
		. ' FROM #__bsms_studies'
		. ' LEFT JOIN #__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)'
		. ' LEFT JOIN #__bsms_teachers ON (#__bsms_studies.teacher_id = #__bsms_teachers.id)'
		. ' LEFT JOIN #__bsms_series ON (#__bsms_studies.series_id = #__bsms_series.id)'
		. ' LEFT JOIN #__bsms_message_type ON (#__bsms_studies.messagetype = #__bsms_message_type.id)'
		. '	LEFT JOIN #__bsms_topics ON (#__bsms_studies.topics_id = #__bsms_topics.id)'
		. ' LEFT JOIN #__bsms_locations ON (#__bsms_studies.location_id = #__bsms_locations.id)'
		. ' where #__bsms_teachers.id = ' .$id;
	*/
	$query = 'SELECT #__bsms_studies.*, #__bsms_teachers.id AS tid, #__bsms_teachers.teachername,'
	  . ' #__bsms_series.id AS sid, #__bsms_series.series_text, #__bsms_message_type.id AS mid,'
	  . ' #__bsms_message_type.message_type AS message_type, #__bsms_books.bookname,'
	  . ' group_concat(#__bsms_topics.id separator ", ") AS tp_id, group_concat(#__bsms_topics.topic_text separator ", ") as topic_text'
	  . ' FROM #__bsms_studies'
	  . ' left join #__bsms_studytopics ON (#__bsms_studies.id = #__bsms_studytopics.study_id)'
	  . ' LEFT JOIN #__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)'
	  . ' LEFT JOIN #__bsms_teachers ON (#__bsms_studies.teacher_id = #__bsms_teachers.id)'
	  . ' LEFT JOIN #__bsms_series ON (#__bsms_studies.series_id = #__bsms_series.id)'
	  . ' LEFT JOIN #__bsms_message_type ON (#__bsms_studies.messagetype = #__bsms_message_type.id)'
	  . ' LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studytopics.topic_id)'
	  . ' where #__bsms_teachers.id = ' .$id
	  . ' GROUP BY #__bsms_studies.id'
	  . ' order by studydate desc'
	  ;
	
	$db->setQuery($query);
	$result = $db->loadObjectList();
	$numrows = $db->getAffectedRows();
	
	$studies = '';
	
	  switch ($params->get('wrapcode')) {
      case '0':
        //Do Nothing
        break;
      case 'T':
        //Table
        $studies .= '<table id="bsms_studytable" width="100%">'; 
        break;
      case 'D':
        //DIV
        $studies .= '<div>';
        break;
      }
	
	$params->get('headercode');
	foreach ($result AS $row)
	{
	    $studies .= getListingExp($row, $params, $oddeven, $params, $params->seriesdetailtemplateid);	
	}
	
	  switch ($params->get('wrapcode')) {
      case '0':
        //Do Nothing
        break;
      case 'T':
        //Table
        $studies .= '</table>'; 
        break;
      case 'D':
        //DIV
        $studies .= '</div>';
        break;
      }
return $studies;
}
?>