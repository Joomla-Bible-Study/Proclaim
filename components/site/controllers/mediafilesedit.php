<?php
/**
 * Media Edit Controller for Bible Study Component
 *

 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Media Edit Controller
 *
 */
class biblestudyControllermediafilesedit extends JController
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{

		$user =& JFactory::getUser();
		global $mainframe, $option;
		$params =& $mainframe->getPageParameters();
		$entry_user = $user->get('gid');
		$entry_access = ($params->get('entry_access')) ;
		$allow_entry = $params->get('allow_entry_study');
		if (!$allow_entry) {$allow_entry = 0;}
		if ($allow_entry < 1) {return JError::raiseError('403', JText::_('Access Forbidden')); }
		if (!$entry_user) { $entry_user = 0; }
		if ($allow_entry > 0) {
			if ($entry_user < $entry_access){return JError::raiseError('403', JText::_('Access Forbidden')); }
		}
		parent::__construct();

		// Register Extra tasks
		$this->registerTask( 'add'  , 	'edit' );
		$this->registerTask( 'upload'  ,     'upload' );
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
		$file = JRequest::getVar('file', null, 'files', 'array' );
		$filename_upload = $file['name'];
		$data = JRequest::get( 'post' );
		if(empty($data['filename'])) $data['filename'] = $file['name'];
		if (isset($filename_upload)){
			$uploadFile=$this->upload();}
			if ($model->store($data)) {
				$msg = JText::_( 'Media Saved!' );
			} else {
				$msg = JText::_( 'Error Saving Media' );
			}

			global $mainframe, $option;
			/*$db=& JFactory::getDBO();
			 $query = "SELECT id"
			 . "\nFROM #__menu"
			 . "\nWHERE link ='index.php?option=com_biblestudy&view=studieslist' and published = 1";
			 $db->setQuery($query);
			 $menuid = $db->loadResult();
			 $menureturn='';
			 if ($menuid) {$menureturn = '&Itemid='.$menuid;}
			 //$link = 'index.php?option=com_biblestudy&view=studieslist&Itemid='.$params->get('alt_link').'&msg='.$msg;
			 $link = JRoute::_('index.php?option='.$option.'&view=studieslist&msg='.$msg.$menureturn);*/
			$database	= & JFactory::getDBO();
			$query = "SELECT id"
			. "\nFROM #__menu"
			. "\nWHERE link ='index.php?option=com_biblestudy&view=studieslist' and published = 1";
			$database->setQuery($query);
			$menuid = $database->loadResult();
			if ($menuid){
				$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&Itemid='.$menuid.'&msg='.$msg);}
				// Check the table in so it can be edited.... we are done with it anyway
				$mainframe->redirect ($link);
				// Check the table in so it can be edited.... we are done with it anyway
				//$link = 'index.php?option=com_biblestudy&view=studieslist';
				//$this->setRedirect($link, $msg);
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
		$db=& JFactory::getDBO();
		$query = "SELECT id"
		. "\nFROM #__menu"
		. "\nWHERE link ='index.php?option=com_biblestudy&view=studieslist' and published = 1";
		$db->setQuery($query);
		$menuid = $db->loadResult();
		$menureturn='';
		if ($menuid) {$menureturn = '&Itemid='.$menuid;}
		global $mainframe, $option;
		$params =& $mainframe->getPageParameters();
		$link = 'index.php?option=com_biblestudy&view=studieslist&msg='.$msg.$menureturn;
		$link = 'index.php?option=com_biblestudy&view=studieslist&Itemid='.$menureturn.'&msg='.$msg;

		// Check the table in so it can be edited.... we are done with it anyway
		$mainframe->redirect ($link);
		//$this->setRedirect( 'index.php?option=com_biblestudy&view=studieslist', $msg );
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
		global $mainframe, $option;
		$db=& JFactory::getDBO();
		$query = "SELECT id"
		. "\nFROM #__menu"
		. "\nWHERE link ='index.php?option=com_biblestudy&view=studieslist' and published = 1";
		$db->setQuery($query);
		$menuid = $db->loadResult();
		$menureturn='';
		if ($menuid) {$menureturn = '&Itemid='.$menuid;}
		//$params =& $mainframe->getPageParameters();
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.$menureturn);
		//$link = 'index.php?option=com_biblestudy&view=studieslist&Itemid='.$menureturn.'&msg='.$msg;

		// Check the table in so it can be edited.... we are done with it anyway
		$mainframe->redirect ($link);
		//$this->setRedirect( 'index.php?option=com_biblestudy&view=studieslist' );
	}


	function unpublish()
	{
		global $mainframe;
		$db=& JFactory::getDBO();
		$query = "SELECT id"
		. "\nFROM #__menu"
		. "\nWHERE link ='index.php?option=com_biblestudy&view=studieslist' and published = 1";
		$db->setQuery($query);
		$menuid = $db->loadResult();
		$menureturn='';
		if ($menuid) {$menureturn = '&Itemid='.$menuid;}
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
		}

		$model = $this->getModel('mediafilesedit');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}
		global $mainframe, $option;
		$params =& $mainframe->getPageParameters();
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.$menureturn);
		//$link = 'index.php?option=com_biblestudy&view=studieslist'.$menureturn.'&msg='.$msg;

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
		$db=& JFactory::getDBO();
		$query = "SELECT id"
		. "\nFROM #__menu"
		. "\nWHERE link ='index.php?option=com_biblestudy&view=studieslist' and published = 1";
		$db->setQuery($query);
		$menuid = $db->loadResult();
		$menureturn='';
		if ($menuid) {$menureturn = '&Itemid='.$menuid;}
		$msg = JText::_( 'Operation Cancelled' );
		global $mainframe, $option;
		$params =& $mainframe->getPageParameters();
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.$menureturn);
		//$link = 'index.php?option=com_biblestudy&view=studieslist'.$menureturn.'&msg='.$msg;

		// Check the table in so it can be edited.... we are done with it anyway
		$mainframe->redirect ($link);
		//$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafileslist', $msg );
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

		$query = "SELECT id"
		. "\nFROM #__menu"
		. "\nWHERE link ='index.php?option=com_biblestudy&view=studieslist' and published = 1";
		$db->setQuery($query);
		$menuid = $db->loadResult();
		$menureturn='';
		if ($menuid) {$menureturn = '&Itemid='.$menuid;}
		$filename = $file['name'];
		if ($filename == 'index.htm'){
			$mainframe->redirect("index.php?option=$option&view=mediafileslist".$menureturn, "File of this type not allowed.");
			return;
		}
		if ($filename == 'index.html'){
			$mainframe->redirect("index.php?option=$option&view=mediafileslist".$menureturn, "File of this type not allowed.");
			return;
		}
		if ($filename == 'index.php'){
			$mainframe->redirect("index.php?option=$option&view=mediafileslist".$menureturn, "File of this type not allowed.");
			return;
		}
		if(isset($file) && is_array($file) && $file['name'] != '')
		{
			$fullfilename = JPATH_SITE.$folderpath. $file['name'];
			$filename = $file['name'];
			jimport('joomla.filesystem.file');
			 
			 
			if (JFile::exists($fullfilename)) {
				$mainframe->redirect("index.php?option=$option&view=mediafileslist".$menureturn, "Upload failed, file already exists.");
				return;
			}

			if (!JFile::upload($file['tmp_name'], $fullfilename)) {
				$mainframe->redirect("index.php?option=$option&view=mediafileslist".$menureturn, "Upload failed, check to make sure that /components/$option/calendars exists.");
				return;
			}
			 
		}
	}

}
?>
