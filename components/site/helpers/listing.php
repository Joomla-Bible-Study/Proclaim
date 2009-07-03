<?php defined('_JEXEC') or die('Restriced Access');
//Helper file - master list creater for study lists
function getListing($row, &$params, $oddeven, &$admin_params)
{//dump ($admin_params, 'admin-listing: ');
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'elements.php');
	include_once($path1.'custom.php');
	
	
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
	//$user =& JFactory::getUser();
	//$entry_user = $user->get('gid');
	//if (!$entry_user) { $entry_user = 0;}
	$entry_access = $params->get('entry_access');
	//if (!$entry_access) {$entry_access = 23;}
	$allow_entry = $params->get('allow_entry_study');
	//This is the beginning of row 1
	$lastrow = 0;
 	if ($rows == 1) {$lastrow = 1;}
	
	$listing .= '<tr class="'.$oddeven; //This begins the row of the display data
	if ($lastrow == 1) {$listing .= ' lastrow';}
	$listing .= '">
	'; 
	
		$rowcolid = 'row1col1';
		if ($params->get('row1col1') == 24) {$elementid = getCustom($params->get('row1col1'), $params->get('r1c1custom'), $row, $params);}
		else {$elementid = getElementid($params->get('row1col1'), $row, $params, &$admin_params);}
		//dump ($params->get('row1col1'), 'elementid: ');
 		$colspan = $params->get('r1c1span');
 		$rowspan = $params->get('rowspanr1c1');;
		 $lastcol = 0;
		 if ($columns == 1 || $colspan > 3) {$lastcol = 1;}
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr1c1'),$id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry);
 	
	if ($columns > 1 && $params->get('r1c1span') < 2 )
	{
 		$rowcolid = 'row1col2';
		if ($params->get('row1col2') == 24) {$elementid = getCustom($params->get('row1col2'), $params->get('r1c2custom'), $row, $params);}
		else {$elementid = getElementid($params->get('row1col2'), $row, $params, &$admin_params);}
 		$colspan = $params->get('r1c2span');
 		$rowspan = $params->get('rowspanr1c2');
 		$lastcol = 0;
 		if ($columns == 2 || $colspan > 2) {$lastcol = 1;}
 		$listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr1c2'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry);
	}
	
	if ($columns > 2  && ( $params->get('r1c1span') < 3 && $params->get('r1c2span') < 2)) 
	{
		 $rowcolid = 'row1col3';
		 if ($params->get('row1col3') == 24) {$elementid = getCustom($params->get('row1col3'), $params->get('r1c3custom'), $row, $params);}
		else {$elementid = getElementid($params->get('row1col3'), $row, $params, &$admin_params);}
		 $colspan = $params->get('r1c3span');
		 $rowspan = $params->get('rowspanr1c3');
		 $lastcol = 0;
		 if ($columns == 3 || $colspan > 1) {$lastcol = 1;}
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr1c3'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry);
	}
	
	if ($columns > 3 && ( $params->get('r1c1span') < 4 && $params->get('r1c2span') < 3 && $params->get('r1c3span') < 2))
	{
		 $rowcolid = 'row1col4';
		 if ($params->get('row1col4') == 24) {$elementid = getCustom($params->get('row1col4'), $params->get('r1c4custom'), $row, $params);}
		else {$elementid = getElementid($params->get('row1col4'), $row, $params, &$admin_params);}
		 $colspan = $params->get('r1c4span');
		 $rowspan = $params->get('rowspanr1c4');
		 $lastcol = 0;
		 if ($columns == 4) {$lastcol = 1;}
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr1c4'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry);
	}
	$listing .= '
	</tr>
	'; //This ends the row of the data to be displayed				 
	//This is the end of row 1
	
	//This is the beginning of row 2
	
	$lastrow = 0;
 	if ($rows == 2) {$lastrow = 1;}
	$listing .= '<tr class="'.$oddeven; //This begins the row of the display data
	if ($lastrow == 1) {$listing .= ' lastrow';}
	
	$listing .= '">
	'; 
	
		 $rowcolid = 'row2col1';
		 if ($params->get('row2col1') == 24) {$elementid = getCustom($params->get('row2col1'), $params->get('r2c1custom'), $row, $params);}
		else {$elementid = getElementid($params->get('row2col1'), $row, $params, &$admin_params);}
 		$colspan = $params->get('r2c1span');
 		$rowspan = $params->get('rowspanr2c1');;
		 $lastcol = 0;
		 if ($columns == 1 || $colspan > 3) {$lastcol = 1;}
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr2c1'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry);
 	
	if ($columns > 1  && $params->get('r2c1span') < 2)
	{
 		$rowcolid = 'row2col2';
		if ($params->get('row2col2') == 24) {$elementid = getCustom($params->get('row2col2'), $params->get('r2c2custom'), $row, $params);}
		else {$elementid = getElementid($params->get('row2col2'), $row, $params, &$admin_params);}
 		$colspan = $params->get('r2c2span');
 		$rowspan = $params->get('rowspanr2c2');
 		$lastcol = 0;
 		if ($columns == 2 || $colspan > 2) {$lastcol = 1;}
 		$listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr2c2'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry);
	}
	
	if ($columns > 2   && ( $params->get('r2c1span') < 3 && $params->get('r2c2span') < 2)) 
	{
		 $rowcolid = 'row2col3';
		 if ($params->get('row2col3') == 24) {$elementid = getCustom($params->get('row2col3'), $params->get('r2c3custom'), $row, $params);}
		 else {$elementid = getElementid($params->get('row2col3'), $row, $params, &$admin_params);}
		 if (!$elementid->id){$element->id = ''; $element->element = '';}
		 $colspan = $params->get('r2c3span');
		 $rowspan = $params->get('rowspanr2c3');
		 $lastcol = 0;
		 if ($columns == 3 || $colspan > 1) {$lastcol = 1;}
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr2c3'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry);
	}
	
	if ($columns > 3  && (  $params->get('r2c1span') < 4 && $params->get('r2c2span') < 3 && $params->get('r2c3span') < 2))
	{
		 $rowcolid = 'row2col4';
		 if ($params->get('row2col4') == 24) {$elementid = getCustom($params->get('row2col4'), $params->get('r2c4custom'), $row, $params);}
		 else {$elementid = getElementid($params->get('row2col4'), $row, $params, &$admin_params);}
		 $colspan = $params->get('r2c4span');
		 $rowspan = $params->get('rowspanr2c4');
		 $lastcol = 0;
		 if ($columns == 4) {$lastcol = 1;}
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr2c4'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry);
	}
	$listing .= '
	</tr>
	'; //This ends the row of the data to be displayed		
