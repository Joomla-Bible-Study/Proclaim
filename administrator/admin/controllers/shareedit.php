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


class biblestudyControllershareedit extends controllerClass
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	 protected $view_list = 'sharelist';
	 
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
		JRequest::setVar( 'view', 'shareedit' );
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
		$model = $this->getModel('shareedit');

		if ($model->store($post)) {
			$msg = JText::_( 'JBS_CMN_SAVED' );
		} else {
			$msg = JText::_( 'JBS_CMN_ERROR_SAVING' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_biblestudy&view=sharelist';
		$this->setRedirect($link, $msg);
	}
		
	/**
	 * apply a record
	 * @return void
	 */
	function legacyApply()
	{
		$model = $this->getModel('shareedit');
		$cid 	= JRequest::getVar( 'id', 1, 'post', 'int' );
		if ($model->store($post)) {
			$msg = JText::_( 'JBS_CMN_SAVED' );
		} else {
			$msg = JText::_( 'JBS_CMN_ERROR_SAVING' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_biblestudy&controller=shareedit&task=edit&cid[]='.$cid.'';
		$this->setRedirect($link, $msg);
	}


	/**
	 * remove record(s)
	 * @return void
	 */
	function legacyRemove()
	{
		$model = $this->getModel('shareedit');
		if(!$model->delete()) {
			$msg = JText::_( 'JBS_SHR_ERROR_DELETING_SOCIAL' );
		} else {
			$msg = JText::_( 'JBS_SHR_SOCIAL_DELETED' );
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=sharelist', $msg );
	}
function legacypublish()
	{
		$mainframe =& JFactory::getApplication();

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'JBS_CMN_SELECT_ITEM_PUBLISH' ) );
		}

		$model = $this->getModel('shareedit');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=sharelist' );
	}


	function legacyunpublish()
	{
		$mainframe =& JFactory::getApplication();

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'JBS_CMN_SELECT_ITEM_UNPUBLISH' ) );
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
	function legacycancel()
	{
		$msg = JText::_( 'JBS_CMN_OPERATION_CANCELLED' );
		$this->setRedirect( 'index.php?option=com_biblestudy&view=sharelist', $msg );
	}
}
?>