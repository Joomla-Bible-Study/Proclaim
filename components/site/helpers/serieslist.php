<?php defined('_JEXEC') or die('Restriced Access');
function getSerieslist($row, $params, $oddeven, $admin_params, $template, $view)
{ //dump ($row->series_thumbnail, 'series: ');
	//dump ($row);
	$listing = '';
	//$listing = '<table id="bslisttable" cellspacing="0">';
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'elements.php');
	include_once($path1.'custom.php');
	include_once($path1.'image.php');
	
	if ($params->get('series_show_description') == 0) {$listing .= '<tr class="onlyrow '.$oddeven.'">';}
	else {$listing .= '<tr class="firstrow firstcol '.$oddeven.'">';}
	
	$custom = $params->get('seriescustom1');
	$listelementid = $params->get('serieselement1'); //dump ($listelementid, 'listelementid: ');
	$islink = $params->get('seriesislink1');
	$r = 'firstcol';
	//if (!$custom){$listelement = seriesGetelement($r, $row, $listelementid, $custom, $islink, $admin_params, $params);}
	//if ($custom){$listelement = seriesGetcustom($r, $row, $listelementid, $custom, $islink, $admin_params, $params);} 
	$listelement = seriesGetelement($r, $row, $listelementid, $custom, $islink, $admin_params, $params, $view);
	$listing .= $listelement;
	if (!$listelementid)
		{
	$listing .= '<td class="firstrow firstcol">';
	$listing .= '</td>';
		}
	
	$custom = $params->get('seriescustom2');
	$listelementid = $params->get('serieselement2');
	$islink = $params->get('seriesislink2');
	$r = '';
	//dump ($row);
	//if (!$custom){$listelement = seriesGetelement($r, $row, $listelementid, $custom, $islink, $admin_params, $params);}
	//if ($custom){$listelement = seriesGetcustom($r, $row, $listelementid, $custom, $islink, $admin_params, $params);}
	$listelement = seriesGetelement($r, $row, $listelementid, $custom, $islink, $admin_params, $params, $view); 
		$listing .= $listelement;
	if (!$listelementid)
		{
	$listing .= '<td >';
	$listing .= '</td>';
		}
	$custom = $params->get('seriescustom3');
	$listelementid = $params->get('serieselement3');
	$islink = $params->get('seriesislink3');
	$r = '';
	//if (!$custom){$listelement = seriesGetelement($r, $row, $listelementid, $custom, $islink, $admin_params, $params);}
	//if ($custom){$listelement = seriesGetcustom($r, $row, $listelementid, $custom, $islink, $admin_params, $params);}
	$listelement = seriesGetelement($r, $row, $listelementid, $custom, $islink, $admin_params, $params, $view); 
	$listing .= $listelement;
	if (!$listelementid)
		{
	$listing .= '<td >';
	$listing .= '</td>';
		}
	
	$custom = $params->get('seriescustom4');
	$listelementid = $params->get('serieselement4');
	$islink = $params->get('seriesislink4');
	$r = 'lastcol';
	//if (!$custom){$listelement = seriesGetelement($r, $row, $listelementid, $custom, $islink, $admin_params, $params);}
	//if ($custom){$listelement = seriesGetcustom($r, $row, $listelementid, $custom, $islink, $admin_params, $params);} 
	//dump ($listelement, 'listelement: ');
	$listelement = seriesGetelement($r, $row, $listelementid, $custom, $islink, $admin_params, $params, $view);
	$listing .= $listelement; 
	//dump ($listelement);
		if (!$listelementid)
		{
			$listing .= '<td class="lastcol"></td>'; 
		}
	$listing .= '</tr>';
	
	//add if last row to above
	
	if ($params->get('series_show_description') > 0 ) 
		{
			$listing .= '<tr class="lastrow '.$oddeven.'">';
			$listing .= '<td colspan="4" class="description">';
			if ($params->get('series_characters') && $view == 0) {
				$listing .= substr($row->description,0,$params->get('series_characters'));
				$listing .= ' - '.'<a href="index.php?option=com_biblestudy&view=seriesdetail&templatemenuid='.$params->get('seriesdetailtemplateid', 1).'&id='.$row->id.'">'.JText::_('Read More').'</a>';
			}
			else {$listing .= $row->description;}
			//elseif ($view == 1) {$listing .= getSeriesstudies($row, $params, $admin_params);}
			$listing .= '</td></tr>';
		}
	//dump ($listing, 'listing: ');
	//$listing .= '</table>';
	return $listing;
}

