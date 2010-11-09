<?php
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.controller');
class biblestudyControllertemplateedit extends JController {

	function __construct() {
		parent::__construct();

		//register extra tasks
		$this->registerTask('add', 'edit');
		$this->registerTask( 'apply',    'save');
	}

	function edit() {
		JRequest::setVar('view', 'templateedit');
		JRequest::setVar('layout', 'form');
		JRequest::setVar('hidemenu', 1);
		JRequest::setVar('hidemainmenu', 1);

		parent::display();
	}

	function copy() {
		$cid =  JRequest::getVar( 'cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		$model	=& $this->getModel('templateedit');

		if ($model->copy($cid)) {
                        $msg = JText::_('JBS_TPE_TEMPLATE_COPIED');
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect('index.php?option=com_biblestudy&view=templateslist', $msg);
	}

	function save() {
		$model = $this->getModel('templateedit');
		$data = JRequest::get('post');
		if ($model->store($post)) {
			$msg = JText::_( 'JBS_TPE_TEMPLATE_SAVED' );
		} else {
			$msg = JText::_( 'JBS_TPE_ERROR_SAVING_TEMPLATE' );
		}

		switch ($this->_task) {
			case 'apply':
				$msg = JText::_( 'JBS_TPE_TEMPLATE_CHANGES_UPDATED' );
				$cid 	= JRequest::getVar( 'id', 1, 'post', 'int' );
				$link = 'index.php?option=com_biblestudy&view=templateedit&layout=form&task=edit&cid[]='. $cid;
				break;

			case 'save':
			default:
			
				// Check the table in so it can be edited.... we are done with it anyway
				$link = 'index.php?option=com_biblestudy&view=templateslist';
				break;
		}
		$this->setRedirect($link, $msg);
	}

	function publish(){
		$mainframe =& JFactory::getApplication();
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'JBS_CMN_SELECT_ITEM_PUBLISH' ) );
		}

		$model = $this->getModel('templateedit');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=templateslist' );
	}

	function unpublish(){
		$mainframe =& JFactory::getApplication();
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		if ($cid[0] == 1) {$msg = JText::_( 'JBS_TPE_ERROR_NO_UNPUBLISH_DEFAULT_TEMPLATE' );}
		else
		{
			if (!is_array( $cid ) || count( $cid ) < 1) {
				JError::raiseError(500, JText::_( 'JBS_CMN_SELECT_ITEM_UNPUBLISH' ) );
			}

			$model = $this->getModel('templateedit');
			if(!$model->publish($cid, 0)) {
				echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
			}
		}
		$this->setRedirect( 'index.php?option=com_biblestudy&view=templateslist', $msg );
	}

	function makeDefault() {
		$mainframe =& JFactory::getApplication();
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'JBS_CMN_SELECT_ITEM_UNPUBLISH' ) );
		}

		$model = $this->getModel('templateedit');
		if(!$model->makeDefault($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=templateslist' );
	}
	function remove(){
		$model = $this->getModel('templateedit');
		if(!$model->delete()) {
			$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );
			if ($cid[0] == 1) {$msg = JText::_( 'JBS_TPE_ERROR_NO_DELETE_DEFAULT_TEMPLATE' );}
			else {$msg = JText::_( 'JBS_TPE_ERROR_DELETING_TEMPLATE' );}
		} else {
			$msg = JText::_( 'JBS_TPE_TEMPLATE_DELETED' );
		}
		$this->setRedirect( 'index.php?option=com_biblestudy&view=templateslist', $msg );
	}

	function cancel(){
		$msg = JText::_( 'JBS_CMN_OPERATION_CANCELLED' );
		$this->setRedirect( 'index.php?option=com_biblestudy&view=templateslist', $msg );
	}
}
?>