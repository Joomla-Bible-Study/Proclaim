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

class biblestudyControllerpodcastedit extends controllerClass
{
    protected $view_list = 'podcastlist';

    /**
 * constructor (registers additional tasks to methods)
 * @return void
 */
 function __construct()
 {
  parent::__construct();

  // Register Extra tasks
  	$this->registerTask( 'add' , 'edit', 'WriteXMLFile' );

 }

 /**
 * display the edit form
 * @return void
 */
 function legacyEdit()
 {
  JRequest::setVar( 'view', 'podcastedit' );
  JRequest::setVar( 'layout', 'form' );
  JRequest::setVar('hidemainmenu', 1);

  parent::display();
 }

 /**
 * save a record (and redirect to main page)
 * @return void
 */
 function legtacySave()
 {
  $model = $this->getModel('podcastedit');

  if ($model->store($post)) {
   $msg = JText::_( 'JBS_PDC_PODCAST_SAVED' );
  } else {
   $msg = JText::_( 'JBS_PDC_ERROR_SAVING_PODCAST' );
  }

  // Check the table in so it can be edited.... we are done with it anyway
  $link = 'index.php?option=com_biblestudy&view=podcastlist';
  $this->setRedirect($link, $msg);
 }
 
	/**
	 * apply a record
	 * @return void
	 */
	function legacyApply()
	{
		$model = $this->getModel('podcastedit');
		$cid 	= JRequest::getVar( 'id', 1, 'post', 'int' );
		if ($model->store($post)) {
			$msg = JText::_( 'JBS_PDC_PODCAST_SAVED' );
		} else {
			$msg = JText::_( 'JBS_PDC_ERROR_SAVING_PODCAST' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_biblestudy&controller=podcastedit&task=edit&cid[]='.$cid.'';
		$this->setRedirect($link, $msg);
	}

 /**
 * remove record(s)
 * @return void
 */
 function legacyRemove()
 {
  $model = $this->getModel('podcastedit');
  if(!$model->delete()) {
   $msg = JText::_( 'JBS_PDC_ERROR_DELETING_PODCAST' );
  } else {
   $msg = JText::_( 'JBS_PDC_PODCAST_DELETED' );
  }

  $this->setRedirect( 'index.php?option=com_biblestudy&view=podcastlist', $msg );
 }
function legacyPublish()
 {
  $mainframe =& JFactory::getApplication();

  $cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );

  if (!is_array( $cid ) || count( $cid ) < 1) {
   JError::raiseError(500, JText::_( 'JBS_CMN_SELECT_ITEM_PUBLISH' ) );
  }

  $model = $this->getModel('podcastedit');
  if(!$model->publish($cid, 1)) {
   echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
  }

  $this->setRedirect( 'index.php?option=com_biblestudy&view=podcastlist' );
 }


 function legacyUnpublish()
 {
  $mainframe =& JFactory::getApplication();

  $cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );

  if (!is_array( $cid ) || count( $cid ) < 1) {
   JError::raiseError(500, JText::_( 'JBS_CMN_SELECT_ITEM_UNPUBLISH' ) );
  }

  $model = $this->getModel('podcastedit');
  if(!$model->publish($cid, 0)) {
   echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
  }

  $this->setRedirect( 'index.php?option=com_biblestudy&view=podcastlist' );
 }
 /**
 * cancel editing a record
 * @return void
 */
 function legacyCancel()
 {
  $msg = JText::_( 'JBS_CMN_OPERATION_CANCELLED' );
  $this->setRedirect( 'index.php?option=com_biblestudy&view=podcastlist', $msg );
 }


 function writeXMLFile()
 {

	$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
	$path1 = JPATH_SITE.'/components/com_biblestudy/helpers/';
	include_once($path1.'writexml.php');
	include_once($path1.'helper.php');
	$model = $this->getModel('podcastedit');
	$admin_params = getAdminsettings();
	//$admin_params = null;
	//$admin = $this->get('Admin');
	//$admin_params = new JParameter($admin[0]->params);
	//$model =& $this->getModel();
		//$admin=& $model->getAdmin();
		//$admin_params = new JParameter($admin[0]->params);
	//$adminsettings = getAdminsettings(); //dump ($adminsettings, 'adminsettings: ');
	//$admin_params = new JParameter($adminsettings->params);
 $result= writeXML();
  if ($result)
  {
    $mainframe->redirect('index.php?option='.$option.'&view=podcastlist', JText::_('JBS_PDC_NO_ERROR_REPORTED'));
  
/*   $task = JRequest::getCmd('task');
   switch($task)
   {
    case 'apply_source':
     $mainframe->redirect('index.php?option='.$option.'&view=podcastlist', $podinfo->filename.' '.JText::_('JBS_CMN_SAVED'));
     break;

    case 'save_source':
    default:
     $mainframe->redirect('index.php?option='.$option.'&view=podcastlist', $podinfo->filename.' '.JText::_('JBS_CMN_SAVED'));
     break;
   } */
  }
  else {
   $mainframe->redirect('index.php?option='.$option.'&view=podcastlist', JText::_('JBS_CMN_OPERATION_FAILED').': '.JText::_('JBS_CMN_FAILED_OPEN_FOR_WRITE').'.');
  }

 }

}
?>