<?php defined('_JEXEC') or die('Restriced Access');
//Helper file - master list creater for study lists
function getListing($row, $params, $oddeven)
{
	$path1 = JPATH_BASE.DS.'components'.DS.'com_biblestudy/helpers/';
	include_once($path1.'scripture.php');
	include_once($path1.'duration.php');
	include_once($path1.'date.php');
	include_once($path1.'filesize.php');
	include_once($path1.'textlink.php');
	include_once($path1.'mediatable.php');
	include_once($path1.'store.php');
	include_once($path1.'filepath.php');
	global $mainframe;
	$db	= & JFactory::getDBO();
	//Need to know if last column and last row
	$columns = 1;
	if ($params->get('row1col2') > 0) {$columns = 2;}
	if ($params->get('row1col3') > 0) {$columns = 3;}
	if ($params->get('row1col4') > 0) {$columns = 4;}
	$rows = 1;
	if ($params->get('row2col1') > 0) {$rows = 2;}
	if ($params->get('row3col1') > 0) {$rows = 3;}
	if ($params->get('row4col1') > 0) {$rows = 4;}
	$islink = $params->get('islink');
	$id3 = $row->id;
	$smenu = $params->get('detailsitemid');
	$tmenu = $params->get('teacheritemid');
	$tid = $row->tid;
	//This is the beginning of row 1
	$lastrow = 0;
 	if ($rows == 1) {$lastrow = 1;}
	
	$listing .= '<tr class="'.$oddeven; //This begins the row of the display data
	if ($lastrow == 1) {$listing .= ' lastrow';}
	$listing .= '">'; 
	
		 $rowcolid = 'row1col1';
 		$elementid = getElementid($params->get('row1col1'), $row, $params);
		//dump ($params->get('row1col1'), 'elementid: ');
 		$colspan = $params->get('r1c1span');
 		$rowspan = $params->get('rowspanr1c1');;
		 $lastcol = 0;
		 if ($columns == 1 || $colspan > 3) {$lastcol = 1;}
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr1c1'),$id3, $tid, $smenu, $tmenu, $params->get('r1c1custom'));
 	
	if ($columns > 1 && $params->get('row1col2') > 0)
	{
 		$rowcolid = 'row1col2';
 		$elementid = getElementid($params->get('row1col2'), $row, $params);
 		$colspan = $params->get('r1c2span');
 		$rowspan = $params->get('rowspanr1c2');
 		$lastcol = 0;
 		if ($columns == 2 || $colspan > 2) {$lastcol = 1;}
 		$listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr1c2'), $id3, $tid, $smenu, $tmenu, $params->get('r1c2custom'));
	}
	
	if ($columns > 2 && $params->get('row1col3') > 0) 
	{
		 $rowcolid = 'row1col3';
		 $elementid = getElementid($params->get('row1col3'), $row, $params);
		 $colspan = $params->get('r1c3span');
		 $rowspan = $params->get('rowspanr1c3');
		 $lastcol = 0;
		 if ($columns == 3 || $colspan > 1) {$lastcol = 1;}
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr1c3'), $id3, $tid, $smenu, $tmenu, $params->get('r1c3custom'));
	}
	
	if ($columns > 3 && $params->get('row1col4') > 0)
	{
		 $rowcolid = 'row1col4';
		 $elementid = getElementid($params->get('row1col4'), $row, $params);
		 $colspan = $params->get('r1c4span');
		 $rowspan = $params->get('rowspanr1c4');
		 $lastcol = 0;
		 if ($columns == 4) {$lastcol = 1;}
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr1c4'), $id3, $tid, $smenu, $tmenu, $params->get('r1c4custom'));
	}
	$listing .= '</tr>'; //This ends the row of the data to be displayed				 
	//This is the end of row 1
	
	//This is the beginning of row 2
	
	$lastrow = 0;
 	if ($rows == 2) {$lastrow = 1;}
	$listing .= '<tr class="'.$oddeven; //This begins the row of the display data
	if ($lastrow == 1) {$listing .= ' lastrow';}
	
	$listing .= '">'; 
	
		 $rowcolid = 'row2col1';
 		$elementid = getElementid($params->get('row2col1'), $row, $params);
 		$colspan = $params->get('r2c1span');
 		$rowspan = $params->get('rowspanr2c1');;
		 $lastcol = 0;
		 if ($columns == 1 || $colspan > 3) {$lastcol = 1;}
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr2c2'), $id3, $tid, $smenu, $tmenu, $params->get('r2c1custom'));
 	
	if ($columns > 1 && $params->get('row2col2') > 0)
	{
 		$rowcolid = 'row2col2';
 		$elementid = getElementid($params->get('row2col2'), $row, $params);
 		$colspan = $params->get('r2c2span');
 		$rowspan = $params->get('rowspanr2c2');
 		$lastcol = 0;
 		if ($columns == 2 || $colspan > 2) {$lastcol = 1;}
 		$listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr2c2'), $id3, $tid, $smenu, $tmenu, $params->get('r2c2custom'));
	}
	
	if ($columns > 2 && $params->get('row2col3') > 0) 
	{
		 $rowcolid = 'row2col3';
		 $elementid = getElementid($params->get('row2col3'));
		 $colspan = $params->get('r2c3span');
		 $rowspan = $params->get('rowspanr2c3');
		 $lastcol = 0;
		 if ($columns == 3 || $colspan > 1) {$lastcol = 1;}
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr2c3'), $id3, $tid, $smenu, $tmenu, $params->get('r2c3custom'));
	}
	
	if ($columns > 3 && $params->get('row2col4') > 0)
	{
		 $rowcolid = 'row2col4';
		 $elementid = getElementid($params->get('row2col4'), $row, $params);
		 $colspan = $params->get('r2c4span');
		 $rowspan = $params->get('rowspanr2c4');
		 $lastcol = 0;
		 if ($columns == 4) {$lastcol = 1;}
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr2c4'), $id3, $tid, $smenu, $tmenu, $params->get('r2c4custom'));
	}
	$listing .= '</tr>'; //This ends the row of the data to be displayed		
//End of row 2

//Beginning of row 3

	$lastrow = 0;
 	if ($rows == 3) {$lastrow = 1;}
	$listing .= '<tr class="'.$oddeven; //This begins the row of the display data
	if ($lastrow == 1) {$listing .= ' lastrow';}
	
	$listing .= '">'; 
	
		 $rowcolid = 'row3col1';
 		$elementid = getElementid($params->get('row3col1'), $row, $params);
 		$colspan = $params->get('r3c1span');
 		$rowspan = $params->get('rowspanr3c1');;
		 $lastcol = 0;
		 if ($columns == 1 || $colspan > 3) {$lastcol = 1;}
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr3c1'), $id3, $tid, $smenu, $tmenu, $params->get('r3c1custom'));
 	
	if ($columns > 1 && $params->get('row3col2') > 0)
	{
 		$rowcolid = 'row3col2';
 		$elementid = getElementid($params->get('row3col2'), $row, $params);
 		$colspan = $params->get('r3c2span');
 		$rowspan = $params->get('rowspanr3c2');
 		$lastcol = 0;
 		if ($columns == 2 || $colspan > 2) {$lastcol = 1;}
 		$listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr3c2'), $id3, $tid, $smenu, $tmenu, $params->get('r3c2custom'));
	}
	
	if ($columns > 2 && $params->get('row3col3') > 0) 
	{
		 $rowcolid = 'row3col3';
		 $elementid = getElementid($params->get('row3col3'), $row, $params);
		 $colspan = $params->get('r3c3span');
		 $rowspan = $params->get('rowspanr3c3');
		 $lastcol = 0;
		 if ($columns == 3 || $colspan > 1) {$lastcol = 1;}
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr3c3'), $id3, $tid, $smenu, $tmenu, $params->get('r3c3custom'));
	}
	
	if ($columns > 3 && $params->get('row3col4') > 0)
	{
		 $rowcolid = 'row3col4';
		 $elementid = getElementid($params->get('row3col4'), $row, $params);
		 $colspan = $params->get('r3c4span');
		 $rowspan = $params->get('rowspanr3c4');
		 $lastcol = 0;
		 if ($columns == 4) {$lastcol = 1;}
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr3c4'), $id3, $tid, $smenu, $tmenu, $params->get('r3c4custom'));
	}
	$listing .= '</tr>'; //This ends the row of the data to be displayed		
	//end of row 3
	
	//beginning of row 4
	
	$lastrow = 0;
 	if ($rows == 4) {$lastrow = 1;}
	$listing .= '
	<tr class="'.$oddeven; //This begins the row of the display data
	if ($lastrow == 1) {$listing .= ' lastrow';}
	
	$listing .= '">'; 
	
		 $rowcolid = 'row4col1';
 		$elementid = getElementid($params->get('row4col1'), $row, $params);
 		$colspan = $params->get('r4c1span');
 		$rowspan = $params->get('rowspanr4c1');;
		 $lastcol = 0;
		 if ($columns == 1 || $colspan > 3) {$lastcol = 1;}
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr4c1'), $id3, $tid, $smenu, $tmenu, $params->get('r4c1custom'));
 	
	if ($columns > 1 && $params->get('row4col2') > 0)
	{
 		$rowcolid = 'row4col2';
 		$elementid = getElementid($params->get('row4col2'), $row, $params);
 		$colspan = $params->get('r4c2span');
 		$rowspan = $params->get('rowspanr4c2');
 		$lastcol = 0;
 		if ($columns == 2 || $colspan > 2) {$lastcol = 1;}
 		$listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr4c2'), $id3, $tid, $smenu, $tmenu, $params->get('r4c2custom'));
	}
	
	if ($columns > 2 && $params->get('row4col3') > 0) 
	{
		 $rowcolid = 'row4col3';
		 $elementid = getElementid($params->get('row4col3'), $row, $params);
		 $colspan = $params->get('r4c3span');
		 $rowspan = $params->get('rowspanr4c3');
		 $lastcol = 0;
		 if ($columns == 3 || $colspan > 1) {$lastcol = 1;}
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr4c3'), $id3, $tid, $smenu, $tmenu, $params->get('r4c3custom'));
	}
	
	if ($columns > 3 && $params->get('row4col4') > 0)
	{
		 $rowcolid = 'row4col4';
		 $elementid = getElementid($params->get('row4col4'), $row, $params);
		 $colspan = $params->get('r4c4span');
		 $rowspan = $params->get('rowspanr4c4');
		 $lastcol = 0;
		 if ($columns == 4) {$lastcol = 1;}
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr4c4'), $id3, $tid, $smenu, $tmenu, $params->get('r4c4custom'));
	}
	$listing .= '</tr>
	'; //This ends the row of the data to be displayed		
	
return $listing;
}

