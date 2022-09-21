<?php
/**
 * @package     Proclaim
 * @subpackage  Plugin.JBSPodcast
 * @copyright   2007 - 2019 (C) CWM Team All rights reserved
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        https://www.christianwebministries.org
 * */
namespace Joomla\Plugin\System\ProclaimPodcast;

use CWM\Component\Proclaim\Site\Helper\CWMPodcast;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

defined('_JEXEC') or die;

/**
 * JBSPodcast jPlugin class
 *
 * @package     Proclaim
 * @subpackage  Plugin.JBSPodcast
 * @since       7.0.0
 */
class PlgSystemProclaimPodcast extends CMSPlugin
{
	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @since   1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);

		$this->loadLanguage();

		// Always load JBSM API if it exists.
		$api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

		if (file_exists($api))
		{
			require_once $api;
		}
	}

	/**
	 * After Initialise system
	 *
	 * @return void
	 *
	 * @since   1.5
	 */
	public function onAfterInitialise()
	{
		$params = $this->params;

		// First check to see what method of updating the podcast we are using
		$method = $params->get('method', '0');

		if ($method === '0')
		{
			$check = $this->checktime($params);
		}
		else
		{
			$check = $this->checkdays($params);
		}

		if ($check)
		{
			// Perform the podcast and email and update time
			$dopodcast = $this->doPodcast();

			// Update the database to show a new time
			$this->updatetime();

			// Check to see if we need to email anything
			if ($params->get('email') > 0)
			{
				if ($params->get('email') > 1)
				{
					$iserror = substr_count($dopodcast, 'not');

					if ($iserror)
					{
						$this->doEmail($params, $dopodcast);
					}
				}
				else
				{
					$this->doEmail($params, $dopodcast);
				}
			}
		}
	}

	/**
	 * Check Time
	 *
	 * @param   object  $params  Plugin params
	 *
	 * @return boolean   True if Time is difference. False if not grater then now.
	 *
	 * @since   7.0.5
	 */
	public function checktime($params)
	{
		$now   = time();
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select('timeset')
			->from('#__jbspodcast_timeset');
		$db->setQuery($query, 0, 1);
		$result     = $db->loadObject();
		$lasttime   = $result->timeset;
		$frequency  = $params->get('xhours', '86400');
		$difference = $frequency * 3600;
		$checkit    = $now - $lasttime;

		return $checkit > $difference;
	}

	/**
	 * Check Days
	 *
	 * @param   Joomla\Registry\Registry  $params  Plugin params
	 *
	 * @return boolean Check to see if to day is right.
	 *
	 * @since   7.0.5
	 */
	public function checkdays($params)
	{
		$checkdays = false;
		$config    = Factory::getConfig();
		$offset    = $config->get('config.offset');

		$now   = time();
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select('timeset')
			->from('#__jbspodcast_timeset');
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
	 * Do Podcast
	 *
	 * @return boolean|string
	 *
	 * @since   7.0.5
	 */
	public function doPodcast()
	{
		$podcasts = new CWMPodcast;

		return $podcasts->makePodcasts();
	}

	/**
	 * Update Time
	 *
	 * @return boolean
	 *
	 * @since 7.0.0
	 */
	public function updatetime()
	{
		$time  = time();
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->update('#__jbspodcast_timeset')
			->set('timeset = ' . $time);
		$db->setQuery($query);
		$db->execute();
		$updateresult = $db->getAffectedRows();

		return $updateresult > 0;
	}

	/**
	 * Send the Email
	 *
	 * @param   Joomla\Registry\Registry  $params     Plugin params
	 * @param   bool|string               $dopodcast  Podcast rendering to send in email.
	 *
	 * @return void
	 *
	 * @since 7.0.0
	 */
	public function doEmail($params, $dopodcast)
	{
		$livesite = JUri::root();
		jimport('joomla.filesystem.file');

		$mail = Factory::getMailer();
		$mail->isHtml(true);
		jimport('joomla.utilities.date');
		$date = date('r');
		$Body = $params->get('body') . '<br />';
		$Body .= Text::_('JBS_PLG_PODCAST_EMAIL_BODY_RUN') . $date . '<br />';
		$Body2    = $dopodcast;
		$Body3    = $Body . $Body2;
		$Subject  = $params->get('subject');

		$recipients = explode(",", $params->get('recipients'));

		foreach ($recipients AS $recipient)
		{
			$mail->addRecipient($recipient);
			$mail->setSubject($Subject . ' ' . $livesite);
			$mail->setBody($Body3);
			$mail->Send();
		}
	}
}
