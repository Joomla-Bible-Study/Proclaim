<?php
defined('_JEXEC') or die();

/**
 * Media Edit Controller
 *
 */
class biblestudyControllermediafilesedit extends JController {
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();
		// Register Extra tasks
		$this->registerTask( 'add'  , 	'edit' );
		$this->registerTask( 'upload'  ,     'upload' );
	}
	function upload()
	{
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.path');
		$db=& JFactory::getDBO();
			//get admin params
		$query = 'SELECT params'
			. ' FROM #__bsms_admin'
			. ' WHERE id = 1';
		$db->setQuery($query);
		$admin = $db->loadObject();
		$admin_params = new JParameter($admin->params);
		//end get admin params
		$file = JRequest::getVar('file', null, 'files', 'array' );
		$filename = '';
		$path = JRequest::getVar('path', null, 'POST', 'INT');
		$query = 'SELECT id, folderpath FROM #__bsms_folders WHERE id = '.$path.' LIMIT 1';
		$db->setQuery($query);
		$folder = $db->loadObject();
		$folderpath = $folder->folderpath;
		//This is where we check the make the extension is of a filetype that is okay to upload
		$filename = $file['name'];
		if ($filename == 'index.htm'){
			$mainframe->redirect("index.php?option=$option&view=mediafileslist", "File of this type not allowed.");   // santon review
			return;
		}
		if ($filename == 'index.html'){
			$mainframe->redirect("index.php?option=$option&view=mediafileslist", "File of this type not allowed.");
			return;
		}
		if ($filename == 'index.php'){
			$mainframe->redirect("index.php?option=$option&view=mediafileslist", "File of this type not allowed.");
			return;
		}
		if ($filename == 'index.htm' || $filename == 'index.html' || $filename == 'index.php'){
			$mainframe->redirect("index.php?option=$option&view=studieslist".$templatemenuid, "File of this type not allowed.");
		}
		if ($admin_params->get('character_filter') > 0)
		{
			//This removes any characters that might cause headaches to browsers. This also does the same thing in the model
			$badchars = array(' ', '`', '@', '^', '!', '#', '$', '%', '*', '(', ')', '[', ']', '{', '}', '~', '?', '>', '<', ',', '|', '\\', ';');
			$file['name'] = str_replace($badchars, '_', $file['name']);
		}
		$file['name'] = str_replace('&', '_and_', $file['name']);
		$filename = str_replace($badchars, '_', $file['name']);
		$filename = str_replace('&', '_and_', $file['name']);
		if(isset($file) && is_array($file) && $file['name'] != '')
		{
			$fullfilename = JPATH_SITE.$folderpath. strtolower($file['name']);
			$filename = strtolower($file['name']);


			if (JFile::exists($fullfilename)) {
				$mainframe->redirect("index.php?option=$option&view=mediafileslist", "Upload failed, file already exists.");
				return;
			}

			if (!JFile::upload($file['tmp_name'], $fullfilename)) {
				$mainframe->redirect("index.php?option=$option&view=mediafileslist", "Upload failed.");
				return;
			}

		}


	}


	/**
	 * display the edit form
	 * @return void
	 */
	function edit()
	{
		JRequest::setVar( 'view', 'mediafilesedit' );
		JRequest::setVar( 'layout', 'form'  );
		JRequest::setVar('hidemainmenu', 1);

		parent::display();
	}

	/**
	 * save a record (and redirect to main page)
	 * @return void
	 */
	function save()
	{

		$model = $this->getModel('mediafilesedit');
		//dump ($post, 'post: ');
		$file = JRequest::getVar('file', null, 'files', 'array' );
		$setDocman = JRequest::getVar('docManItem', null, 'post');
		$setFile = $file['name'];
		$setArticle = JRequest::getVar('articleTitle', null, 'post');

		if ($model->store($post)) {
			$msg = JText::_( 'JBS_MED_MEDIA_SAVED' );
		} else {
			$msg = JText::_( 'JBS_MED_ERROR_SAVING_MEDIA' );
		}

		$filename_upload = strtolower($file['name']);
		if (isset($filename_upload)){
			$uploadFile=$this->upload();}
			// Check the table in so it can be edited.... we are done with it anyway
			$link = 'index.php?option=com_biblestudy&view=mediafileslist';
			$this->setRedirect($link, $msg);
	}
	
	/**
	* apply rcord
	* @return Void
	*/
	function apply()
	{

		$model = $this->getModel('mediafilesedit');
		$cid 	= JRequest::getVar( 'id', 1, 'post', 'int' );
		$file = JRequest::getVar('file', null, 'files', 'array' );
		$setDocman = JRequest::getVar('docManItem', null, 'post');
		$setFile = $file['name'];
		$setArticle = JRequest::getVar('articleTitle', null, 'post');

		if ($model->store($post)) {
			$msg = JText::_( 'JBS_MED_MEDIA_SAVED' );
		} else {
			$msg = JText::_( 'JBS_MED_ERROR_SAVING_MEDIA' );
		}

		$filename_upload = strtolower($file['name']);
		if (isset($filename_upload)){
			$uploadFile=$this->upload();}
			// Check the table in so it can be edited.... we are done with it anyway
			$link = 'index.php?option=com_biblestudy&controller=mediafilesedit&task=edit&cid[]='.$cid.'';
			$this->setRedirect($link, $msg);
	}

	/**
	 * remove record(s)
	 * @return void
	 */
	function remove()
	{
		$model = $this->getModel('mediafilesedit');
		if(!$model->delete()) {
			$msg = JText::_( 'JBS_MED_ERROR_DELETING_MEDIA' );
		} else {
			$msg = JText::_( 'JBS_MED_MEDIA_DELETED' );
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafileslist', $msg );
	}
	function publish()
	{
		$mainframe =& JFactory::getApplication();

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'JBS_CMN_SELECT_ITEM_PUBLISH' ) );
		}

		$model = $this->getModel('mediafilesedit');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafileslist' );
	}

	function orderup()
	{
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (isset($cid[0]) && $cid[0]) {
			$id = $cid[0];
		} else {
			$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafileslist', JText::_('JBS_CMN_NO_ITEMS_SELECTED') );
			return false;
		}

		$model =& $this->getModel('mediafilesedit');
		$model->setid($id);
		if ($model->move(-1)) {
			$msg = JText::_( 'JBS_MED_MEDIA_MOVED_UP' );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafileslist', $msg );
	}

	function orderdown()
	{
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (isset($cid[0]) && $cid[0]) {
			$id = $cid[0];
		} else {
			$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafileslist', JText::_('JBS_CMN_NO_ITEMS_SELECTED') );
			return false;
		}

		$model =& $this->getModel('mediafilesedit');
		$model->setid($id);
		if ($model->move(1)) {
			$msg = JText::_('JBS_MED_MEDIA_MOVED_DOWN');
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafileslist', $msg );
	}

	function saveorder()
	{
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );

		$model =& $this->getModel('mediafilesedit');
		if ($model->saveorder($cid, null)) {
			$msg = JText::_( 'JBS_CMN_ORDERING_SAVED' );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafileslist', $msg );
	}

	function unpublish()
	{
		$mainframe =& JFactory::getApplication();

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'JBS_CMN_SELECT_ITEM_UNPUBLISH' ) );
		}

		$model = $this->getModel('mediafilesedit');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafileslist' );
	}

	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$msg = JText::_( 'JBS_CMN_OPERATION_CANCELLED' );
		$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafileslist', $msg );
	}

	function docmanCategoryItems() {
		//hide errors and warnings
		error_reporting(0);
		$catId = JRequest::getVar('catId');

		$model =& $this->getModel('mediafilesedit');
		$items =& $model->getdocManCategoryItems($catId);
		echo $items;
	}

	function articlesSectionCategories() {
		error_reporting(0);
		$secId = JRequest::getVar('secId');

		$model =& $this->getModel('mediafilesedit');
		$items =& $model->getArticlesSectionCategories($secId);
		echo $items;

	}

	function articlesCategoryItems() {
		error_reporting(0);
		$catId = JRequest::getVar('catId');

		$model =& $this->getModel('mediafilesedit');
		$items =& $model->getCategoryItems($catId);
		echo $items;
	}
	function virtueMartItems(){
		error_reporting(0);
		$catId = JRequest::getVar('catId');

		$model =& $this->getModel('mediafilesedit');
		$items =& $model->getVirtueMartItems($catId);
		echo $items;
	}
	function fixAVR()
	{
		jimport('joomla.filesystem.file');
		$src = JPATH_SITE.DS.'components/com_biblestudy/assets/avr/view.html.php';
		$dest = JPATH_SITE.DS.'components/com_avreloaded/views/popup/view.html.php';
		$avrbackup = JPATH_SITE.DS.'components/com_avreloaded/views/popup/view2.html.php';
		$avrexists = JFile::exists($dest);
		if ($avrexists)
			{
				if (!JFile::copy($dest, $avrbackup))
				{
					echo "<script> alert('Copy Operation 1 Failed'); window.history.go(-1); </script>\n";
					$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafileslist' );
				}

				if (!JFile::copy($src, $dest))
				{
					echo "<script> alert('Copy Operation 2 Failed'); window.history.go(-1); </script>\n";
					$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafileslist' );
				}
			}
		$dest = JPATH_SITE.DS.'/components/com_avreloaded/views/popup/view.html.php';
		$avrexists = JFile::exists($dest);
		$avrread = JFile::read($dest);
		$isbsms = substr_count($avrread,'JoomlaBibleStudy');
		if ($isbsms)
		{
			$msg = JText::_( 'JBS_MED_OPERATION_SUCCESS_AVR_FILE_COPIED' );
			$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafileslist', $msg );
		}
	}

	function resetDownloads()
	{
		$msg = null;
		$id 	= JRequest::getInt( 'id', 0, 'post'); //dump ($cid, 'cid: ');
		$db = JFactory::getDBO();
		$db->setQuery("UPDATE #__bsms_mediafiles SET downloads='0' WHERE id = ".$id);
		$reset = $db->query();
        if ($db->getErrorNum() > 0)
				{
					$error = $db->getErrorMsg();
                    $msg = JText::_('JBS_CMN_ERROR_RESETTING_DOWNLOADS').' '.$error;
					$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafilesedit&controller=admin&layout=form&cid[]='.$id, $msg );
				}
		else
			{
				$updated = $db->getAffectedRows();
                $msg = JText::_('JBS_CMN_RESET_SUCCESSFUL').' '.$updated.' '.JText::_('JBS_CMN_ROWS_RESET');
				$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafilesedit&controller=studiesedit&layout=form&cid[]='.$id, $msg );
			}
	}

