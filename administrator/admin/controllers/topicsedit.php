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

class biblestudyControllertopicsedit extends controllerClass
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	protected $view_list = 'topicslist';
	 
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
	function legacyedit()
	{
		JRequest::setVar( 'view', 'topicsedit' );
		JRequest::setVar( 'layout', 'form'  );
		JRequest::setVar('hidemainmenu', 1);

		parent::display();
	}

	/**
	 * save a record (and redirect to main page)
	 * @return void
	 */
	function legacysave()
	{
		$model = $this->getModel('topicsedit');

		if ($model->store($post)) {
			$msg = JText::_( 'JBS_TPC_TOPIC_SAVED' );
		} else {
			$msg = JText::_( 'JBS_TPC_ERROR_SAVING_TOPIC' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_biblestudy&view=topicslist';
		$this->setRedirect($link, $msg);
	}
	
	/**
	 * apply a record
	 * @return void
	 */
	function legacyapply()
	{
		$model = $this->getModel('topicsedit');
		$cid 	= JRequest::getVar( 'id', 1, 'post', 'int' );
		if ($model->store($post)) {
			$msg = JText::_( 'JBS_TPC_TOPIC_SAVED' );
		} else {
			$msg = JText::_( 'JBS_TPC_ERROR_SAVING_TOPIC' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_biblestudy&controller=topicsedit&task=edit&cid[]='.$cid.'';
		$this->setRedirect($link, $msg);
	}
	
	/**
	 * remove record(s)
	 * @return void
	 */
	function legacyremove()
	{
		$model = $this->getModel('topicsedit');
		if(!$model->delete()) {
			$msg = JText::_( 'JBS_TPC_ERROR_DELETING_TOPIC' );
		} else {
			$msg = JText::_( 'JBS_TPC_TOPIC_DELETED' );
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=topicslist', $msg );
	}
function legacypublish()
	{
		$mainframe =& JFactory::getApplication();

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'JBS_CMN_SELECT_ITEM_PUBLISH' ) );
		}

		$model = $this->getModel('topicsedit');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=topicslist' );
	}


	function legacyunpublish()
	{
		$mainframe =& JFactory::getApplication();

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'JBS_CMN_SELECT_ITEM_UNPUBLISH' ) );
		}

		$model = $this->getModel('topicsedit');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=topicslist' );
	}

	/**
	 * cancel editing a record
	 * @return void
	 */
	function legacycancel()
	{
		$msg = JText::_( 'JBS_CMN_OPERATION_CANCELLED' );
		$this->setRedirect( 'index.php?option=com_biblestudy&view=topicslist', $msg );
	}
}
?>