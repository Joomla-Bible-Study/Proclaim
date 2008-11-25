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

	function inlinePlayer() {
		$mediaPath = JRequest::getVar('url');
		$player =
					'<script language="JavaScript" src="'.JURI::base().'components/com_biblestudy/audio-player.js"></script>
<object type="application/x-shockwave-flash" data="'.JURI::base().'components/com_biblestudy/player.swf" id="audioplayer0" height="24" width="290">
<param name="movie" value="'.JURI::base().'components/com_biblestudy/player.swf">
<param name="FlashVars" value="playerID=0&amp;soundFile='.$mediaPath.'">
<param name="quality" value="high">
<param name="menu" value="false">
<param name="wmode" value="transparent">
</object> ';
		echo $player;
	}
		
}

?>
