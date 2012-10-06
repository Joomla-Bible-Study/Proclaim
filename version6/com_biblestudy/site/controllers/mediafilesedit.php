<?php
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

class biblestudyControllermediafilesedit extends JController {

	function __construct() {
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
		
		$user =& JFactory::getUser();
		$params =& $mainframe->getPageParameters();
		
		//ACL
		$entry_user = $user->get('gid');
		$entry_access = ($params->get('entry_access')) ;
		$allow_entry = $params->get('allow_entry_study');
		if (!$allow_entry) {$allow_entry = 0;}
		if (!$entry_user) { $entry_user = 0; }
		if ($allow_entry > 0) {
			if ($entry_user < $entry_access){return JError::raiseError('403', JText::_('Access Forbidden')); }
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
			$msg = JText::_('Media Saved!');
		} else {
			$msg = JText::_('Error Saving Media');
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
			$msg = JText::_( 'Error: One or More Media Items Could not be Deleted' );
		} else {
			$msg = JText::_( 'Media Item(s) Deleted' );
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
			JError::raiseError(500, JText::_( 'Select an item to publish' ) );
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
			JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
		}

		$model = $this->getModel('mediafilesedit');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}
		$templatemenuid = JRequest::getVar('templatemenuid', 1, 'get', 'int');
		if (!$templatmenuid) {$templatemenuid = 1;}
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.'&templatemenuid='.$templatemenuid);
		// Check the table in so it can be edited.... we are done with it anyway
		$mainframe->redirect ($link);
	}

	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$mainframe =& JFactory::getApplication();
		$msg = JText::_( 'Operation Cancelled' );

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
		$folderpath = $folder->folderpath;
		$folderpath = str_replace("/",DS,$folderpath);
		$templatemenuid = JRequest::getVar('templatemenuid', 1, 'get', 'int');
		if (!$templatmenuid) {$templatemenuid = 1;}
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
			
			$fullfilename = JPATH_ROOT.$folderpath. $file['name'];
			
			$filename = $file['name'];
			jimport('joomla.filesystem.file');

			if (JFile::exists($fullfilename)) {
				$mainframe->redirect("index.php?option=$option&view=studieslist".$menureturn, "Upload failed, file already exists.");
				return;
			}
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