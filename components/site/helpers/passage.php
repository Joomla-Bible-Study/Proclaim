<?php defined('_JEXEC') or die();

function getPassage($params, $row)
{
//global $mainframe, $option;
$esv = 1;
$path1 = JPATH_BASE.DS.'components'.DS.'com_biblestudy/helpers/';
include_once($path1.'scripture.php');
$scripture = getScripture($params, $row, $esv);
$key = "IP";
$response = "".$scripture." (ESV)";
  $passage = urlencode($scripture);
  $options = "include-passage-references=false";
  $url = "http://www.esvapi.org/v2/rest/passageQuery?key=$key&passage=$passage&$options";
  $p = (get_extension_funcs("curl")); // This tests to see if the curl functions are there. It will return false if curl not installed
  if ($p) { // If curl is installed then we go on
  $ch = curl_init($url); // This will return false if curl is not enabled
  if ($ch) { //This will return false if curl is not enabled
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
  $response .= curl_exec($ch);
  curl_close($ch);
  } // End of if ($ch)
  } // End if ($p)
  
  
return $response;
}