//elements are: series title, series image, series pastor + image, description

function getSerieslink($islink, $row, $element, $params, $admin_params)
{
	if ($islink == 1)
	{
		$link = '<a href="'.JRoute::_('index.php?option=com_biblestudy&view=seriesdetail&templatemenuid='.$params->get('seriesdetailtemplateid', 1).'&id='.$row->id).'">'.$element.'</a>';
	}
	else
	{
		$link = '<a href="'.JRoute::_('index.php?option=com_biblestudy&view=teacherdisplay&templatemenuid='.$params->get('teachertemplateid', 1).'&id='.$row->id).'">'.$element.'</a>';	
	}
	return $link;
}

function getStudieslink($islink, $row, $element, $params, $admin_params)
{
	$link = '<a href="'.JRoute::_('index.php?option=com_biblestudy&view=studydetails&templatemenuid='.$params->get('studiesdetailtemplateid', 1).'&id='.$row->id).'">'.$element.'</a>';
	return $link;
}

function seriesGetelement($r, $row, $listelementid, $custom, $islink, $admin_params, $params, $view)
{//dump ($admin_params->get('teachers_imagefolder'), 'listelementidcheck: ');
	//dump ($r);
	$element = '';
	switch ($listelementid)
	{ 
		case 1:
			$element = $row->series_text;
			if ($islink > 0) {$element = getSerieslink($islink, $row, $element, $params, $admin_params);}
			$element = '<td class="'.$r.' title">'.$element.'</td>';
			break;
		case 2:
			if ($row->series_thumbnail && !$admin_params->get('series_imagefolder')) { $i_path = 'components/com_biblestudy/images/'.$row->series_thumbnail; }
			if ($row->series_thumbnail && $admin_params->get('series_imagefolder')) { $i_path = 'images/'.$admin_params->get('series_imagefolder').'/'.$row->series_thumbnail;}
			$image = getImage($i_path);
			$element = '<img src="'.$image->path.'" height="'.$image->height.'" width="'.$image->width.'" alt="'.$row->series_text.'">';
			if ($islink > 0 && $view == 0) {$element = getSerieslink($islink, $row, $element, $params, $admin_params);}
			$element = '<td class="'.$r.' thumbnail image">'.$element.'</td>';
			break;
		case 3: //dump ($admin_params->get('series_imagefolder'), 'imagefolder: ');
			if ($row->series_thumbnail && !$admin_params->get('series_imagefolder')) { $i_path = 'components/com_biblestudy/images/'.$row->series_thumbnail; }
			if ($row->series_thumbnail && $admin_params->get('series_imagefolder')) { $i_path = 'images/'.$admin_params->get('series_imagefolder').'/'.$row->series_thumbnail;}
			$image = getImage($i_path); //dump ($image, 'image: ');
			$element1 = '<td class="'.$r.' thumbnail"> <table id="seriestable" cellspacing="0"><tr class="noborder"><td>';
			$element2 = '<img src="'.$image->path.'" height="'.$image->height.'" width="'.$image->width.'" alt="'.$row->series_text.'">';
			$element3 = '</td></tr>';
			$element4 = $row->series_text;
			if ($islink > 0 && $view == 0) {$element4 = getSerieslink($islink, $row, $element4, $params, $admin_params);}
			$element = $element1.$element2.$element3.'</td></tr>';
			$element .= '<tr class="noborder"><td class="'.$r.' title">'.$element4.'</td>';
			$element .= '</tr></table></td>';
			break;
		case 4:
			$element = $row->teachertitle.' - '.$row->teachername;
			if ($islink > 0) {$element = getSerieslink($islink, $row, $element, $params, $admin_params);}
			$element = '<td class="'.$r.' teacher">'.$element.'</td>';
			break;
		case 5:
			if ($row->teacher_thumbnail == '- Select Image -' || !$row->teacher_thumbnail) { $image->path = $row->thumb; $image->height = $row->thumbh; $image->width = $row->thumbw;}
			if ($row->teacher_thumbnail && !$admin_params->get('teachers_imagefolder')) { $i_path = 'components/com_biblestudy/images/stories/'.$row->teacher_thumbnail; }
			if ($row->teacher_thumbnail && $admin_params->get('teachers_imagefolder')) { $i_path = 'images/'.$admin_params->get('teachers_imagefolder').'/'.$row->teacher_thumbnail;}
			$image = getImage($i_path);
			$element = '<img src="'.$image->path.'" height="'.$image->height.'" width="'.$image->width.'" alt="'.$row->teachername.'">';
			if ($islink > 0) {$element = getSerieslink($islink, $row, $element, $params, $admin_params);}
			$element = '<td class="'.$r.' teacher image">'.$element.'</td>';
			break;
		case 6:
			$element1 = '<table id="seriestable" cellspacing="0"><tr class="noborder"><td class="'.$r.' teacher">';
			if ($row->teacher_thumbnail == '- Select Image -' || !$row->teacher_thumbnail) { $image->path = $row->thumb; $image->height = $row->thumbh; $image->width = $row->thumbw;}
			else 
			{
				if ($row->teacher_thumbnail && !$admin_params->get('teachers_imagefolder')) { $i_path = 'components/com_biblestudy/images/stories/'.$row->teacher_thumbnail; }
				if ($row->teacher_thumbnail && $admin_params->get('teachers_imagefolder')) { $i_path = 'images/'.$admin_params->get('teachers_imagefolder').'/'.$row->teacher_thumbnail;}
				$image = getImage($i_path);
			}
			$element2 = '<img src="'.$image->path.'" height="'.$image->height.'" width="'.$image->width.'" alt="'.$row->teachername.'">';
			$element3 = '</td></tr><tr class="noborder"><td class="'.$r.' teacher">';
			$element4 = $row->teachertitle.' - '.$row->teachername;
			if ($islink > 0) {$element4 = getSerieslink($islink, $row, $element4, $params, $admin_params);}
			$element = $element1.$element2.$element3.$element4.'</td></tr></table>';
			$element = '<td class="'.$r.' teacher image">'.$element.'</td>';
			break;
		case 7:
			$element = $row->description;
			if ($islink > 0) {$element = getSerieslink($islink, $row, $element, $params, $admin_params);}
			$element = '<td class="'.$r.' description"><p>'.$element.'</p></td>';
			break;
	}//dump ($element, 'element: ');
	return $element;
}	
	function seriesGetcustom($r, $row, $customelement, $custom, $islink, $admin_params, $params)
	{
	$countbraces = substr_count($custom, '{');
	$braceend = 0;
	while ($countbraces > 0)
	{
		$bracebegin = strpos($custom,'{');
		$braceend = strpos($custom, '}');
		$subcustom = substr($custom, ($bracebegin + 1), (($braceend - $bracebegin) - 1));
		$customelement = getseriesElementnumber($subcustom);
		$element = seriesGetelement($r, $row, $customelement, $custom, $islink, $admin_params, $params);
		$custom = substr_replace($custom,$element,$bracebegin,(($braceend - $bracebegin) + 1));
		$countbraces = $countbraces - 1;
	}
	
	return $custom;
	}

