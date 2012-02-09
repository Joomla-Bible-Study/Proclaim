<?php
/**
 * @version $Id: studydetails.php 1 $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

//No Direct Access
defined('_JEXEC') or die;
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
	}

	/**
	 * display the edit form
	 * @return void
	 */


	function view()
	{

		//Get the params so we can set the proper view
		$model = $this->getModel('studydetails');
		$menu =& JSite::getMenu();
		$item =& $menu->getActive();
		$params 			=& $mainframe->getPageParameters();
		$t = $params->get('t');
		if (!$t){
			$t = 1;
		}
		JRequest::setVar( 't', $t, 'get');

        // Convert parameter fields to objects.
				$registry = new JRegistry;
				$registry->loadJSON($template[0]->params);
                $params = $registry;
		if ($params->get('useexpert_details') > 0)
		{
			JRequest::setVar('layout', 'custom');
		}
		else
		{
			JRequest::setVar( 'layout', 'default'  );
		}
		JRequest::setVar( 'view', 'studydetails' );

			$model->hit();

		parent::display();
	}



	function comment()
	{

	$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
	$model = $this->getModel('studydetails');
	$menu =& JSite::getMenu();
		$item =& $menu->getActive();
		$params 			=& $mainframe->getPageParameters();
		$t = $params->get('t');
		if (!$t){
			$t = 1;
		}
		JRequest::setVar( 't', $t, 'get');

        // Convert parameter fields to objects.
				$registry = new JRegistry;
				$registry->loadJSON($model->_template[0]->params);
                $params = $registry;

	$cap = 1;

	if ($params->get('use_captcha') > 0)
	{
	//Begin reCaptcha
	  require_once(JPATH_SITE .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_biblestudy' .DIRECTORY_SEPARATOR. 'assets' .DIRECTORY_SEPARATOR. 'captcha' .DIRECTORY_SEPARATOR. 'recaptchalib.php');
        $privatekey = $params->get('private_key');
  $resp = recaptcha_check_answer ($privatekey,
                                $_SERVER["REMOTE_ADDR"],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);

  if (!$resp->is_valid) {
    // What happens when the CAPTCHA was entered incorrectly
    $mess = JText::_('JBS_STY_INCORRECT_KEY');
    echo "<script language='javascript' type='text/javascript'>alert('" . $mess ."')</script>";
    echo "<script language='javascript' type='text/javascript'>window.parent.location.reload()";
    return;
    $cap = 0;
  } else {
    $cap = 1;
  }

	}

	if ($cap == 1) {
		if ($model->storecomment()) {
			$msg = JText::_( 'JBS_STY_COMMENT_SUBMITTED' );
		} else {
			$msg = JText::_( 'JBS_STY_ERROR_SUBMITTING_COMMENT' );
		}

		if ($params->get('email_comments') > 0){
		$EmailResult=$this->commentsEmail($params);
		}
		$study_detail_id = JRequest::getVar('study_detail_id', 0, 'POST', 'INT');

		$mainframe->redirect ('index.php?option=com_biblestudy&id='.$study_detail_id.'&view=studydetails&t='.$t.'&msg='.$msg, 'Comment Added');
	} // End of $cap
	}
	//Begin scripture links plugin function
	function biblegateway_link()
	{
	$return = false;
	$row->text = JRequest::getVar('scripture1');
	JPluginHelper::importPlugin('content', 'scripturelinks' );

    // Convert parameter fields to objects.
				$registry = new JRegistry;
				$registry->loadJSON($plugin->params);
                $slparams = $registry;

	$dispatcher =& JDispatcher::getInstance();
	$results = $mainframe->triggerEvent( 'onPrepareContent', array( &$row, &$params , 1));
	}
	//End of scripture links plugin function

	/**
	 * save a record (and redirect to main page)
	 * @return void
	 */
	function save()
	{

		$model = $this->getModel('studydetails');

		if ($model->store($post)) {
			$msg = JText::_( 'JBS_STY_STUDIES_SAVED' );
		} else {
			$msg = JText::_( 'JBS_STY_ERROR_SAVING_STUDIES' );
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
			$msg = JText::_( 'JBS_STY_ERROR_DELETING_STUDY' );
		} else {
			$msg = JText::_( 'JBS_STY_STUDY_DELETED' );
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=studieslist', $msg );
	}
function publish()
	{
		$mainframe =& JFactory::getApplication();

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'JBS_CMN_SELECT_ITEM_PUBLISH' ) );
		}

		$model = $this->getModel('studydetails');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=studieslist' );
	}


	function unpublish()
	{
		$mainframe =& JFactory::getApplication();

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'JBS_CMN_SELECT_ITEM_UNPUBLISH' ) );
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
		$msg = JText::_( 'JBS_CMN_OPERATION_CANCELLED' );
		$this->setRedirect( 'index.php?option=com_biblestudy&view=studieslist', $msg );
	}
	function download() {
	$abspath    = JPATH_SITE;
	require_once($abspath.DIRECTORY_SEPARATOR.'components/com_biblestudy/class.biblestudydownload.php');
	$task = JRequest::getVar('task');
	if ($task == 'download')
		{
		$downloader = new Dump_File();
		$downloader->download();
		 die;
  		}
	}
function commentsEmail($params) {
		$mainframe =& JFactory::getApplication();
		$menuitemid = JRequest::getInt( 'Itemid' );
  if ($menuitemid)
  {
    $menu = JSite::getMenu();
    $menuparams = $menu->getParams( $menuitemid );
  }
		$comment_author = JRequest::getVar('full_name', 'Anonymous', 'POST', 'WORD');
		$comment_study_id = JRequest::getVar('study_detail_id', 0, 'POST', 'INT');
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
		$comment_details = $db->loadObject();
		$comment_title = $comment_details->studytitle;
		$comment_study_date = $comment_details->studydate;
		$mail =& JFactory::getMailer();
		$ToEmail       = $params->get( 'recipient', '' );
		$Subject       = $params->get( 'subject', 'Comments' );
		$FromName       = $params->get( 'fromname', $comment_fromname );
		if (empty($ToEmail) ) $ToEmail=$comment_mailfrom;
		$Body = $comment_author.' '.JText::_('JBS_STY_HAS_ENTERED_COMMENT').': '.$comment_title.' - '.$comment_study_date.' '.JText::_('JBS_STY_ON').': '.$comment_date;
		if ($comment_published > 0){
			$Body = $Body.' '.JText::_('JBS_STY_COMMENT_PUBLISHED');
		}else{
			$Body=$Body.' '.JText::_('JBS_STY_COMMENT_NOT_PUBLISHED');
		}
		$Body = $Body.' '.JText::_('JBS_STY_REVIEW_COMMENTS_LOGIN').': '.$comment_livesite;
		$mail->addRecipient($ToEmail);
		$mail->setSubject($Subject.' '.$comment_livesite);
		$mail->setBody($Body);
		$mail->Send();
	}
}