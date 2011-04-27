<?php

/**
 * @author Tom Fuller
 * @copyright 2010-2011
 */

defined('_JEXEC') or die('Restricted access');

/* Import library dependencies */
jimport('joomla.event.plugin');
jimport('joomla.plugin.plugin');
class plgSystemjbspodcast extends JPlugin {
    
    	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
        $this->loadLanguage('com_biblestudy',JPATH_ADMINISTRATOR);
	}

    function onAfterInitialise() {
		
                
		$plugin =& JPluginHelper::getPlugin( 'system', 'jbspodcast' );
	//	$params = new JParameter( $plugin->params );
        $params = $this->params;
        
        //First check to see what method of updating the podcast we are using
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
            //perform the podcast and email and update time
            $dopodcast = $this->doPodcast();
        
            //If we have run the podcastcheck and it returned no errors then the last thing we do is reset the time we did it to current
            if ($dopodcast)
            {
                $updatetime = $this->updatetime(); 
            }
            
            // Last we check to see if we need to email anything
            if ($params->get('email')> 0)
            {
                $email = $this->doEmail($params, $dopodcast);
            }
        }
        
    }
    
    function checktime($params)
    {
        
        $now = time();
        $db = JFactory::getDBO();
        $db->setQuery('SELECT `timeset` FROM `#__bsms_timeset`', 0, 1);
        $result = $db->loadObject();
        $lasttime = $result->timeset;
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
        $db->setQuery('SELECT `timeset` FROM `#__bsms_timeset`', 0, 1);
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
        $db->setQuery('UPDATE `#__bsms_timeset` SET `timeset` = '.$time);
        $db->query();
        $updateresult = $db->getAffectedRows();
        if ($updateresult > 0) {return true;} else {return false;}
    }
    
    function doPodcast()
    {
        $path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
        include_once($path1.'writexml.php');
        $podcast = writeXML();
        return $podcast; 
    }
    
    function doEmail($params, $dopodcast)
    {
            $livesite = JURI::root();
            $config = JFactory::getConfig();
            $mailfrom   = $config->getValue('config.mailfrom');
            $fromname   = $config->getValue('config.fromname');
    		jimport('joomla.filesystem.file');
    		
            $mail = JFactory::getMailer();
    	 	$mail->IsHTML(true);
            jimport('joomla.utilities.date');
    		$year = '('.date('Y').')';
    		$date = date('r');
    	 	$Body   = $params->def( 'Body', '<strong>Podcast Publishing Update for this website: '.$fromname.'</strong><br />' );
            $Body .= JText::_('Process run at: ').$date.'<br />';
            $Body2 = '';
    	 	$db =& JFactory::getDBO();
    		$query = 'SELECT * FROM #__bsms_podcast WHERE #__bsms_podcast.published = 1';
    		$db->setQuery($query);
    		$podid = $db->loadObjectList();
    		//Here we get links to the actual podcast files					
    		if (count($podid)) 
    		{
    			foreach ($podid as $podids2) 
    			{
    				$file = JURI::root().$podids2->filename; 
					$Body2 .= '<br><a href="'.$file.'">'.$podids2->title.'</a>';
                    if (!$dopodcast){$Body2 .= ' - '.JText::_('There were errors reported. Please check files.');}
                    if ($dopodcast){$Body2 .= ' - '.JText::_('There were no errors reported.');}
    			}
    		}
    		$Body3 = $Body.$Body2;
    		$Subject       = $params->def( 'subject', 'Podcast Publishing Update' );
    		$FromName       = $params->def( 'fromname', $fromname );
    		
    		$recipients = explode(",",$params->get('recipients'));
            foreach ($recipients AS $recipient)
            {
                $mail->addRecipient($recipient);
        		$mail->setSubject($Subject.' '.$livesite);
        		$mail->setBody($Body3);
                $mail->Send();
            }
    
    }
}

?>