function getseriesElementnumber($subcustom)
{
	switch ($subcustom)
	{
		case 'title':
		$customelement = 1;
		break;
		
		case 'thumbnail':
		$customelement = 2;
		break;
		
		case 'thumbnail-title':
		$customelement = 3;
		break;
		
		case 'teacher':
		$customelement = 4;
		break;
		
		case 'teacherimage':
		$customelement = 5;
		break;
		
		case 'teacher-title':
		$customelement = 6;
		break;
		
		case 'descriiption':
		$customelement = 7;
		break;
	}
	return $customelement;
}
function getSeriesstudies($id, $params, $admin_params, $template)
{
	$studies = '';
	$limit = '';
	$nolimit = JRequest::getVar('nolimit', 'int', 0);
	if ($params->get('series_detail_limit')) {$limit = ' LIMIT '.$params->get('series_detail_limit');}
	if ($nolimit == 1) {$limit = '';}
	$db	= & JFactory::getDBO();
	$query = 'SELECT s.series_id FROM #__bsms_studies AS s WHERE s.published = 1 AND s.series_id = '.$id;
	$db->setQuery($query);
	$allrows = $db->loadObjectList();
	$rows = $db->getAffectedRows();
	
	$query = 'SELECT s.*, se.id AS seid, t.id AS tid, t.teachername, t.title AS teachertitle, t.thumb, t.thumbh, t.thumbw, '
	. ' t.teacher_thumbnail, se.series_text, se.description AS sdescription, '
	. ' se.series_thumbnail, #__bsms_message_type.id AS mid,'
	. ' #__bsms_message_type.message_type AS message_type, #__bsms_books.bookname,'
	. ' #__bsms_topics.id AS tp_id, #__bsms_topics.topic_text, #__bsms_locations.id AS lid, #__bsms_locations.location_text '
	. ' FROM #__bsms_studies AS s'
	. ' LEFT JOIN #__bsms_series AS se ON (s.series_id = se.id)'
	. ' LEFT JOIN #__bsms_teachers AS t ON (s.teacher_id = t.id)'
	. ' LEFT JOIN #__bsms_books ON (s.booknumber = #__bsms_books.booknumber)'
	. ' LEFT JOIN #__bsms_message_type ON (s.messagetype = #__bsms_message_type.id)'
	. '	LEFT JOIN #__bsms_topics ON (s.topics_id = #__bsms_topics.id)'
	. ' LEFT JOIN #__bsms_locations ON (s.location_id = #__bsms_locations.id)'
	.' WHERE s.series_id = '.$id.' AND s.published = 1 ORDER BY '.$params->get('series_detail_sort', 'studydate').' '.$params->get('series_detail_order', 'DESC').$limit;
	$db->setQuery($query);
	$result = $db->loadObjectList();
	$numrows = $db->getAffectedRows();
	//dump ($rows, 'rows: ');
	$class1 = 'bsodd';
 	$class2 = 'bseven';
 	$oddeven = $class1;
	foreach ($result AS $row)
	{
		if($oddeven == $class1){ //Alternate the color background
			$oddeven = $class2;
		} else {
			$oddeven = $class1;
		}
		$studies .= '<tr class="'.$oddeven;
		if ($numrows > 1) {$studies .=' studyrow';} else {$studies .= ' lastrow';}
		$studies .= '">
		';
		$element = getElementid($params->get('series_detail_1'), $row, $params, $admin_params, $template);
		if ($params->get('series_detail_islink1') > 0) {$element->element = getStudieslink($params->get('series_detail_islink1'), $row, $element->element, $params, $admin_params);}
		$studies .= '<td class="'.$element->id.'">'.$element->element.'</td>
		';
		$element = getElementid($params->get('series_detail_2'), $row, $params, $admin_params, $template);
		if ($params->get('series_detail_islink2') > 0) {$element->element = getStudieslink($params->get('series_detail_islink1'), $row, $element->element, $params, $admin_params);}
		$studies .= '<td class="'.$element->id.'">'.$element->element.'</td>
		';
		$element = getElementid($params->get('series_detail_3'), $row, $params, $admin_params, $template);
		if ($params->get('series_detail_islink3') > 0) {$element->element = getStudieslink($params->get('series_detail_islink1'), $row, $element->element, $params, $admin_params);}
		$studies .= '<td class="'.$element->id.'">'.$element->element.'</td>
		';
		$element = getElementid($params->get('series_detail_4'), $row, $params, $admin_params, $template);
		if ($params->get('series_detail_islink4') > 0) {$element->element = getStudieslink($params->get('series_detail_islink1'), $row, $element->element, $params, $admin_params);}
		$studies .= '<td class="'.$element->id.'">'.$element->element.'</td>
		';
		$numrows = $numrows - 1;
		
	}
	//dump ($result, 'result: ');
	//$studies = '<tr class="lastrow bsodd">';
	//$studies .= '<td class="studies">';
	$templatemenuid = $params->get('serieslisttemplateid');
					if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}
	$studies .= '</tr>
	<tr><td>';
		if ($params->get('series_detail_show_link') > 0 && $nolimit != 1 && $rows > $params->get('series_detail_limit')) 
			{
				$studies .= '<a href="'.JRoute::_('index.php?option=com_biblestudy&view=seriesdetail&id='.$id.'&nolimit=1&templatemenuid='.$templatemenuid).'">'.JText::_('Show All').' '.$rows.' '.JText::_('Studies From This Series').' >></a>';
			}
		$studies .= '</td></tr>
		';
	if ($params->get('series_list_return') > 0) 
		{
			
			$studies .= '<tr class="seriesreturnlink"><td><a href="'.JRoute::_('index.php?option=com_biblestudy&view=serieslist&templatemenuid='.$templatemenuid).'">'.' << '.JText::_('Return To Series List').'</a></td></tr>';
		}
