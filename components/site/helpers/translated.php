<?php

/**
 * @author Calvary Chapel Newberg
 * @copyright 2009
 */

<?php defined('_JEXEC') or die('Restriced Access');

function getTranslated($result)
{
	foreach ($result as $result2)
	{
		$format = $result2->text;
		$output = JText::_($format);
		$key = $result2->value;
		$array1[] = $key;
		$array2[] = $output;
	}
	$translated = array_merge($array1, $array2);
return $translated;
}

?>