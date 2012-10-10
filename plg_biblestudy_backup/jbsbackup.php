<?php

/**
 * Joomla BibleStudy Backup Plugin
 * @package BibleStudy
 * @subpackage Plugin.JBSBackup
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

/* Import library dependencies */

jimport('joomla.plugin.plugin');

/**
 * JBSBackup jplugin class
 * @package BibleStudy
 * @subpackage Plugin.JBSBackup
 * @since 7.1.0
 */
class plgSystemjbsbackup extends JPlugin {

    /**
     * Constructor
     *
     * @access      protected
     * @param       object  $subject The object to observe
     * @param       array   $config  An array that holds the plugin configuration
     * @since       1.6
     *
     */
    public function __construct(& $subject, $config) {

        parent::__construct($subject, $config);

        $this->loadLanguage();
        $this->loadLanguage('com_biblestudy', JPATH_ADMINISTRATOR);
    }

    /**
     * After Initialise system
     */
    public function onAfterInitialise() {


        $params = $this->params;


        //First check to see what method of updating the backup we are using
        $method = $params->get('method', '0');
        if ($method == '0') {
            $check = $this->checktime($params);
        } else {
            $check = $this->checkdays($params);
        }

        if ($check) {
            //perform the backup and email and update time and zip file
            $dobackup = $this->doBackup();

            //If we have run the backupcheck and it returned no errors then the last thing we do is reset the time we did it to current
            if ($dobackup) {
                $updatetime = $this->updatetime();
                // check to see if we need to email anything
                if ($check && $params->get('email') > 0) {
                    $email = $this->doEmail($params, $dobackup);
                }
                $updatefiles = $this->updatefiles($params);
            }
        }
    }

    /**
     * Check Time
     * @param array $params
     * @return boolean
     */
    function checktime($params) {

        $now = time();
        $db = JFactory::getDBO();
        $db->setQuery('SELECT `backup` FROM `#__jbsbackup_timeset`', 0, 1);
        $result = $db->loadObject();
        $lasttime = $result->backup;
        $frequency = $params->get('xhours', '86400');
        $difference = $frequency * 3600;
        $checkit = $now - $lasttime;
        if ($checkit > $difference) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check Days
     * @param array $params
     * @return boolean
     */
    function checkdays($params) {
        $checkdays = FALSE;
        $config = JFactory::getConfig();
        $offset = $config->getValue('config.offset');

        $now = time();
        $db = JFactory::getDBO();
        $db->setQuery('SELECT `backup` FROM `#__jbsbackup_timeset`', 0, 1);
        $result = $db->loadObject();
        $lasttime = $result->timeset;
        $difference = $now - $lasttime;
        $date = getdate($now);
        $day = $date['wday'];
        $systemhour = $date['hours'];
        if ($params->get('offset', '0') > 0) {
            $hour = $systemhour + $offset;
        } else {
            $hour = $systemhour;
        }

        if ($params->get('day1') == $day && $params->get('hour1') == $hour && $difference > 3600) {
            $checkdays = TRUE;
        }
        if ($params->get('day2') == $day) {
            if ($params->get('hour2') == $hour && $difference > 3600) {
                $checkdays = TRUE;
            }
        }
        if ($params->get('day3') == $day) {
            if ($params->get('hour3') == $hour && $difference > 3600) {
                $checkdays = TRUE;
            }
        }
        if ($params->get('day4') == $day) {
            if ($params->get('hour4') == $hour && $difference > 3600) {
                $checkdays = TRUE;
            }
        }
        if ($params->get('day5') == $day) {
            if ($params->get('hour5') == $hour && $difference > 3600) {
                $checkdays = TRUE;
            }
        }
        if ($params->get('day6') == $day) {
            if ($params->get('hour6') == $hour && $difference > 3600) {
                $checkdays = TRUE;
            }
        }
        if ($params->get('day7') == $day) {
            if ($params->get('hour7') == $hour && $difference > 3600) {
                $checkdays = TRUE;
            }
        }
        if ($params->get('day8') == $day) {
            if ($params->get('hour8') == $hour && $difference > 3600) {
                $checkdays = TRUE;
            }
        }
        if ($params->get('day9') == $day) {
            if ($params->get('hour9') == $hour && $difference > 3600) {
                $checkdays = TRUE;
            }
        }
        if ($params->get('day10') == $day) {
            if ($params->get('hour10') == $hour && $difference > 3600) {
                $checkdays = TRUE;
            }
        }

        return $checkdays;
    }

    /**
     * Update the time
     * @return boolean
     */
    function updatetime() {
        $time = time();
        $db = JFactory::getDBO();
        $db->setQuery('UPDATE `#__jbsbackup_timeset` SET `backup` = ' . $time);
        $db->query();
        $updateresult = $db->getAffectedRows();
        if ($updateresult > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Do the backup
     * @return object
     */
    function doBackup() {
        $path1 = JPATH_ADMINISTRATOR . '/components/com_biblestudy/lib/';
        ;
        include_once($path1 . 'biblestudy.backup.php');
        $dbbackup = new JBSExport();
        $backup = $dbbackup->exportdb($run = 2);
        return $backup;
    }

    /**
     * Send the Email
     * @param array $params
     * @param object $dobackup
     */
    function doEmail($params, $dobackup) {
        $livesite = JURI::root();
        $config = JFactory::getConfig();
        $mailfrom = $config->getValue('config.mailfrom');
        $fromname = $config->getValue('config.fromname');
        jimport('joomla.filesystem.file');

        //Check for existence of backup file, then attach to email
        $backupexists = JFile::exists($dobackup);
        if (!$backupexists) {
            $msg = JText::_('PLG_JBSBACKUP_ERROR');
        } else {
            $msg = JText::_('PLG_JBSBACKUP_SUCCESS');
        }
        $mail = JFactory::getMailer();
        $mail->IsHTML(true);
        jimport('joomla.utilities.date');
        $year = '(' . date('Y') . ')';
        $date = date('r');
        $Body = $params->def('Body', '<strong>' . JText::_('PLG_JBSBACKUP_HEADER') . ' ' . $fromname . '</strong><br />');
        $Body .= JText::_('Process run at: ') . $date . '<br />';
        $Body2 = '';


       // $Body2 .= '<br><a href="' . JURI::root() . $dobackup . '</a>';
        $Body2 .= $msg;


        $Body3 = $Body . $Body2;
        $Subject = $params->def('subject', JText::_('PLG_JBSBACKUP_REPORT'));
        $FromName = $params->def('fromname', $fromname);

        $recipients = explode(",", $params->get('recipients'));
        foreach ($recipients AS $recipient) {
            $mail->addRecipient($recipient);
            $mail->setSubject($Subject . ' ' . $livesite);
            $mail->setBody($Body3);
            if ($params->get('includedb') == 1) {
                $mail->addAttachment($dobackup);
            }
            $mail->Send();
        }
    }

    /**
     * Update files
     * @param array $params
     */
    function updatefiles($params) {
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        $path = JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'database';
        $exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX');
        $excludefilter = array('^\..*', '.*~');
        $files = JFolder::files($path, '.sql', 'false', 'true', $exclude, $excludefilter); // print_r($files);
        arsort($files, SORT_STRING);
        $parts = array();
        $numfiles = count($files);
        $totalnumber = $params->get('filestokeep', '5');

        for ($counter = $numfiles; $counter > $totalnumber; $counter--) {
            $parts[] = array_pop($files);
        }
        foreach ($parts as $part) {
            JFile::delete($part);
        }
    }

}