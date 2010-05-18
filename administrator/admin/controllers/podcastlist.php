<?php

/**
 * @author Joomla Bible Study
 * @copyright 2010
 * @desc Controller for the podcast list.
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class biblestudyControllerpodcastlist extends JController
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

 function writeXMLFile()
 {

	global $mainframe, $option;
	$path1 = JPATH_SITE.'/components/com_biblestudy/helpers/';
	include_once($path1.'writexml.php');

	
 $result= writeXML(); 
  if ($result)
  {
   
     $mainframe->redirect('index.php?option='.$option.'&view=podcastlist', JText::_('File(s) written: '.$result));
    
  }
  else {
   $mainframe->redirect('index.php?option='.$option.'&view=podcastlist', JText::_('Operation Failed').': '.JText::_('Failed to open file for writing.'));
  }
  
 }
}
?>