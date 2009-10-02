<?php
defined('_JEXEC') or die();

function getHeader($row, $params, $admin_params, $template, $showheader, $ismodule)
{ 
//dump ($template, 'Header - Template: ');
	//$nh checks to see if there is a header in use, otherwise it puts a line at the top of the listing
	$nh = FALSE;
	if  ($showheader < 1){$nh = TRUE;}
	//dump ($params->get('use_headers_list'), 'nh: ');
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'elements.php');
	
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
	
	$columns = 1;
	$columns = 1;
	if ($params->get('row1col2') > 0 || $params->get('row2col2') > 0 || $params->get('row3col2') > 0 || $params->get('row4col2') > 0){$columns = 2;}
	if ($params->get('row1col3') > 0 || $params->get('row2col3') > 0 || $params->get('row3col3') > 0 || $params->get('row4col3') > 0) {$columns = 3;}
	if ($params->get('row1col4') > 0 || $params->get('row2col4') > 0 || $params->get('row3col4') > 0 || $params->get('row4col4') > 0) {$columns = 4;}
	$rows = 1;
	if ($params->get('row2col1') > 0 || $params->get('row2col2') > 0 || $params->get('row2col3') > 0 || $params->get('row2col4') > 0) {$rows = 2;}
	if ($params->get('row3col1') > 0 || $params->get('row3col2') > 0 || $params->get('row3col3') > 0 || $params->get('row3col4') > 0) {$rows = 3;}
	if ($params->get('row4col1') > 0 || $params->get('row4col2') > 0 || $params->get('row4col3') > 0 || $params->get('row4col4') > 0) {$rows = 4;}
	
	if ($nh) {$listing = '<thead><tr>';
	while ($columns > 0)
		{$listing .= '<th class="firstrow"></th>';
		$columns = $columns - 1;
		}
	$listing .= '</tr></thead>';}
	else {
	//here we go through each position to see if it has a positive value, get the cell using getHeadercell and return the final header
 	$lastrow = 0;
 	//if ($rows == 1) {$lastrow = 1;}
 	$listing = '<thead><tr';
	if ($rows == 1) {$listing .= ' class = "lastrow"';}
	$listing .='>
	';

 	//Beginning of first column
 	$colspan = $params->get('r1c1span');
	$rowspan = $params->get('rowspanr1c1');
	$rowcolid = 'row1col1';
	$lastcol = 0;
	if ($columns == 1 || $colspan > 3) {$lastcol = 1;}
	if ($params->get('row1col1') < 1) {$params->set('row1col1', 100);}
 	$listing .= getHeadercell($params->get('row1col1'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $nh, $admin_params, $template);
 	
 	
 	if ($columns > 1  && $params->get('r1c1span') < 2)
 	{
	 	$colspan = $params->get('r1c2span');
 		$rowspan = $params->get('rowspanr1c2');
 		$rowcolid = 'row1col2';
 		$lastcol = 0;
 		if ($columns == 2 || $colspan > 2) {$lastcol = 1;}
		if ($params->get('row1col2') < 1) {$params->set('row1col2', 100);}
		$listing .= getHeadercell($params->get('row1col2'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	}
 	if ($columns > 2  && ($params->get('r1c1span') < 3 && $params->get('r1c2span') < 2))
 	{
	 	$colspan = $params->get('r1c3span');
		 $rowspan = $params->get('rowspanr1c3');
		 $rowcolid = 'row1col3';
		 $lastcol = 0;
		 if ($columns == 3 || $colspan > 1) {$lastcol = 1;}
		 if ($params->get('row1col3') < 1) {$params->set('row1col3', 100);}
		$listing .= getHeadercell($params->get('row1col3'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	}
 	if ($columns > 3 && (  $params->get('r1c1span') < 4 && $params->get('r1c2span') < 3 && $params->get('r1c3span') < 2))
 	{
	 	$colspan = $params->get('r1c4span');
		 $rowspan = $params->get('rowspanr1c4');
		 $rowcolid = 'row1col4';
		 $lastcol = 0;
		 if ($columns == 4) {$lastcol = 1;}
		 if ($params->get('row1col4') < 1) {$params->set('row1col4', 100);}
		$listing .= getHeadercell($params->get('row1col4'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	}
 	$listing .= '</tr>
	';
 	
 	
	 	$lastrow = 0;
	 	if ($rows == 2) {$lastrow = 1;}
		$listing .= '<tr'; //This begins the row of the display data
		if ($lastrow == 1) {$listing .= ' class="lastrow"';}
		$listing .= '>';
		 $colspan = $params->get('r2c1span');
 		$rowspan = $params->get('rowspanr2c1');
 		$rowcolid = 'row2col1';
		 $lastcol = 0;
		 if ($columns == 1 || $colspan > 3) {$lastcol = 1;}
		 if ($params->get('row2col1') < 1) {$params->set('row2col1', 100);}
		$listing .= getHeadercell($params->get('row2col1'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	
 	if ($columns > 1  && $params->get('r2c1span') < 2)
 	{
	 	$colspan = $params->get('r2c2span');
 		$rowspan = $params->get('rowspanr2c2');
 		$rowcolid = 'row2col2';
 		$lastcol = 0;
 		if ($columns == 2 || $colspan > 2) {$lastcol = 1;}
		if ($params->get('row1col2') < 1) {$params->set('row1col2', 100);}
		$listing .= getHeadercell($params->get('row2col2'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	}
 	if ($columns > 2  && ($params->get('r2c1span') < 3 && $params->get('r2c2span') < 2))
 	{
	 	 $colspan = $params->get('r2c3span');
		 $rowspan = $params->get('rowspanr2c3');
		 $rowcolid = 'row2col3';
		 $lastcol = 0;
		 if ($columns == 3 || $colspan > 1) {$lastcol = 1;}
		 if ($params->get('row2col3') < 1) {$params->set('row2col3', 100);}
		$listing .= getHeadercell($params->get('row2col3'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	}
 	if ($columns > 3 && (  $params->get('r2c1span') < 4 && $params->get('r2c2span') < 3 && $params->get('r2c3span') < 2))
 	{
	 	$colspan = $params->get('r2c4span');
		 $rowspan = $params->get('rowspanr2c4');
		 $rowcolid = 'row2col4';
		 $lastcol = 0;
		 if ($columns == 4) {$lastcol = 1;}
		 if ($params->get('row2col4') < 1) {$params->set('row2col4', 100);}
		$listing .= getHeadercell($params->get('row2col4'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	}
 	$listing .= '</tr>
	';
 	
 	$lastrow = 0;
	$listing .= '<tr'; //This begins the row of the display data
	if ($rows == 3) {$listing .= ' class= "lastrow"';}
	
	$listing .= '>
	'; 
	 	$colspan = $params->get('r3c1span');
 		$rowspan = $params->get('rowspanr3c1');
 		$rowcolid = 'row3col1';
		 $lastcol = 0;
		 if ($columns == 1 || $colspan > 3) {$lastcol = 1;}
		 if ($params->get('row3col1') < 1) {$params->set('row3col1', 100);}
		$listing .= getHeadercell($params->get('row3col1'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	
 	if ($columns > 1  && $params->get('r3c1span') < 2)
 	{
	 	$colspan = $params->get('r3c2span');
 		$rowspan = $params->get('rowspanr3c2');
 		$rowcolid = 'row3col2';
 		$lastcol = 0;
 		if ($columns == 2 || $colspan > 2) {$lastcol = 1;}
		if ($params->get('row3col3') < 1) {$params->set('row3col2', 100);}
		$listing .= getHeadercell($params->get('row3col2'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	}
 	if ($columns > 2  && ($params->get('r3c1span') < 3 && $params->get('r3c2span') < 2))
 	{
	 	$colspan = $params->get('r3c3span');
		 $rowspan = $params->get('rowspanr3c3');
		 $rowcolid = 'row3col3';
		 $lastcol = 0;
		 if ($columns == 3 || $colspan > 1) {$lastcol = 1;}
		 if ($params->get('row3col3') < 1) {$params->set('row3col3', 100);}
		$listing .= getHeadercell($params->get('row3col3'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	}
 	if ($columns > 3 && (  $params->get('r3c1span') < 4 && $params->get('r3c2span') < 3 && $params->get('r3c3span') < 2))
 	{
	 	$colspan = $params->get('r3c4span');
		 $rowspan = $params->get('rowspanr3c4');
		 $rowcolid = 'row3col4';
		 $lastcol = 0;
		 if ($columns == 4) {$lastcol = 1;}
		 if ($params->get('row3col4') < 1) {$params->set('row3col4', 100);}
		$listing .= getHeadercell($params->get('row3col4'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	}
 	$listing .= '</tr>
	';
 	$listing .= '
	<tr'; //This begins the row of the display data
	$lastrow = 0;
	if ($rows == 4) {$listing .= ' class="lastrow"';}
	
	$listing .= '>
	'; 
	 	$colspan = $params->get('r4c1span');
 		$rowspan = $params->get('rowspanr4c1');
 		$rowcolid = 'row4col1';
		 $lastcol = 0;
		 if ($columns == 1 || $colspan > 3) {$lastcol = 1;}
		 if ($params->get('row4col1') < 1) {$params->set('row4col1', 100);}
		$listing .= getHeadercell($params->get('row4col1'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	
 	
	
 	if ($columns > 1 && $params->get('r4c1span') < 2)
 	{
	 	$colspan = $params->get('r4c2span');
 		$rowspan = $params->get('rowspanr4c2');
 		$rowcolid = 'row4col2';
 		$lastcol = 0;
 		if ($columns == 2 || $colspan > 2) {$lastcol = 1;}
		if ($params->get('row4col2') < 1) {$params->set('row4col2', 100);}
		$listing .= getHeadercell($params->get('row4col2'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	}
 	if ($columns > 2  && ($params->get('r4c1span') < 3 && $params->get('r4c2span') < 2))
 	{
	 	$colspan = $params->get('r4c3span');
		 $rowspan = $params->get('rowspanr4c3');
		 $rowcolid = 'row4col3';
		 $lastcol = 0;
		 if ($columns == 3 || $colspan > 1) {$lastcol = 1;}
		 if ($params->get('row4col3') < 1) {$params->set('row4col3', 100);}
		$listing .= getHeadercell($params->get('row4col3'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	}
 	if ($columns > 3  && (  $params->get('r4c1span') < 4 && $params->get('r4c2span') < 3 && $params->get('r4c3span') < 2))
 	{
	 	 $colspan = $params->get('r4c4span');
		 $rowspan = $params->get('rowspanr4c4');
		 $rowcolid = 'row4col4';
		 $lastcol = 0;
		 if ($columns == 4) {$lastcol = 1;}
		 if ($params->get('row4col4') < 1) {$params->set('row4col4', 100);}
		$listing .= getHeadercell($params->get('row4col4'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	}
	$listing .= '</tr>
	';
 	$listing .= '</thead>';
	}//end of if else for $nh
	 return $listing;
}

function getHeadercell($rowid, $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template)
{
		
	 	$headercell = '<th id="';
	 	$elementid = getElementid($rowid, $row, $params, $admin_params, $template);
		if (!isset($elementid->id)) {$headercell .= 'customhead';}
	 	else {$headercell .= $elementid->id.'head';}
		$headercell .= '" class="'.$rowcolid;
		if ($lastcol == 1) {$headercell .= ' lastcol';}
		$headercell .= '"';
		if ($colspan > 1) {$headercell .= 'colspan="'.$colspan.'" ';}
		if ($rowspan > 1){$headercell .='rowspan="'.$rowspan.'"';}
		$headercell .= '>';
		//if (!$elementid->headertext) {$headercell .= JText::_('Study Information');}
	 	if (isset($elementid)) {$headercell .= $elementid->headertext;}
	 	$headercell .= '</th>
		';
 	return $headercell;
		
}
        

