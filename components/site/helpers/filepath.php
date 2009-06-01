<?php defined('_JEXEC') or die();

function getFilepath($id3, $idfield, $mime) 
{

	global $mainframe;
	//dump ($id3, 'id3: ');
	$database	= & JFactory::getDBO();
	$query = 'SELECT #__bsms_mediafiles.*,'
	  . ' #__bsms_servers.id AS ssid, #__bsms_servers.server_path AS spath,'
	  . ' #__bsms_folders.id AS fid, #__bsms_folders.folderpath AS fpath'
	  . ' FROM #__bsms_mediafiles'
	  . ' LEFT JOIN #__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server)'
	  . ' LEFT JOIN #__bsms_folders ON (#__bsms_folders.id = #__bsms_mediafiles.path)'
	  . ' WHERE '.$idfield.' = '.$id3.' AND #__bsms_mediafiles.published = 1 '.$mime;
	  $database->setQuery( $query );
	  $filepathresults = $database->loadObject();
	  $number_rows = $database->getAffectedRows($query);
	  if ($number_rows > 0) 
		  {
			$filepath = $filepathresults->spath.$filepathresults->fpath.$filepathresults->filename;
			//dump ($filepath, 'filepath: ');
			//Check url for "http://" prefix, and add it if it doesn't exist
			if(!eregi('http://', $filepath)) 
				{
					$filepath = 'http://'.$filepath;
				}
		  }
		  else { $filepath = ''; }
   
  return $filepath;
}