//End of row 2

//Beginning of row 3

	$lastrow = 0;
 	if ($rows == 3) {$lastrow = 1;}
	$listing .= '<tr class="'.$oddeven; //This begins the row of the display data
	if ($lastrow == 1) {$listing .= ' lastrow';}
	
	$listing .= '">'; 
	
		 $rowcolid = 'row3col1';
		 if ($params->get('row3col1') == 24) {$elementid = getCustom($params->get('row3col1'), $params->get('r3c1custom'), $row, $params);}
		else {$elementid = getElementid($params->get('row3col1'), $row, $params, &$admin_params);}
 		$colspan = $params->get('r3c1span');
 		$rowspan = $params->get('rowspanr3c1');;
		 $lastcol = 0;
		 if ($columns == 1 || $colspan > 3) {$lastcol = 1;}
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr3c1'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry);
 	
	if ($columns > 1 && $params->get('r3c1span') < 2)
	{
 		$rowcolid = 'row3col2';
		if ($params->get('row3col2') == 24) {$elementid = getCustom($params->get('row3col2'), $params->get('r3c2custom'), $row, $params);}
		else {$elementid = getElementid($params->get('row3col2'), $row, $params, &$admin_params);}
 		$colspan = $params->get('r3c2span');
 		$rowspan = $params->get('rowspanr3c2');
 		$lastcol = 0;
 		if ($columns == 2 || $colspan > 2) {$lastcol = 1;}
 		$listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr3c2'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry);
	}
	
	if ($columns > 2   && ( $params->get('r3c1span') < 3 && $params->get('r3c2span') < 2) )
	{
		 $rowcolid = 'row3col3';
		 if ($params->get('row3col3') == 24) {$elementid = getCustom($params->get('row3col3'), $params->get('r3c3custom'), $row, $params);}
		else {$elementid = getElementid($params->get('row3col3'), $row, $params, &$admin_params);}
		 $colspan = $params->get('r3c3span');
		 $rowspan = $params->get('rowspanr3c3');
		 $lastcol = 0;
		 if ($columns == 3 || $colspan > 1) {$lastcol = 1;}
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr3c3'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry);
	}
	
	if ($columns > 3 && (  $params->get('r3c1span') < 4 && $params->get('r3c2span') < 3 && $params->get('r3c3span') < 2))
	{
		 $rowcolid = 'row3col4';
		 if ($params->get('row3col4') == 24) {$elementid = getCustom($params->get('row3col4'), $params->get('r3c4custom'), $row, $params);}
		else {$elementid = getElementid($params->get('row3col4'), $row, $params, &$admin_params);}
		 $colspan = $params->get('r3c4span');
		 $rowspan = $params->get('rowspanr3c4');
		 $lastcol = 0;
		 if ($columns == 4) {$lastcol = 1;}
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr3c4'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry);
	}
	$listing .= '
	</tr>
	'; //This ends the row of the data to be displayed		
	//end of row 3
	
	//beginning of row 4