//<td class="row1col2 bstitle" headers="bstitlehead">

function getElementid($rowid, $row, $params)
	{
		switch ($rowid)
		{
	 case 1:
		$elementid->id = 'scripture1';
		$esv = 0;
		$scripturerow = 1;
		$elementid->element = getScripture($params, $row, $esv, $scripturerow);
		break;
	case 2:
		$elementid->id = 'scripture2';
		$esv = 0;
		$scripturerow = 2;
		$elementid->element = getScripture($params, $row, $esv, $scripturerow);
		break;
	case 3:
		$elementid->id = 'secondary';
		$elementid->element = $row->secondary_reference;
		break;
	case 4:
		$elementid->id = 'duration';
		$elementid->element = getDuration($params, $row);
		break;
	case 5:
		$elementid->id = 'title';
		$elementid->element = $row->studytitle;
		break;
	case 6:
		$elementid->id = 'studyintro';
		$elementid->element = $row->studyintro;
		break;
	case 7:
		$elementid->id = 'teacher';
		$elementid->element = $row->teachername;
		break;
	case 8:
		$elementid->id = 'teacher';
		$elementid->element = $row->teachertitle.' '.$row->teachername;
		break;
	case 9:
		$elementid->id = 'series';
		$elementid->element = $row->series_text;
		break;
	case 10:
		$elementid->id = 'date';
		$elementid->element = getstudyDate($params, $row->studydate);
		break;
	case 11:
		$elementid->id = 'submitted';
		$elementid->element = $row->submitted;
		break;
	case 12:
		$elementid->id = 'hits';
		$elementid->element = JText::_('Hits: ').$row->hits;
		break;
	case 13:
		$elementid->id = 'studynumber';
		$elementid->element = $row->studynumber;
		break;
	case 14:
		$elementid->id = 'topic';
		$elementid->element = $row->topic_text;
		break;
	case 15:
		$elementid->id = 'location';
		$elementid->element = $row->location_text;
		break;
	case 16:
		$elementid->id = 'messagetype';
		$elementid->element = $row->message_type;
		break;
	case 17:
		$elementid->id = 'details';
		$textorpdf = 'text';
		$elementid->element = getTextlink($params, $row, $textorpdf);
		break;
	case 18:
		$elementid->id = 'details';
		$textorpdf = 'text';
		$elementid->element = '<table class="detailstable"><tbody><tr><td>';
		$elementid->element .= getTextlink($params, $row, $textorpdf).'</td><td>';
		$textorpdf = 'pdf';
		$elementid->element .= getTextlink($params, $row, $textorpdf).'</td></tr></table>';
		break;
	case 19:
		$elementid->id = 'details';
		$textorpdf = 'pdf';
		$elementid->element = getTextlink($params, $row, $textorpdf);
		break;
	case 20:
		$elementid->id = 'media';
		$elementid->element = getMediatable($params, $row);
		break;
	case 22:
		$elementid->id = 'store';
		$elementid->element = getStore($params, $row);
		break;
	case 23:
		$elementid->id = 'filesize';
		$query_media1 = 'SELECT #__bsms_mediafiles.id AS mid, #__bsms_mediafiles.size, #__bsms_mediafiles.published, #__mediafiles.mime_type, #__bsms_studies.id AS sid, #__bsms_studies.study_id'
		. ' FROM #__bsms_mediafiles'
		. ' WHERE #__bsms_mediafiles.study_id = '.$row->id.' AND #__bsms_mediafiles.published = 1, AND #__bsms_mediafiles.mime_type = 1';
		$db->setQuery( $query_media1 );
		$media1 = $db->loadObjectList('id');
		$elementid->element = getFilesize($media1->size);
		break;
	case 24:
		$elementid->id = 'custom';
		$element->element = getCustom($custom, $row, $params)
		break;
		}
	return $elementid;
	}

