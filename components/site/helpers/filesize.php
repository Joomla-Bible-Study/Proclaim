<?php defined('_JEXEC') or die('Restricted Access');

function getFilesize($id4, $filesizefield) 
{

	global $mainframe;
	
	$database	= & JFactory::getDBO();
	//dump ($id4, 'id4: ');
	$query = 'SELECT #__bsms_mediafiles.id, #__bsms_mediafiles.size'
	  . ' FROM #__bsms_mediafiles'
	  . ' WHERE '.$filesizefield.' LIKE '.$id4.' LIMIT 1';
	  $database->setQuery( $query );
	  $filesize = $database->loadObject();
	  $number_rows = $database->getAffectedRows($query);
	  //dump ($filesize->size, 'Size: ');
	  if ($number_rows > 0) 
	  {
		   $file_size = $filesize->size;
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
		
	  } //end of if $number_rows > 0
	  else { $file_size = 0;}
	
	  return $file_size;
} //End of function