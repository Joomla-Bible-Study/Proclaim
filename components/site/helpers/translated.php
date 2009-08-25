<?php

/**
 * @author Calvary Chapel Newberg
 * @copyright 2009
 */

defined('_JEXEC') or die('Restriced Access');

function getTranslated($result)

{
	//dump ($result);
    $output2 = array();
	foreach ($result as $value)
    {
		empty($output2);
		$format = $value->text;
		$text = JText::_($format);
		$bookn = $value->value;
		$id1 = $value->id;
		$published1 = $value->published;
		$output2 = array('value'=>$bookn, 'text'=>$text, 'published'=>$published1, 'id'=>$id1);
		$output[] = $output2;
    }
return $output;
}



