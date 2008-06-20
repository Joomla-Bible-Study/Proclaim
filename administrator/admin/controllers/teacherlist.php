<?php
/**
 * Media Files list Controller for Bible Study Component
 * 
 
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Media Edit Controller
 *
 */
class biblestudyControllerteacherlist extends JController
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
	function manage()
	{
		$type	= JRequest::getWord('view', 'teacherlist');
		$model	= &$this->getModel( $type );
		$view	= &$this->getView( $type );

		//$ftp =& JClientHelper::setCredentialsFromRequest('ftp');
		//$view->assignRef('ftp', $ftp);

		$view->setModel( $model, true );
		$view->display();
	}

	/**
	 * display the edit form
	 * @return void
	 */
	function edit()
	{
		JRequest::setVar( 'view', 'teacheredit' );
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
		$model = $this->getModel('teacheredit');

		if ($model->store($post)) {
			$msg = JText::_( 'Teacher Saved!' );
		} else {
			$msg = JText::_( 'Error Saving Teacher' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_biblestudy&view=teacherlist';
		$this->setRedirect($link, $msg);
	}

	/**
	 * remove record(s)
	 * @return void
	 */
	function remove()
	{
		$model = $this->getModel('teacheredit');
		if(!$model->delete()) {
			$msg = JText::_( 'Error: One or More Teacher Could not be Deleted' );
		} else {
			$msg = JText::_( 'Teacher(s) Deleted' );
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=teacherlist', $msg );
	}
function publish()
	{
		global $mainframe;

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to publish' ) );
		}

		$model = $this->getModel('teacheredit');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=teacherlist' );
	}


	function unpublish()
	{
		global $mainframe;

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
		}

		$model = $this->getModel('teacheredit');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=teacherlist' );
	}

	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$msg = JText::_( 'Operation Cancelled' );
		$this->setRedirect( 'index.php?option=com_biblestudy&view=teacherlist', $msg );
	}
	
	function orderup()
	{
		// Check for request forgeries
		//JRequest::checkToken() or die( 'Invalid Token' );

		$model = $this->getModel('teacheredit');
		$model->move(-1);

		$this->setRedirect( 'index.php?option=com_biblestudy&view=teacherlist');
	}

	function orderdown()
	{
		// Check for request forgeries
		//JRequest::checkToken() or die( 'Invalid Token' );

		$model = $this->getModel('teacheredit');
		$model->move(1);

		$this->setRedirect( 'index.php?option=com_biblestudy&view=teacherlist');
	}

	function saveorder()
	{
		// Check for request forgeries
		//JRequest::checkToken() or die( 'Invalid Token' );

		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$order 	= JRequest::getVar( 'order', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		JArrayHelper::toInteger($order);

		$model = $this->getModel('teacheredit');
		$model->saveorder($cid, $order);

		$msg = 'New ordering saved';
		$this->setRedirect( 'index.php?option=com_biblestudy&view=teacherlist', $msg );
	}
}
?>
