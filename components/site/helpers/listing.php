<?php defined('_JEXEC') or die('Restriced Access');
//Helper file - master list creater for study lists
function getListing($row, $params, $oddeven, $admin_params, $template, $ismodule)
{
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'elements.php');
	include_once($path1.'custom.php');
	include_once($path1.'helper.php');
	//Here we test to see if this is a studydetails or list view. If details, we reset the params to the details. this keeps us from having to rewrite all this code.
	$view = JRequest::getVar('view', 'get');
	if ($view == 'studydetails' && $ismodule < 1)
		{
			
		$params->set('row1col1', $params->get('drow1col1'));
		$params->set('r1c1custom', $params->get('dr1c1custom'));
		$params->set('r1c1span', $params->get('dr1c1span'));
		$params->set('linkr1c1', $params->get('dlinkr1c1'));
		
		$params->set('row1col2', $params->get('drow1col2'));
		$params->set('r1c2custom', $params->get('dr1c2custom'));
		$params->set('r1c2span', $params->get('dr1c2span'));
		$params->set('linkr1c2', $params->get('dlinkr1c2'));
		
		$params->set('row1col3', $params->get('drow1col3'));
		$params->set('r1c3custom', $params->get('dr1c3custom'));
		$params->set('r1c3span', $params->get('dr1c3span'));
		$params->set('linkr1c3', $params->get('dlinkr1c3'));
		
		$params->set('row1col4', $params->get('drow1col4'));
		$params->set('r1c4custom', $params->get('dr1c4custom'));
		$params->set('linkr1c4', $params->get('dlinkr1c4'));
		
		
		$params->set('row2col1', $params->get('drow2col1'));
		$params->set('r2c1custom', $params->get('dr2c1custom'));
		$params->set('r2c1span', $params->get('dr2c1span'));
		$params->set('linkr2c1', $params->get('dlinkr2c1'));
		
		$params->set('row2col2', $params->get('drow2col2'));
		$params->set('r2c2custom', $params->get('dr2c2custom'));
		$params->set('r2c2span', $params->get('dr2c2span'));
		$params->set('linkr2c2', $params->get('dlinkr2c2'));
		
		$params->set('row2col3', $params->get('drow2col3'));
		$params->set('r2c3custom', $params->get('dr2c3custom'));
		$params->set('r2c3span', $params->get('dr2c3span'));
		$params->set('linkr2c3', $params->get('dlinkr2c3'));
		
		$params->set('row2col4', $params->get('drow2col4'));
		$params->set('r2c4custom', $params->get('dr2c4custom'));
		$params->set('linkr2c4', $params->get('dlinkr2c4'));
		
		
		$params->set('row3col1', $params->get('drow3col1'));
		$params->set('r3c1custom', $params->get('dr3c1custom'));
		$params->set('r3c1span', $params->get('dr3c1span'));
		$params->set('linkr3c1', $params->get('dlinkr3c1'));
		
		$params->set('row3col2', $params->get('drow3col2'));
		$params->set('r3c2custom', $params->get('dr3c2custom'));
		$params->set('r3c2span', $params->get('dr3c2span'));
		$params->set('linkr3c2', $params->get('dlinkr3c2'));
		
		$params->set('row3col3', $params->get('drow3col3'));
		$params->set('r3c3custom', $params->get('dr3c3custom'));
		$params->set('r3c3span', $params->get('dr3c3span'));
		$params->set('linkr3c3', $params->get('dlinkr3c3'));
		
		$params->set('row3col4', $params->get('drow3col4'));
		$params->set('r3c4custom', $params->get('dr3c4custom'));
		$params->set('linkr3c4', $params->get('dlinkr3c4'));
		
	
		$params->set('row4col1', $params->get('drow4col1'));
		$params->set('r4c1custom', $params->get('dr4c1custom'));
		$params->set('r4c1span', $params->get('dr4c1span'));
		$params->set('linkr4c1', $params->get('dlinkr4c1'));
		
		$params->set('row4col2', $params->get('drow4col2'));
		$params->set('r4c2custom', $params->get('dr4c2custom'));
		$params->set('r4c2span', $params->get('dr4c2span'));
		$params->set('linkr4c2', $params->get('dlinkr4c2'));
		
		$params->set('row4col3', $params->get('drow4col3'));
		$params->set('r4c3custom', $params->get('dr4c3custom'));
		$params->set('r4c3span', $params->get('dr4c3span'));
		$params->set('linkr4c3', $params->get('dlinkr4c3'));
		
		$params->set('row4col4', $params->get('drow4col4'));
		$params->set('r4c4custom', $params->get('dr4c4custom'));
		$params->set('linkr4c4', $params->get('dlinkr4c4'));
		
		}
	//Need to know if last column and last row
	$columns = 1;
	if ($params->get('row1col2') > 0 || $params->get('row2col2') > 0 || $params->get('row3col2') > 0 || $params->get('row4col2') > 0){$columns = 2;}
	if ($params->get('row1col3') > 0 || $params->get('row2col3') > 0 || $params->get('row3col3') > 0 || $params->get('row4col3') > 0) {$columns = 3;}
	if ($params->get('row1col4') > 0 || $params->get('row2col4') > 0 || $params->get('row3col4') > 0 || $params->get('row4col4') > 0) {$columns = 4;}
	$rows = 1;
	if ($params->get('row2col1') > 0 || $params->get('row2col2') > 0 || $params->get('row2col3') > 0 || $params->get('row2col4') > 0) {$rows = 2;}
	if ($params->get('row3col1') > 0 || $params->get('row3col2') > 0 || $params->get('row3col3') > 0 || $params->get('row3col4') > 0) {$rows = 3;}
	if ($params->get('row4col1') > 0 || $params->get('row4col2') > 0 || $params->get('row4col3') > 0 || $params->get('row4col4') > 0) {$rows = 4;}
	$islink = $params->get('islink');
	$id3 = $row->id;
	$smenu = $params->get('detailsitemid');
	$tmenu = $params->get('teacheritemid');
	$tid = $row->tid;
	$entry_access = $admin_params->get('entry_access');
	$allow_entry = $admin_params->get('allow_entry_study');
	//This is the beginning of row 1
	$lastrow = 0;
 	if ($rows == 1) {$lastrow = 1;}
	
	$listing = '<tr class="'.$oddeven; //This begins the row of the display data
	if ($lastrow == 1) {$listing .= ' lastrow';}
	$listing .= '">
	'; 
	
		$rowcolid = 'row1col1';
		if ($params->get('row1col1') < 1) {$params->set('row1col1', 100);}
		if ($params->get('row1col1') == 24) {$elementid = getCustom($params->get('row1col1'), $params->get('r1c1custom'), $row, $params, $admin_params, $template);}
		else {$elementid = getElementid($params->get('row1col1'), $row, $params, $admin_params, $template);}
		//dump ($params->get('row1col1'), 'elementid: ');
 		$colspan = $params->get('r1c1span');
 		$rowspan = $params->get('rowspanr1c1');
		 $lastcol = 0;
		 if ($columns == 1 || $colspan > 3) {$lastcol = 1;}
		 if (isset($elementid)) {
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr1c1'),$id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template);
		 }
	if ($columns > 1 && $params->get('r1c1span') < 2 )
	{
 		$rowcolid = 'row1col2';
		if ($params->get('row1col2') < 1) {$params->set('row1col2', 100);}
		if ($params->get('row1col2') == 24) {$elementid = getCustom($params->get('row1col2'), $params->get('r1c2custom'), $row, $params, $admin_params, $template);}
		else {$elementid = getElementid($params->get('row1col2'), $row, $params, $admin_params, $template);}
 		$colspan = $params->get('r1c2span');
 		$rowspan = $params->get('rowspanr1c2');
 		$lastcol = 0;
 		if ($columns == 2 || $colspan > 2) {$lastcol = 1;} 
 		if (isset($elementid)) {
		$listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr1c2'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template);
		}
	}
	
	if ($columns > 2  && ( $params->get('r1c1span') < 3 && $params->get('r1c2span') < 2)) 
	{
		 $rowcolid = 'row1col3';
		 if ($params->get('row1col3') < 1) {$params->set('row1col3', 100);}
		 if ($params->get('row1col3') == 24) {$elementid = getCustom($params->get('row1col3'), $params->get('r1c3custom'), $row, $params, $admin_params, $template);}
		else {$elementid = getElementid($params->get('row1col3'), $row, $params, $admin_params, $template);}
		 $colspan = $params->get('r1c3span');
		 $rowspan = $params->get('rowspanr1c3');
		 $lastcol = 0;
		 if ($columns == 3 || $colspan > 1) {$lastcol = 1;} 
		 if (isset($elementid)) {
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr1c3'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template);
		 }
	}
	
	if ($columns > 3 && ( $params->get('r1c1span') < 4 && $params->get('r1c2span') < 3 && $params->get('r1c3span') < 2))
	{
		 $rowcolid = 'row1col4';
		 if ($params->get('row1col4') < 1) {$params->set('row1col4', 100);}
		 if ($params->get('row1col4') == 24) {$elementid = getCustom($params->get('row1col4'), $params->get('r1c4custom'), $row, $params, $admin_params, $template);}
		else {$elementid = getElementid($params->get('row1col4'), $row, $params, $admin_params, $template);}
		 $colspan = $params->get('r1c4span');
		 $rowspan = $params->get('rowspanr1c4');
		 $lastcol = 0;
		 if ($columns == 4) {$lastcol = 1;}
		 if (isset($elementid)) {
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr1c4'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template);
		 }
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
		 if ($params->get('row2col1') < 1) {$params->set('row2col1', 100);}
		 if ($params->get('row2col1') == 24) {$elementid = getCustom($params->get('row2col1'), $params->get('r2c1custom'), $row, $params, $admin_params, $template);}
		else {$elementid = getElementid($params->get('row2col1'), $row, $params, $admin_params, $template);}
 		$colspan = $params->get('r2c1span');
 		$rowspan = $params->get('rowspanr2c1');;
		 $lastcol = 0;
		 if ($columns == 1 || $colspan > 3) {$lastcol = 1;}
		 if (isset($elementid)) {
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr2c1'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template);
		 }
 	//dump ($elementid, 'elementid: ');
	if ($columns > 1  && $params->get('r2c1span') < 2)
	{
 		$rowcolid = 'row2col2';
		if ($params->get('row2col2') < 1) {$params->set('row2col2', 100);} 
		if ($params->get('row2col2') == 24) {$elementid = getCustom($params->get('row2col2'), $params->get('r2c2custom'), $row, $params, $admin_params, $template);}
		else {$elementid = getElementid($params->get('row2col2'), $row, $params, $admin_params, $template);}
 		$colspan = $params->get('r2c2span');
 		$rowspan = $params->get('rowspanr2c2');
 		$lastcol = 0;
 		if ($columns == 2 || $colspan > 2) {$lastcol = 1;} 
		if (isset($elementid)) {
 		$listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr2c2'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template);
		}
	}
	
	if ($columns > 2   && ( $params->get('r2c1span') < 3 && $params->get('r2c2span') < 2)) 
	{
		 $rowcolid = 'row2col3';
		 if ($params->get('row2col3') < 1) {$params->set('row2col3', 100);}
		 if ($params->get('row2col3') == 24) {$elementid = getCustom($params->get('row2col3'), $params->get('r2c3custom'), $row, $params, $admin_params, $template);}
		 else {$elementid = getElementid($params->get('row2col3'), $row, $params, $admin_params, $template);}
		 //if (!$elementid->id){$element->id = ''; $element->element = '';}
		 $colspan = $params->get('r2c3span');
		 $rowspan = $params->get('rowspanr2c3');
		 $lastcol = 0;
		 if ($columns == 3 || $colspan > 1) {$lastcol = 1;}
		 if (isset($elementid)) {
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr2c3'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template);
		 }
	}
	
	if ($columns > 3  && (  $params->get('r2c1span') < 4 && $params->get('r2c2span') < 3 && $params->get('r2c3span') < 2))
	{
		 $rowcolid = 'row2col4';
		 if ($params->get('row2col4') < 1) {$params->set('row2col4', 100);}
		 if ($params->get('row2col4') == 24) {$elementid = getCustom($params->get('row2col4'), $params->get('r2c4custom'), $row, $params, $admin_params, $template);}
		 else {$elementid = getElementid($params->get('row2col4'), $row, $params, $admin_params, $template);}
		 $colspan = $params->get('r2c4span');
		 $rowspan = $params->get('rowspanr2c4');
		 $lastcol = 0;
		 if ($columns == 4) {$lastcol = 1;}
		 if (isset($elementid)) {
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr2c4'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template);
		 }
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
		 if ($params->get('row3col1') < 1) {$params->set('row3col1', 100);}
		 if ($params->get('row3col1') == 24) {$elementid = getCustom($params->get('row3col1'), $params->get('r3c1custom'), $row, $params, $admin_params, $template);}
		else {$elementid = getElementid($params->get('row3col1'), $row, $params, $admin_params, $template);}
 		$colspan = $params->get('r3c1span');
 		$rowspan = $params->get('rowspanr3c1');;
		 $lastcol = 0;
		 if ($columns == 1 || $colspan > 3) {$lastcol = 1;}
		 if (isset($elementid))
		 {$listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr3c1'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template);
		 }
	if ($columns > 1 && $params->get('r3c1span') < 2)
	{
 		$rowcolid = 'row3col2';
		if ($params->get('row3col2') < 1) {$params->set('row3col2', 100);}
		if ($params->get('row3col2') == 24) {$elementid = getCustom($params->get('row3col2'), $params->get('r3c2custom'), $row, $params, $admin_params, $template);}
		else {$elementid = getElementid($params->get('row3col2'), $row, $params, $admin_params, $template);}
 		$colspan = $params->get('r3c2span');
 		$rowspan = $params->get('rowspanr3c2');
 		$lastcol = 0;
 		if ($columns == 2 || $colspan > 2) {$lastcol = 1;}
		if (isset($elementid)) {
 		$listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr3c2'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template);
		}
	}
	
	if ($columns > 2   && ( $params->get('r3c1span') < 3 && $params->get('r3c2span') < 2) )
	{
		 $rowcolid = 'row3col3';
		 if ($params->get('row3col3') < 1) {$params->set('row3col3', 100);}
		 if ($params->get('row3col3') == 24) {$elementid = getCustom($params->get('row3col3'), $params->get('r3c3custom'), $row, $params, $admin_params, $template);}
		else {$elementid = getElementid($params->get('row3col3'), $row, $params, $admin_params, $template);}
		 $colspan = $params->get('r3c3span');
		 $rowspan = $params->get('rowspanr3c3');
		 $lastcol = 0;
		 if ($columns == 3 || $colspan > 1) {$lastcol = 1;}
		 if (isset($elementid)) {
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr3c3'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template);
		 }
	}
	
	if ($columns > 3 && (  $params->get('r3c1span') < 4 && $params->get('r3c2span') < 3 && $params->get('r3c3span') < 2))
	{
		 $rowcolid = 'row3col4';
		 if ($params->get('row3col4') < 1) {$params->set('row3col4', 100);}
		 if ($params->get('row3col4') == 24) {$elementid = getCustom($params->get('row3col4'), $params->get('r3c4custom'), $row, $params, $admin_params, $template);}
		else {$elementid = getElementid($params->get('row3col4'), $row, $params, $admin_params, $template);}
		 $colspan = $params->get('r3c4span');
		 $rowspan = $params->get('rowspanr3c4');
		 $lastcol = 0;
		 if ($columns == 4) {$lastcol = 1;}
		 if (isset($elementid)) {
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr3c4'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template);
		 }
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
		 if ($params->get('row4col1') < 1) {$params->set('row4col1', 100);}
		 if ($params->get('row4col1') == 24) {$elementid = getCustom($params->get('row4col1'), $params->get('r4c1custom'), $row, $params, $admin_params, $template);}
		else {$elementid = getElementid($params->get('row4col1'), $row, $params, $admin_params, $template);}
 		$colspan = $params->get('r4c1span');
 		$rowspan = $params->get('rowspanr4c1');;
		 $lastcol = 0;
		 if ($columns == 1 || $colspan > 3) {$lastcol = 1;}
		 if (isset($elementid)) {
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr4c1'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template);
		 }
 	
	if ($columns > 1  && $params->get('r4c1span') < 2)
	{
 		$rowcolid = 'row4col2';
		if ($params->get('row4col2') < 1) {$params->set('row4col2', 100);}
		if ($params->get('row4col2') == 24) {$elementid = getCustom($params->get('row4col2'), $params->get('r4c2custom'), $row, $params, $admin_params, $template);}
		else {$elementid = getElementid($params->get('row4col2'), $row, $params, $admin_params, $template);}
 		$colspan = $params->get('r4c2span');
 		$rowspan = $params->get('rowspanr4c2');
 		$lastcol = 0;
 		if ($columns == 2 || $colspan > 2) {$lastcol = 1;}
		if (isset($elementid)) {
 		$listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr4c2'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template);
		}
	}
	
	if ($columns > 2   && ( $params->get('r4c1span') < 3 && $params->get('r4c2span') < 2) )
	{
		 $rowcolid = 'row4col3';
		 if ($params->get('row4col3') < 1) {$params->set('row4col3', 100);}
		 if ($params->get('row4col3') == 24) {$elementid = getCustom($params->get('row4col3'), $params->get('r4c3custom'), $row, $params, $tempalte);}
		else {$elementid = getElementid($params->get('row4col3'), $row, $params, $admin_params, $template);}
		 $colspan = $params->get('r4c3span');
		 $rowspan = $params->get('rowspanr4c3');
		 $lastcol = 0;
		 if ($columns == 3 || $colspan > 1) {$lastcol = 1;}
		 if (isset($elementid)) {
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr4c3'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template);
		 }
	}
	
	if ($columns > 3 && ( $params->get('r4c1span') < 4 && $params->get('r4c2span') < 3 && $params->get('r4c3span') < 2))
	{
		 $rowcolid = 'row4col4';
		 if ($params->get('row4col4') < 1) {$params->set('row4col4', 100);}
		 if ($params->get('row4col4') == 24) {$elementid = getCustom($params->get('row4col4'), $params->get('r4c4custom'), $row, $params, $admin_params, $template);}
		else {$elementid = getElementid($params->get('row4col4'), $row, $params, $admin_params, $template);}
		 $colspan = $params->get('r4c4span');
		 $rowspan = $params->get('rowspanr4c4');
		 $lastcol = 0;
		 if ($columns == 4) {$lastcol = 1;}
		 if (isset($elementid)) {
		 $listing .= getCell($elementid->id, $elementid->element, $rowcolid, $colspan, $rowspan, $lastcol, $params->get('linkr4c4'), $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $row, $template);
		 }
	}
	$listing .= '
	</tr>
	'; //This ends the row of the data to be displayed		
	
return $listing;
}

	function getCell($elementid, $element, $rowcolid, $colspan, $rowspan, $lastcol, $islink, $id3, $tid, $smenu, $tmenu, $entry_access, $allow_entry, $params, $admin_params, $row, $template)
		{
$entry_user = 0;			
if (($allow_entry > 0) && ($rowcolid == 'row1col1')){
$user =& JFactory::getUser();
$entry_user = $user->get('gid');
if (!$entry_user) { $entry_user = 0;}
if (!$entry_access) {$entry_access = 23;}
$Itemid = JRequest::getVar('Itemid');
}
		
			$cell = '
						<td class="'.$rowcolid.' '.$elementid;
						if ($lastcol == 1) {$cell .= ' lastcol';}
						$cell .= '" ';
						if ($colspan > 1) {$cell .= 'colspan="'.$colspan.'" ';}
						//if ($rowspan > 1){$cell .='rowspan="'.$rowspan.'"';}
						$cell .= '>';
						if (($rowcolid == 'row1col1') && ($entry_user >= $entry_access) && ($allow_entry > 0)){
							$cell .= '<a href="'.JURI::base().'index.php?option=com_biblestudy&controller=studiesedit&view=studiesedit&task=edit&layout=form&cid[]='.$id3.'&item='.$Itemid.'">'.JText::_(' [Edit] ').'</a>';}
						if ($islink > 0){$cell .= getLink($islink, $id3, $tid, $smenu, $tmenu, $params, $admin_params, $row, $template);}
						$cell .= $element;
						switch ($islink)
						{
							case 0:
								$cell .= '</a>';
							break;
							
							case 1:
								$cell .= '</a>';
							break;
							
							case 3:
								$cell .= '</a>';
							break;
							
							case 4:
								$cell .= '</a></span>';
							break;
							
							case 5:
								$cell .= '</a></span>';
							break;
						
						}
						//if ($islink > 0){$cell .= '</a>';}
						$cell .='</td>';
			return $cell;
		}
	
	function getLink($islink, $id3, $tid, $smenu, $tmenu, $params, $admin_params, $row, $template)
		{
			$Itemid = JRequest::getVar('Itemid');
			$column = '';
			$mime = ' AND #__bsms_mediafiles.mime_type = 1';
			//$Itemid = '';
			$itemlink = $params->get('itemidlinktype');
			//dump ($islink,  'islink: ');
			switch ($islink) { 
		
			case 1 : 
			//$addItemid = getItemidLink($isplugin=0, $admin_params);
				if (!$Itemid)
					{
				 	$link = JRoute::_('index.php?option=com_biblestudy&view=studydetails' . '&id=' . $id3.'&templatemenuid='.$params->get('detailstemplateid')); 
				 	}
				}
				$column = '<a href="'.$link.'">';
			 break;

			case 2 :
				 $filepath = getFilepath($id3, 'study_id',$mime);
				 $link = JRoute::_($filepath);
				 $column .= '<a href="'.$link.'">';
			 break;

			case 3 :
				 $link = JRoute::_('index.php?option=com_biblestudy&view=teacherdisplay' . '&id=' . $tid.'&templatemenuid='.$params->get('teachertemplateid'));
				 if ($tmenu > 0) {$link .= '&Itemid='.$tmenu;}
				 $column .= '<a href="'.$link.'">';
			 break;

			case 4 :
				//Case 4 is a details link with tooltip
				//$addItemid = getItemidLink($isplugin=0, $admin_params);
				if (!$Itemid)
					{
				 	$link = JRoute::_('index.php?option=com_biblestudy&view=studydetails' . '&id=' . $id3.'&templatemenuid='.$params->get('detailstemplateid')); 
				 	}
				 else 
				 	{
				 	$link = JRoute::_('index.php?option=com_biblestudy&view=studydetails' . '&id=' . $id3.'&templatemenuid='.$params->get('detailstemplateid')); 
		 		}
				$column = getTooltip($row->id, $row, $params, $admin_params, $template); 
		   		$column .= '<a href="'.$link.'">';
		   	
  			break;
  			
  			case 5 :
  				//Case 5 is a file link with Tooltip
 				$filepath = getFilepath($id3, 'study_id',$mime);
				$link = JRoute::_($filepath);
				//$column .= '<a href="'.$link.'">';
				$column = getTooltip($row->id, $row, $params, $admin_params, $template); 
			   	$column .= '<a href="'.$link.'">';
			   	
  			break;
		   }
		   
		   return $column;
		}
		
		function getListingExp($row, $params, $oddeven, $admin_params, $template)
{
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'elements.php');
	include_once($path1.'scripture.php');
	include_once($path1.'custom.php');
	include_once($path1.'date.php');
	include_once($path1.'media.php');
	
    dump (JPATH_SITE);
    $label = $params->get('templatecode');
    $label = str_replace('{{teacher}}', $row->teachername, $label);
	$label = str_replace('{{title}}', $row->studytitle, $label);
	$label = str_replace('{{date}}', getStudydate($params, $row->studydate), $label);
	$label = str_replace('{{studyintro}}', $row->studyintro, $label);
	$label = str_replace('{{scripture}}', getScripture($params, $row, 0, 1), $label);
	$label = str_replace('{{topics}}', $row->topic_text, $label);
    $label = str_replace('{{url}}', 'index.php?option=com_biblestudy&view=studydetails&id='.$row->id .'&templatemenuid='.$template, $label);
    $label = str_replace('{{mediatime}}', $row->media_hours.':'.$row->media_minutes.':'.$row->media_seconds, $label);
    $label = str_replace('{{thumbnail}}', '<img src="images/'.$admin_params->get('study_images')."/".$row->thumbnailm.'" width="'.$row->thumbwm.'" height="'.$row->thumbhm.'" id="bsms_studyThumbnail" />', $label);
    $label = str_replace('{{seriestext}}', $row->series_text, $label);
    $label = str_replace('{{messagetype}}', $row->message_type, $label);
    $label = str_replace('{{bookname}}', $row->bookname, $label);
    $label = str_replace('{{topics}}', $row->topic_text, $label);
//    		$social = getShare($this->detailslink, $row, $params, $this->admin_params);
//		echo $social;
    $media = getMedia($row->id);

    $mediaTable = "<table id='bsms_mediatable'><TR>";
    foreach ($media as $item) {
        //Loop through the media items and see what each is.
        $mediaTable .= "<td>".getDownloadLink ($item, $params, $admin_params)."</td>";
    }
    $mediaTable .= "<td>".getPdf($row, $params, $admin_params)."</td>";
    $mediaTable .= "</TR></table>";
    
    $label = str_replace('{{media}}', $mediaTable, $label);
    //Need to add template items for media...
    
	return $label;
}

