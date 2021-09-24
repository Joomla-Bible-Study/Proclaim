<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Site\Controller;
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use CWM\Component\Proclaim\Site\Helper\CWMMedia;

// Always load JBSM API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

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
class proclaim extends BaseController
{
	/** @var  string Media Code
	 * @since    7.0.0 */
	public $mediaCode;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *                          Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 *
	 * @throws  Exception
	 * @since    7.0.0
	 */
	public function __construct($config = array())
	{
		$this->input = Factory::getApplication()->input;

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
	 * @return  BaseController|boolean  A BaseController object to support chaining.
	 *
	 * @throws  Exception
	 * @since   7.0.0
	 */
	public function display($cachable = true, $urlparams = array())
    {
        /*
        Set the default view name and format from the Request.
        Note we are using a_id to avoid collisions with the router and the return page.
        Frontend is a bit messier than the backend.
        */
        $id = $this->input->getInt('a_id');
        $vName = $this->input->getCmd('view', 'CWMLandingPage', 'cmd');
        $this->input->set('view', $vName);
        $user = Factory::getUser();

        /*
         * This is to check and see if we need to not cache popup pages
         * With this we Look to see if things are coming from the Landing page using the vairble sendingview"
         */
        if ($user->get('id')
            || ($this->input->getMethod() === 'POST' && strpos($vName, 'form') !== false)
            || $vName === 'popup' || $vName === 'sermons'
        ) {
            $cachable = false;
        }

		// Attempt to change mysql for error in large select
		$db = Factory::getDbo();
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
		if ($vName === 'form' && !$this->checkEditId('com_proclaim.edit.message', $id))
		{
            // Somehow the person just went to the form - we don't allow that.
            throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 403);
		}

		parent::display($cachable, $safeurlparams);

		return $this;
	}

	/**
	 * Comments
	 *
	 * @return boolean|void
	 *
	 * @throws   Exception
	 * @since    7.0.0
	 */
	public function comment()
	{
		$mainframe = Factory::getApplication();

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
            $dispatcher = Joomla\CMS\Factory::getApplication()->TriggerEvent();
            $event = new Joomla\Event\Event('onCheckAnswer', [$_POST['recaptcha_response_field']]);
            $res = $dispatcher->dispatch('onCheckAnswer', $event);
		    //JPluginHelper::importPlugin('captcha');
			//$dispatcher = JEventDispatcher::getInstance();
			//$res        = $dispatcher->trigger('onCheckAnswer', $_POST['recaptcha_response_field']);

			if (!$res[0])
			{
				// What happens when the CAPTCHA was entered incorrectly
				$mess = Text::_('JBS_STY_INCORRECT_KEY');
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
				$msg = Text::_('JBS_STY_COMMENT_UNPUBLISHED');
			}
			else
			{
				$msg = Text::_('JBS_STY_COMMENT_SUBMITTED');
			}

			if (!$model->storecomment())
			{
				$msg = Text::_('JBS_STY_ERROR_SUBMITTING_COMMENT');
			}

			if ($params->get('email_comments') > 0)
			{
				$this->commentsEmail($params);
			}

			$study_detail_id = $this->input->get('study_detail_id', 0, 'INT');

			$mainframe->redirect('index.php?option=com_proclaim&id=' . $study_detail_id . '&view=sermon&t=' . $t, $msg);
		}
	}

	/**
	 * Comments Email
	 *
	 * @param   Registry  $params  To pass to the email
	 *
	 * @return void
	 *
	 * @throws   Exception
	 * @since    7.0.0
	 */
	public function commentsEmail($params)
	{
		$mainframe  = Factory::getApplication();
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
		$config            = Factory::getConfig();
		$comment_mailfrom  = $config->get('config.mailfrom');
		$comment_fromname  = $config->get('config.fromname');
		$comment_livesite  = JUri::root();
		$db                = Factory::getDbo();
		$query             = $db->getQuery(true);
		$query->select('id, studytitle, studydate')->from('#__bsms_studies')->where('id = ' . $comment_study_id);
		$db->setQuery($query);
		$comment_details    = $db->loadObject();
		$comment_title      = $comment_details->studytitle;
		$comment_study_date = $comment_details->studydate;
		$mail               = Factory::getMailer();
		$ToEmail            = $params->get('recipient', '');
		$Subject            = $params->get('subject', 'Comments');
		$FromName           = $params->get('fromname', $comment_fromname);

		if (empty($ToEmail))
		{
			$ToEmail = $comment_mailfrom;
		}

		$Body = $comment_author . ' ' . Text::_('JBS_STY_HAS_ENTERED_COMMENT') . ': ' . $comment_title .
			' - ' . $comment_study_date . ' '
			. Text::_('JBS_STY_ON') . ': ' . $comment_date;

		if ($comment_published > 0)
		{
			$Body = $Body . ' ' . Text::_('JBS_STY_COMMENT_PUBLISHED');
		}
		else
		{
			$Body = $Body . ' ' . Text::_('JBS_STY_COMMENT_NOT_PUBLISHED');
		}

		$Body = $Body . ' ' . Text::_('JBS_STY_REVIEW_COMMENTS_LOGIN') . ': ' . $comment_livesite;
		$mail->addRecipient($ToEmail);
		$mail->setSubject($Subject . ' ' . $comment_livesite);
		$mail->setBody($Body);
		$mail->Send();
	}

	/**
	 * Download
	 *
	 * @return void
	 *
	 * @since    7.0.0
	 */
	public function download()
	{
		$task = $this->input->get('task');

		if ($task === 'download')
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
	 *
	 * @since    7.0.0
	 */
	public function avplayer()
	{
		$task = $this->input->get('task', '', 'cmd');

		if ($task === 'avplayer')
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
	 *
	 * @since    7.0.0
	 */
	public function playHit()
	{
		// Check for request forgeries.
		JSession::checkToken('get') or jexit(Text::_('JINVALID_TOKEN'));

		$input    = Factory::getApplication('site');
		$getMedia = new CWMMedia;
		$getMedia->hitPlay($input->get('id', '', 'int'));

		$this->redirect = base64_decode($input->getCmd('return'));
		$this->redirect();

		return;
	}
}
