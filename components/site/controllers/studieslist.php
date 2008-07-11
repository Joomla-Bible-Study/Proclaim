<?php

defined('_JEXEC') or die();
jimport('joomla.application.component.controller');


class biblestudyControllerstudieslist extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function display()
	{
		parent::display();
	}

function download() {
	$abspath    = JPATH_SITE;
	require_once($abspath.DS.'components/com_biblestudy/class.biblestudydownload.php');
	$task = JRequest::getVar('task');
	if ($task == 'download')
		{
		$downloader = new Dump_File();
		$downloader->download();
		 die;
  		}
	}

	function avplayer() 
	{
		$task = JRequest::getVar('task');
		if ($task == 'avplayer')
			{
			$mediacode = JRequest::getVar('code');
			echo $mediacode;
			return;
			}
	}
}
?>
