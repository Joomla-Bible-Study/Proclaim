<?php

/**
 * @version     $Id: podcastedit.php 1466 2011-01-31 23:13:03Z bcordis $
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
  //	$this->registerTask( 'add' , 'edit', 'WriteXMLFile' );

 }


 function writeXMLFile()
 {

	$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
	$path1 = JPATH_SITE.'/components/com_biblestudy/helpers/';
	include_once($path1.'writexml.php');
	include_once($path1.'helper.php');
	$model = $this->getModel('podcastedit');
	$admin_params = getAdminsettings();
	
 $result= writeXML();
  if ($result)
  {
    $mainframe->redirect('index.php?option='.$option.'&view=podcastlist', JText::_('JBS_PDC_NO_ERROR_REPORTED'));
  

  }
  else {
   $mainframe->redirect('index.php?option='.$option.'&view=podcastlist', JText::_('JBS_CMN_OPERATION_FAILED').': '.JText::_('JBS_CMN_FAILED_OPEN_FOR_WRITE').'.');
  }

 }

}
?>