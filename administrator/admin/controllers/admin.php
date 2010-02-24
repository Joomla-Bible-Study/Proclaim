<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class biblestudyControlleradmin extends JController {
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	
	function __construct()
	{
		parent::__construct();
	

		// Register Extra tasks
		$this->registerTask( 'add'  , 	'edit' );
		$this->registerTask( 'apply',    'save');
	}

	/**
	 * display the edit form
	 * @return void
	 */
	function edit()
	{
		JRequest::setVar( 'view', 'admin' );
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
		$model = $this->getModel('admin');

		if ($model->store($post)) {
			$msg = JText::_( 'Saved!' );
		} else {
			$msg = JText::_( 'Error Saving' );
		}

		switch ($this->_task) {
			case 'apply':
				$msg = JText::_( 'Changes to Admin Settings Updated! (by Apply)' );
				$cid 	= JRequest::getVar( 'id', 1, 'post', 'int' );
				$link = 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form';
				break;

			case 'save':
			default:
				$msg = JText::_( 'Data Saved!' );
				//$link = 'index.php?option=com_driver';

				// Check the table in so it can be edited.... we are done with it anyway
				$link = 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form';
				break;
		}
		
		// Check the table in so it can be edited.... we are done with it anyway
	//	$link = 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form';
		$this->setRedirect($link, $msg);
	}

	/**
	 * remove record(s)
	 * @return void
	 */
	function remove()
	{
		$model = $this->getModel('booksedit');
		if(!$model->delete()) {
			$msg = JText::_( 'Error: One or More Books Could not be Deleted' );
		} else {
			$msg = JText::_( 'Book(s) Deleted' );
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=bookslist', $msg );
	}
function publish()
	{
		global $mainframe;

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to publish' ) );
		}

		$model = $this->getModel('booksedit');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=bookslist' );
	}


	function unpublish()
	{
		global $mainframe;

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
		}

		$model = $this->getModel('booksedit');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=bookslist' );
	}
	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$msg = JText::_( 'Operation Cancelled' );
		$this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );
	}

	function updatesef()
		{
			$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
			include_once($path1.'updatesef.php');
			$update = updateSEF();
			if ($update)
			{
				$this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $update );	
			}
			else
			{
				$msg = JText::_('Update successful. No error messages generated.');
				$this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );
			}
			
		}
		
	function resetHits()
	{
		$msg = null;
		$db = JFactory::getDBO();
		$db->setQuery("UPDATE #__bsms_studies SET hits='0'");
		$reset = $db->query();
		if ($db->getErrorNum() > 0)
				{
					$error = $db->getErrorMsg();
					$msg = 'An error occured while resetting the hits: '.$error;
					$this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );
				}
		else
			{
				$updated = $db->getAffectedRows();
				$msg = JText::_('Reset successful. No error messages generated. '.$updated.' row(s) reset.');
				$this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );
			}
	}
function resetDownloads()
	{
		$msg = null;
		$db = JFactory::getDBO();
		$db->setQuery("UPDATE #__bsms_mediafiles SET downloads='0'");
		$reset = $db->query();
		if ($db->getErrorNum() > 0)
				{
					$error = $db->getErrorMsg();
					$msg = 'An error occured while resetting the downloads: '.$error;
					$this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );
				}
		else
			{
				$updated = $db->getAffectedRows();
				$msg = JText::_('Reset successful. No error messages generated. '.$updated.' row(s) reset.');
				$this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );
			}
	}
}
?>
