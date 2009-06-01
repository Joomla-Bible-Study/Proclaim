<?php
defined('_JEXEC') or die();

function getHeader($params)
{
	$columns = 1;
	if ($params->get('row1col2') > 0) {$columns = 2;}
	if ($params->get('row1col3') > 0) {$columns = 3;}
	if ($params->get('row1col4') > 0) {$columns = 4;}
	$rows = 1;
	if ($params->get('row2col1') > 0) {$rows = 2;}
	if ($params->get('row3col1') > 0) {$rows = 3;}
	if ($params->get('row4col1') > 0) {$rows = 4;}
	
	//here we go through each position to see if it has a positive value, get the cell using getHeadercell and return the final header
	  
	   
	 return $header;
}

function getHeadercell($params, $rowid)
{
	//In this function we would create the cell for each header using getHeadertext function
<thead>
        <tr>
          <th id="bsdatehead" class="row1col1">Date</th>
          <th id="bstitlehead" class="row1col2">Title</th>
          <th id="bsserieshead" class="row1col3">Series</th>

          <th id="bsmediahead" class="row1col4 lastcol" rowspan="2">Media</th>
        </tr>
        <tr>
          <th id="bsscripthead" class="row2col1">Scripture</th>
          <th id="bsteacherhead" class="row2col2">Teacher</th>
          <th id="bsdurhead" class="row2col3">Duration</th>
        </tr>

        <tr class="lastrow">
          <th id="bsdeschead" class="row3col1 lastcol" colspan="4">Description</th>
        </tr>
      </thead>

return $headercell;
}

function getHeadertext($params)
{
	
	//We would take the rowid and use a switch case to turn it into text and return the text
	return $headertext;
}