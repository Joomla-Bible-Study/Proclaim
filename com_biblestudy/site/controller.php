<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Bible Study Core Difines
 */
require_once(JPATH_ADMINISTRATOR . '/components/com_biblestudy/lib/biblestudy.defines.php');

JLoader::register('JBSMUpload', BIBLESTUDY_PATH_HELPERS . '/upload.php');
jimport('joomla.application.component.controller');

/**
 * Controller for Core BibleStudy
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyController extends JControllerLegacy
{
	/** @var  string Media Code */
	public $mediaCode;

	/**
	 * Constructor.
	 *
	 * @param   array $config   An optional associative array of configuration settings.
	 *                          Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 */
	public function __construct($config = array())
	{
		$input = JFactory::getApplication()->input;

		// Article frontpage Editor pagebreak proxying:
		if ($input->get('view') === 'sermon' && $input->get('layout') === 'pagebreak')
		{
			$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
		}
		// Article frontpage Editor article proxying:
		elseif ($input->get('view') === 'sermons' && $input->get('layout') === 'modal')
		{
			JHtml::_('stylesheet', 'system/adminlist.css', array(), true);
			$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
		}

		parent::__construct($config);
	}

	/**
	 * Display
	 *
	 * This function is provide as a default implementation, in most cases
	 * you will need to override it in your own controllers.
	 *
	 * @param   boolean $cachable   If true, the view output will be cached
	 * @param   array   $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JControllerLegacy  A JControllerLegacy object to support chaining.
	 */
	public function display($cachable = false, $urlparams = array())
	{
		$cachable = true;

		/* Set the default view name and format from the Request.
		   Note we are using a_id to avoid collisions with the router and the return page.
		   Frontend is a bit messier than the backend. */
		$input = JFactory::getApplication()->input;
		$id    = $input->getInt('a_id');
		$vName = $input->get('view', 'landingpage', 'cmd');
		$input->set('view', $vName);

		$user = JFactory::getUser();

		if ($vName == 'popup')
		{
			$cachable = false;
		}

		if ($user->get('id') ||
			($_SERVER['REQUEST_METHOD'] == 'POST' &&
				($vName == 'archive'))
		)
		{
			$cachable = false;
		}

		// Attempt to change mysql for error in large select
		$db = JFactory::getDBO();
		$db->setQuery('SET SQL_BIG_SELECTS=1');
		$db->query();
		$t = $input->get('t', '', 'int');

		if (!$t)
		{
			$t = 1;
		}
		$input->set('t', $t, 'string');

		$safeurlparams = array(
			'id'               => 'INT',
			'cid'              => 'ARRAY',
			'year'             => 'INT',
			'month'            => 'INT',
			'limit'            => 'INT',
			'limitstart'       => 'INT',
			'showall'          => 'INT',
			'return'           => 'BASE64',
			'filter'           => 'STRING',
			'filter_order'     => 'CMD',
			'filter_order_Dir' => 'CMD',
			'filter-search'    => 'STRING',
			'print'            => 'BOOLEAN',
			'lang'             => 'CMD'
		);

		// Check for edit form.TF commented this out as checkEditId was causing an error due to it having a protect member
		/*if ($vName == 'form' && !$this->checkEditId('com_biblestudy.edit.message', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			JFactory::getApplication()->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');

			return false;
		}
        */
		parent::display($cachable, $safeurlparams);

		return $this;
	}

	/**
	 * Comments
	 *
	 * @return boolean|void
	 */
	public function comment()
	{
		$input     = JFactory::getApplication()->input;
		$mainframe = JFactory::getApplication();
		$option    = $input->get('option', '', 'cmd');

		$model = $this->getModel('sermon');
		$t     = $input->get('t');

		if (!$t)
		{
			$t = 1;
		}
		$input->set('t', $t);

		// Convert parameter fields to objects.
		$registry = new JRegistry;
		$registry->loadString($model->_template[0]->params);
		$params = $registry;
		$cap    = 1;

		if ($params->get('use_captcha') > 0)
		{
			// Begin reCaptcha
			require_once(JPATH_SITE . '/media/com_biblestudy/captcha/recaptchalib.php');
			$privatekey = $params->get('private_key');
			$challenge  = $input->get('recaptcha_challenge_field', '', 'post');
			$response   = $input->get('recaptcha_response_field', '', 'string');
			$resp       = recaptcha_check_answer($privatekey, $_SERVER["REMOTE_ADDR"], $challenge, $response);

			if (!$resp->is_valid)
			{
				// What happens when the CAPTCHA was entered incorrectly
				$mess = JText::_('JBS_STY_INCORRECT_KEY');
				echo "<script language='javascript' type='text/javascript'>alert('" . $mess . "')</script>";
				echo "<script language='javascript' type='text/javascript'>window.history.back()</script>";

				$cap = 0;

				return;
			}
			else
			{
				$cap = 1;
			}
		}

		if ($cap == 1)
		{
			if ($input->get('published', '', 'int') == 0)
			{
				$msg = JText::_('JBS_STY_COMMENT_UNPUBLISHED');
			}
			else
			{
				$msg = JText::_('JBS_STY_COMMENT_SUBMITTED');
			}
			if (!$model->storecomment())
			{
				$msg = JText::_('JBS_STY_ERROR_SUBMITTING_COMMENT');
			}

			if ($params->get('email_comments') > 0)
			{
				$this->commentsEmail($params);
			}
			$study_detail_id = $input->get('study_detail_id', 0, 'INT');

			$mainframe->redirect('index.php?option=com_biblestudy&id=' . $study_detail_id . '&view=sermon&t=' . $t, $msg);

		} // End of $cap
	}

	/**
	 * Comments Email
	 *
	 * @param   string $params  To pass to the email
	 *
	 * @return void
	 */
	public function commentsEmail($params)
	{
		$input      = JFactory::getApplication()->input;
		$mainframe  = JFactory::getApplication();
		$menuitemid = $input->get('Itemid', '', 'int');

		if ($menuitemid)
		{
			$menu       = $mainframe->getMenu();
			$menuparams = $menu->getParams($menuitemid);
		}
		$comment_author    = $input->get('full_name', 'Anonymous', 'WORD');
		$comment_study_id  = $input->get('study_detail_id', 0, 'INT');
		$comment_email     = $input->get('user_email', 'No Email', 'WORD');
		$comment_text      = $input->get('comment_text', 'None', 'WORD');
		$comment_published = $input->get('published', 0, 'INT');
		$comment_date      = $input->get('comment_date', 0, 'INT');
		$comment_date      = date('Y-m-d H:i:s');
		$config            = JFactory::getConfig();
		$comment_abspath   = JPATH_SITE;
		$comment_mailfrom  = $config->getValue('config.mailfrom');
		$comment_fromname  = $config->getValue('config.fromname');
		$comment_livesite  = JURI::root();
		$db                = JFactory::getDBO();
		$query             = $db->getQuery(true);
		$query->select('id, studytitle, studydate')->from('#__bsms_studies')->where('id = ' . $comment_study_id);
		$db->setQuery($query);
		$comment_details    = $db->loadObject();
		$comment_title      = $comment_details->studytitle;
		$comment_study_date = $comment_details->studydate;
		$mail               = JFactory::getMailer();
		$ToEmail            = $params->get('recipient', '');
		$Subject            = $params->get('subject', 'Comments');
		$FromName           = $params->get('fromname', $comment_fromname);

		if (empty($ToEmail))
		{
			$ToEmail = $comment_mailfrom;
		}
		$Body = $comment_author . ' ' . JText::_('JBS_STY_HAS_ENTERED_COMMENT') . ': ' . $comment_title . ' - ' . $comment_study_date . ' '
			. JText::_('JBS_STY_ON') . ': ' . $comment_date;

		if ($comment_published > 0)
		{
			$Body = $Body . ' ' . JText::_('JBS_STY_COMMENT_PUBLISHED');
		}
		else
		{
			$Body = $Body . ' ' . JText::_('JBS_STY_COMMENT_NOT_PUBLISHED');
		}
		$Body = $Body . ' ' . JText::_('JBS_STY_REVIEW_COMMENTS_LOGIN') . ': ' . $comment_livesite;
		$mail->addRecipient($ToEmail);
		$mail->setSubject($Subject . ' ' . $comment_livesite);
		$mail->setBody($Body);
		$mail->Send();
	}

	/**
	 * Download
	 *
	 * @return void
	 */
	public function download()
	{
		$input = JFactory::getApplication()->input;
		JLoader::register('Dump_File', BIBLESTUDY_PATH_LIB . '/biblestudy.download.class.php');
		$task = $input->get('task');

		if ($task == 'download')
		{
			$mid        = $input->get('mid', '0', 'int');
			$downloader = new Dump_File;
			$downloader->download($mid);

			die;
		}
	}

	/**
	 * AV Player
	 *
	 * @return void
	 */
	public function avplayer()
	{
		$input = JFactory::getApplication()->input;
		$task  = $input->get('task', '', 'cmd');

		if ($task == 'avplayer')
		{
			$input           = new JInput;
			$mediacode       = $input->get('code', '', 'string');
			$this->mediaCode = $mediacode;

			//echo $mediacode;

			return;
		}
	}

	/**
	 * Play Hit
	 *
	 * @return void
	 */
	public function playHit()
	{
		$input = new JInput;
		JLoader::register('jbsMedia', BIBLESTUDY_PATH_LIB . '/biblestudy.media.class.php');
		$getMedia = new jbsMedia;
		$getMedia->hitPlay($input->get('id', '', 'int'));
	}

	/**
	 * This function is supposed to generate the Media Player that is requested via AJAX
	 * from the sermons view "default.php". It has not been implemented yet, so its not used.
	 *
	 * @return void
	 */
	public function inlinePlayer()
	{
		echo('{m4vremote}http://www.livingwatersweb.com/video/John_14_15-31.m4v{/m4vremote}');
	}

	/**
	 * Adds the ability to uploade with flash
	 *
	 * @return void
	 *
	 * @since 7.1.0
	 */
	public function uploadflash()
	{
		$input = JFactory::getApplication()->input;
		// JRequest::checktoken() or jexit('Invalid Token');
		$option = $input->get('option', '', 'cmd');
		jimport('joomla.filesystem.file');

		// Get the server and folder id from the request
		$serverid = $input->get('upload_server', '', 'int');
		$folderid = $input->get('upload_folder', '', 'int');
		$app      = JFactory::getApplication();
		$app->setUserState($option, 'serverid', $serverid);
		$app->setUserState($option . 'folderid', $folderid);
		$form     = $input->get('jform', '', 'array');
		$returnid = $form['id'];

		// Get temp file details
		$temp        = JBSMUpload::gettempfile();
		$temp_folder = JBSMUpload::gettempfolder();
		$tempfile    = $temp_folder . $temp;

		// Get path and abort if none
		$layout = $input->get('layout', '', 'string');

		if ($layout == 'modal')
		{
			$url = 'index.php?option=' . $option . '&view=mediafileform&task=edit&tmpl=component&layout=modal&a_id=' . $returnid;
		}
		else
		{
			$url = 'index.php?option=' . $option . '&view=mediafileform&task=edit&a_id=' . $returnid;
		}
		$path = JBSMUpload::getpath($url, $tempfile);

		// Check filetype is allowed
		$allow = JBSMUpload::checkfile($temp);

		if ($allow)
		{
			$filename = JBSMUpload::buildpath($temp, 1, $serverid, $folderid, $path, 1);


			// Process file
			$uploadmsg = JBSMUpload::processflashfile($tempfile, $filename);

			if (!$uploadmsg)
			{
				// Set folder and link entries

				$uploadmsg = JText::_('JBS_MED_FILE_UPLOADED');
			}
		}
		else
		{
			$uploadmsg = JText::_('JBS_MED_NOT_UPLOAD_THIS_FILE_EXT');
		}
		//  $podmsg = PIHelperadmin::setpods($row);
		// delete temp file

		JBSMUpload::deletetempfile($tempfile);
		$mediafileid = $input->get('id', '', 'int');

		if ($layout == 'modal')
		{
			$this->setRedirect('index.php?option=' . $option . '&view=mediafileform&task=edit&tmpl=component&layout=modal&a_id=' . $returnid, $uploadmsg);
		}
		else
		{
			$this->setRedirect('index.php?option=' . $option . '&view=mediafileform&task=edit&a_id=' . $returnid, $uploadmsg);
		}
	}

	/*
	 * Upload Flash System
	 * @return text
	 */
	/*    function upflash() {
		   jimport('joomla.filesystem.file');
			jimport('joomla.filesystem.folder');
			$serverid = JRequest::getInt('upload_server', '', 'post');
			$folderid = JRequest::getInt('upload_folder', '', 'post');
			//import joomla filesystem functions, we will do all the filewriting with joomlas functions,
			//so if the ftp layer is on, joomla will write with that, not the apache user, which might
			//not have the correct permissions
			$abspath = JPATH_SITE;
			//this is the name of the field in the html form, filedata is the default name for swfupload
			//so we will leave it as that
			$fieldName = 'Filedata';
			//any errors the server registered on uploading
			$fileError = $_FILES[$fieldName]['error'];
			if ($fileError > 0) {
				switch ($fileError) {
				   case 1:
						echo JText::_('JBS_MED_FILE_TOO_LARGE_THAN_PHP_INI_ALLOWS');
						return;

				   case 2:
						echo JText::_('JBS_MED_FILE_TO_LARGE_THAN_HTML_FORM_ALLOWS');
						return;

					case 3:
						echo JText::_('JBS_MED_ERROR_PARTIAL_UPLOAD');
						return;

					case 4:
						echo JText::_('JBS_MED_ERROR_NO_FILE');
						return;
				}
			}

			// Check for filesize
			$fileSize = $_FILES[$fieldName]['size'];
			if ($fileSize > 500000000) {
				echo JText::_('JBS_MED_FILE_BIGGER_THAN') . ' 500MB';
			}

			// Check the file extension is ok
			$fileName = $_FILES[$fieldName]['name'];
			$extOk = JBSMUpload::checkfile($fileName);
			$app = JFactory::getApplication();
			$option = JRequest::getCmd('option');
			$app->setUserState($option.'fname', $_FILES[$fieldName]['name']);
			$app->setUserState($option.'size', $_FILES[$fieldName]['size']);
			$app->setUserState($option.'serverid', $serverid);
			$app->setUserState($option.'folderid', $folderid);
			if ($extOk == false) {
				echo JText::_('JBS_MED_NOT_UPLOAD_THIS_FILE_EXT');
				return;
			}

			// The name of the file in PHP's temp directory that we are going to move to our folder
			$fileTemp = $_FILES[$fieldName]['tmp_name'];

			// Always use constants when making file paths, to avoid the possibilty of remote file inclusion

			$uploadPath = $abspath . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'swfupload' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $fileName;


			if (!JFile::upload($fileTemp, $uploadPath)) {
				echo JText::_('JBS_MED_ERROR_MOVING_FILE');
				return;
			} else {

				// success, exit with code 0 for Mac users, otherwise they receive an IO Error
				exit(0);
			}
		} */

	/**
	 * Upload function
	 *
	 * @return void
	 */
	public function upload()
	{
		$input     = JFactory::getApplication()->input;
		$option    = $input->get('option', '', 'cmd');
		$uploadmsg = '';
		$serverid  = $input->get('upload_server', '', 'int');
		$folderid  = $input->get('upload_folder', '', 'int');
		$form      = $input->get('jform', array(), 'array');
		$layout    = $input->get('layout', '', 'string');
		$returnid  = $form['id'];
		$url       = 'index.php?option=com_biblestudy&view=mediafile&id=' . $returnid;
		$path      = JBSMUpload::getpath($url, '');
		$files     = new JInputFiles;
		$file      = $files->get('uploadfile');

		// Check filetype allowed
		$allow = JBSMUpload::checkfile($file['name']);

		if ($allow)
		{
			$filename = JBSMUpload::buildpath($file, 1, $serverid, $folderid, $path);

			// Process file
			$uploadmsg = JBSMUpload::processuploadfile($file, $filename);

			if (!$uploadmsg)
			{
				$uploadmsg = JText::_('JBS_MED_FILE_UPLOADED');
			}
			$app = JFactory::getApplication();
			$app->setUserState($option . 'fname', $filename->file);
			$app->setUserState($option . 'size', $file['size']);
			$app->setUserState($option . 'serverid', $serverid);
			$app->setUserState($option . 'folderid', $folderid);
		}

		if ($layout == 'modal')
		{
			$this->setRedirect('index.php?option=' . $option . '&view=mediafileform&task=edit&tmpl=component&layout=modal&a_id=' . $returnid, $uploadmsg);
		}
		else
		{
			$this->setRedirect('index.php?option=' . $option . '&view=mediafileform&task=edit&a_id=' . $returnid, $uploadmsg);
		}
	}


}
