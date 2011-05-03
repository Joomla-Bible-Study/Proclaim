<?php

/**
 * @author Tom Fuller
 * @copyright 2011
 */

defined('_JEXEC') or die('Restricted access');

/* Import library dependencies */

jimport('joomla.plugin.plugin');
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

 public function __construct(& $subject, $config)
        {
 
                parent::__construct($subject, $config);
 
                $this->loadLanguage();
                $this->loadLanguage('com_biblestudy',JPATH_ADMINISTRATOR);
 
        }
    function onAfterInitialise() {
		
                
	    $params = $this->params;
        
        //First check to see what method of updating the backup we are using
        $method = $params->get('method','0');
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
            //perform the backup and email and update time and zip file
            $dobackup = $this->doBackup();
        
            //If we have run the backupcheck and it returned no errors then the last thing we do is reset the time we did it to current
            if ($dobackup)
            {
                $updatetime = $this->updatetime();
                $updatefiles = $this->updatefiles($params); 
            }
            
            // Last we check to see if we need to email anything
            if ($check && $params->get('email')> 0)
            {
                $email = $this->doEmail($params, $dobackup);
            }
        }
        
    }
    
    function checktime($params)
    {
        
        $now = time();
        $db = JFactory::getDBO();
        $db->setQuery('SELECT `backup` FROM `#__bsms_timeset`', 0, 1);
        $result = $db->loadObject();
        $lasttime = $result->backup;
        $frequency = $params->get('xhours','86400');
        $difference = $frequency * 3600;
        $checkit = $now - $lasttime;
         if ($checkit > $difference) {return true;}
         else {return false;}
    }
    
    function checkdays($params)
    {
        $checkdays = FALSE;
        $config =& JFactory::getConfig();
        $offset = $config->getValue('config.offset');
        
        $now = time();
        $db = JFactory::getDBO();
        $db->setQuery('SELECT `backup` FROM `#__bsms_timeset`', 0, 1);
        $result = $db->loadObject();
        $lasttime = $result->timeset;
        $difference = $now - $lasttime;
        $date = getdate($now);
        $day = $date['wday'];
        $systemhour = $date['hours'];
        if ($params->get('offset', '0') > 0){$hour = $systemhour + $offset;} else {$hour = $systemhour;}
        
        if ($params->get('day1')== $day && $params->get('hour1') == $hour && $difference > 3600)
        {
           $checkdays = TRUE;
        }
        if ($params->get('day2')== $day)
        {
            if ($params->get('hour2') == $hour && $difference > 3600)
            {
                $checkdays = TRUE;
            }
        }
        if ($params->get('day3')== $day)
        {
            if ($params->get('hour3') == $hour && $difference > 3600)
            {
                $checkdays = TRUE;
            }
        }
        if ($params->get('day4')== $day)
        {
            if ($params->get('hour4') == $hour && $difference > 3600)
            {
                $checkdays = TRUE;
            }
        }
        if ($params->get('day5')== $day)
        {
            if ($params->get('hour5') == $hour && $difference > 3600)
            {
                $checkdays = TRUE;
            }
        }
        if ($params->get('day6')== $day)
        {
            if ($params->get('hour6') == $hour && $difference > 3600)
            {
                $checkdays = TRUE;
            }
        }
        if ($params->get('day7')== $day)
        {
            if ($params->get('hour7') == $hour && $difference > 3600)
            {
                $checkdays = TRUE;
            }
        }
        if ($params->get('day8')== $day)
        {
            if ($params->get('hour8') == $hour && $difference > 3600)
            {
                $checkdays = TRUE;
            }
        }
        if ($params->get('day9')== $day)
        {
            if ($params->get('hour9') == $hour && $difference > 3600)
            {
                $checkdays = TRUE;
            }
        }
        if ($params->get('day10')== $day)
        {
            if ($params->get('hour10') == $hour && $difference > 3600)
            {
                $checkdays = TRUE;
            }
        }
        
        return $checkdays;
    }
    function updatetime()
    {
        $time = time();
        $db = JFactory::getDBO();
        $db->setQuery('UPDATE `#__bsms_timeset` SET `backup` = '.$time);
        $db->query();
        $updateresult = $db->getAffectedRows();
        if ($updateresult > 0) {return true;} else {return false;}
    }
    
    function doBackup()
    {
        $backupfolder = 'media'.DS.$this->params->get('backupfolder');
        $path1 = JPATH_SITE.DS.'plugins'.DS.'system'.DS.'jbsbackup'.DS;
        include_once($path1.'backup.php');
        $dbbackup = new JBSExport();
        $backup = $dbbackup->exportdb($backupfolder);
        return $backup; 
    }
    
    function doEmail($params, $dobackup)
    {
            $livesite = JURI::root();
            $config = JFactory::getConfig();
            $mailfrom   = $config->getValue('config.mailfrom');
            $fromname   = $config->getValue('config.fromname');
    		jimport('joomla.filesystem.file');
    		
            //Check for existence of backup file, then attach to email
            $backupexists = JFile::exists($dobackup['serverfile']);
            if (!$backupexists){$msg = JText::_('PLG_JBSBACKUP_ERROR');}
            else {$msg = JText::_('PLG_JBSBACKUP_SUCCESS');}
            $mail = JFactory::getMailer();
    	 	$mail->IsHTML(true);
            jimport('joomla.utilities.date');
    		$year = '('.date('Y').')';
    		$date = date('r');
    	 	$Body   = $params->def( 'Body', '<strong>'.JText::_('PLG_JBSBACKUP_HEADER'). ' '.$fromname.'</strong><br />' );
            $Body .= JText::_('Process run at: ').$date.'<br />';
            $Body2 = '';
    	 				
    		
					$Body2 .= '<br><a href="'.JURI::root().'media'.DS.$dobackup['localfilename'].'">'.$dobackup['localfilename'].'</a>';
                    $Body2 .= ' - '.$msg;
    			
    		
    		$Body3 = $Body.$Body2;
    		$Subject       = $params->def( 'subject', JText::_('PLG_JBSBACKUP_REPORT'));
    		$FromName       = $params->def( 'fromname', $fromname );
    		
    		$recipients = explode(",",$params->get('recipients'));
            foreach ($recipients AS $recipient)
            {
                $mail->addRecipient($recipient);
        		$mail->setSubject($Subject.' '.$livesite);
        		$mail->setBody($Body3);
                if ($params->get('includedb')== 1)
                {$mail->addAttachment($dobackup['serverfile']);}
                $mail->Send();
            }
    
    }
    
    function updatefiles($params)
    {
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        $path = JPATH_SITE.DS.'media'.DS.$params->get('backupfolder');
        $exclude = array('.svn', 'CVS','.DS_Store','__MACOSX');
        $excludefilter = array('^\..*','.*~');
        $files = JFolder::files($path, '', '', 'false' , $exclude,$excludefilter);
        foreach ($files as $i => $value)
        {
            if (!substr_count($value,'jbs-db-backup')){unset($files[$i]);};
        }
       $part = array();
       $numfiles = count($files);
       $totalnumber = $params->get('filestokeep','5');
       foreach ($files as $file)
       {
            $part[] = array('number'=>substr($file,-14,10), 'filename'=>$file);
       }
       
      for ($counter = $numfiles; $counter > $totalnumber; $counter --)
      {
        $sort = asort($part);
        JFile::delete($part[0]['filename']);
      }
    }
}

?>