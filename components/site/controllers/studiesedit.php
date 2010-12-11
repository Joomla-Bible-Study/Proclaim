<?php
defined('_JEXEC') or die();
jimport('joomla.application.component.controller');
class biblestudyControllerstudiesedit extends JController
{
	function __construct() {
		$user =& JFactory::getUser();
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
		$params =& $mainframe->getPageParameters();
		
		//$model = $this->getModel('studiesedit');
		//$templatemenuid = $params->get('templatemenuid');
		
        require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.admin.class.php');
        $admin = new JBSAdmin();
        $allow = $admin->getPermission();
        if (!$allow)
        {
            return JError::raiseError('403', JText::_('JBS_CMN_ACCESS_FORBIDDEN'));
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
		
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
		//$params =& $mainframe->getPageParameters();
		$model = $this->getModel('studiesedit');
		$admin=& $this->get('Admin');
		$admin_params = new JParameter($admin[0]->params);
		$model->_data = JRequest::get('post');
		if ($model->store()) {
			$msg = JText::_( 'JBS_STY_STUDY_SAVED' );
		} else {
			$msg = JText::_( 'JBS_STY_ERROR_SAVING_STUDY' );
		}
		//$params =& $mainframe->getPageParameters();
		$new = JRequest::getVar('new', '0', 'post', 'int' );
		if ($new > 0){
			$link = 'index.php?option=com_biblestudy&controller=mediafilesedit&view=mediafilesedit&layout=form&new='.$new;
			$mainframe->redirect (str_replace("&amp;","&",$link));
		}
		
		$templatemenuid = JRequest::getVar('templatemenuid', 1, 'get', 'int');
		if (!$templatmenuid) {$templatemenuid = 1;}
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.'&templatemenuid='.$templatemenuid.'&Itemid=1');
		// Check the table in so it can be edited.... we are done with it anyway
		$mainframe->redirect (str_replace("&amp;","&",$link));
	}

	/**
	 * remove record(s)
	 * @return void
	 */
	function remove()
	{
		$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
		$model = $this->getModel('studiesedit');
		if(!$model->delete()) {
			$msg = JText::_( 'JBS_STY_ERROR_DELETING_STUDY' );
		} else {
			$msg = JText::_( 'JBS_STY_STUDY_DELETED' );
		}
		//$params =& $mainframe->getPageParameters();
		$templatemenuid = JRequest::getVar('templatemenuid', 1, 'get', 'int');
		if (!$templatmenuid) {$templatemenuid = 1;}
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.'&templatemenuid='.$templatemenuid.'&Itemid=1');
		// Check the table in so it can be edited.... we are done with it anyway
		$mainframe->redirect (str_replace("&amp;","&",$link));
			
	}
	function publish()
	{
		$mainframe =& JFactory::getApplication();

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'JBS_CMN_SELECT_ITEM_PUBLISH' ) );
		}

		$model = $this->getModel('studiesedit');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}
		$templatemenuid = JRequest::getVar('templatemenuid', 1, 'get', 'int');
		if (!$templatmenuid) {$templatemenuid = 1;}
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.'&templatemenuid='.$templatemenuid.'&Itemid=1');
		// Check the table in so it can be edited.... we are done with it anyway
		$mainframe->redirect (str_replace("&amp;","&",$link));
	}


	function unpublish()
	{
		$mainframe =& JFactory::getApplication();

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'JBS_CMN_SELECT_ITEM_UNPUBLISH' ) );
		}

		$model = $this->getModel('studiesedit');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}
		$templatemenuid = JRequest::getVar('templatemenuid', 1, 'get', 'int');
		if (!$templatmenuid) {$templatemenuid = 1;}
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.'&templatemenuid='.$templatemenuid.'&Itemid=1');
		// Check the table in so it can be edited.... we are done with it anyway
		$mainframe->redirect (str_replace("&amp;","&",$link));
	}

	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$mainframe =& JFactory::getApplication();
		$msg = JText::_( 'JBS_CMN_OPERATION_CANCELLED' );

		$templatemenuid = JRequest::getVar('templatemenuid', 1, 'get', 'int');
		if (!$templatmenuid) {$templatemenuid = 1;}
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&msg='.$msg.'&templatemenuid='.$templatemenuid);
		// Check the table in so it can be edited.... we are done with it anyway
		$mainframe->redirect (str_replace("&amp;","&",$link));
	}
}
?>