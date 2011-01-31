<?php

/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

    jimport('joomla.application.component.controllerform');

    abstract class controllerClass extends JControllerForm {

    }

class biblestudyControllerbooksedit extends controllerClass {
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
		JRequest::setVar( 'view', 'booksedit' );
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
		$model = $this->getModel('booksedit');

		if ($model->store($post)) {
			$msg = JText::_( 'JBS_BOK_BOOK_SAVED' );
		} else {
			$msg = JText::_( 'JBS_BOK_ERROR_SAVING_BOOK' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_biblestudy&view=bookslist';
		$this->setRedirect($link, $msg);
	}

	/**
	* apply rcord
	* @return Void
	*/
	function apply()
	{
		$model = $this->getModel('booksedit');
		$cid 	= JRequest::getVar( 'id', 1, 'post', 'int' );

		if ($model->store($post)) {
			$msg = JText::_( 'JBS_BOK_BOOK_SAVED' );
		} else {
			$msg = JText::_( 'JBS_BOK_ERROR_SAVING_BOOK' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_biblestudy&controller=booksedit&task=edit&cid[]='.$cid.'';
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
			$msg = JText::_( 'JBS_BOK_ERROR_DELETING_BOOK' );
		} else {
			$msg = JText::_( 'JBS_BOK_BOOKS_DELETED' );
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=bookslist', $msg );
	}
function publish()
	{
		$mainframe =& JFactory::getApplication();

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'JBS_CMN_SELECT_ITEM_PUBLISH' ) );
		}

		$model = $this->getModel('booksedit');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=bookslist' );
	}


	function unpublish()
	{
		$mainframe =& JFactory::getApplication();

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'JBS_CMN_SELECT_ITEM_UNPUBLISH' ) );
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
		$msg = JText::_( 'JBS_CMN_OPERATION_CANCELLED' );
		$this->setRedirect( 'index.php?option=com_biblestudy&view=bookslist', $msg );
	}
}
?>