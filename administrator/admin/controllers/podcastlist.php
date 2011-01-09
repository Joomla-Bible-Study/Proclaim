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
    jimport('joomla.application.component.controlleradmin');

    abstract class controllerClass extends JControllerAdmin {

    }

} catch (Exception $e) {
    jimport('joomla.application.component.controller');

    abstract class controllerClass extends JController {

    }

}

class biblestudyControllerpodcastlist extends controllerClass
{
 /**
 * constructor (registers additional tasks to methods)
 * @return void
 */
 function __construct()
 {
  parent::__construct();

  // Register Extra tasks
  	$this->registerTask('add', 'edit' );
  }
  function edit()
 {
  JRequest::setVar( 'view', 'podcastedit' );
  JRequest::setVar( 'layout', 'form' );
  JRequest::setVar('hidemainmenu', 1);

  parent::display();
 }

/**
 * remove record(s)
 * @return void
 */
 function remove()
 {
  $model = $this->getModel('podcastedit');
  if(!$model->delete()) {
   $msg = JText::_( 'JBS_PDC_ERROR_DELETING_PODCAST' );
  } else {
   $msg = JText::_( 'JBS_PDC_PODCAST_DELETED' );
  }

  $this->setRedirect( 'index.php?option=com_biblestudy&view=podcastlist', $msg );
 }

 function writeXMLFile()
 {

	$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
	$path1 = JPATH_SITE.'/components/com_biblestudy/helpers/';
	include_once($path1.'writexml.php');


 $result= writeXML();
  if ($result)
  {

    // $mainframe->redirect('index.php?option='.$option.'&view=podcastlist', JText::_('JBS_PDC_FILES_WRITTEN').': '.$result);
     $mainframe->redirect('index.php?option='.$option.'&view=podcastlist', JText::_('JBS_PDC_NO_ERROR_REPORTED'));

  }
  else {
  // $mainframe->redirect('index.php?option='.$option.'&view=podcastlist', JText::_('JBS_CMN_OPERATION_FAILED').': '.JText::_('JBS_CMN_FAILED_OPEN_FOR_WRITE').'.');

$mainframe->redirect('index.php?option='.$option.'&view=podcastlist', JText::_('JBS_CMN_OPERATION_FAILED').': '.JText::_('JBS_PDC_ERRORS_REPORTED'));  }

 }
}
?>