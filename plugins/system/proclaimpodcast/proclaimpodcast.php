<?php

/**
 * @package     Proclaim
 * @subpackage  Plugin.JBSPodcast
 * @copyright   2007 - 2019 (C) CWM Team All rights reserved
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        https://www.christianwebministries.org
 * */

namespace Joomla\Plugin\System\ProclaimPodcast;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use PHPMailer\PHPMailer\Exception;

/**
 * Proclaim Podcast class
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
     * @param   array    $config   An optional associative array of configuration settings.
     *                             Recognized key values include 'name', 'group', 'params', 'language'
     *                             (this list is not meant to be comprehensive).
     *
     * @since   1.5
     */
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);

        $this->loadLanguage();

        // Always load CWM API if it exists.
        $api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

        if (!\defined('BIBLESTUDY_COMPONENT_NAME')) {
            require_once $api;
        }
    }

    /**
     * After Initialise system
     *
     * @return void
     *
     * @throws \Exception
     * @since   1.5
     */
    public function onAfterInitialise(): void
    {
        $params = $this->params;

        // First check to see what method of updating the podcast we are using
        $method = $params->get('method', '0');

        if ($method === '0') {
            $check = $this->checktime($params);
        } else {
            $check = $this->checkdays($params);
        }

        if ($check) {
            // Perform the podcast and email and update time
            $dopodcast = $this->doPodcast();

            // Update the database to show a new time
            $this->updatetime();

            // Check to see if we need to email anything
            if ($params->get('email') > 0) {
                if ($params->get('email') > 1) {
                    $iserror = substr_count($dopodcast, 'not');

                    if ($iserror) {
                        $this->doEmail($params, $dopodcast);
                    }
                } else {
                    $this->doEmail($params, $dopodcast);
                }
            }
        }
    }

    /**
     * Check Time
     *
     * @param   Registry|null  $params  Plugin params
     *
     * @return boolean   True if Time is difference. False if not grater then now.
     *
     * @since   7.0.5
     */
    public function checktime(?Registry $params): bool
    {
        $now   = time();
        $db    = Factory::getContainer()->get('DatabaseDriver');
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
     * @param   Registry|null  $params  Plugin params
     *
     * @return boolean Check to see if to day is right.
     *
     * @throws \Exception
     * @since   7.0.5
     */
    public function checkdays(?Registry $params)
    {
        $checkdays = false;
        $config    = Factory::getApplication()->get();
        $offset    = $config->get('config.offset');

        $now   = time();
        $db    = Factory::getContainer()->get('DatabaseDriver');
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

        if ($params->get('offset', '0') > 0) {
            $hour = $systemhour + $offset;
        } else {
            $hour = $systemhour;
        }

        if ($params->get('day1') == $day && $params->get('hour1') == $hour && $difference > 3600) {
            $checkdays = true;
        }

        if ($params->get('day2') == $day) {
            if ($params->get('hour2') == $hour && $difference > 3600) {
                $checkdays = true;
            }
        }

        if ($params->get('day3') == $day) {
            if ($params->get('hour3') == $hour && $difference > 3600) {
                $checkdays = true;
            }
        }

        if ($params->get('day4') == $day) {
            if ($params->get('hour4') == $hour && $difference > 3600) {
                $checkdays = true;
            }
        }

        if ($params->get('day5') == $day) {
            if ($params->get('hour5') == $hour && $difference > 3600) {
                $checkdays = true;
            }
        }

        if ($params->get('day6') == $day) {
            if ($params->get('hour6') == $hour && $difference > 3600) {
                $checkdays = true;
            }
        }

        if ($params->get('day7') == $day) {
            if ($params->get('hour7') == $hour && $difference > 3600) {
                $checkdays = true;
            }
        }

        if ($params->get('day8') == $day) {
            if ($params->get('hour8') == $hour && $difference > 3600) {
                $checkdays = true;
            }
        }

        if ($params->get('day9') == $day) {
            if ($params->get('hour9') == $hour && $difference > 3600) {
                $checkdays = true;
            }
        }

        if ($params->get('day10') == $day) {
            if ($params->get('hour10') == $hour && $difference > 3600) {
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
     * @throws \Exception
     * @since   7.0.5
     */
    public function doPodcast(): bool|string
    {
        return (new Cwmpodcast())->makePodcasts();
    }

    /**
     * Update Time
     *
     * @return boolean
     *
     * @since 7.0.0
     */
    public function updatetime(): bool
    {
        $time  = time();
        $db    = Factory::getContainer()->get('DatabaseDriver');
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
     * @param   Registry     $params     Plugin params
     * @param   bool|string  $dopodcast  Podcast rendering to send in email.
     *
     * @return void
     *
     * @throws Exception
     * @since 7.0.0
     */
    public function doEmail($params, $dopodcast): void
    {
        $livesite = Uri::root();
        jimport('joomla.filesystem.file');

        $mail = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
        $mail->isHtml(true);
        jimport('joomla.utilities.date');
        $date    = date('r');
        $Body    = $params->get('body') . '<br />';
        $Body    .= Text::_('JBS_PLG_PODCAST_EMAIL_BODY_RUN') . $date . '<br />';
        $Body2   = $dopodcast;
        $Body3   = $Body . $Body2;
        $Subject = $params->get('subject');

        $recipients = explode(",", $params->get('recipients'));

        foreach ($recipients as $recipient) {
            $mail->addRecipient($recipient);
            $mail->setSubject($Subject . ' ' . $livesite);
            $mail->setBody($Body3);
            $mail->Send();
        }
    }
}
