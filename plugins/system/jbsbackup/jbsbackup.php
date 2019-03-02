<?php
/**
 * @package     Proclaim
 * @subpackage  Plugin.JBSBackup
 * @copyright   2007 - 2018 (C) CWM Team All rights reserved
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        https://www.joomlabiblestudy.org
 * */
defined('_JEXEC') or die;

/**
 * JBSBackup jPlugin class
 *
 * @package     Proclaim
 * @subpackage  Plugin.JBSBackup
 * @since       7.1.0
 */
class PlgSystemJBSBackup extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @since 1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);

		// Always load JBSM API if it exists.
		$api = JPATH_ADMINISTRATOR . '/components/com_biblestudy/api.php';

		if (file_exists($api))
		{
			require_once $api;
		}

		$this->loadLanguage();
	}

	/**
	 * After Initialise system
	 *
	 * @return void
	 *
	 * @since 1.5
	 */
	public function onAfterInitialise()
	{
		$params = $this->params;

		// First check to see what method of updating the backup we are using
		$method = $params->get('method', '0');

		if ($method == '0')
		{
			$check = $this->checktime($params);
		}
		else
		{
			$check = $this->checkdays($params);
		}

		if ($check)
		{
			// Perform the backup and email and update time and zip file
			$dobackup = $this->doBackup();

			// If we have run the backup check and it returned no errors then the last thing we do is reset the time we did it to current
			$this->updatetime();

			// Check to see if we need to email anything
			if ($check && $params->get('email') > 0)
			{
				$this->doEmail($params, $dobackup);
			}

			// Clean up files after update. (Default 5 files)
			$this->updatefiles($params);
		}
	}

	/**
	 * Check Time
	 *
	 * @param   Joomla\Registry\Registry  $params  ?
	 *
	 * @return boolean
	 *
	 * @since 7.1.0
	 */
	public function checktime($params)
	{
		$now   = time();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('backup')->from('#__jbsbackup_timeset');
		$db->setQuery($query, 0, 1);
		$result     = $db->loadObject();
		$lasttime   = $result->backup;
		$frequency  = $params->get('xhours', '86400');
		$difference = $frequency * 3600;
		$checkit    = $now - $lasttime;

		if ($checkit > $difference)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Check Days
	 *
	 * @param   Joomla\Registry\Registry  $params  ?
	 *
	 * @return boolean
	 *
	 * @since 7.1.0
	 */
	public function checkdays($params)
	{
		$checkdays = false;
		$config    = JFactory::getConfig();
		$offset    = $config->get('config.offset');

		$now   = time();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('backup')->from('#__jbsbackup_timeset');
		$db->setQuery($query, 0, 1);
		$result     = $db->loadObject();
		$lasttime   = $result->timeset;
		$difference = $now - $lasttime;
		$date       = getdate($now);
		$day        = $date['wday'];
		$systemhour = $date['hours'];

		if ($params->get('offset', '0') > 0)
		{
			$hour = $systemhour + $offset;
		}
		else
		{
			$hour = $systemhour;
		}

		if ($params->get('day1') == $day && $params->get('hour1') == $hour && $difference > 3600)
		{
			$checkdays = true;
		}

		if ($params->get('day2') == $day)
		{
			if ($params->get('hour2') == $hour && $difference > 3600)
			{
				$checkdays = true;
			}
		}

		if ($params->get('day3') == $day)
		{
			if ($params->get('hour3') == $hour && $difference > 3600)
			{
				$checkdays = true;
			}
		}

		if ($params->get('day4') == $day)
		{
			if ($params->get('hour4') == $hour && $difference > 3600)
			{
				$checkdays = true;
			}
		}

		if ($params->get('day5') == $day)
		{
			if ($params->get('hour5') == $hour && $difference > 3600)
			{
				$checkdays = true;
			}
		}

		if ($params->get('day6') == $day)
		{
			if ($params->get('hour6') == $hour && $difference > 3600)
			{
				$checkdays = true;
			}
		}

		if ($params->get('day7') == $day)
		{
			if ($params->get('hour7') == $hour && $difference > 3600)
			{
				$checkdays = true;
			}
		}

		if ($params->get('day8') == $day)
		{
			if ($params->get('hour8') == $hour && $difference > 3600)
			{
				$checkdays = true;
			}
		}

		if ($params->get('day9') == $day)
		{
			if ($params->get('hour9') == $hour && $difference > 3600)
			{
				$checkdays = true;
			}
		}

		if ($params->get('day10') == $day)
		{
			if ($params->get('hour10') == $hour && $difference > 3600)
			{
				$checkdays = true;
			}
		}

		return $checkdays;
	}

	/**
	 * Do the backup
	 *
	 * @return boolean
	 *
	 * @since 7.1.0
	 */
	public function doBackup()
	{
		$dbbackup = new JBSMBackup;
		$backup   = $dbbackup->exportdb($run = 2);

		return $backup;
	}

	/**
	 * Update Time
	 *
	 * @return boolean
	 *
	 * @since 7.1.0
	 */
	public function updatetime()
	{
		$time  = time();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__jbsbackup_timeset')->set($db->qn('backup') . ' = ' . $db->q($time));
		$db->setQuery($query);
		$db->execute();
		$updateresult = $db->getAffectedRows();

		if ($updateresult > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Send the Email
	 *
	 * @param   Joomla\Registry\Registry  $params    Component Params
	 * @param   string                    $dobackup  File of Backup
	 *
	 * @return void
	 *
	 * @since 7.1.0
	 */
	public function doEmail($params, $dobackup)
	{
		$livesite = JUri::root();
		$config   = JFactory::getConfig();
		$mailfrom = $config->get('config.mailfrom');
		$fromname = $config->get('config.fromname');
		jimport('joomla.filesystem.file');

		// Check for existence of backup file, then attach to email
		$backupexists = JFile::exists($dobackup);

		if (!$backupexists)
		{
			$msg = JText::_('JBS_PLG_BACKUP_EMAIL_MSG_ERROR');
		}
		else
		{
			$msg = JText::_('JBS_PLG_BACKUP_EMAIL_MSG_SUCCESS');
		}

		if ($params->def('fromname', $fromname))
		{
			$fromname = $params->def('fromname', $fromname);
		}

		$mail = JFactory::getMailer();
		$mail->isHtml(true);
		jimport('joomla.utilities.date');
		$sender = array(
			$mailfrom,
			$fromname);
		$mail->setSender($sender);
		$Body = $params->def('Body', '<strong>' . JText::_('PLG_JBSBACKUP_HEADER') . ' ' . $fromname . '</strong><br />');
		$Body .= JText::_('Process run at: ') . JHtml::date($input = 'now', 'm/d/Y h:i:s a', false) . '<br />';
		$Body .= '';
		$Body .= $msg;
		$Subject = $params->def('subject', JText::_('PLG_JBSBACKUP_REPORT'));

		$recipients = explode(',', $params->get('recipients'));

		if ($recipients == false)
		{
			$recipients = array(
				$config->get('config.mailfrom'));
		}

		$mail->addRecipient($recipients);

		$mail->setSubject($Subject . ' ' . $livesite);
		$mail->setBody($Body);

		if ($params->get('includedb') == 1)
		{
			$mail->addAttachment($dobackup);
		}

		if (!$mail->Send())
		{
			JLog::add('JBSM Bakup Plugin email faild.', JLog::ERROR, 'com_biblestudy', DateTime::W3C);
		}
	}

	/**
	 * Update files
	 *
	 * @param   Joomla\Registry\Registry  $params  JBSM Params
	 *
	 * @return void
	 *
	 * @since 7.1.0
	 */
	public function updatefiles($params)
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$path          = JPATH_SITE . '/media/com_biblestudy/database';
		$exclude = array('.git', '.svn', 'CVS', '.DS_Store', '__MACOSX', '.html');
		$excludefilter = array('^\..*', '.*~');
		$files         = JFolder::files($path, '.', 'false', 'true', $exclude, $excludefilter);
		arsort($files, SORT_STRING);
		$parts       = array();
		$numfiles    = count($files);
		$totalnumber = $params->get('filestokeep', '5');

		for ($counter = $numfiles; $counter > $totalnumber; $counter--)
		{
			$parts[] = array_pop($files);
		}

		foreach ($parts as $part)
		{
			JFile::delete($part);
		}
	}
}