return $studies;
}

function getSeriesLandingPage($params, $id, $admin_params)
{
	
	global $mainframe, $option;
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'image.php');
	include_once($path1.'helper.php');
//	$addItemid = '';
//	$addItemid = getItemidLink($isplugin=0, $admin_params); //dump ($addItemid, 'AddItemid: ');
	$series = null;
	$seriesid = null;
	$templatemenuid = $params->get('templatemenuid');
	//$templatemenuid = $params->get('teachertemplateid');
	if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}
	$limit = $params->get('landingserieslimit');
	if (!$limit) {$limit = 10000;}
	
		$series = '<table id="landing_table" width="100%"><tr>';
		$db	=& JFactory::getDBO();
		$query = 'select distinct a.* from #__bsms_series a inner join #__bsms_studies b on a.id = b.series_id';
		
		$db->setQuery($query);
		
        $tresult = $db->loadObjectList();
        $t = 0;
        $series .= '
		<tr>'; 
		$showdiv = 0;       
        foreach ($tresult as &$b) {
            
            $series .= '<td id="landing_td">';
            if ($t >= $limit)
		{
			if ($showdiv < 1)
			{
			
			$series .= "</td></tr></table>";
			$series .= '<div id="showhideseries" style="display:none;">';
			$series .= '<table width = "100%" id="landing_table"><tr><td>';
		
			$showdiv = 1;
			}
		}   
            if ($params->get('series_linkto') == '0') {
                $series .= '<a href="index.php?option=com_biblestudy&view=studieslist&filter_series='.$b->id.'&filter_book=0&filter_teacher=0&filter_topic=0&filter_location=0&filter_year=0&filter_messagetype=0&templatemenuid='.$templatemenuid.'">';
            } else {
                $series .= '<a href="index.php?option=com_biblestudy&view=seriesdetail&id='.$b->id.'&templatemenuid='.$templatemenuid.$addItemid.'">';    
            }
		    
		    $series .= $numRows;
		    $series .= $b->series_text;
    		
            $series .='</a>';
            
            $series .= '</td>';
            
            $i++;
            $t++;
            if ($i == 3) {
                $series .= '</tr><tr>';
                $i = 0;
            }
        }
        if ($i == 1) {
            $series .= '<td id="landing_td"></td><td id="landing_td"></td>';
        };
        if ($i == 2) {
            $series .= '<td id="landing_td"></td>';
        };
         if ($showdiv == 1)
			{	
        	$series .= '</td></tr></table>';
			$series .= '</div>';
			$showdiv = 2;
			}
        $series .= '</tr>';
		$series .= '</table>';
        
	return $series;
}