function getCell($elementid, $element, $rowcolid, $colspan, $rowspan, $lastcol, $islink, $id3, $tid, $smenu, $tmenu, $custom)
	{
		$cell .= '
					<td class="'.$rowcolid.' '.$elementid;
					if ($lastcol == 1) {$cell .= ' lastcol';}
					$cell .= '" ';
					if ($colspan > 1) {$cell .= 'colspan="'.$colspan.'" ';}
					if ($rowspan > 1){$cell .='rowspan="'.$rowspan.'"';}
					$cell .= '>';
					if ($islink > 0){$cell .= getLink($islink, $id3, $tid, $smenu, $tmenu);}
					$cell .= $element;
					if ($islink > 0){$cell .= '</a>';}
					$cell .='</td>';
		return $cell;
	}
	
	function getLink($islink, $id3, $tid, $smenu, $tmenu)
	{
		$mime = ' AND #__bsms_mediafiles.mime_type = 1';
		switch ($islink) {
        case 1 :
         $link = 'index.php?option=com_biblestudy&view=studydetails' . '&id=' . $id3;
		 if ($smenu > 0) {$link .= '&Itemid='.$smenu;}
         $column .= '<a href="'.$link.'">';
         break;
        case 2 :
         $filepath = getFilepath($id3, 'study_id',$mime);
		 $link = JRoute::_($filepath);
         $column .= '<a href="'.$link.'">';
         break;
        case 3 :
         $link = 'index.php?option=com_biblestudy&view=teacherdisplay' . '&id=' . $tid;
		 if ($tmenu > 0) {$link .= '&Itemid='.$tmenu;}
         $column .= '<a href="'.$link.'">';
         break;
       }
       return $column;
	}