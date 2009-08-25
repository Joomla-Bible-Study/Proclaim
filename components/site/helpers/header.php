<?php
defined('_JEXEC') or die();

function getHeader($row, $params, $admin_params, $template, $showheader)
{ 
//dump ($template, 'Header - Template: ');
	//$nh checks to see if there is a header in use, otherwise it puts a line at the top of the listing
	$nh = FALSE;
	if  ($showheader < 1){$nh = TRUE;}
	//dump ($params->get('use_headers_list'), 'nh: ');
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'elements.php');
	$columns = 1;
	if ($params->get('row1col2') > 0) {$columns = 2;}
	if ($params->get('row1col3') > 0) {$columns = 3;}
	if ($params->get('row1col4') > 0) {$columns = 4;}
	$rows = 1;
	if ($params->get('row2col1') > 0) {$rows = 2;}
	if ($params->get('row3col1') > 0) {$rows = 3;}
	if ($params->get('row4col1') > 0) {$rows = 4;}
	
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
 	$listing .= getHeadercell($params->get('row1col1'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $nh, $admin_params, $template);
 	
 	
 	if ($columns > 1  && $params->get('r1c1span') < 2)
 	{
	 	$colspan = $params->get('r1c2span');
 		$rowspan = $params->get('rowspanr1c2');
 		$rowcolid = 'row1col2';
 		$lastcol = 0;
 		if ($columns == 2 || $colspan > 2) {$lastcol = 1;}
		$listing .= getHeadercell($params->get('row1col2'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	}
 	if ($columns > 2  && ($params->get('r1c1span') < 3 && $params->get('r1c2span') < 2))
 	{
	 	$colspan = $params->get('r1c3span');
		 $rowspan = $params->get('rowspanr1c3');
		 $rowcolid = 'row1col3';
		 $lastcol = 0;
		 if ($columns == 3 || $colspan > 1) {$lastcol = 1;}
		$listing .= getHeadercell($params->get('row1col3'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	}
 	if ($columns > 3 && (  $params->get('r1c1span') < 4 && $params->get('r1c2span') < 3 && $params->get('r1c3span') < 2))
 	{
	 	$colspan = $params->get('r1c4span');
		 $rowspan = $params->get('rowspanr1c4');
		 $rowcolid = 'row1col4';
		 $lastcol = 0;
		 if ($columns == 4) {$lastcol = 1;}
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
		$listing .= getHeadercell($params->get('row2col1'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	
 	if ($columns > 1  && $params->get('r2c1span') < 2)
 	{
	 	$colspan = $params->get('r2c2span');
 		$rowspan = $params->get('rowspanr2c2');
 		$rowcolid = 'row2col2';
 		$lastcol = 0;
 		if ($columns == 2 || $colspan > 2) {$lastcol = 1;}
		$listing .= getHeadercell($params->get('row2col2'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	}
 	if ($columns > 2  && ($params->get('r2c1span') < 3 && $params->get('r2c2span') < 2))
 	{
	 	 $colspan = $params->get('r2c3span');
		 $rowspan = $params->get('rowspanr2c3');
		 $rowcolid = 'row2col3';
		 $lastcol = 0;
		 if ($columns == 3 || $colspan > 1) {$lastcol = 1;}
		$listing .= getHeadercell($params->get('row2col3'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	}
 	if ($columns > 3 && (  $params->get('r2c1span') < 4 && $params->get('r2c2span') < 3 && $params->get('r2c3span') < 2))
 	{
	 	$colspan = $params->get('r2c4span');
		 $rowspan = $params->get('rowspanr2c4');
		 $rowcolid = 'row2col4';
		 $lastcol = 0;
		 if ($columns == 4) {$lastcol = 1;}
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
		$listing .= getHeadercell($params->get('row3col1'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	
 	if ($columns > 1  && $params->get('r3c1span') < 2)
 	{
	 	$colspan = $params->get('r3c2span');
 		$rowspan = $params->get('rowspanr3c2');
 		$rowcolid = 'row3col2';
 		$lastcol = 0;
 		if ($columns == 2 || $colspan > 2) {$lastcol = 1;}
		$listing .= getHeadercell($params->get('row3col2'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	}
 	if ($columns > 2  && ($params->get('r3c1span') < 3 && $params->get('r3c2span') < 2))
 	{
	 	$colspan = $params->get('r3c3span');
		 $rowspan = $params->get('rowspanr3c3');
		 $rowcolid = 'row3col3';
		 $lastcol = 0;
		 if ($columns == 3 || $colspan > 1) {$lastcol = 1;}
		$listing .= getHeadercell($params->get('row3col3'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	}
 	if ($columns > 3 && (  $params->get('r3c1span') < 4 && $params->get('r3c2span') < 3 && $params->get('r3c3span') < 2))
 	{
	 	$colspan = $params->get('r3c4span');
		 $rowspan = $params->get('rowspanr3c4');
		 $rowcolid = 'row3col4';
		 $lastcol = 0;
		 if ($columns == 4) {$lastcol = 1;}
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
		$listing .= getHeadercell($params->get('row4col1'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	
 	
	
 	if ($columns > 1 && $params->get('r4c1span') < 2)
 	{
	 	$colspan = $params->get('r4c2span');
 		$rowspan = $params->get('rowspanr4c2');
 		$rowcolid = 'row4col2';
 		$lastcol = 0;
 		if ($columns == 2 || $colspan > 2) {$lastcol = 1;}
		$listing .= getHeadercell($params->get('row4col2'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	}
 	if ($columns > 2  && ($params->get('r4c1span') < 3 && $params->get('r4c2span') < 2))
 	{
	 	$colspan = $params->get('r4c3span');
		 $rowspan = $params->get('rowspanr4c3');
		 $rowcolid = 'row4col3';
		 $lastcol = 0;
		 if ($columns == 3 || $colspan > 1) {$lastcol = 1;}
		$listing .= getHeadercell($params->get('row4col3'), $row, $params, $lastcol, $colspan, $rowspan, $rowcolid, $nh, $admin_params, $template);
 	}
 	if ($columns > 3  && (  $params->get('r4c1span') < 4 && $params->get('r4c2span') < 3 && $params->get('r4c3span') < 2))
 	{
	 	 $colspan = $params->get('r4c4span');
		 $rowspan = $params->get('rowspanr4c4');
		 $rowcolid = 'row4col4';
		 $lastcol = 0;
		 if ($columns == 4) {$lastcol = 1;}
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
        