function getSerieslistExp($row, $params, $admin_params)
{ //dump ($row->series_thumbnail, 'series: ');
	
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'elements.php');
	include_once($path1.'custom.php');
	include_once($path1.'image.php');
	include_once($path1.'helper.php');
	$templatemenuid = $params->get('serieslisttemplateid');
	
	//dump ($templatemenuid, "Template");
	//dump ($row, "Row - SeriesList.php");

	$label = $params->get('series_templatecode');
    $label = str_replace('{{teacher}}', $row->teachername, $label);
    $label = str_replace('{{teachertitle}}', $row->teachertitle, $label);
	$label = str_replace('{{title}}', $row->series_text, $label);
	$label = str_replace('{{description}}', $row->description, $label);
	$label = str_replace('{{thumbnail}}', '<img src="'. $row->thumb .'" width="' .$row->thumbw .'" height="' . $row->thumbh . '" />', $label);
	$label = str_replace('{{thumbh}}', $row->thumbh, $label);
	$label = str_replace('{{thumbw}}', $row->thumbw, $label);
	$label = str_replace('{{url}}', 'index.php?option=com_biblestudy&view=seriesdetail&templatemenuid='.$templatemenuid.'&id='.$row->id, $label);
	return $label;
}

function getSeriesDetailsExp($row, $params, $admin_params, $template)
    {
        //seriesdesc_template
        $path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	    include_once($path1.'elements.php');
	    include_once($path1.'scripture.php');
	    include_once($path1.'custom.php');
	    include_once($path1.'passage.php');
	    //include_once($path1.'mediatable.php');
	    //This will eventually replace mediatable in this context.  Just for clarity.
	    include_once($path1.'media.php');
        include_once($path1.'share.php');
        include_once($path1.'comments.php');
        include_once($path1.'date.php');
            
        $label = $params->get('series_detailcode');
        $label = str_replace('{{teacher}}', $row->teachername, $label);
        $label = str_replace('{{teachertitle}}', $row->teachertitle, $label);
        $label = str_replace('{{description}}', $row->description, $label);
	    $label = str_replace('{{title}}', $row->series_text, $label);
	    
	    return $label;
	
    }

