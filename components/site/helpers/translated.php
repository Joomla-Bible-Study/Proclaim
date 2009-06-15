<?php

/**
 * @author Calvary Chapel Newberg
 * @copyright 2009
 */

defined('_JEXEC') or die('Restriced Access');

function getTranslated($result)

{
    //$output = new stdClass;
	//(stdClass)array('value' => '100', 'text'=>'Genesis', 'published'=>'1');
    //$output2 = new stdClass;
	//(stdClass)array('value' => '100', 'text'=>'Genesis', 'published'=>'1');
	//$output = array();
	//$arr = (array) $result;
	$output2 = array();
	//$output = array();
	foreach ($result as $value)
    {
      //empty($output);
      
	  	$format = $value->text;
        $text = JText::_($format);
		$bookn = $value->value;
		$id1 = $value->id;
		$published1 = $value->published;
		$output = array(array('value'=>$bookn, 'text'=>$text, 'published'=>$published1, 'id'=>$id1));
        //$value->id = $key;
          //unset($result->text);
        //array_push($output2,$output);   

    }
   
return $output;
}



