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

		parent::display();
	}

	function copy() {
		$cid =  JRequest::getVar( 'cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		$model	=& $this->getModel('templateedit');

		if ($model->copy($cid)) {
			$msg = JText::sprintf('Template(s) have been copied');
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect('index.php?option=com_biblestudy&view=templateslist', $msg);
	}

	function save() {
		$model = $this->getModel('templateedit');
		$data = JRequest::get('post');
		if ($model->store($post)) {
			$msg = JText::_( 'Template Saved!' );
		} else {
			$msg = JText::_( 'Error Saving Template' );
		}

		switch ($this->_task) {
			case 'apply':
				$msg = JText::_( 'Changes to Template Updated! (by Apply)' );
				$cid 	= JRequest::getVar( 'id', 1, 'post', 'int' );
				$link = 'index.php?option=com_biblestudy&view=templateedit&layout=form&task=edit&cid[]='. $cid;
				break;

			case 'save':
			default:
				//$msg = JText::_( 'Data Saved!' );
				//$link = 'index.php?option=com_driver';

				// Check the table in so it can be edited.... we are done with it anyway
				$link = 'index.php?option=com_biblestudy&view=templateslist';
				break;
		}
		$this->setRedirect($link, $msg);
	}

	function publish(){
		global $mainframe;
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to publish' ) );
		}

		$model = $this->getModel('templateedit');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=templateslist' );
	}

	function unpublish(){
		global $mainframe;
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		if ($cid[0] == 1) {$msg = JText::_( 'Error: You cannot unpublish the default template' );}
		else
		{
			if (!is_array( $cid ) || count( $cid ) < 1) {
				JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
			}

			$model = $this->getModel('templateedit');
			if(!$model->publish($cid, 0)) {
				echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
			}
		}
		$this->setRedirect( 'index.php?option=com_biblestudy&view=templateslist', $msg );
	}

	function makeDefault() {
		global $mainframe;
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
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
			if ($cid[0] == 1) {$msg = JText::_( 'Error: You cannot delete the default template' );}
			else {$msg = JText::_( 'Error: One or More Templates Could not be Deleted (You cannot delete the default template)' );}
		} else {
			$msg = JText::_( 'Template(s) Deleted' );
		}
		$this->setRedirect( 'index.php?option=com_biblestudy&view=templateslist', $msg );
	}

	function cancel(){
		$msg = JText::_( 'Operation Cancelled' );
		$this->setRedirect( 'index.php?option=com_biblestudy&view=templateslist', $msg );
	}
}
?>