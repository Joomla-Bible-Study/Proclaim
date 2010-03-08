<?php
/**
 * Series Detail Controller for Bible Study Component
 * 
 
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted Access');
//jimport('joomla.application.componet.controller');
/**
 *
 */
class biblestudyControllerseriesdetail extends JController
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		$this->registerTask('play');
		
		$start = JRequest::getInt('start',0,'get');
		if ($start == 1)
		{
			JRequest::setVar('start',1,'get');
		//	dump ($start, 'start: ');
			echo "<script> window.history.go(-1); </script>\n";
		}
		parent::__construct();
	}

	/**
	 * display the edit form
	 * @return void
	 */
	function view()
	{
		JRequest::setVar( 'view', 'seriesdetail' );
		JRequest::setVar( 'layout', 'default'  );
		$start = JRequest::getInt('start',0,'get');
		if ($start == 1)
		{
			JRequest::setVar('start',1,'get');
		//	dump ($start, 'start: ');
			echo "<script> window.history.go(-1); </script>\n";
		}
		parent::display();
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
	
	//	if ($start == 1 && $player == 2)
	//	{
			JRequest::setVar('start',1,'get');
		//	dump ($start, 'start: ');
			echo "<script> window.history.go(-1); </script>\n";
	//	}
//		if ($start == 1 && $player == 1)
//		{
//			JRequest::setVar('start',1,'get');
//			echo "<script> tryStartPlayer(".$media.")</script>\n
//			<script>window.history.go(-1); </script>\n";
//		}
/*		if ($start == 1 && $player == 0)
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
		return; */
	}		
	
}
	

?>
