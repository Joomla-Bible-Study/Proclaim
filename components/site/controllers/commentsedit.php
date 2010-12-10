<?php
/**
 * Media Edit Controller for Bible Study Component
 * 
 
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Media Edit Controller
 *
 */
class biblestudyControllercommentsedit extends JController
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
		JRequest::setVar( 'view', 'commentsedit' );
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
		$model = $this->getModel('commentsedit');
        $mainframe =& JFactory::getApplication();
		if ($model->store($post)) {
			$msg = JText::_( 'JBS_CMT_COMMENT_SAVED' );
		} else {
			$msg = JText::_( 'JBS_CMT_ERROR_SAVING_COMMENT' );
		}

		
			$this->setRedirect( 'index.php?option=com_biblestudy&view=commentslist', $msg );
			// Check the table in so it can be edited.... we are done with it anyway
		//	$mainframe->redirect (str_replace("&amp;","&",$link));
	}

	/**
	 * remove record(s)
	 * @return void
	 */
	function remove()
	{
		$model = $this->getModel('commentsedit');
		if(!$model->delete()) {
			$msg = JText::_( 'JBS_CMT_ERROR_DELETING_ITEM' );
		} else {
			$msg = JText::_( 'JBS_CMT_ITEMS_DELETED' );
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=commentslist', $msg );
	}
function publish()
	{
		$mainframe =& JFactory::getApplication();

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'JBS_CMN_SELECT_ITEM_PUBLISH' ) );
		}

		$model = $this->getModel('commentsedit');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=commentslist' );
	}


	function unpublish()
	{
		$mainframe =& JFactory::getApplication();

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'JBS_CMN_SELECT_ITEM_UNPUBLISH' ) );
		}

		$model = $this->getModel('commentsedit');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=commentslist' );
	}

	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$mainframe =& JFactory::getApplication();
		$msg = JText::_( 'JBS_CMN_OPERATION_CANCELLED' );

		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
		$db=& JFactory::getDBO();
		$query = "SELECT id"
		. "\nFROM #__menu"
		. "\nWHERE link ='index.php?option=com_biblestudy&view=studieslist' and published = 1";
		$db->setQuery($query);
		$menuid = $db->loadResult();
		$menureturn='';
		if ($menuid) {$menureturn = '&Itemid='.$menuid;}
		$item = JRequest::getVar('Itemid');
		$link = JRoute::_('index.php?option='.$option.'&view=studieslist');
		if ($item){
			//$link = JRoute::_('index.php?option='.$option.'&view=studieslist&Itemid='.$item.'&msg='.$msg);}
			$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.$menureturn);}
			//$link = 'index.php?option=com_biblestudy&view=studieslist&Itemid='.$menureturn.'&msg='.$msg;

			// Check the table in so it can be edited.... we are done with it anyway
			$mainframe->redirect (str_replace("&amp;","&",$link));
	}
}
?>