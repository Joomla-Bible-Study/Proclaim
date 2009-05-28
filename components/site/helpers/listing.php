<?php defined('_JEXEC') or die('Restriced Access');
//Helper file - master list creater for study lists
function getListing($row, $params)
{
	//Need to know if last column and last row
	$columns = 1;
	if ($params->get('row1col2') > 0) {$columns = 2;}
	if ($params->get('row1col3') > 0) {$columns = 3;}
	if ($params->get('row1col4') > 0) {$columns = 4;}
	$rows = 1;
	if ($params->get('row2col1') > 0) {$rows = 2;}
	if ($params->get('row3col1') > 0) {$rows = 3;}
	if ($params->get('row4col1') > 0) {$rows = 4;}
	
	if ($params->get('row1col1') {
					 $rowcolid = 'row1col1';
					 $elementid = $this->getElementid($params->get('row1col1'));
					 $colspan = $params->get('r1c1span');
					 $rowspan = $params->get('rowspanr1c1');
					 $lastcol = 0;
					 if ($columns == 1) {$lastcol = 1;}
					 $lastrow = 0;
					 if ($rows == 1) {$lastrow = 1;}
					 $listing .= $this->getCell($elementid, $rowcolid, $colspan, $rowspan, $lastcol, $lastrow);
					 
					 
	}
	
	
return $listing;
}

//$result=$this->writeXML();

//<td class="row1col2 bstitle" headers="bstitlehead">

function getElementid($rowid)
	{
		switch $elementid
		{
	 case 1:
		$elementid = 'scripture';
		break;
	case 2:
		$elementid = 'scripture2';
		break;
	case 3:
		$elementid = 'secondary';
		break;
	case 4:
		$elementid = 'duration';
		break;
	case 5:
		$elementid = 'title';
		break;
	case 6:
		$elementid = 'studyintro';
		break;
	case 7:
		$elementid = 'teacher';
		break;
	case 8:
		$elementid = 'teacher';
		break;
	case 9:
		$elementid = 'series';
		break;
	case 10:
		$elementid = 'date';
		break;
	case 11:
		$elementid = 'submitted';
		break;
	case 12:
		$elementid = 'hits';
		break;
	case 13:
		$elementid = 'studynumber';
		break;
	case 14:
		$elementid = 'topic';
		break;
	case 15:
		$elementid = 'location';
		break;
	case 16:
		$elementid = 'messagetype';
		break;
	case 18:
		$elementid = 'details';
		break;
	case 19:
		$elementid = 'details';
		break;
	case 20:
		$elementid = 'media';
		break;
	case 22:
		$elementid = 'store';
		break;
	case 23:
		$elementid = 'filesize';
		break;
	case 24:
		$elementid = 'custom';
		break;
		}
	return $elementid;
	}

function getCell ($elementid, $rowcolid, $colspan, $rowspan, $lastcol, $lastrow)
	{
		$cell .= '<td class="'.$rowcolid.' '.$elementid;}
					if ($lastcol == 1) {$cell .= ' lastcol ';}
					$cell .= '" ';
					if ($colspan > 1) {$cell .= 'colspan="'.$colspan.'" ';}
					if ($rowspan > 1){$cell .='rowspan="'.$rowspan.'"';}
					 
					 $cell .= '>';
		return $cell;
	}