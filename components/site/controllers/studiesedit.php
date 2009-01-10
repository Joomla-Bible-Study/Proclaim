<?php
defined('_JEXEC') or die();

class biblestudyControllerstudiesedit extends JController
{
	function __construct() {
		$user =& JFactory::getUser();
		global $mainframe, $option;
		$params =& $mainframe->getPageParameters();
		$entry_user = $user->get('gid');
		$entry_access = ($params->get('entry_access')) ;
		$allow_entry = $params->get('allow_entry_study');
		if (!$allow_entry) {$allow_entry = 0;}
		if ($allow_entry < 1) {return JError::raiseError('403', JText::_('Access Forbidden')); }
		if (!$entry_user) { $entry_user = 0; }
		if ($allow_entry > 0) {
			if ($entry_user < $entry_access){return JError::raiseError('403', JText::_('Access Forbidden')); }
		}
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
		$model->_data = JRequest::get('post');
		if ($model->store()) {
			$msg = JText::_( 'Study Saved!' );
		} else {
			$msg = JText::_( 'Error Saving Study' );
		}
		$params =& $mainframe->getPageParameters();
		$new = JRequest::getVar('new', '0', 'post', 'int' );
		if ($new > 0){
			$link = 'index.php?option=com_biblestudy&controller=mediafilesedit&view=mediafilesedit&layout=form&new='.$new;
			$mainframe->redirect ($link);
		}
		$db=& JFactory::getDBO();
		$query = "SELECT id"
		. "\nFROM #__menu"
		. "\nWHERE link ='index.php?option=com_biblestudy&view=studieslist' and published = 1";
		$db->setQuery($query);
		$menuid = $db->loadResult();
		$menureturn='';
		if ($menuid) {$menureturn = '&Itemid='.$menuid;}
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.$menureturn);
		//$link = 'index.php?option=com_biblestudy&view=studieslist&Itemid='.$menureturn.'&msg='.$msg;

		// Check the table in so it can be edited.... we are done with it anyway
		$mainframe->redirect ($link);
	}

	/**
	 * remove record(s)
	 * @return void
	 */
	function remove()
	{
		global $mainframe, $option;
		$model = $this->getModel('studiesedit');
		if(!$model->delete()) {
			$msg = JText::_( 'Error: One or More studies Items Could not be Deleted' );
		} else {
			$msg = JText::_( 'Study or Studies Deleted' );
		}
		//$params =& $mainframe->getPageParameters();
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
			$link = JRoute::_('index.php?option='.$option.'&view=studieslist&Itemid='.$item.'&msg='.$msg);}
			//$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.$menureturn);
			//$link = 'index.php?option=com_biblestudy&view=studieslist&Itemid='.$menureturn.'&msg='.$msg;

			// Check the table in so it can be edited.... we are done with it anyway
			$mainframe->redirect ($link);
			/*if ($params->get('view_link') == 1){
			 $link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg);}
			 if ($params->get('view_link') == 2){
			 $link = 'index.php?option=com_biblestudy&view=studieslist&Itemid='.$params->get('alt_link').'&msg='.$msg;}

			 // Check the table in so it can be edited.... we are done with it anyway
			 $mainframe->redirect ($link);
			 //$this->setRedirect( 'index.php?option=com_biblestudy&view=studieslist', $msg );*/
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
		global $mainframe, $option;
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
			$link = JRoute::_('index.php?option='.$option.'&view=studieslist&Itemid='.$item.'&msg='.$msg);}
			//$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.$menureturn);
			//$link = 'index.php?option=com_biblestudy&view=studieslist&Itemid='.$menureturn.'&msg='.$msg;

			// Check the table in so it can be edited.... we are done with it anyway
			$mainframe->redirect ($link);
			/*$params =& $mainframe->getPageParameters();

			if ($params->get('view_link') == 1){
			$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg);}
			if ($params->get('view_link') == 2){
			$link = 'index.php?option=com_biblestudy&view=studieslist&Itemid='.$params->get('alt_link').'&msg='.$msg;}

			// Check the table in so it can be edited.... we are done with it anyway
			$mainframe->redirect ($link);*/
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
		global $mainframe, $option;
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
			$link = JRoute::_('index.php?option='.$option.'&view=studieslist&Itemid='.$item.'&msg='.$msg);}
			//$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.$menureturn);
			//$link = 'index.php?option=com_biblestudy&view=studieslist&Itemid='.$menureturn.'&msg='.$msg;

			// Check the table in so it can be edited.... we are done with it anyway
			$mainframe->redirect ($link);

			/*$params =& $mainframe->getPageParameters();

			if ($params->get('view_link') == 1){
			$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg);}
			if ($params->get('view_link') == 2){
			$link = 'index.php?option=com_biblestudy&view=studieslist&Itemid='.$params->get('alt_link').'&msg='.$msg;}

			// Check the table in so it can be edited.... we are done with it anyway
			$mainframe->redirect ($link);*/
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

		global $mainframe, $option;
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
			$link = JRoute::_('index.php?option='.$option.'&view=studieslist&Itemid='.$item.'&msg='.$msg);}
			//$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.$menureturn);
			//$link = 'index.php?option=com_biblestudy&view=studieslist&Itemid='.$menureturn.'&msg='.$msg;

			// Check the table in so it can be edited.... we are done with it anyway
			$mainframe->redirect ($link);
			/*$params =& $mainframe->getPageParameters();

			if ($params->get('view_link') == 1){
			$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg);}
			if ($params->get('view_link') == 2){
			$link = 'index.php?option=com_biblestudy&view=studieslist&Itemid='.$params->get('alt_link').'&msg='.$msg;}

			// Check the table in so it can be edited.... we are done with it anyway
			$mainframe->redirect ($link);*/
			//$this->setRedirect( 'index.php?option=com_biblestudy&view=studieslist', $msg );
	}
}
?>
