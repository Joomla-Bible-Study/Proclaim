<?php

/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();


//Joomla 1.6 <-> 1.5 Branch
try {
    jimport('joomla.application.component.controllerform');

    abstract class controllerClass extends JControllerForm {

    }

} catch (Exception $e) {
    jimport('joomla.application.component.controller');

    abstract class controllerClass extends JController {

    }

}

class biblestudyControllermimetypeedit extends controllerClass
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	
    protected $view_list = 'mimetypelist';
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
		JRequest::setVar( 'view', 'mimetypeedit' );
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
		$model = $this->getModel('mimetypeedit');

		if ($model->store($post)) {
			$msg = JText::_( 'JBS_MMT_MIMETYPE_SAVED' );
		} else {
			$msg = JText::_( 'JBS_MMT_ERROR_SAVING_MIMETYPE' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_biblestudy&view=mimetypelist';
		$this->setRedirect($link, $msg);
	}
	
	/**
	 * apply a record
	 * @return void
	 */
	function legacyApply()
	{
		$model = $this->getModel('mimetypeedit');
		$cid 	= JRequest::getVar( 'id', 1, 'post', 'int' );
		if ($model->store($post)) {
			$msg = JText::_( 'JBS_MMT_MIMETYPE_SAVED' );
		} else {
			$msg = JText::_( 'JBS_MMT_ERROR_SAVING_MIMETYPE' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_biblestudy&controller=mimetypeedit&task=edit&cid[]='.$cid.'';
		$this->setRedirect($link, $msg);
	}
	
	/**
	 * remove record(s)
	 * @return void
	 */
	function legacyRemove()
	{
		$model = $this->getModel('mimetypeedit');
		if(!$model->delete()) {
			$msg = JText::_( 'JBS_MMT_ERROR_DELETING_MIMETYPE' );
		} else {
			$msg = JText::_( 'JBS_MMT_MIMETYPE_DELETED' );
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=mimetypelist', $msg );
	}
function legacyPublish()
	{
		$mainframe =& JFactory::getApplication();

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'JBS_CMN_SELECT_ITEM_PUBLISH' ) );
		}

		$model = $this->getModel('mimetypeedit');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=mimetypelist' );
	}


	function legacyUnpublish()
	{
		$mainframe =& JFactory::getApplication();

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'JBS_CMN_SELECT_ITEM_UNPUBLISH' ) );
		}

		$model = $this->getModel('mimetypeedit');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=mimetypelist' );
	}
	/**
	 * cancel editing a record
	 * @return void
	 */
	function legacyCancel()
	{
		$msg = JText::_( 'JBS_CMN_OPERATION_CANCELLED' );
		$this->setRedirect( 'index.php?option=com_biblestudy&view=mimetypelist', $msg );
	}
}
?>