function resetPlays()
	{
		$msg = null;
		$id 	= JRequest::getInt( 'id', 0, 'post'); //dump ($cid, 'cid: ');
		$db = JFactory::getDBO();
		$db->setQuery("UPDATE #__bsms_mediafiles SET plays='0' WHERE id = ".$id);
		$reset = $db->query();
        if ($db->getErrorNum() > 0)
				{
					$error = $db->getErrorMsg();
                    $msg = JText::_('JBS_CMN_ERROR_RESETTING_PLAYS').' '.$error;
					$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafilesedit&controller=admin&layout=form&cid[]='.$id, $msg );
				}
		else
			{
				$updated = $db->getAffectedRows();
                $msg = JText::_('JBS_CMN_RESET_SUCCESSFUL').' '.$updated.' '.JText::_('JBS_CMN_ROWS_RESET');
                $this->setRedirect( 'index.php?option=com_biblestudy&view=mediafilesedit&controller=studiesedit&layout=form&cid[]='.$id, $msg );
			}
	}
}
//New File Size System Should work on all server now.
function getSizeFile ($url){ 
	$head = ""; 
	$url_p = parse_url($url); 
	$host = $url_p["host"]; 
	if(!preg_match("/[0-9]*\.[0-9]*\.[0-9]*\.[0-9]*/",$host)){
		// a domain name was given, not an IP
		$ip=gethostbyname($host);
		if(!preg_match("/[0-9]*\.[0-9]*\.[0-9]*\.[0-9]*/",$ip)){
			//domain could not be resolved
			return -1;
		}
	}
	$port = intval($url_p["port"]); 
	if(!$port) $port=80;
	$path = $url_p["path"]; 
	//echo "Getting " . $host . ":" . $port . $path . " ...";

	$fp = fsockopen($host, $port, $errno, $errstr, 20); 
	if(!$fp) { 
		return false; 
		} else { 
		fputs($fp, "HEAD "  . $url  . " HTTP/1.1\r\n"); 
		fputs($fp, "HOST: " . $host . "\r\n"); 
		fputs($fp, "User-Agent: http://www.example.com/my_application\r\n");
		fputs($fp, "Connection: close\r\n\r\n"); 
		$headers = ""; 
		while (!feof($fp)) { 
			$headers .= fgets ($fp, 128); 
			} 
		} 
	fclose ($fp); 
	//echo $errno .": " . $errstr . "<br />";
	$return = -2; 
	$arr_headers = explode("\n", $headers); 
	// echo "HTTP headers for <a href='" . $url . "'>..." . substr($url,strlen($url)-20). "</a>:";
	// echo "<div class='http_headers'>";
	foreach($arr_headers as $header) { 
		// if (trim($header)) echo trim($header) . "<br />";
		$s1 = "HTTP/1.1"; 
		$s2 = "Content-Length: "; 
		$s3 = "Location: "; 
		if(substr(strtolower ($header), 0, strlen($s1)) == strtolower($s1)) $status = substr($header, strlen($s1)); 
		if(substr(strtolower ($header), 0, strlen($s2)) == strtolower($s2)) $size   = substr($header, strlen($s2));  
		if(substr(strtolower ($header), 0, strlen($s3)) == strtolower($s3)) $newurl = substr($header, strlen($s3));  
		} 
	// echo "</div>";
	if(intval($size) > 0) {
		$return=intval($size);
	} else {
		$return=$status;
	}
	// echo intval($status) .": [" . $newurl . "]<br />";
	if (intval($status)==302 && strlen($newurl) > 0) {
		// 302 redirect: get HTTP HEAD of new URL
		$return=getSizeFile($newurl);
	}
	return $return; 
} 
?>