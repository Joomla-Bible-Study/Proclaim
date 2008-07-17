<?php
/**
 * studies Edit Controller for Bible Study Component
 * 
 
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * studies Edit Controller
 *
 */
class biblestudyControllerstudiesedit extends JController
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
		JRequest::setVar( 'view', 'studiesedit' );
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
		global $mainframe, $option;
		$model = $this->getModel('studiesedit');

		if ($model->store($post)) {
		
			$msg = JText::_( 'Study Saved!' );
		} else {
			$msg = JText::_( 'Error Saving Study' );
		}
		$params =& $mainframe->getPageParameters();
		$new = JRequest::getVar('new', '0', 'post', 'int' );
		//if ($params->get('view_link') == 1){
		//$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg);}
		//if ($params->get('view_link') == 2){
		$link = 'index.php?option=com_biblestudy&controller=mediafilesedit&view=mediafilesedit&layout=form&new='.$new;
    	//$link = 'index.php?option=com_biblestudy&view=studieslist&Itemid='.$params->get('alt_link').'&msg='.$msg;}
		
		// Check the table in so it can be edited.... we are done with it anyway
		$mainframe->redirect ($link);
		//$link = 'index.php?option=com_biblestudy&view=studieslist&msg='.$msg;
		//$this->setRedirect($link, $msg);
	}

	/**
	 * remove record(s)
	 * @return void
	 */
	function remove()
	{
		global $mainframe;
		$model = $this->getModel('studiesedit');
		if(!$model->delete()) {
			$msg = JText::_( 'Error: One or More studies Items Could not be Deleted' );
		} else {
			$msg = JText::_( 'Study or Studies Deleted' );
		}
		$params =& $mainframe->getPageParameters();

		if ($params->get('view_link') == 1){
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg);}
		if ($params->get('view_link') == 2){
    	$link = 'index.php?option=com_biblestudy&view=studieslist&Itemid='.$params->get('alt_link').'&msg='.$msg;}
		
		// Check the table in so it can be edited.... we are done with it anyway
		$mainframe->redirect ($link);
		//$this->setRedirect( 'index.php?option=com_biblestudy&view=studieslist', $msg );
	}
function publish()
	{
		global $mainframe;

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to publish' ) );
		}

		$model = $this->getModel('studiesedit');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}
		$params =& $mainframe->getPageParameters();

		if ($params->get('view_link') == 1){
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg);}
		if ($params->get('view_link') == 2){
    	$link = 'index.php?option=com_biblestudy&view=studieslist&Itemid='.$params->get('alt_link').'&msg='.$msg;}
		
		// Check the table in so it can be edited.... we are done with it anyway
		$mainframe->redirect ($link);
		//$this->setRedirect( 'index.php?option=com_biblestudy&view=studieslist' );
	}


	function unpublish()
	{
		global $mainframe;

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
		}

		$model = $this->getModel('studiesedit');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}
		$params =& $mainframe->getPageParameters();

		if ($params->get('view_link') == 1){
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg);}
		if ($params->get('view_link') == 2){
    	$link = 'index.php?option=com_biblestudy&view=studieslist&Itemid='.$params->get('alt_link').'&msg='.$msg;}
		
		// Check the table in so it can be edited.... we are done with it anyway
		$mainframe->redirect ($link);
		//$this->setRedirect( 'index.php?option=com_biblestudy&view=studieslist' );
	}

	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		global $mainframe;
		$msg = JText::_( 'Operation Cancelled' );
		$params =& $mainframe->getPageParameters();

		if ($params->get('view_link') == 1){
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg);}
		if ($params->get('view_link') == 2){
    	$link = 'index.php?option=com_biblestudy&view=studieslist&Itemid='.$params->get('alt_link').'&msg='.$msg;}
		
		// Check the table in so it can be edited.... we are done with it anyway
		$mainframe->redirect ($link);
		//$this->setRedirect( 'index.php?option=com_biblestudy&view=studieslist', $msg );
	}
}
?>
