<?php defined('_JEXEC') or die('Restricted access');

class Dump_File{
  var $pathname = NULL;
  var $filename = NULL;
	var $filesieze = NULL;
  function Dump_File($id){
  
  }
	
  function download($inline = false, $server, $path, $filename, $size, $mime_type, $id){
 	
  	$id = JRequest::getVar('id', 0, 'GET', 'INT');
	//dump ($id, 'id= ');
	$db	= & JFactory::getDBO();
	$query = 'SELECT #__bsms_mediafiles.*,'
		. ' #__bsms_servers.id AS ssid, #__bsms_servers.server_path AS spath,'
		. ' #__bsms_folders.id AS fid, #__bsms_folders.folderpath AS fpath,'
		. ' #__bsms_mimetype.id AS mtid, #__bsms_mimetype.mimetext'
		. ' FROM #__bsms_mediafiles'
		. ' LEFT JOIN #__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server)'
		. ' LEFT JOIN #__bsms_folders ON (#__bsms_folders.id = #__bsms_mediafiles.path)'
		. ' LEFT JOIN #__bsms_mimetype ON (#__bsms_mimetype.id = #__bsms_mediafiles.mime_type)'
		. ' WHERE #__bsms_mediafiles.id = '.$id.' LIMIT 1';
		$db->setQuery( $query );
		//echo $id; 
	$media = $db->LoadObject();
	$server = $media->spath;
	$path = $media->fpath;
	$filename = $media->filename;
	$size = $media->size;
	$download_file = 'http://'.$server.$path.$filename;
	//if ($size < 1) { $size = filesize($download_file); }
	$mime_type = $media->mimetext;
    $user_agent = (isset($_SERVER["HTTP_USER_AGENT"]) ) ? $_SERVER["HTTP_USER_AGENT"] : $HTTP_USER_AGENT;
    while (@ob_end_clean());
    
	$filesize = $size;
	/*//dump ($filesize, 'Filesize: ');
	//dump ($download_file, 'Download File: ');
    header("HTTP/1.1 200 OK");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Expires: 0");
    header("Content-Length: ".$filesize);
//	header("Content-type: application/octet-stream");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=".$filename);
    header("Content-Transfer-Encoding: binary");
	$this->readfile_chunked($download_file);*/

//Begin code from studydetails
$url = $download_file;
/*$p = (get_extension_funcs("curl")); // This tests to see if the curl functions are there. It will return false if curl not installed
  if ($p) { // If curl is installed then we go on
  $ch = curl_init($url); // This will return false if curl is not enabled
  if ($ch) { //This will return false if curl is not enabled
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
  $response = curl_exec($ch);
  curl_close($ch);
  
  return $response;
  	} // End of if ($ch)
  } // End if ($p)
//End code from studydetails
*/
//Begin code from weberdev
/*$err_msg = '';
    echo "<br>Attempting message download for $download_file<br>";
    $out = fopen($filename, 'wb');
    if ($out == FALSE){
      print "File not opened<br>";
      exit;
    }
   
    $ch = curl_init();
           
    curl_setopt($ch, CURLOPT_FILE, $out);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_URL, $download_file);
               
    curl_exec($ch);
    echo "<br>Error is : ".curl_error ( $ch);
   
    curl_close($ch);
    //fclose($handle); 
//End code from weberdev*/

//Begin code from webdigity
/*set_time_limit(0);
ini_set('display_errors',true);//Just in case we get some errors, let us know....

$fp = fopen (dirname(__FILE__) . $filename, 'w+');//This is the file where we save the information
$ch = curl_init($download_file);//Here is the file we are downloading
curl_setopt($ch, CURLOPT_TIMEOUT, 50);
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_exec($ch);
curl_close($ch);
fclose($fp);
//End code from webdigity*/

//Begin code from OSU
$ch = curl_init();
   curl_setopt ($ch, CURLOPT_URL, $url);
   curl_setopt ($ch, CURLOPT_USERAGENT, $user_agent);
   curl_setopt ($ch, CURLOPT_HEADER, 0);
   curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
   $result = curl_exec ($ch);
   curl_close ($ch);
   return $result; 

//End code from OSU
//Begin Docman code


		/*$cont_dis = $inline ? 'inline' : 'attachment';

		// required for IE, otherwise Content-disposition is ignored
		if(ini_get('zlib.output_compression'))  {
			ini_set('zlib.output_compression', 'Off');
		}

        header("Pragma: public");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Expires: 0");

        header("Content-Transfer-Encoding: binary");
		header('Content-Disposition:' . $cont_dis .';'
			. ' filename="' . $filename . '";'
			. ' modification-date="' . $media->createdate . '";'
			. ' size=' . $filesize .';'
			); //RFC2183
        header("Content-Type: "    . $mime_type );			// MIME type
        header("Content-Length: "  . $filesize);

        if( ! ini_get('safe_mode') ) { // set_time_limit doesn't work in safe mode
		    @set_time_limit(0);
        }
		$this->readfile_chunked($download_file);*/
//End DocMan Code
  }

  function readfile_chunked($download_file, $retbytes=true){
    $chunksize = 1*(1024*1024); // how many bytes per chunk
    $buffer = '';
    $cnt =0;
    $handle = fopen($download_file, 'rb');
    if ($handle === false){
        return false;
    }
    while (!feof($handle)){
          $buffer = fread($handle, $chunksize);
          echo $buffer;
		  //added from Docman
		  @ob_flush();
			flush();
		//end added from Docman
          if ($retbytes){
             $cnt += strlen($buffer);
          }
    }
    $status = fclose($handle);
    if ($retbytes && $status) {
       return $cnt; // return num. bytes delivered like readfile() does.
    }
    return $status;
  }
}


?>