/*

*/
function getSeriesstudiesExp($id, $params, $admin_params, $template)
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
		. ' where #__bsms_series.id = ' .$id;
	*/
	// 6.2
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
        . ' where #__bsms_series.id = ' .$id
        . ' GROUP BY #__bsms_studies.id'
        . ' order by studydate desc'
        ;	
	$db->setQuery($query);
	$result = $db->loadObjectList();
	$numrows = $db->getAffectedRows();
	
	$studies = '';
	
	switch ($params->get('series_wrapcode')) {
      case '0':
        //Do Nothing
        break;
      case 'T':
        //Table
        $studies .= '<table id="bsms_seriestable" width="100%">'; 
        break;
      case 'D':
        //DIV
        $studies .= '<div>';
        break;
      }
  echo $params->get('series_headercode');
  
	
	foreach ($result AS $row)
	{
	    $oddeven = 0;
		$studies .= getListingExp($row, $params, $oddeven, $params, $params->get('seriesdetailtemplateid'));	
	}
	
switch ($params->get('series_wrapcode')) {
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
  echo $params->get('series_headercode');
  
	
	if ($params->get('series_list_return') > 0) 
		{		
			$studies .= '<tr class="seriesreturnlink"><td><a href="'.JRoute::_('index.php?option=com_biblestudy&view=serieslist&templatemenuid='.$templatemenuid).'">'.' << '.JText::_('Return To Series List').'</a></td></tr>';
		}
return $studies;
}

