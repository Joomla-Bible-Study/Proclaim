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
		global $mainframe, $option;

		$db=& JFactory::getDBO();
		$file = JRequest::getVar('file', null, 'files', 'array' );
		$filename = '';
		$path = JRequest::getVar('path', null, 'POST', 'INT');
		$query = 'SELECT id, folderpath FROM #__bsms_folders WHERE id = '.$path.' LIMIT 1';
		$db->setQuery($query);
		$folder = $db->loadObject();
		$folderpath = $folder->folderpath;

		//This is where we check the make the extension is of a filetype that is okay to upload
		//$file_extension = JFile::getExt($file);
		//$fname_reject = 'index.htm|index.html|index.php';
		//$ext_reject = 'asp|php';
		$filename = $file['name'];
		if ($filename == 'index.htm'){
			$mainframe->redirect("index.php?option=$option&view=mediafileslist", "File of this type not allowed.");
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
		//This removes any characters that might cause headaches to browsers. This also does the same thing in the model
		$badchars = array(' ', '`', '@', '^', '!', '#', '$', '%', '*', '(', ')', '[', ']', '{', '}', '~', '?', '/', '>', '<', ',', '|', '\\', ';', ':');
		$file['name'] = str_replace($badchars, '_', $file['name']);
		$file['name'] = str_replace('&', '_and_', $file['name']);
		
		if(isset($file) && is_array($file) && $file['name'] != '')
		{
			$fullfilename = JPATH_SITE.$folderpath. strtolower($file['name']);
			$filename = strtolower($file['name']);
			jimport('joomla.filesystem.file');


			if (JFile::exists($fullfilename)) {
				$mainframe->redirect("index.php?option=$option&view=mediafileslist", "Upload failed, file already exists.");
				return;
			}

			if (!JFile::upload($file['tmp_name'], $fullfilename)) {
				$mainframe->redirect("index.php?option=$option&view=mediafileslist", "Upload failed, check to make sure that /components/$option/calendars exists.");
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

		if ($model->store($post)) {
			$msg = JText::_( 'Media Saved!' );
		} else {
			$msg = JText::_( 'Error Saving Media' );
		}
		$file = JRequest::getVar('file', null, 'files', 'array' );
		$filename_upload = strtolower($file['name']);
		if (isset($filename_upload)){
			$uploadFile=$this->upload();}
			// Check the table in so it can be edited.... we are done with it anyway
			$link = 'index.php?option=com_biblestudy&view=mediafileslist';
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
			$msg = JText::_( 'Error: One or More Media Items Could not be Deleted' );
		} else {
			$msg = JText::_( 'Media Item(s) Deleted' );
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafileslist', $msg );
	}
	function publish()
	{
		global $mainframe;

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to publish' ) );
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
			$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafileslist', JText::_('No Items Selected') );
			return false;
		}

		$model =& $this->getModel('mediafilesedit');
		$model->setid($id);
		if ($model->move(-1)) {
			$msg = JText::_( 'Media file Moved Up' );
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
			$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafileslist', JText::_('No Items Selected') );
			return false;
		}

		$model =& $this->getModel('mediafilesedit');
		$model->setid($id);
		if ($model->move(1)) {
			$msg = JText::_('Media file Moved Down');
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
			$msg = JText::_( 'New ordering saved' );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafileslist', $msg );
	}
	
	function unpublish()
	{
		global $mainframe;

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
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
		$msg = JText::_( 'Operation Cancelled' );
		$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafileslist', $msg );
	}

	function docmanCategoryItems() {
		//hide errors and warnings
		error_reporting(0);
		
		$catId = JRequest::getVar('catId');

		$model =& $this->getModel('mediafilesedit');
		$items =& $model->getdocManCategoryItems($catId);
		//dump($catId);
		echo $items;
	}
}
?>
