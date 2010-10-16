<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class biblestudyControllerpodcastedit extends JController
{
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
 function edit()
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
 function save()
 {
  $model = $this->getModel('podcastedit');

  if ($model->store($post)) {
   $msg = JText::_( 'Podcast Saved!' );
  } else {
   $msg = JText::_( 'Error Saving Podcast' );
  }

  // Check the table in so it can be edited.... we are done with it anyway
  $link = 'index.php?option=com_biblestudy&view=studieslist';
  $this->setRedirect($link, $msg);
 }

 /**
 * remove record(s)
 * @return void
 */
 function remove()
 {
  $model = $this->getModel('podcastedit');
  if(!$model->delete()) {
   $msg = JText::_( 'Error: One or More podcast Could not be Deleted' );
  } else {
   $msg = JText::_( 'Podcast(s) Deleted' );
  }

  $this->setRedirect( 'index.php?option=com_biblestudy&view=studieslist', $msg );
 }
function publish()
 {
  $mainframe =& JFactory::getApplication();

  $cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );

  if (!is_array( $cid ) || count( $cid ) < 1) {
   JError::raiseError(500, JText::_( 'Select an item to publish' ) );
  }

  $model = $this->getModel('podcastedit');
  if(!$model->publish($cid, 1)) {
   echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
  }

  $this->setRedirect( 'index.php?option=com_biblestudy&view=studieslist' );
 }


 function unpublish()
 {
  $mainframe =& JFactory::getApplication();

  $cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );

  if (!is_array( $cid ) || count( $cid ) < 1) {
   JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
  }

  $model = $this->getModel('podcastedit');
  if(!$model->publish($cid, 0)) {
   echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
  }

  $this->setRedirect( 'index.php?option=com_biblestudy&view=studieslist' );
 }
 /**
 * cancel editing a record
 * @return void
 */
 function cancel()
 {
  $msg = JText::_( 'Operation Cancelled' );
  $this->setRedirect( 'index.php?option=com_biblestudy&view=studieslist', $msg );
 }


 function writeXMLFile()
 {

	$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
	$path1 = JPATH_SITE.'/components/com_biblestudy/helpers/';
	include_once($path1.'writexml.php');
	include_once($path1.'helper.php');
	$model = $this->getModel('podcastedit');
	//$admin_params = getAdminsettings();
	$admin_params = null;
	//$admin = $this->get('Admin');
	//$admin_params = new JParameter($admin[0]->params);
	//$model =& $this->getModel();
		//$admin=& $model->getAdmin();
		//$admin_params = new JParameter($admin[0]->params);
	//$adminsettings = getAdminsettings(); //dump ($adminsettings, 'adminsettings: ');
	//$admin_params = new JParameter($adminsettings->params);
 $result= writeXML(); //dump ($result, 'result: ');
  if ($result)
  {
    $mainframe->redirect('index.php?option='.$option.'&view=studieslist', $result.' '.JText::_('saved'));
  }
  else {
   $mainframe->redirect('index.php?option='.$option.'&view=studieslist', JText::_('Operation Failed').': '.JText::_('Failed to open file for writing.'));
  }

 }

}
?>