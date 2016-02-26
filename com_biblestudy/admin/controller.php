<?php
/**
 * Admin Controller
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Always load JBSM API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_biblestudy/api.php';

if (file_exists($api))
{
	require_once $api;
}

/**
 * JController for BibleStudy Admin class
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyController extends JControllerLegacy
{

	/**
	 * Default view var.
	 *
	 * @var string
	 */
	protected $default_view = 'cpanel';

	/**
	 * Core Display
	 *
	 * @param   boolean  $cachable   Cachable system
	 * @param   boolean  $urlparams  Url params
	 *
	 * @return  JController        This object to support chaining.
	 */
	public function display($cachable = false, $urlparams = false)
	{

		$app = JFactory::getApplication();

		$view   = $app->input->getCmd('view', 'cpanel');
		$layout = $app->input->getCmd('layout', 'default');

		if ($layout !== 'modal')
		{
			JBSMBibleStudyHelper::addSubmenu($view);
		}

		$jbsstate = JBSMDbHelper::getInstallState();

		if ($jbsstate)
		{
			$cache = new JCache(array('defaultgroup' => 'default'));
			$cache->clean();
			$app->input->set('view', 'install');
			$app->input->set('scanstate', 'start');
		}

		if (!$view)
		{
			$app->input->set('view ', 'cpanel');
		}
		if ($view == 'admin')
		{
			$tool = $app->input->get('tooltype', '', 'post');

			if ($tool)
			{
				switch ($tool)
				{
					case 'players':
						$player = $this->changePlayers();
						$this->setRedirect('index.php?option=com_biblestudy&view=admin&' . JSession::getFormToken() . '=1', $player);
						break;

					case 'popups':
						$popups = $this->changePopup();
						$this->setRedirect('index.php?option=com_biblestudy&view=admin&' . JSession::getFormToken() . '=1', $popups);
						break;
				}
			}
		}

		return parent::display();
	}

	/**
	 * Change Players
	 *
	 * @return string
	 *
	 * @todo need to update this to new JBSM
	 */
	public function changePlayers()
	{
		$app  = JFactory::getApplication();
		$db   = JFactory::getDbo();
		$msg  = null;
		$data = $app->input->get('jform', array(), 'post  ');
		$from = $data['params']['from'];
		$to   = $data['params']['to'];

		switch ($from)
		{
			case '100':
				$query = $db->getQuery(true);
				$query->update('#__bsms_mediafiles')
					->set($db->qn('player') . ' = ' . $db->q($to))
					->where($db->qn('player') . ' = ' . (int) 0 . ' OR ' . $db->qn('player') . ' = ' . (int) 100 . ' OR ' . $db->qn('player') . ' IS NULL');
				break;

			default:
				$query = $db->getQuery(true);
				$query->update('#__bsms_mediafiles')
					->set($db->qn('player') . ' = ' . $db->q($to))
					->where($db->qn('player') . ' = ' . $db->q($from));
				break;
		}
		$db->setQuery($query);

		if (!$db->execute())
		{
			$msg = JText::_('JBS_ADM_ERROR_OCCURED') . ' ' . $db->getErrorMsg();
		}
		else
		{
			$num_rows = $db->getAffectedRows();
			$msg      = JText::_('JBS_CMN_OPERATION_SUCCESSFUL') . '<br /> ' . JText::_('JBS_ADM_AFFECTED_ROWS') . ': ' . $num_rows;
		}

		return $msg;
	}

	/**
	 * Change Popup
	 *
	 * @return string
	 *
	 * @todo need to update this for new JBSM
	 */
	public function changePopup()
	{
		$app  = JFactory::getApplication();
		$db   = JFactory::getDbo();
		$msg  = null;
		$data = $app->input->get('jform', array(), 'post');
		$from = $data['params']['pFrom'];
		$to   = $data['params']['pTo'];

		$query = $db->getQuery(true);
		$query->update('#__bsms_mediafiles')
			->set($db->quoteName('popup') . ' = ' . $db->quote($to))
			->where($db->qn('popup') . ' = ' . $db->quote($from));
		$db->setQuery($query);

		if (!$db->execute())
		{
			$msg = JText::_('JBS_ADM_ERROR_OCCURED ') . ' ' . $db->getErrorMsg();
		}
		else
		{
			$num_rows = $db->getAffectedRows();
			$msg      = JText::_('JBS_CMN_OPERATION_SUCCESSFUL') . '<br /> ' . JText::_('JBS_ADM_AFFECTED_ROWS') . ': ' . $num_rows;
		}

		return $msg;
	}

	/**
	 * Write the XML file
	 *
	 * @return null
	 */
	public function writeXMLFile()
	{
		$podcasts = new JBSMPodcast;
		$result   = $podcasts->makePodcasts();
		$this->setRedirect('index.php?option=com_biblestudy&view=podcasts&' . JSession::getFormToken() . '=1', $result);
	}

	/**
	 * Resets the hits
	 *
	 * @return null
	 */
	public function resetHits()
	{
		$app   = JFactory::getApplication();
		$id    = $app->input->getInt('id', 0);
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__bsms_studies')
			->set('hits=' . 0)
			->where('id = ' . (int) $id);
		$db->setQuery($query);

		if (!$db->execute())
		{
			$msg = JText::_('JBS_CMN_ERROR_RESETTING_HITS');
		}
		else
		{
			$updated = $db->getAffectedRows();
			$msg     = JText::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . JText::_('JBS_CMN_ROWS_RESET');
		}
		$this->setRedirect('index.php?option=com_biblestudy&view=message&layout=edit&id=' . $id . '&' . JSession::getFormToken() . '=1', $msg);
	}

	/**
	 * Resets Downloads
	 *
	 * @return null
	 */
	public function resetDownloads()
	{
		$app   = JFactory::getApplication();
		$id    = $app->input->getInt('id', 0);
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__bsms_mediafiles')
			->set('downloads = ' . 0)
			->where('id = ' . (int) $id);
		$db->setQuery($query);

		if (!$db->execute())
		{
			$msg = JText::_('JBS_CMN_ERROR_RESETTING_DOWNLOADS');
		}
		else
		{
			$updated = $db->getAffectedRows();
			$msg     = JText::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . JText::_('JBS_CMN_ROWS_RESET');
		}
		$this->setRedirect('index.php?option=com_biblestudy&view=mediafile&layout=edit&id=' . $id . '&' . JSession::getFormToken() . '=1', $msg);
	}

	/**
	 * Resets Plays
	 *
	 * @return null
	 */
	public function resetPlays()
	{
		$jinput = new JInput;
		$id     = $jinput->getInt('id', 0);
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$query->update('#__bsms_mediafiles')
			->set('plays = ' . 0)
			->where('id = ' . (int) $id);
		$db->setQuery($query);

		if (!$db->execute())
		{
			$error = $db->getErrorMsg();
			$msg   = JText::_('JBS_CMN_ERROR_RESETTING_PLAYS') . ' ' . $error;
		}
		else
		{
			$updated = $db->getAffectedRows();
			$msg     = JText::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . JText::_('JBS_CMN_ROWS_RESET');
		}
		$this->setRedirect('index.php?option=com_biblestudy&view=mediafile&layout=edit&id=' . $id . '&' . JSession::getFormToken() . '=1', $msg);
	}

	/**
	 * Adds the ability to upload with flash
	 *
	 * @return null
	 *
	 * @since 7.1.0
	 *        Note: This function is not used in 7.1.0 since it caused problems with the session and closing the media form
	 */
	public function uploadflash()
	{
		$app = JFactory::getApplication();
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		jimport('joomla.filesystem.file');

		// Get the server and folder id from the request
		$serverid = $jinput->getInt('upload_server', '');
		$folderid = $jinput->getInt('upload_folder', '');
		$app      = JFactory::getApplication();
		$app->setUserState($option . 'serverid', $serverid);
		$app->setUserState($option . 'folderid', $folderid);
		$form     = $jinput->getArray('jform');
		$returnid = $form['id'];

		// Get temp file details
		$temp        = JBSMUpload::gettempfile();
		$temp_folder = JBSMUpload::gettempfolder();
		$tempfile    = $temp_folder . $temp;

		// Get path and abort if none
		$layout = $jinput->getWord('layout');

		if ($layout == 'modal')
		{
			$url = 'index.php?option=' . $option . '&view=mediafile&task=edit&tmpl=component&layout=modal&id=' . $returnid .
					'&' . JSession::getFormToken() . '=1';
		}
		else
		{
			$url = 'index.php?option=' . $option . '&view=mediafile&task=edit&id=' . $returnid . '&' . JSession::getFormToken() . '=1';
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
				$uploadmsg = JText::_('JBS_MED_FILE_UPLOADED

        ');
			}
		}
		else
		{
			$uploadmsg = JText::_('JBS_MED_NOT_UPLOAD_THIS_FILE_EXT  ');
		}

		// Delete temp file
		JBSMUpload::deletetempfile($tempfile);

		if ($layout == ' modal')
		{
			$this->setRedirect('index.php?option=' . $option . '&view=mediafile&task=edit&tmpl=component&layout=modal&id=' . $returnid .
					'&' . JSession::getFormToken() . '=1', $uploadmsg
			);
		}
		else
		{
			$this->setRedirect('index.php?option=' . $option . '&view=mediafile&task=edit&id=' . $returnid . '&' .
					JSession::getFormToken() . '=1', $uploadmsg
			);
		}

		return;
	}

	/**
	 * Upload function
	 *
	 * @return null
	 */
	public function upload()
	{
		$app = JFactory::getApplication();
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$jinput    = $app->input;
		$option    = $jinput->getCmd('option');
		$uploadmsg = '';
		$size      = 0;
		$serverid  = $jinput->getInt('upload_server', '');
		$folderid  = $jinput->getInt('upload_folder', '');
		$form      = $jinput->get('jform', array(), 'post', 'array');
		$returnid  = $form['id'];
		$url       = 'index.php?option=com_biblestudy&view=mediafile&id=' . $form['id'] . '&' . JSession::getFormToken() . '=1';
		$path      = JBSMUpload::getpath($url, '');
		$file      = $jinput->files->get('uploadfile');

		// Check file type allowed
		$allow = JBSMUpload::checkfile($file);

		if ($allow)
		{
			$filename = JBSMUpload::buildpath($file, 1, $serverid, $folderid, $path);

			// Process file
			$uploadmsg = JBSMUpload::processuploadfile($file, $filename);

			if (!$uploadmsg)
			{
				$uploadmsg = JText::_('JBS_MED_FILE_UPLOADED');
			}

			$app->setUserState($option . 'fname', $filename->file);
			$app->setUserState($option . 'size', $file['size']);
			$app->setUserState($option . 'serverid', $serverid);
			$app->setUserState($option . 'folderid', $folderid);
		}

		$layout = $jinput->getWord('layout');

		if ($layout == 'modal')
		{
			$this->setRedirect('index.php?option=' . $option . '&view=mediafile&task=edit&tmpl=component&layout=modal&id=' . $returnid .
					'&' . JSession::getFormToken() . '=1', $uploadmsg
			);
		}
		else
		{
			$this->setRedirect('index.php?option=' . $option . '&view=mediafile&task=edit&id=' . $returnid . '&' .
					JSession::getFormToken() . '=1', $uploadmsg
			);
		}

		return;
	}

}
