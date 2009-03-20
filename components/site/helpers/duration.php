<?php defined('_JEXEC') or die();

function getDuration($duration_type, $hours, $minutes, $seconds) 
{

switch ($duration_type) {
   case 1:
     if (!$hours){
      $duration = $minutes.' mins '.$seconds.' secs';
     }
     else {
      $duration = $hours.' hour(s) '.$minutes.' mins '.$seconds.' secs';
     }
     break;
   case 2:
     if (!$hours){
      $duration = $minutes.':'.$seconds;
     }
     else {
      $duration = $hours.':'.$minutes.':'.$seconds;
     }
     break;
  } // end switch
  
 return $duration;
}