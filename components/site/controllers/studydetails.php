<?php
/**
 * studies Edit Controller for Bible Study Component
 * 
 
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted Access');
//jimport('joomla.application.componet.controller');
/**
 * studies Edit Controller
 *
 */
class biblestudyControllerstudydetails extends JController
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		//$this->registerTask( 'view' );
	}

	/**
	 * display the edit form
	 * @return void
	 */
	function view()
	{
		JRequest::setVar( 'view', 'studydetails' );
		JRequest::setVar( 'layout', 'default'  );
		//JRequest::setVar('hidemainmenu', 1);
		//update the hit count for the study
		//if(JRequest::getCmd('view') == 'studydetails')
		//{
			//$model =& $this->getModel('studydetails');
		//$table =& $this->getTable('studydetails');
			$model->hit();
		//}
		
		parent::display();
	}
	
	function comment()
	{
	global $option, $mainframe;
	$params =& $mainframe->getPageParameters();

	$model = $this->getModel('studydetails');
		// Begin captcha
			if ($this->params->get('use_captcha') == 1) {
				session_start();
				$number = JRequest::getVar('txtNumber', 'null', 'POST');
					
				if ($message != NULL)
				{
					if (md5($number) != $_SESSION['image_random_value'])
					{
						$mess = 'Incorrect Key';
						echo "<script language='javascript' type='text/javascript'>alert('" . $mess . "')</script>";
						echo "<script language='javascript' type='text/javascript'>window.history.back()</script>";
						return;
						die();
						//break;
					}
				}
			}
			
		// Finish captcha
		if ($model->storecomment()) {
			$msg = JText::_( 'Comment Submitted!' );
		} else {
			$msg = JText::_( 'Error Submitting Comment' );
		}
		if ($params->get('email_comments') > 0){
		$EmailResult=$this->commentsEmail();
		}
		$study_detail_id = JRequest::getVar('study_detail_id', 0, 'POST', 'INT');
		$mainframe->redirect ('index.php?option=com_biblestudy&id='.$study_detail_id.'&view=studydetails&task=view&msg='.$msg, 'Comment Added.');

	}
	/**
	 * save a record (and redirect to main page)
	 * @return void
	 */
	function save()
	{
		
		$model = $this->getModel('studydetails');

		if ($model->store($post)) {
			$msg = JText::_( 'studies Saved!' );
		} else {
			$msg = JText::_( 'Error Saving studies' );
		}
		
		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_biblestudy&view=studieslist';
		$this->setRedirect($link, $msg);
	}

	/**
	 * remove record(s)
	 * @return void
	 */
	function remove()
	{
		$model = $this->getModel('studydetails');
		if(!$model->delete()) {
			$msg = JText::_( 'Error: One or More studies Items Could not be Deleted' );
		} else {
			$msg = JText::_( 'studies Item(s) Deleted' );
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=studieslist', $msg );
	}
function publish()
	{
		global $mainframe;

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to publish' ) );
		}

		$model = $this->getModel('studydetails');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=studieslist' );
	}


	function unpublish()
	{
		global $mainframe;

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
		}

		$model = $this->getModel('studydetails');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=studieslist' );
	}

	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$msg = JText::_( 'Operation Cancelled' );
		$this->setRedirect( 'index.php?option=com_biblestudy&view=studieslist', $msg );
	}
	function download() {
	$abspath    = JPATH_SITE;
	require_once($abspath.DS.'components/com_biblestudy/class.biblestudydownload.php');
	$task = JRequest::getVar('task');
	if ($task == 'download')
		{
		$downloader = new Dump_File();
		$downloader->download();
		 die;
  		}
	}
function commentsEmail() {
		global $mainframe;
		$params =& $mainframe->getPageParameters();
		$comment_author = JRequest::getVar('full_name', 'Anonymous', 'POST', 'WORD');
		$comment_study_id = JRequest::getVar('study_detail_id', 0, 'POST', 'INT');
		//$comment_study_id = $this->thestudy;
		$comment_email = JRequest::getVar('user_email', 'No Email', 'POST', 'WORD');
		$comment_text = JRequest::getVar('comment_text', 'None', 'POST', 'WORD');
		$comment_published = JRequest::getVar('published', 0, 'POST', 'INT');
		$comment_date = JRequest::getVar('comment_date', 0, 'POST', 'INT');
		$comment_date = date('Y-m-d H:i:s');
		$config =& JFactory::getConfig();
		$comment_abspath    = JPATH_SITE;
		$comment_mailfrom   = $config->getValue('config.mailfrom');
		$comment_fromname   = $config->getValue('config.fromname');;
		$comment_livesite   = JURI::root();
		$db =& JFactory::getDBO();
		$query = 'SELECT id, studytitle, studydate FROM #__bsms_studies WHERE id = '.$comment_study_id;
		$db->setQuery($query);
		$comment_details = $db->loadObject($query);
		$comment_title = $comment_details->studytitle;
		$comment_study_date = $comment_details->studydate;
		$mail =& JFactory::getMailer();
		$ToEmail       = $params->get( 'recipient', '' );
		$Subject       = $params->get( 'subject', 'Comments' );
		$FromName       = $params->get( 'fromname', $comment_fromname );
		if (empty($ToEmail) ) $ToEmail=$comment_mailfrom;
		$Body = $comment_author.JText::_(' has entered a comment for the study entitled: ').$comment_title.' - '.$comment_study_date.JText::_(' on: ').$comment_date;
		if ($comment_published > 0){$Body = $Body.JText::_(' This comment has been published.');}else{$Body=$Body.JText::_(' This comment has not been published.');}
		$Body = $Body.JText::_(' You may review the comments by logging in to the site: ').$comment_livesite;
		$mail->addRecipient($ToEmail);
		$mail->setSubject($Subject.' '.$comment_livesite);
		$mail->setBody($Body);
		$mail->Send();
	}
}
?>
