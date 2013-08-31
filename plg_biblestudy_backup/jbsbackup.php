<?php

/**
 * Joomla BibleStudy Backup Plugin
 *
 * @package     BibleStudy
 * @subpackage  Plugin.JBSBackup
 * @copyright   (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

/* Import library dependencies */

jimport('joomla.plugin.plugin');

/**
 * JBSBackup jplugin class
 *
 * @package     BibleStudy
 * @subpackage  Plugin.JBSBackup
 * @since       7.1.0
 */
class PlgSystemjbsbackup extends JPlugin
{

	/**
	 * Constructor
	 *
	 * @param   object &$subject   The object to observe
	 * @param   array  $config     An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 */
	public function __construct(& $subject, $config)
	{

		parent::__construct($subject, $config);

		$this->loadLanguage();
		$this->loadLanguage('com_biblestudy', JPATH_ADMINISTRATOR);
	}

	/**
	 * After Initialise system
	 *
	 * @return void
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

			// If we have run the backupcheck and it returned no errors then the last thing we do is reset the time we did it to current

			$updatetime = $this->updatetime();

			// Check to see if we need to email anything
			if ($check && $params->get('email') > 0)
			{
				$this->doEmail($params, $dobackup);
			}
			//$this->updatefiles($params);

		}
	}

	/**
	 * Check Time
	 *
	 * @param   object $params  ?
	 *
	 * @return boolean
	 */
	public function checktime($params)
	{

		$now   = time();
		$db    = JFactory::getDBO();
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
	 * @param   object $params  ?
	 *
	 * @return boolean
	 */
	public function checkdays($params)
	{
		$checkdays = false;
		$config    = JFactory::getConfig();
		$offset    = $config->get('config.offset');

		$now   = time();
		$db    = JFactory::getDBO();
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
	 * Update the time
	 *
	 * @return boolean
	 */
	public function updatetime()
	{
		$time  = time();
		$db    = JFactory::getDBO();
		$query = 'UPDATE #__jbsbackup_timeset SET `backup` = ' . $time;
		$db->setQuery($query);
		$db->query();
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
	 * Do the backup
	 *
	 * @return object
	 */
	public function doBackup()
	{
		$path1 = JPATH_ADMINISTRATOR . '/components/com_biblestudy/lib/';
		include_once $path1 . 'biblestudy.backup.php';
		$dbbackup = new JBSExport;
		$backup   = $dbbackup->exportdb($run = 2);

		return $backup;
	}

	/**
	 * Send the Email
	 *
	 * @param   object $params    ?
	 * @param   object $dobackup  ?
	 *
	 * @return void
	 */
	public function doEmail($params, $dobackup)
	{
		$livesite = JURI::root();
		$config   = JFactory::getConfig();
		$mailfrom = $config->get('config.mailfrom');
		$fromname = $config->get('config.fromname');
		jimport('joomla.filesystem.file');

		// Check for existence of backup file, then attach to email
		$backupexists = JFile::exists($dobackup);

		if (!$backupexists)
		{
			$msg = JText::_('JBS_PLG_BACKUP_ERROR');
		}
		else
		{
			$msg = JText::_('JBS_PLG_BACKUP_SUCCESS');
		}
		$mail = JFactory::getMailer();
		$mail->IsHTML(true);
		jimport('joomla.utilities.date');
		$year = '(' . date('Y') . ')';
		$date = date('r');
		$Body = $params->get('body') . '<br />';
		$Body .= JText::_('JBS_PLG_BACKUP_EMAIL_BODY_RUN') . $date . '<br />';
		$Body2 = '';

		// $Body2 .= '<br><a href="' . JURI::root() . $dobackup . '</a>';
		$Body2 .= $msg;

		$Body3    = $Body . $Body2;
		$Subject  = $params->get('subject');
		$FromName = $params->def('fromname', $fromname);

		$recipients = explode(",", $params->get('recipients'));

		foreach ($recipients AS $recipient)
		{
			$mail->addRecipient($recipient);
			$mail->setSubject($Subject . ' ' . $livesite);
			$mail->setBody($Body3);

			if ($params->get('includedb') == 1)
			{
				$mail->addAttachment($dobackup);
			}
			$mail->Send();
		}
	}

	/**
	 * Update files
	 *
	 * @param   object $params  ?
	 *
	 * @return void
	 */
	public function updatefiles($params)
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$path          = JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'database';
		$exclude       = array('.svn', 'CVS', '.DS_Store', '__MACOSX');
		$excludefilter = array('^\..*', '.*~');
		$files         = JFolder::files($path, '.sql', 'false', 'true', $exclude, $excludefilter);
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
