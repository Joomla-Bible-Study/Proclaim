<?php

/**
 * @author Calvary Chapel Newberg
 * @copyright 2009
 */

defined('_JEXEC') or die('Restriced Access');

function getTranslated($result)
{
	
	
	//$array1 = array('id' => '');
	//$array2 = array( 'text' => '');
	foreach ($result as $key => $value)
	{
		$format = $value->text;
		$output->text = JText::_($format);
		$output->id = $key;
		//$key = $result2->value;
		//if ($format == $result->text)
		//{
			unset($value[$key]);
			array_push($value,$output);	
		//}
		//$array1['id'] = $key;
		//$array2['text'] = $output;
	}
	//$translated = array_combine($array1, $array2);
//return $translated;
return $value;
}



