<?php

/**
 * @author Calvary Chapel Newberg
 * @copyright 2009
 */

defined('_JEXEC') or die('Restriced Access');

function getTranslated($result)

{
    $output = array();
	//$arr = (array) $result;
	foreach ($result as $value)
    {
      $format = $value->text;
        $output=>text = JText::_($format);
		$output=>value = $value->value;
		$output=>id = $value->id;
		$output=>published = $value->published;
        //$value->id = $key;
          //unset($result->text);
         array_push($value,$output);   

    }
   
return $value;
}



