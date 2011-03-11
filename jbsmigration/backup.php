<?php

/**
 * @author Tom Fuller
 * @copyright 2011
 */
defined( '_JEXEC' ) or die( 'Restricted access' ); 

class JBSExport{
//backup_tables('localhost','username','password','blog');

 function exportdb()
    {
        $result = false;
      
        $db =& JFactory::getDBO();
		$config =& JFactory::getConfig();
		$abspath    = JPATH_SITE;
		$host       = $config->getValue('config.host');
		$user       = $config->getValue('config.user');
		$password   = $config->getValue('config.password');
		$dbname         = $config->getValue('config.db');
        $dbprefix = $config->getValue('dbprefix');
        $tables = $db->getTableList();
        $prefix = $db->getPrefix();
       
        $backuptables = array();
        $isjbs = false;
        foreach ($tables AS $table)
        {
             $jbs = $prefix.'bsms_';
             $jbstables = substr_count($table,$jbs);
             if ($jbstables)
             {
                $isjbs = true;
                $backuptables[] = $table;
             }
        }
        if (!$isjbs)
        {
        JError::raiseNotice('SOME_ERROR_CODE', 'JBS_EI_NO_TABLES');
        }
        
       //Copy tables to a temp copy, changing TEXT to BLOB
       $newbackuptables = $this->copytables($backuptables);
            
       $dobackup = false;
       $dobackup = $this->backup_tables($host,$user,$password,$dbname,$newbackuptables);
       if (!$dobackup)
       {
            JError::raiseNotice('SOME_ERROR_CODE', 'JBS_EI_NO_BACKUP');
       }
       else
       {
            $downloadfile = $this->output_file($dobackup[0], $dobackup[1], $mime_type='');
       }
    }

/* backup the db OR just a table */
function backup_tables($host,$user,$pass,$name,$tables = '*')
{
	$config =& JFactory::getConfig();
    $dbprefix = $config->getValue('dbprefix');
    $prefixlength = strlen($dbprefix);
    $theresult = false;
	$link = mysql_connect($host,$user,$pass);
	mysql_select_db($name,$link);
	
	//get all of the tables
	if($tables == '*')
	{
		$tables = array();
		$result = mysql_query('SHOW TABLES');
		while($row = mysql_fetch_row($result))
		{
			$tables[] = $row[0];
		}
	}
	else
	{
		$tables = is_array($tables) ? $tables : explode(',',$tables);
	}
	
	//cycle through
	foreach($tables as $table)
	{
		$result = mysql_query('SELECT * FROM '.$table);
		$num_fields = mysql_num_fields($result);
		$table2 = substr_replace($table,'#__',0,$prefixlength);
		$return.= 'DROP TABLE IF EXISTS '.$table2.';';
		$row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
        $prefixposition = strpos($row2[1],$dbprefix);
        $row2[1] = substr_replace($row2[1],'#__',$prefixposition,$prefixlength);
		$return.= "\n\n".$row2[1].";\n\n";
		
		for ($i = 0; $i < $num_fields; $i++) 
		{
			while($row = mysql_fetch_row($result))
			{
				$return.= 'INSERT INTO '.$table2.' VALUES(';
				for($j=0; $j<$num_fields; $j++) 
				{
					$row[$j] = addslashes($row[$j]);
					$row[$j] = ereg_replace("\n","\\n",$row[$j]);
					if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
					if ($j<($num_fields-1)) { $return.= ','; }
				}
				$return.= ");\n";
			}
		}
		$return.="\n\n\n";
	}
	
	//save file
    $localfilename = 'jbs-db-backup-'.time().'.sql';
    $serverfile = JPATH_SITE .DS. 'tmp' .DS. $localfilename;
	$handle = fopen(JPATH_SITE .DS. 'tmp' .DS. $localfilename,'w+');
    $returnfile = array($serverfile,$localfilename);
    
	fwrite($handle,$return);
	fclose($handle);
    return $returnfile;
}


 
    
