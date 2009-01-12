<?php
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.controller');
class biblestudyControllertemplateedit extends JController {

	function __construct() {
		parent::__construct();

		//register extra tasks
		$this->registerTask('add', 'edit');
	}

	function edit() {
		JRequest::setVar('view', 'templateedit');
		JRequest::setVar('layout', 'form');
		JRequest::setVar('hidemenu', 1);

		parent::display();
	}

	function save() {
		$model = $this->getModel('templateedit');
		$data = JRequest::get('post');
		if ($model->store($post)) {
			$msg = JText::_( 'Template Saved!' );
		} else {
			$msg = JText::_( 'Error Saving Template' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_biblestudy&view=templateslist';
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

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
		}

		$model = $this->getModel('templateedit');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=templateslist' );
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
			$msg = JText::_( 'Error: One or More Templates Could not be Deleted' );
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