<?php
/**
 * Folders Edit Controller for Bible Study Component
 * 
 
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Folders Edit Controller
 *
 */
class biblestudyControllerfoldersedit extends JController
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask( 'add'  , 	'edit' );
	}

	/**
	 * display the edit form
	 * @return void
	 */
	function edit()
	{
		JRequest::setVar( 'view', 'foldersedit' );
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
		$model = $this->getModel('foldersedit');
		
		if ($model->store($post)) {
			$msg = JText::_( 'JBS_FLD_FOLDER_SAVED' );
		} else {
			$msg = JText::_( 'JBS_FLD_ERROR_SAVING_FOLDER' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_biblestudy&view=folderslist';
		$this->setRedirect($link, $msg);
	}

	/**
	 * apply a record
	 * @return void
	 */
	function apply()
	{
		$model = $this->getModel('foldersedit');
		$cid 	= JRequest::getVar( 'id', 1, 'post', 'int' );		
		if ($model->store($post)) {
			$msg = JText::_( 'JBS_FLD_FOLDER_SAVED' );
		} else {
			$msg = JText::_( 'JBS_FLD_ERROR_SAVING_FOLDER' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_biblestudy&controller=foldersedit&task=edit&cid[]='.$cid.'';
		$this->setRedirect($link, $msg);
	}

	/**
	 * remove record(s)
	 * @return void
	 */
	function remove()
	{
		$model = $this->getModel('foldersedit');
		if(!$model->delete()) {
			$msg = JText::_( 'JBS_FLD_ERROR_DELETING_FOLDER' );
		} else {
			$msg = JText::_( 'JBS_FLD_FOLDERS_DELETED' );
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=folderslist', $msg );
	}
function publish()
	{
		$mainframe =& JFactory::getApplication();

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'JBS_CMN_SELECT_ITEM_PUBLISH' ) );
		}

		$model = $this->getModel('foldersedit');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=folderslist' );
	}


	function unpublish()
	{
		$mainframe =& JFactory::getApplication();

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'JBS_CMN_SELECT_ITEM_UNPUBLISH' ) );
		}

		$model = $this->getModel('foldersedit');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=folderslist' );
	}

	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$msg = JText::_( 'JBS_CMN_OPERATION_CANCELLED' );
		$this->setRedirect( 'index.php?option=com_biblestudy&view=folderslist', $msg );
	}
}
?>