    function output_file($file, $name, $mime_type='')
{
 /*
 This function takes a path to a file to output ($file), 
 the filename that the browser will see ($name) and 
 the MIME type of the file ($mime_type, optional).
 
 If you want to do something on download abort/finish,
 register_shutdown_function('function_name');
 */
 if(!is_readable($file)) die('File not found or inaccessible!');
 
 $size = filesize($file);
 $name = rawurldecode($name);
 
 /* Figure out the MIME type (if not specified) */
 $known_mime_types=array(
 	"pdf" => "application/pdf",
 	"txt" => "text/plain",
 	"html" => "text/html",
 	"htm" => "text/html",
	"exe" => "application/octet-stream",
	"zip" => "application/zip",
	"doc" => "application/msword",
	"xls" => "application/vnd.ms-excel",
	"ppt" => "application/vnd.ms-powerpoint",
	"gif" => "image/gif",
	"png" => "image/png",
	"jpeg"=> "image/jpg",
	"jpg" =>  "image/jpg",
	"php" => "text/plain",
    "sql" => "text/x-sql"
 );
 
 if($mime_type==''){
	 $file_extension = strtolower(substr(strrchr($file,"."),1));
	 if(array_key_exists($file_extension, $known_mime_types)){
		$mime_type=$known_mime_types[$file_extension];
	 } else {
		$mime_type="application/force-download";
	 };
 };
 
 @ob_end_clean(); //turn off output buffering to decrease cpu usage
 
 // required for IE, otherwise Content-Disposition may be ignored
 if(ini_get('zlib.output_compression'))
  ini_set('zlib.output_compression', 'Off');
 
 header('Content-Type: ' . $mime_type);
 header('Content-Disposition: attachment; filename="'.$name.'"');
 header("Content-Transfer-Encoding: binary");
 header('Accept-Ranges: bytes');
 
 /* The three lines below basically make the 
    download non-cacheable */
 header("Cache-control: private");
 header('Pragma: private');
 header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
 
 // multipart-download and download resuming support
 if(isset($_SERVER['HTTP_RANGE']))
 {
	list($a, $range) = explode("=",$_SERVER['HTTP_RANGE'],2);
	list($range) = explode(",",$range,2);
	list($range, $range_end) = explode("-", $range);
	$range=intval($range);
	if(!$range_end) {
		$range_end=$size-1;
	} else {
		$range_end=intval($range_end);
	}
 
	$new_length = $range_end-$range+1;
	header("HTTP/1.1 206 Partial Content");
	header("Content-Length: $new_length");
	header("Content-Range: bytes $range-$range_end/$size");
 } else {
	$new_length=$size;
	header("Content-Length: ".$size);
 }
 
 /* output the file itself */
 $chunksize = 1*(1024*1024); //you may want to change this
 $bytes_send = 0;
 if ($file = fopen($file, 'r'))
 {
	if(isset($_SERVER['HTTP_RANGE']))
	fseek($file, $range);
 
	while(!feof($file) && 
		(!connection_aborted()) && 
		($bytes_send<$new_length)
	      )
	{
		$buffer = fread($file, $chunksize);
		print($buffer); //echo($buffer); // is also possible
		flush();
		$bytes_send += strlen($buffer);
	}
 fclose($file);
 } else die('Error - can not open file.');
 
die();
}	
/* This function is not used */
function writefile($dobackup)
    {
        // Set FTP credentials, if given
		jimport('joomla.client.helper');
		jimport('joomla.filesystem.file');
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');
		$client =& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$localfilename = 'jbs-db-backup-'.time().'.sql';
		$file = $client->path.DS.$localfilename;
        $returnfile = array($file, $localfilename);
		// Try to make the template file writeable
		if (JFile::exists($file) && !$ftp['enabled'] && !JPath::setPermissions($file, '0755')) {
			JError::raiseNotice('SOME_ERROR_CODE', 'Could not make the file writable');
		}

		$fileit = JFile::write($file, $dobackup);
        if ($fileit){return $returnfile;}
        else {return false;}
		// Try to make the template file unwriteable
		if (!$ftp['enabled'] && !JPath::setPermissions($file, '0555')) {
			JError::raiseNotice('SOME_ERROR_CODE', 'Could not make the file unwritable');
		}
    }
/* This function is not used */        
    function downloadfile($fname)
    {
        
        $serverfile = $fname[0];
        $localfile = $fname[1];
        $user_agent = (isset($_SERVER["HTTP_USER_AGENT"]) ) ? $_SERVER["HTTP_USER_AGENT"] : $HTTP_USER_AGENT;
        while (@ob_end_clean());
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
    	header("Content-Disposition: attachment; filename=".basename($localfile));
        header("Content-Type: text/plain");
        header("Content-Transfer-Encoding: binary");
      	
        readfile($serverfile);
    	
    	$url = $serverfile;
    	$out_file_name = $localfile;
    //    ini_set('memory_limit', '1000M');
    
    	$out = fopen($localfile,"wb");
    	
    	if($out){
    		
    		fwrite($out,file_get_contents($url));
    	
    	}else{
    	   JError::raiseNotice('SOME_ERROR_CODE', 'Error : Set Permissions 777 to the current directory');
    		
    	}
    	
    	fclose($out);
   		return true;
    }

    function copytables($backuptables)
    {
        $newbackuptables = array();
        $db = JFactory::getDBO();
        foreach ($backuptables AS $backuptable)
        {
            $query = 'CREATE TABLE '.$backuptable.'_genesis SELECT * FROM '.$backuptable;
            print_r ($query);
            $db->setQuery($query);
            $db->query();
            $newbackuptables[] = $backuptable.'_genesis';
            if (substr_count($backuptable,'studies'))
            {
                $query = 'ALTER TABLE '.$backuptable.'_genesis MODIFY studytext BLOB';
                $db->setQuery($query);
                $db->query();
                
                $query = 'ALTER TABLE '.$backuptable.'_genesis MODIFY studytext2 BLOB';
                $db->setQuery($query);
                $db->query();
            }
            if (substr_count($backuptable,'podcast'))
            {
                $query = 'ALTER TABLE '.$backuptable.'_genesis MODIFY description BLOB';
                $db->setQuery($query);
                $db->query();
            }
             if (substr_count($backuptable,'series'))
            {
                $query = 'ALTER TABLE '.$backuptable.'_genesis MODIFY description BLOB';
                $db->setQuery($query);
                $db->query();
            }
             if (substr_count($backuptable,'teachers'))
            {
                $query = 'ALTER TABLE '.$backuptable.'_genesis MODIFY information BLOB';
                $db->setQuery($query);
                $db->query();
            }
        }
        return $newbackuptables;
    }

    function deletecopy($backuptables)
    {
        $db = JFactory::getDBO();
        foreach ($backuptables AS $backuptable)
        {
            $query = 'DROP TABLE '.$backuptable;
            $db->setQuery($query);
            $db->query();
        }
    }

} // end of class
?>