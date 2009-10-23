<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();


class biblestudyControllershareedit extends JController
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
		JRequest::setVar( 'view', 'shareedit' );
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
		$model = $this->getModel('shareedit');

		if ($model->store($post)) {
			$msg = JText::_( 'Saved!' );
		} else {
			$msg = JText::_( 'Error Saving' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_biblestudy&view=sharelist';
		$this->setRedirect($link, $msg);
	}

	/**
	 * remove record(s)
	 * @return void
	 */
	function remove()
	{
		$model = $this->getModel('shareedit');
		if(!$model->delete()) {
			$msg = JText::_( 'Error: One or More Social Networks Could not be Deleted' );
		} else {
			$msg = JText::_( 'Social Network(s) Deleted' );
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=sharelist', $msg );
	}
function publish()
	{
		global $mainframe;

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to publish' ) );
		}

		$model = $this->getModel('shareedit');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=sharelist' );
	}


	function unpublish()
	{
		global $mainframe;

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
		}

		$model = $this->getModel('shareedit');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=sharelist' );
	}
	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$msg = JText::_( 'Operation Cancelled' );
		$this->setRedirect( 'index.php?option=com_biblestudy&view=sharelist', $msg );
	}
}
?>