function getStudyExp($row, $params, $admin_params, $template)
{
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
        
    $label = $params->get('study_detailtemplate');
    $label = str_replace('{{teacher}}', $row->teachername, $label);
	$label = str_replace('{{title}}', $row->studytitle, $label);
	$label = str_replace('{{date}}', getStudydate($params, $row->studydate), $label);
	$label = str_replace('{{studyintro}}', $row->studyintro, $label);
	$label = str_replace('{{scripture}}', getScripture($params, $row, 0, 1), $label);
	$label = str_replace('{{topics}}', $row->topic_text, $label);
    $label = str_replace('{{mediatime}}', $row->media_hours.':'.$row->media_minutes.':'.$row->media_seconds, $label);
    $label = str_replace('{{thumbnail}}', '<img src="'.$row->thumbnailm.'" width="'.$row->thumbwm.'" height="'.$row->thumbhm.'" id="bsms_studyThumbnail" />', $label);
    $label = str_replace('{{seriestext}}', $row->series_text, $label);
    $label = str_replace('{{messagetype}}', $row->message_type, $label);
    $label = str_replace('{{bookname}}', $row->bookname, $label);
    $label = str_replace('{{studytext}}', $row->studytext, $label);
    //Passage
    $link = '<strong><a class="heading" href="javascript:ReverseDisplay(\'bsms_scripture\')">>>'. JText::_('Show/Hide Scipture Passage').'<<</a>';
    $link .= '<div id="bsms_scripture" style="display:none;"></strong>';
    $response = getPassage($params, $row);
    $link .= $response;
    $link .= '</div>';
    $label = str_replace('{{scripturelink}}', $link, $label);
    
    //Media
    $media = getMedia($row->id);
    
    $mediaTable = "<table class='bsms_mediatable'>";
    //File Type - Download - Player 
    foreach ($media as $item) {
        $mediaTable .= "<TR>";
        $mediaTable .= "<TD>" . getTypeIcon($item, $params, $admin_params) . "</TD>";
        $mediaTable .= "<TD>" . getDownloadLink($item, $params, $admin_params) . "</TD>";
        if (strpos($item->imname, "mp3") !== false) {
            $mediaTable .= "<TD>" . getInternalPlayer($item, $params, $admin_params) . "</TD>";
        } else {
            $mediaTable .= "<TD></TD>";
        }
        $mediaTable .= "</TR>";
    }
    $mediaTable .= "</table>";
    
    $label = str_replace('{{media}}', $mediaTable, $label);
    
    //Share
    //Prepares a link string for use in social networking
	$u =& JURI::getInstance();
	$detailslink = htmlspecialchars($u->toString());
	$detailslink = JRoute::_($detailslink);
	//$this->assignRef('detailslink', $detailslink);
	//End social networking
    //$label = str_replace('{{media}}', $mt, $label);
    $share = getShare($detailslink, $row, $params, $admin_params);
    $label = str_replace('{{share}}', $share, $label);

    //PrintableView
    $printview = JHTML::_('image.site',  'printButton.png', '/images/M_images/', NULL, NULL, JText::_( 'Print' ) );
    $printview = '<a href="#&tmpl=component" onclick="window.print();return false;">'.$printview.'</a>';
	
	$label = str_replace('{{printview}}', $printview, $label);
	
	//PDF View
	$url = 'index.php?option=com_biblestudy&view=studydetails&id='.$row->id.'&format=pdf';
    $status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';
    $text = JHTML::_('image.site', 'pdf24.png', '/components/com_biblestudy/images/', NULL, NULL, JText::_('PDF'), JText::_('PDF'));
    $attribs['title']	= JText::_( 'PDF' );
    $attribs['onclick'] = "window.open(this.href,'win2','".$status."'); return false;";
    $attribs['rel']     = 'nofollow';
    $link = JHTML::_('link', JRoute::_($url), $text, $attribs);
    
    $label = str_replace('{{pdfview}}', $link, $label);
    
    //Comments
    $comments = getComments($params, $row, $row->id);
	$label = str_replace('{{comments}}', $comments, $label);

    return $label;
}
