<?php
defined('_JEXEC') or die();
jimport('joomla.application.component.controller');
class biblestudyControllerstudiesedit extends JController
{
	function __construct() {
		$user =& JFactory::getUser();
		global $mainframe, $option;
		$params =& $mainframe->getPageParameters();
		
		//$model = $this->getModel('studiesedit');
		//$templatemenuid = $params->get('templatemenuid');
		
		$entry_user = $user->get('gid');
		$entry_access = ($params->get('entry_access')) ;
		$allow_entry = $params->get('allow_entry_study');
		if (!$allow_entry) {$allow_entry = 0;}
		//if ($allow_entry < 1) {return JError::raiseError('403', JText::_('Access Forbidden')); }
		if (!$entry_user) { $entry_user = 0; }
		if ($allow_entry > 0) {
			if ($entry_user < $entry_access){return JError::raiseError('403', JText::_('Access Forbidden')); }
		}
		//dump ($entry_user, 'entry_user: ');
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
		//$params =& $mainframe->getPageParameters();
		$model = $this->getModel('studiesedit');
		$admin=& $this->get('Admin');
		$admin_params = new JParameter($admin[0]->params);
		$model->_data = JRequest::get('post');
		if ($model->store()) {
			$msg = JText::_( 'Study Saved!' );
		} else {
			$msg = JText::_( 'Error Saving Study' );
		}
		//$params =& $mainframe->getPageParameters();
		$new = JRequest::getVar('new', '0', 'post', 'int' );
		if ($new > 0){
			$link = 'index.php?option=com_biblestudy&controller=mediafilesedit&view=mediafilesedit&layout=form&new='.$new;
			$mainframe->redirect (str_replace("&amp;","&",$link));
		}
		
		$templatemenuid = JRequest::getVar('templatemenuid', 1, 'get', 'int');
		if (!$templatmenuid) {$templatemenuid = 1;}
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.'&templatemenuid='.$templatemenuid);
		// Check the table in so it can be edited.... we are done with it anyway
		$mainframe->redirect (str_replace("&amp;","&",$link));
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
		$templatemenuid = JRequest::getVar('templatemenuid', 1, 'get', 'int');
		if (!$templatmenuid) {$templatemenuid = 1;}
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.'&templatemenuid='.$templatemenuid);
		// Check the table in so it can be edited.... we are done with it anyway
		$mainframe->redirect (str_replace("&amp;","&",$link));
			
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
		$templatemenuid = JRequest::getVar('templatemenuid', 1, 'get', 'int');
		if (!$templatmenuid) {$templatemenuid = 1;}
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.'&templatemenuid='.$templatemenuid);
		// Check the table in so it can be edited.... we are done with it anyway
		$mainframe->redirect (str_replace("&amp;","&",$link));
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
		$templatemenuid = JRequest::getVar('templatemenuid', 1, 'get', 'int');
		if (!$templatmenuid) {$templatemenuid = 1;}
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.'&templatemenuid='.$templatemenuid);
		// Check the table in so it can be edited.... we are done with it anyway
		$mainframe->redirect (str_replace("&amp;","&",$link));
	}

	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		global $mainframe;
		$msg = JText::_( 'Operation Cancelled' );

		$templatemenuid = JRequest::getVar('templatemenuid', 1, 'get', 'int');
		if (!$templatmenuid) {$templatemenuid = 1;}
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.'&templatemenuid='.$templatemenuid);
		// Check the table in so it can be edited.... we are done with it anyway
		$mainframe->redirect (str_replace("&amp;","&",$link));
	}
}
?>
