<?php
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

class biblestudyControllermediafilesedit extends JController {

	function __construct() {
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
		
		$user =& JFactory::getUser();
		$params =& $mainframe->getPageParameters();
		
		//@todo Improve
		//ACL
		$entry_user = $user->get('gid');
        if (!$entry_user) { $entry_user = 0; }
		$entry_access = ($params->get('entry_access')) ;
		$allow_entry = $params->get('allow_entry_study');
		if (!$allow_entry) {$allow_entry = 0;}
		//if ($allow_entry < 1) {return JError::raiseError('403', JText::_('JBS_CMN_ACCESS_FORBIDDEN')); }
		if (!$entry_user) { $entry_user = 0; }
		if ($allow_entry > 0) {
			if ($entry_user < $entry_access){return JError::raiseError('403', JText::_('JBS_CMN_ACCESS_FORBIDDEN')); }
		}
		parent::__construct();

		// Register Extra tasks
		$this->registerTask('add', 'edit');
		$this->registerTask('upload', 'upload');
	}

	/**
	 * @desc display the edit form
	 * @return void
	 */
	function edit() {
		JRequest::setVar( 'view', 'mediafilesedit' );
		JRequest::setVar( 'layout', 'form'  );
	
		parent::display();
	}

	/**
	 * @desc Save a record (and redirect to main page)
	 * @return void
	 */
	function save() {
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');

		$model = $this->getModel('mediafilesedit');
		$file = JRequest::getVar('file', null, 'files', 'array' );
		$filename_upload = $file['name'];
		$data = JRequest::get( 'post' );
		if(empty($data['filename'])) $data['filename'] = $file['name'];
		if (isset($filename_upload)){
			$this->upload();
		}
		if ($model->store($data)) {
			$msg = JText::_('JBS_MED_MEDIA_SAVED');
		} else {
			$msg = JText::_('JBS_MED_ERROR_SAVING_MEDIA');
		}

		$templatemenuid = JRequest::getVar('templatemenuid', 1, 'get', 'int');
		if (!$templatmenuid) {$templatemenuid = 1;}
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.'&templatemenuid='.$templatemenuid);
		// Check the table in so it can be edited.... we are done with it anyway
		$mainframe->redirect (str_replace("&amp;","&",$link));
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
		$templatemenuid = JRequest::getVar('templatemenuid', 1, 'get', 'int');
		if (!$templatmenuid) {$templatemenuid = 1;}
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.'&templatemenuid='.$templatemenuid);
		// Check the table in so it can be edited.... we are done with it anyway
		$mainframe->redirect (str_replace("&amp;","&",$link));
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
		$templatemenuid = JRequest::getVar('templatemenuid', 1, 'get', 'int');
		if (!$templatmenuid) {$templatemenuid = 1;}
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.'&templatemenuid='.$templatemenuid);
		// Check the table in so it can be edited.... we are done with it anyway
		$mainframe->redirect (str_replace("&amp;","&",$link));
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
		//$params =& $mainframe->getPageParameters();
		$templatemenuid = JRequest::getVar('templatemenuid', 1, 'get', 'int');
		if (!$templatmenuid) {$templatemenuid = 1;}
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.'&templatemenuid='.$templatemenuid);
		// Check the table in so it can be edited.... we are done with it anyway
		$mainframe->redirect ($link);
		//$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafileslist' );
	}

	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$mainframe =& JFactory::getApplication();
		$msg = JText::_( 'JBS_CMN_OPERATION_CANCELLED' );

		$templatemenuid = JRequest::getVar('templatemenuid', 1, 'get', 'int');
		if (!$templatmenuid) {$templatemenuid = 1;}
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.'&templatemenuid='.$templatemenuid);
		// Check the table in so it can be edited.... we are done with it anyway
		$mainframe->redirect (str_replace("&amp;","&",$link));
			
	}

	function upload()
	{
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
		//get admin params
		$db=& JFactory::getDBO();
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
		//$DSS = DS;
		$folderpath = $folder->folderpath;
		$folderpath = str_replace("/",DS,$folderpath);
		//dump ($folderpath, 'Folderpath: ');
		$templatemenuid = JRequest::getVar('templatemenuid', 1, 'get', 'int');
		if (!$templatmenuid) {$templatemenuid = 1;}
		//$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.'&templatemenuid='.$templatemenuid);}
		$templatemenuid = '&templatemenuid='.$templatemenuid;
		$filename = $file['name'];
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
			//$fullfilename = JPATH_SITE.$folderpath. $file['name'];
			
			$fullfilename = JPATH_ROOT.$folderpath. $file['name'];
			
			
			//dump ($fullfilename, 'fullfilename: ');
			$filename = $file['name'];
			jimport('joomla.filesystem.file');

			if (JFile::exists($fullfilename)) {
				$mainframe->redirect("index.php?option=$option&view=studieslist".$menureturn, "Upload failed, file already exists.");
				return;
			}
			//if (!JFile::upload($file['tmp_name'], $filepath)) {
			//if (!JFile::upload($file['tmp_name'], $fullfilename)) {
				//$fullfilename = $_SERVER['DOCUMENT_ROOT'].$folderpath. $file['name'];}
			if (!JFile::upload($file['tmp_name'], $fullfilename)) {	
				$mainframe->redirect("index.php?option=$option&view=studieslist".$templatemenuid, 'Upload failed, check to make sure that the path "'.$fullfilename.'" exists on this server');
				return;
			}

		}
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

}
?>