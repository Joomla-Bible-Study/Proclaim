<?php
/**
 * @package     Proclaim
 * @subpackage  Plugin.JBSBackup
 * @copyright   2007 - 2019 (C) CWM Team All rights reserved
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Lib\CWMBackup;
use Joomla\CMS\Factory;
use Joomla\Event\DispatcherInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/**
 * JBSBackup jPlugin class
 *
 * @package     Proclaim
 * @subpackage  Plugin.JBSBackup
 * @since       7.1.0
 */
class PlgSystemProclaimBackup extends CMSPlugin
{
	/**
	 * The name of the plugin
	 *
	 * @var    string
	 * @since  1.5
	 */
	protected $_name = 'proclaimbackup';

	/**
	 * The plugin type
	 *
	 * @var    string
	 * @since  1.5
	 */
	protected $_type = 'system';

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * After Initialise system
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since 1.5
	 */
	public function onAfterInitialise(): void
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
	 * @param  Registry  $params  ?
	 *
	 * @return boolean
	 *
	 * @since 7.1.0
	 */
	public function checktime($params): bool
	{
		$now   = time();
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select('backup')->from('#__jbsbackup_timeset');
		$db->setQuery($query, 0, 1);
		$result     = $db->loadObject();
		$lasttime   = $result->backup;
		$frequency  = $params->get('xhours', '86400');
		$difference = $frequency * 3600;
		$checkit    = $now - $lasttime;

		return $checkit > $difference;
	}

	/**
	 * Check Days
	 *
	 * @param  Registry  $params  ?
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 * @since 7.1.0
	 */
	public function checkdays(Registry $params): bool
	{
		$checkdays = false;
		$config    = Factory::getApplication()->getConfig();
		$offset    = $config->get('config.offset');

		$now   = time();
		$db = Factory::getContainer()->get('DatabaseDriver');
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
	public function doBackup(): bool
	{
		return (new CWMBackup)->exportdb($run = 2);
	}

	/**
	 * Update Time
	 *
	 * @return boolean
	 *
	 * @since 7.1.0
	 */
	public function updatetime(): bool
	{
		$time  = time();
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->update('#__jbsbackup_timeset')->set($db->qn('backup') . ' = ' . $db->q($time));
		$db->setQuery($query);
		$db->execute();
		$updateresult = $db->getAffectedRows();

		return $updateresult > 0;
	}

	/**
	 * Send the Email
	 *
	 * @param   Registry  $params    Component Params
	 * @param   string    $dobackup  File of Backup
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since 7.1.0
	 */
	public function doEmail($params, $dobackup): void
	{
		$livesite = Uri::root();
		$config   = Factory::getApplication()->getConfig();
		$mailfrom = $config->get('config.mailfrom');
		$fromname = $config->get('config.fromname');

		// Check for existence of backup file, then attach to email
		$backupexists = file_exists($dobackup);

		if (!$backupexists)
		{
			$msg = Text::_('PLG_SYSTEM_PROCLAIMBACKUP_EMAIL_MSG_ERROR');
		}
		else
		{
			$msg = Text::_('PLG_SYSTEM_PROCLAIMBACKUP_EMAIL_MSG_SUCCESS');
		}

		if ($params->def('fromname', $fromname))
		{
			$fromname = $params->def('fromname', $fromname);
		}

		$mail = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
		$mail->isHtml(true);
		$sender = array(
			$mailfrom,
			$fromname);
		$mail->setSender($sender);
		$Body = $params->def('Body', '<strong>' . Text::_('PLG_JBSBACKUP_HEADER') . ' ' . $fromname . '</strong><br />');
		$Body .= Text::_('Process run at: ') . HTMLHelper::date($input = 'now', 'm/d/Y h:i:s a', false) . '<br />';
		$Body .= '';
		$Body .= $msg;
		$Subject = $params->def('subject', Text::_('PLG_JBSBACKUP_REPORT'));

		$recipients = explode(',', $params->get('recipients'));

		if (!$recipients)
		{
			$recipients = array(
				$config->get('config.mailfrom'));
		}

		$mail->addRecipient($recipients);

		$mail->setSubject($Subject . ' ' . $livesite);
		$mail->setBody($Body);

		if ($params->get('includedb') === 1)
		{
			$mail->addAttachment($dobackup);
		}

		if (!$mail->Send())
		{
			Log::add('CWM Backup Plugin email failed.', Log::ERROR, 'com_proclaim', DateTime::W3C);
		}
	}

	/**
	 * Update files
	 *
	 * @param  Registry  $params  Proclaim Params
	 *
	 * @return void
	 *
	 * @since 7.1.0
	 */
	public function updatefiles($params): void
	{
		$path          = JPATH_SITE . '/media/com_proclaim/backup';
		$exclude = array('.git', '.svn', 'CVS', '.DS_Store', '__MACOSX', '.html');
		$excludefilter = array('^\..*', '.*~');
		$files         = Folder::files($path, '.', 'false', 'true', $exclude, $excludefilter);
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
			File::delete($part);
		}
	}
}
