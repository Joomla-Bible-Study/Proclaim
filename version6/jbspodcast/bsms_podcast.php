<?php
/**
 * Definition file
 *
 * Based on jombackup by Vince Wool
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 */
defined('_JEXEC') or die('Restricted access');

/* Import library dependencies */
jimport('joomla.event.plugin');

class plgSystembsms_podcast extends JPlugin {

	function onAfterInitialise() {
		global $mainframe;
		$site = JURI::root();
		$path1 = JPATH_SITE.DS.'/components/com_biblestudy/helpers/';
		include_once($path1.'writexml.php');
		$plugin =& JPluginHelper::getPlugin( 'system', 'bsms_podcast' );
		$pluginParams = new JParameter( $plugin->params );
		$db =& JFactory::getDBO();
		$config =& JFactory::getConfig();

		$lb_abspath    = JPATH_SITE;
		$lb_mailfrom   = $config->getValue('config.mailfrom');
		$lb_fromname   = $config->getValue('config.fromname');
		$lb_livesite   = JURI::root();
		$testing       = false;
		$mediaPath=$lb_abspath.'/media';
		$checkfileName='bsms_checkfile_';
		$today = date("Y-m-d-H");
		$dateCheckFile=$checkfileName.$today;
		$okToContinue=true;
			
		/**
		 * Check if we need to run again
		 */
		jimport( 'joomla.filesystem.folder' );
		$files = JFolder::files($mediaPath, 'bsms_checkfile(.*)');
		$lastruntime = 0;
		foreach ($files as $key => $filename) {
			if (stristr($filename, 'bsms_checkfile')) {
				$timestamp = strtotime(substr($filename, -13, 13));
				if ($timestamp > $lastruntime) {
					$lastruntime = strtotime(substr($filename, -13, 13));
				}
			}
		}
		$xdays = $pluginParams->def( 'xdays', 24 );
		$triggerdate = time() - ($xdays * 3600);
		//$triggerdate = time() - ($xdays * 86400);
		if ($lastruntime < $triggerdate) {
				
			/**
			 * You can manually set the production flag here if you don't want the
			 * "testing" option to kick in at any point. Effectively it means that
			 * the mambot query will not be run until $okToContinue is true, which
			 * only occurs if today's checkFile doesn't exist.
			 * If you DO manually set this flag, then of course none of the testing
			 * data will be echoed to your browser
			 */
			$production    = false;
				
			/**
			 * This query has been deliberately placed in this part of the code to
			 * allow the user to perform tests to ensure that the script is working,
			 * however it means that this script performs 1 sql query every time
			 * Joomla runs, unless $production is set to true.
			 */
			/* load mambot params info */
			if (!$production) {
				$testing   = $pluginParams->def( 'testing', 0 );
			}
			/* Finish bot parameter loading */
			 
			if ($testing) {
				$yesterday=date("Y-m-d-H" ,$lastruntime );
				$yesterdaysCheckfile=$checkfileName.$yesterday;
				if (is_file($mediaPath.'/'.$dateCheckFile) && @is_writable($mediaPath.'/'.$dateCheckFile) ) {
				 unlink($mediaPath.'/'.$dateCheckFile);
				}
			}
			/* a couple of simple checks to see if we need to actually do anything */
			if (is_writable($mediaPath) ) {
				/* The backup has already been done, no need to continue */
				if (is_file($mediaPath.'/'.$dateCheckFile) )
				$okToContinue=false;
				else
				{
				 if (!$testing) {
				 	/* Oops, we can't create the date check file, no point in continuing otherwise this plugin will run EVERY time a link is clicked in Joomla. Not good.*/
				 	if (!touch($mediaPath.'/'.$dateCheckFile))
				 	$okToContinue=false;
					}
				}
			}
			else
			$okToContinue=false;
			if ($testing) {
				if ($okToContinue)
				echo "Publishing and emailing <br />";
				else
				echo "Not publshing or emailing<br />";
			}
			if ($okToContinue) {
				/* No need to do the require beforehand if not ok to continue, so we'll do it here to save an eeny weeny amount of time */
				//require_once($lb_abspath.'/plugins/system/lazybackup/mysql_db_backup.class.php');
				/* Alternative location for Bot query  */
				if ($production) $testing   = $pluginParams->def( 'testing', 0 );
					

				/* Ok, let's crack on. First we want to get rid of the previous bsms_checkfile, no need to have that lying around now */
				$yesterday=date("Y-m-d-H" ,$lastruntime );
				$yesterdaysCheckfile=$checkfileName.$yesterday;
				if (is_file($mediaPath.'/'.$yesterdaysCheckfile) && @is_writable($mediaPath.'/'.$yesterdaysCheckfile) ) {
					unlink($mediaPath.'/'.$yesterdaysCheckfile);
				}
				/* Now we need to create the backup */

				$result= writeXML();
				//dump ($result, 'result: ');
				/* and email it to wherever */
				if ($pluginParams->get('email') > 0)
				{
					$mail =& JFactory::getMailer();
					$mail->IsHTML(true);
					$Body   = $pluginParams->def( 'Body', '<strong>Podcast Publishing Update confirmation.</strong><br><br> The following podcasts have been published: for this website: <br> <strong>'.$lb_fromname.'</strong>' );
					jimport('joomla.utilities.date');
					$year = '('.date('Y').')';
					$date = date('r');
					$db =& JFactory::getDBO();
					$query = 'SELECT * FROM #__bsms_podcast WHERE #__bsms_podcast.published = 1';
					$db->setQuery($query);
					$podid = $db->loadObjectList();
					//Here we get links to the actual podcast files

					if (count($podid))
					{
						foreach ($podid as $podids2)
						{
							$Body = $Body.'<br><a href="'.$site.$podids2->filename.'">'.$podids2->title.'</a>';
						}
					}
					$ToEmail       = $pluginParams->def( 'recipient', '' );
					$Subject       = $pluginParams->def( 'subject', 'Podcast Publishing Update' );
					$FromName       = $pluginParams->def( 'fromname', $lb_fromname );
					if (empty($ToEmail) ) $ToEmail=$lb_mailfrom;
						
					//$mail->addAttachment($Attachment);
					$mail->addRecipient($ToEmail);
					$mail->setSubject($Subject.' '.$lb_livesite);
					$mail->setBody($Body);
					$mail->Send();
					//$EmailResult=$this->lazybackupEmail($pluginParams,$lb_mailfrom,$lb_fromname,$result['output'],$lb_livesite);
					//dump ($EmailResult, 'email: ');
				}
					
				return true;
			}
			else if ($testing) {
				echo 'Nothing to do';
			}
		}

	}
}
?>