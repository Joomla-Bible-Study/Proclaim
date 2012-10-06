<?php defined('_JEXEC') or die('Restricted Access');

function getFilesize($file_size) 
{

	
	if (!$file_size) {$file_size = null; return $file_size;}
	
		   switch ($file_size ) 
		   {
		     case $file_size < 1024 :
			 	$file_size = $file_size.' '.'Bytes';
			 break;
			 case $file_size < 1048576 :
				 $file_size = $file_size / 1024;
				 $file_size = number_format($file_size,0);
				 $file_size = $file_size.' '.'KB';
			 break;
			case $file_size < 1073741824 :
				 $file_size = $file_size / 1024;
				 $file_size = $file_size / 1024;
				 $file_size = number_format($file_size,1);
				 $file_size = $file_size.' '.'MB';
			 break;
			case $file_size > 1073741824 :
				 $file_size = $file_size / 1024;
				 $file_size = $file_size / 1024;
				 $file_size = $file_size / 1024;
				 $file_size = number_format($file_size,1);
				 $file_size = $file_size.' '.'GB';
			 break;
		   }
		
	  return $file_size;
} //End of function