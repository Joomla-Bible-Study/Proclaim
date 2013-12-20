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

if (!BIBLESTUDY_CHECKREL)
{
	jimport('joomla.application.component.controllerform');
}

/**
 * Class for Sermon
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyControllerSermon extends JControllerForm
{

	/**
	 * View item
	 *
	 * @since    1.6
	 */
	protected $view_item = 'messageform';

	/**
	 * View list
	 *
	 * @since    1.6
	 */
	protected $view_list = 'messagelist';

	/**
	 * Method to add a new record.
	 *
	 * @return    boolean    True if the article can be added, false if not.
	 *
	 * @since    1.6
	 */
	public function add()
	{
		if (!parent::add())
		{
			// Redirect to the return page.
			$this->setRedirect($this->getReturnPage());
		}
	}

	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param   array $data  An array of input data.
	 *
	 * @return    boolean
	 *
	 * @since    1.6
	 */
	protected function allowAdd($data = array())
	{
		$user  = JFactory::getUser();
		$allow = null;

		if ($allow === null)
		{
			// In the absense of better information, revert to the component permissions.
			return parent::allowAdd();
		}
		else
		{
			return $allow;
		}
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array  $data  An array of input data.
	 * @param   string $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since    1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		return true;
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param   string $key  The name of the primary key of the URL variable.
	 *
	 * @return    Boolean    True if access level checks pass, false otherwise.
	 *
	 * @since    1.6
	 */
	public function cancel($key = 'a_id')
	{
		parent::cancel($key);

		// Redirect to the return page.
		$this->setRedirect($this->getReturnPage());
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @param   string $key     The name of the primary key of the URL variable.
	 * @param   string $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return    Boolean    True if access level check and checkout passes, false otherwise.
	 *
	 * @since    1.6
	 */
	public function edit($key = null, $urlVar = 'a_id')
	{
		$result = parent::edit($key, $urlVar);

		return $result;
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string $name    The model name. Optional.
	 * @param   string $prefix  The class prefix. Optional.
	 * @param   array  $config  Configuration array for model. Optional.
	 *
	 * @return    object    The model.
	 *
	 * @since    1.5
	 */
	public function getModel($name = 'Messageform', $prefix = '', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   int    $recordId  The primary key id for the item.
	 * @param   string $urlVar    The name of the URL variable for the id.
	 *
	 * @return    string    The arguments to append to the redirect URL.
	 *
	 * @since    1.6
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'a_id')
	{
		$this->input = new JInput;

		// Need to override the parent method completely.
		$tmpl   = $this->input->get('tmpl');
		$layout = $this->input->get('layout', 'edit');
		$append = '';

		// Setup redirect info.
		if ($tmpl)
		{
			$append .= '&tmpl=' . $tmpl;
		}

		$append .= '&layout=edit';

		if ($recordId)
		{
			$append .= '&' . $urlVar . '=' . $recordId;
		}

		$itemId = $this->input->getInt('Itemid');
		$return = $this->getReturnPage();
		$catId  = $this->input->getInt('catid', null, 'get');

		if ($itemId)
		{
			$append .= '&Itemid=' . $itemId;
		}

		if ($catId)
		{
			$append .= '&catid=' . $catId;
		}

		if ($return)
		{
			$append .= '&return=' . base64_encode($return);
		}

		return $append;
	}

	/**
	 * Get the return URL.
	 *
	 * If a "return" variable has been passed in the request
	 *
	 * @return    string    The return URL.
	 *
	 * @since    1.6
	 */
	protected function getReturnPage()
	{
		$return = JFactory::getApplication()->input->get('return', null, 'base64');

		if (empty($return) || !JUri::isInternal(base64_decode($return)))
		{
			return JURI::base() . 'index.php?option=com_biblestudy&view=messagelist';
		}
		else
		{
			return base64_decode($return);
		}
	}

	/**
	 * Method to save a record.
	 *
	 * @param   string $key     The name of the primary key of the URL variable.
	 * @param   string $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return    Boolean    True if successful, false otherwise.
	 *
	 * @since    1.6
	 */
	public function save($key = null, $urlVar = 'a_id')
	{
		$result = parent::save($key, $urlVar);

		// If ok, redirect to the return page.
		if ($result)
		{
			$this->setRedirect($this->getReturnPage());
		}

		return $result;
	}

	/**
	 * Comment
	 *
	 * @return NULL
	 *
	 *
	 */
	public function comment()
	{

		$mainframe = JFactory::getApplication();
		$input     = new JInput;
		$option    = $input->get('option', '', 'cmd');
		$model     = $this->getModel('sermon');
		$menu      = $mainframe->getMenu();
		$item      = $menu->getActive();
		$t         = '';

		if (!$t)
		{
			$t = 1;
		}
		$input->set('t', $t);

		// Convert parameter fields to objects.
		$registry = new JRegistry;
		$registry->loadString($model->_template[0]->params);
		$params = $registry;

		$cap = 1;

		if ($params->get('use_captcha') > 0)
		{
			// Begin reCaptcha
			require_once JPATH_SITE . DIRECTORY_SEPARATOR . 'media/com_biblestudy/captcha/recaptchalib.php';
			$privatekey = $params->get('private_key');
			$resp       = recaptcha_check_answer(
				$privatekey,
				$_SERVER["REMOTE_ADDR"],
				$_POST["recaptcha_challenge_field"],
				$_POST["recaptcha_response_field"]
			);

			if (!$resp->is_valid)
			{
				// What happens when the CAPTCHA was entered incorrectly
				$mess = JText::_('JBS_STY_INCORRECT_KEY');
				echo "<script language='javascript' type='text/javascript'>alert('" . $mess . "')</script>";
				echo "<script language='javascript' type='text/javascript'>window.parent.location.reload()</script>";

				$cap = 0;

				return null;
			}
			else
			{
				$cap = 1;
			}
		}

		if ($cap == 1)
		{
			if ($model->storecomment())
			{
				$msg = JText::_('JBS_STY_COMMENT_SUBMITTED');
			}
			else
			{
				$msg = JText::_('JBS_STY_ERROR_SUBMITTING_COMMENT');
			}

			if ($params->get('email_comments') > 0)
			{

				$this->commentsEmail($params);
			}
			$study_detail_id = $input->get('study_detail_id', 0, 'int');

			$mainframe->redirect(
				'index.php?option=com_biblestudy&id=' . $study_detail_id . '&view=sermon&t=' . $t . '&msg=' . $msg,
				'Comment Added'
			);

		} // End of $cap

	}


	/**
	 * Download system
	 *
	 * @return null
	 */
	public function download()
	{
		$input = new JInput;
		$task  = $input->get('task');
		$mid   = $input->getInt('id');

		if ($task == 'download')
		{
			$downloader = new JBSMDownload;
			$downloader->download($mid);
			die;
		}
	}

	/**
	 * Email comment out.
	 *
	 * @param   object $params  Params of to parse
	 *
	 * @return null
	 */
	public function commentsEmail($params)
	{
		$mainframe  = JFactory::getApplication();
		$input      = new JInput;
		$menuitemid = $input->get('Itemid', '', 'int');

		if ($menuitemid)
		{
			$menu       = $mainframe->getMenu();
			$menuparams = $menu->getParams($menuitemid);
		}
		$comment_author    = $input->get('full_name', 'Anonymous', 'string');
		$comment_study_id  = $input->get('study_detail_id', 0, 'int');
		$comment_email     = $input->get('user_email', 'No Email', 'string');
		$comment_text      = $input->get('comment_text', 'None', 'string');
		$comment_published = $input->get('published', 0, 'int');
		$comment_date      = $input->get('comment_date', 0, 'int');
		$comment_date      = date('Y-m-d H:i:s');
		$config            = JFactory::getConfig();
		$comment_abspath   = JPATH_SITE;
		$comment_mailfrom  = $config->get('mailfrom');
		$comment_fromname  = $config->get('fromname');

		$comment_livesite = JURI::root();
		$db               = JFactory::getDBO();
		$query            = $db->getQuery(true);
		$query->select('id, studytitle, studydate')->from('#__bsms_studies')->where('id = ' . (int) $comment_study_id);
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
		$Body = $comment_author . ' ' . JText::_(
				'JBS_STY_HAS_ENTERED_COMMENT'
			) . ': ' . $comment_title . ' - ' . $comment_study_date . ' ' . JText::_('JBS_STY_ON') . ': ' . $comment_date;

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

}
