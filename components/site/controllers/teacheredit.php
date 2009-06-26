<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();


class biblestudyControllerteacheredit extends JController
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
		$templatemenuid = JRequest::getVar('templatemenuid', 1, 'get', 'int');
		if (!$templatmenuid) {$templatemenuid = 1;}
		$link = JRoute::_('index.php?option=com_biblestudy&view=teacherlist&msg='.$msg.'&templatemenuid='.$templatemenuid);
		$this->setRedirect($link);
	}

	/**
	 * remove record(s)
	 * @return void
	 */
	function remove()
	{
		$model = $this->getModel('teacheredit');
		if(!$model->delete()) {
			$msg = JText::_( 'Error: One or More Teachers Could not be Deleted' );
		} else {
			$msg = JText::_( 'Teacher(s) Deleted' );
		}
		$templatemenuid = JRequest::getVar('templatemenuid', 1, 'get', 'int');
		if (!$templatmenuid) {$templatemenuid = 1;}
		$link = JRoute::_('index.php?option=com_biblestudy&view=teacherlist&msg='.$msg.'&templatemenuid='.$templatemenuid);
		$this->setRedirect( $link );
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
		$templatemenuid = JRequest::getVar('templatemenuid', 1, 'get', 'int');
		if (!$templatmenuid) {$templatemenuid = 1;}
		$link = JRoute::_('index.php?option=com_biblestudy&view=teacherlist&msg='.$msg.'&templatemenuid='.$templatemenuid);
		$this->setRedirect( $link);
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
		$templatemenuid = JRequest::getVar('templatemenuid', 1, 'get', 'int');
		if (!$templatmenuid) {$templatemenuid = 1;}
		$link = JRoute::_('index.php?option=com_biblestudy&view=teacherlist&msg='.$msg.'&templatemenuid='.$templatemenuid);
		$this->setRedirect($link);
	}
	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$msg = JText::_( 'Operation Cancelled' );
		$templatemenuid = JRequest::getVar('templatemenuid', 1, 'get', 'int');
		if (!$templatmenuid) {$templatemenuid = 1;}
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.'&templatemenuid='.$templatemenuid);
		
		$this->setRedirect($link);
	}
}
?>
