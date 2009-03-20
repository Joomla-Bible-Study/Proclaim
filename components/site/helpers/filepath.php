<?php defined('_JEXEC') or die();

function getFilepath($id3) 
{

	global $mainframe;
	
	$database	= & JFactory::getDBO();
	$query = 'SELECT #__bsms_mediafiles.*,'
	  . ' #__bsms_servers.id AS ssid, #__bsms_servers.server_path AS spath,'
	  . ' #__bsms_folders.id AS fid, #__bsms_folders.folderpath AS fpath,'
	  . ' FROM #__bsms_mediafiles'
	  . ' LEFT JOIN #__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server)'
	  . ' LEFT JOIN #__bsms_folders ON (#__bsms_folders.id = #__bsms_mediafiles.path)'
	  . ' WHERE #__bsms_mediafiles.study_id LIKE '.$row->id.' LIMIT 1';
	  $database->setQuery( $query );
	  $filepath = $database->loadObject();
	  $number_rows = $database->getAffectedRows($query);
	  if ($number_rows > 0) 
		  {
			$filepath = $filepath->spath.$filepath->fpath.$filepath->filename;
			//Check url for "http://" prefix, and add it if it doesn't exist
			if(!eregi('http://', $filepath)) 
				{
					$filepath = 'http://'.$filepath;
				}
		  }
		  else { $filepath = ''; }
     
  return $filepath;
}