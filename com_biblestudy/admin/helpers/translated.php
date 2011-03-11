<?php

/**
 * @author Tom Fuller
 * @copyright 2011
 */

defined('_JEXEC') or die('Restriced Access');

function getTranslated($result)

{
	
    $output2 = array();
    $output = array();
	foreach ($result as $value)
    {
		
		empty($output2);
		$format = $value->text;
		$text = JText::_($format);
		$bookn = $value->value;
		$id1 = $value->id;
		
		$output2 = array('value'=>$bookn, 'text'=>$text, 'id'=>$id1);
		$output[] = $output2;
    }
return $output;
}



