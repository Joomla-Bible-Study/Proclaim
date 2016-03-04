<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use \Joomla\Registry\Registry;

// Always load JBSM API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_biblestudy/api.php';

if (file_exists($api))
{
	require_once $api;
}

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
	 * @param   array  $config  An optional associative array of configuration settings.
	 *                          Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 */
	public function __construct($config = array())
	{
		$this->input = JFactory::getApplication()->input;

		// Article frontpage Editor pagebreak proxying:
		if ($this->input->get('view') === 'sermon' && $this->input->get('layout') === 'pagebreak')
		{
			$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
		}
		// Article frontpage Editor article proxying:
		elseif ($this->input->get('view') === 'sermons' && $this->input->get('layout') === 'modal')
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
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JControllerLegacy  A JControllerLegacy object to support chaining.
	 */
	public function display($cachable = false, $urlparams = array())
	{
		$cachable = true;

		/* Set the default view name and format from the Request.
		   Note we are using a_id to avoid collisions with the router and the return page.
		   Frontend is a bit messier than the backend. */
		$id    = $this->input->getInt('a_id');
		$vName = $this->input->get('view', 'landingpage', 'cmd');
		$this->input->set('view', $vName);

		$user = JFactory::getUser();

		if ($vName == 'popup')
		{
			$cachable = false;
		}

		if ($user->get('id') || ($_SERVER['REQUEST_METHOD'] == 'POST' && ($vName == 'archive')))
		{
			$cachable = false;
		}

		// Attempt to change mysql for error in large select
		$db = JFactory::getDBO();
		$db->setQuery('SET SQL_BIG_SELECTS=1');
		$db->execute();
		$t = $this->input->get('t', '', 'int');

		if (!$t)
		{
			$t = 1;
		}
		$this->input->set('t', $t);

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

		// Check for edit form.
		if ($vName == 'form' && !$this->checkEditId('com_biblestudy.edit.message', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			JFactory::getApplication()->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');

			return false;
		}

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
		$mainframe = JFactory::getApplication();

		$model = $this->getModel('sermon');
		$t     = $this->input->get('t');

		if (!$t)
		{
			$t = 1;
		}
		$this->input->set('t', $t);

		// Convert parameter fields to objects.
		$registry = new Registry;
		$registry->loadString($model->_template[0]->params);
		$params = $registry;
		$cap    = 1;

		if ($params->get('use_captcha') > 0)
		{
			JPluginHelper::importPlugin('captcha');
			$dispatcher = JEventDispatcher::getInstance();
			$res        = $dispatcher->trigger('onCheckAnswer', $_POST['recaptcha_response_field']);
			if (!$res[0])
			{
				// What happens when the CAPTCHA was entered incorrectly
				$mess = JText::_('JBS_STY_INCORRECT_KEY');
				echo "<script language='javascript' type='text/javascript'>alert('" . $mess . "')</script>";
				echo "<script language='javascript' type='text/javascript'>window.history.back()</script>";

				$cap = 0;

				return;
			}
		}

		if ($cap == 1)
		{
			if ($this->input->get('published', '', 'int') == 0)
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
			$study_detail_id = $this->input->get('study_detail_id', 0, 'INT');

			$mainframe->redirect('index.php?option=com_biblestudy&id=' . $study_detail_id . '&view=sermon&t=' . $t, $msg);

		} // End of $cap
	}

	/**
	 * Comments Email
	 *
	 * @param   Registry  $params  To pass to the email
	 *
	 * @return void
	 */
	public function commentsEmail($params)
	{
		$mainframe  = JFactory::getApplication();
		$menuitemid = $this->input->get('Itemid', '', 'int');

		if ($menuitemid)
		{
			$menu       = $mainframe->getMenu();
			$menuparams = $menu->getParams($menuitemid);
		}
		$comment_author    = $this->input->get('full_name', 'Anonymous', 'WORD');
		$comment_study_id  = $this->input->get('study_detail_id', 0, 'INT');
		$comment_published = $this->input->get('published', 0, 'INT');
		$comment_date      = $this->input->get('comment_date', 0, 'WORD');
		$config            = JFactory::getConfig();
		$comment_mailfrom  = $config->get('config.mailfrom');
		$comment_fromname  = $config->get('config.fromname');
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
		$Body = $comment_author . ' ' . JText::_('JBS_STY_HAS_ENTERED_COMMENT') . ': ' . $comment_title .
			' - ' . $comment_study_date . ' '
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
		$task = $this->input->get('task');

		if ($task == 'download')
		{
			$mid        = $this->input->get('mid', '0', 'int');
			$downloader = new JBSMDownload;
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
		$task = $this->input->get('task', '', 'cmd');

		if ($task == 'avplayer')
		{
			$mediacode       = $this->input->get('code', '', 'string');
			$this->mediaCode = $mediacode;
			echo $mediacode;

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
		$input    = new JInput;
		$getMedia = new JBSMMedia;
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
		// JRequest::checktoken() or jexit('Invalid Token');
		$option = $this->input->get('option', '', 'cmd');
		jimport('joomla.filesystem.file');

		// Get the server and folder id from the request
		$serverid = $this->input->get('upload_server', '', 'int');
		$folderid = $this->input->get('upload_folder', '', 'int');
		$app      = JFactory::getApplication();
		$app->setUserState($option . 'serverid', $serverid);
		$app->setUserState($option . 'folderid', $folderid);
		$form     = $this->input->get('jform', '', 'array');
		$returnid = $form['id'];

		// Get temp file details
		$temp        = JBSMUpload::gettempfile();
		$temp_folder = JBSMUpload::gettempfolder();
		$tempfile    = $temp_folder . $temp;

		// Get path and abort if none
		$layout = $this->input->get('layout', '', 'string');

		if ($layout == 'modal')
		{
			$url = 'index.php?option=' . $option . '&view=mediafileform&task=edit&tmpl=component&layout=modal&a_id=' . $returnid;
		}
		else
		{
			$url = 'index.php?option=' . $option . '&view=mediafileform&task=edit&a_id=' . $returnid;
		}
		$path = JBSMUpload::getpath($url, $tempfile);

		// Check file type is allowed
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

		JBSMUpload::deletetempfile($tempfile);

		if ($layout == 'modal')
		{
			$this->setRedirect('index.php?option=' . $option . '&view=mediafileform&task=edit&tmpl=component&layout=modal&a_id=' . $returnid, $uploadmsg);
		}
		else
		{
			$this->setRedirect('index.php?option=' . $option . '&view=mediafileform&task=edit&a_id=' . $returnid, $uploadmsg);
		}
	}

	/**
	 * Upload function
	 *
	 * @return void
	 */
	public function upload()
	{
		$option    = $this->input->get('option', '', 'cmd');
		$uploadmsg = '';
		$serverid  = $this->input->get('upload_server', '', 'int');
		$folderid  = $this->input->get('upload_folder', '', 'int');
		$form      = $this->input->get('jform', array(), 'array');
		$layout    = $this->input->get('layout', '', 'string');
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
		}
		$app = JFactory::getApplication();
		$app->setUserState($option . 'fname', $file['name']);
		$app->setUserState($option . 'size', $file['size']);
		$app->setUserState($option . 'serverid', $serverid);
		$app->setUserState($option . 'folderid', $folderid);

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