//	$row4colspan = $params->get('r4c1span') + $params->get('r4c2span') + $params->get('r4c3span') + $params->get('r4c4span');
	$lastrow = 0;
 	if ($rows == 4) {$lastrow = 1;}
	$listing .= '
	<tr class="'.$oddeven; //This begins the row of the display data
	if ($lastrow == 1) {$listing .= ' lastrow';}
	
	$listing .= '">
	'; 
	
		 $rowcolid = 'row4col1';
		 if ($params->get('row4col1') == 24) {$elementid = getCustom($params->get('row4col1'), $params->get('r4c1custom'), $row, $params);}
		else {$elementid = getElementid($params->get('row4col1'), $row, $params, &$admin_params);}
 		$colspan = $params->get('r4c1span');
 		$rowspan = $params->get('rowspanr4c1');;
		 $lastcol = 0;
		 if ($columns == 1 || $colspan > 3) {$lastcol = 1;}
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr4c1'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry);
 	
	if ($columns > 1  && $params->get('r4c1span') < 2)
	{
 		$rowcolid = 'row4col2';
		if ($params->get('row4col2') == 24) {$elementid = getCustom($params->get('row4col2'), $params->get('r4c2custom'), $row, $params);}
		else {$elementid = getElementid($params->get('row4col2'), $row, $params, &$admin_params);}
 		$colspan = $params->get('r4c2span');
 		$rowspan = $params->get('rowspanr4c2');
 		$lastcol = 0;
 		if ($columns == 2 || $colspan > 2) {$lastcol = 1;}
 		$listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr4c2'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry);
	}
	
	if ($columns > 2   && ( $params->get('r4c1span') < 3 && $params->get('r4c2span') < 2) )
	{
		 $rowcolid = 'row4col3';
		 if ($params->get('row4col3') == 24) {$elementid = getCustom($params->get('row4col3'), $params->get('r4c3custom'), $row, $params);}
		else {$elementid = getElementid($params->get('row4col3'), $row, $params, &$admin_params);}
		 $colspan = $params->get('r4c3span');
		 $rowspan = $params->get('rowspanr4c3');
		 $lastcol = 0;
		 if ($columns == 3 || $colspan > 1) {$lastcol = 1;}
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr4c3'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry);
	}
	
	if ($columns > 3 && ( $params->get('r4c1span') < 4 && $params->get('r4c2span') < 3 && $params->get('r4c3span') < 2))
	{
		 $rowcolid = 'row4col4';
		 if ($params->get('row4col4') == 24) {$elementid = getCustom($params->get('row4col4'), $params->get('r4c4custom'), $row, $params);}
		else {$elementid = getElementid($params->get('row4col4'), $row, $params, &$admin_params);}
		 $colspan = $params->get('r4c4span');
		 $rowspan = $params->get('rowspanr4c4');
		 $lastcol = 0;
		 if ($columns == 4) {$lastcol = 1;}
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr4c4'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry);
	}
	$listing .= '
	</tr>
	'; //This ends the row of the data to be displayed		
	
return $listing;
}

	function getCell($elementid, $element, $rowcolid, $colspan, $rowspan, $lastcol, $islink, $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry)
		{
			
if (($allow_entry > 0) && ($rowcolid == 'row1col1')){
$user =& JFactory::getUser();
$entry_user = $user->get('gid');
if (!$entry_user) { $entry_user = 0;}
if (!$entry_access) {$entry_access = 23;}
$item = JRequest::getVar('Itemid');
}
		
			$cell .= '
						<td class="'.$rowcolid.' '.$elementid;
						if ($lastcol == 1) {$cell .= ' lastcol';}
						$cell .= '" ';
						if ($colspan > 1) {$cell .= 'colspan="'.$colspan.'" ';}
						if ($rowspan > 1){$cell .='rowspan="'.$rowspan.'"';}
						$cell .= '>';
						if (($rowcolid == 'row1col1') && ($entry_user >= $entry_access) && ($allow_entry > 0)){
							$cell .= '<a href="'.JURI::base().'index.php?option=com_biblestudy&controller=studiesedit&view=studiesedit&task=edit&layout=form&cid[]='.$id3.'&item='.$item.'">'.JText::_(' [Edit] ').'</a>';}
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