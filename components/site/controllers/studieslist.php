<?php

defined('_JEXEC') or die();
jimport('joomla.application.component.controller');

class biblestudyControllerstudieslist extends JController
{
	var $mediaCode;
	
	/**
	 *@desc Method to display the view
	 *@access public
	 */
	 
	function display()
	{
		$this->registerTask( 'play' );
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
			$this->mediaCode = $mediacode;
			echo $mediacode;
			return;
		}
	}
	
	/**
	 * @desc: This function is supposed to generate the Media Player that is requested via AJAX
	 * from the studiesList view "default.php". It has not been implemented yet, so its not used.
	 * @return unknown_type
	 */
	function inlinePlayer() {
		//echo $this->mediaCode;
		echo('{m4vremote}http://www.livingwatersweb.com/video/John_14_15-31.m4v{/m4vremote}');
	}

function play()
	{
		$player = null;
		$media = null;
		$t = null;
		$view = null;
		$start = null;
		$player = JRequest::getInt('player',2,'get');
		$media = JRequest::getInt('mediaid',1,'get');
		$t = JRequest::getInt('templatemenuid',1,'get');
		$view = JRequest::getWord('view','studieslist','get');
		$start = JRequest::getInt('start',0,'get');
		$task = JRequest::getVar('task');
		
	//	dump ($start, 'start: ');
	
		if ($start == 1 && $player == 2)
		{
			JRequest::setVar('start',1,'get');
		//	dump ($start, 'start: ');
			echo "<script> window.history.go(-1); </script>\n";
		}
//		if ($start == 1 && $player == 1)
//		{
//			JRequest::setVar('start',1,'get');
//			echo "<script> tryStartPlayer(".$media.")</script>\n
//			<script>window.history.go(-1); </script>\n";
//		}
		if ($start == 1 && $player == 0)
		{
			require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.media.class.php');
			$mediaget = new jbsMedia();
			$medialink = $mediaget->getMediaLink($mediaid);
			$play = $mediaget->hitPlay($media); //dump ($medialink, 'media: ');
	//		$this->setRedirect('http://'.$medialink);
			echo "<script>";
			echo " self.location='http://".$medialink."';";
			echo "</script>"; 
		}
		return;
	}		

}

?>
