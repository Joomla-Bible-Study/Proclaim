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

class biblestudyControllerMessagetypeEdit extends controllerClass
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	
    protected $view_list = 'messagetypelist';
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
	function legacyEdit()
	{
		JRequest::setVar( 'view', 'messagetypeedit' );
		JRequest::setVar( 'layout', 'form'  );
		JRequest::setVar('hidemainmenu', 1);

		parent::display();
	}

	/**
	 * save a record (and redirect to main page)
	 * @return void
	 */
	function legacySave()
	{
		$model = $this->getModel('messagetypeedit');

		if ($model->store($post)) {
			$msg = JText::_( 'JBS_MST_MESSAGETYPE_SAVED' );
		} else {
			$msg = JText::_( 'JBS_MST_ERROR_SAVING_MESSAGETYPE' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_biblestudy&view=messagetypelist';
		$this->setRedirect($link, $msg);
	}
	
	/**
	 * apply a record
	 * @return void
	 */
	function legacyApply()
	{
		$model = $this->getModel('messagetypeedit');
		$cid 	= JRequest::getVar( 'id', 1, 'post', 'int' );
		if ($model->store($post)) {
			$msg = JText::_( 'JBS_MST_MESSAGETYPE_SAVED' );
		} else {
			$msg = JText::_( 'JBS_MST_ERROR_SAVING_MESSAGETYPE' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_biblestudy&controller=messagetypeedit&task=edit&cid[]='.$cid.'';
		$this->setRedirect($link, $msg);
	}

	/**
	 * remove record(s)
	 * @return void
	 */
	function legacyRemove()
	{
		$model = $this->getModel('messagetypeedit');
		if(!$model->delete()) {
			$msg = JText::_( 'JBS_MST_ERROR_DELETING_MESSAGETYPE' );
		} else {
			$msg = JText::_( 'JBS_MST_MESSAGETYPE_DELETED' );
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=messagetypelist', $msg );
	}
function legacyPublish()
	{
		$mainframe =& JFactory::getApplication();

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'JBS_CMN_SELECT_ITEM_PUBLISH' ) );
		}

		$model = $this->getModel('messagetypeedit');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=messagetypelist' );
	}


	function legacyUnpublish()
	{
		$mainframe =& JFactory::getApplication();

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'JBS_CMN_SELECT_ITEM_UNPUBLISH' ) );
		}

		$model = $this->getModel('messagetypeedit');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=messagetypelist' );
	}
	/**
	 * cancel editing a record
	 * @return void
	 */
	function legacyCancel()
	{
		$msg = JText::_( 'JBS_CMN_OPERATION_CANCELLED' );
		$this->setRedirect( 'index.php?option=com_biblestudy&view=messagetypelist', $msg );
	}
}
?>