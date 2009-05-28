<?php defined('_JEXEC') or die('Restriced Access');
//Helper file - master list creater for study lists
function getListing($row, $params)
{
	if ($params->get('row1cell1') {
					 $listing = '<tr ';}
					 if ($params->get('rowspanr1c1') > 1){$listing .='rowspan="'.$params->get('rowspanr1c1').'><td ';}
					 	else $listing .='>';}
					 if ($params->get('r1c1span') > 1) {$listing .= 'colspan="'.$params->get('r1c1span').'">';}
						else {$listing .='>';}
	}
	
	
return $listing;
}