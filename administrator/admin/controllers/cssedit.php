<?php
/**
 * CSS Edit Controller for Bible Study Component
 * 
 
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Series Edit Controller
 *
 */
class biblestudyControllercssedit extends JController
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		//$this->registerTask( 'save'  , 	'edit', 'saveCSS' );
	}

	/**
	 * display the edit form
	 * @return void
	 */
	function edit()
	{
		JRequest::setVar( 'view', 'cssedit' );
		JRequest::setVar( 'layout', 'default'  );
		JRequest::setVar('hidemainmenu', 0);

		parent::display();
	}

	/**
	 * save a record (and redirect to main page)
	 * @return void
	 */
	function save2()
	 {
		 global $mainframe, $option; 
		//$filecontent = $podhead.$episodedetail.$podfoot;
  		$filecontent = JRequest::getVar('config_css', '', 'post', 'string', JREQUEST_ALLOWRAW);
		dump ($filecontent, 'filecontent: ');
  // Initialize some variables
  $option = JRequest::getCmd('option');

  if (!$filecontent) {
   $mainframe->redirect('index.php?option='.$option.'&view=studieslist', JText::_('Operation Failed').': '.JText::_('Content empty.'));
  }

  // Set FTP credentials, if given
  jimport('joomla.client.helper');
  jimport('joomla.filesystem.file');
  JClientHelper::setCredentialsFromRequest('ftp');
  $ftp = JClientHelper::getCredentials('ftp');
  $client =& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
  //$file = $client->path.DS.'templates'.DS.$template.DS.'index.php';
  $file = JPATH_ROOT.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css';


  // Try to make the template file writeable
  if (JFile::exists($file) && !$ftp['enabled'] && !JPath::setPermissions($file, '0755')) {
   JError::raiseNotice('SOME_ERROR_CODE', 'Could not make the file writable');
  }

  $return = JFile::write($file, $filecontent);

  // Try to make the template file unwriteable
  if (!$ftp['enabled'] && !JPath::setPermissions($file, '0555')) {
   JError::raiseNotice('SOME_ERROR_CODE', 'Could not make the file unwritable');
  }

  if ($return)
  {
   $task = JRequest::getCmd('task');
   switch($task)
   {
    case 'apply_source':
     $mainframe->redirect('index.php?option='.$option.'&view=studielist', JText::_($podinfo->filename.' saved'));
     break;

    case 'save_source':
    default:
     $mainframe->redirect('index.php?option='.$option.'&view=studieslist', JText::_($podinfo->filename.' saved'));
     break;
   }
  }
  else {
   $mainframe->redirect('index.php?option='.$option.'&view=studieslist', JText::_('Operation Failed').': '.JText::_('Failed to open file for writing.'));
  }
  
	//$config_css = mosGetParam( $_POST, 'config_css' );
	//$config_css = JRequest::getVar('config_css', 'POST');
	//$config_css = JRequest::getVar('config_css', '', 'post', 'string', JREQUEST_ALLOWRAW);
	
	//dump ($config_css, 'config_css');
		//$configcss=str_replace("[CR][NL]","\n",$config_css);
		//$configcss=str_replace("[ES][SQ]","'",$configcss);
		//$configcss=nl2br($configcss);
		//$configcss=str_replace("<br />"," ",$configcss);
		//$filename = JPATH_ROOT.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css';
		//jimport('joomla.filesystem.file');
		//$filecontent = $configcss;
		//$return = JFile::write($filename, $filecontent);
		//$cssfilein=fopen($filename,"w+") or die("Can't open file $filename");
		//$filecontent=fread($cssfilein,filesize($filename));
		//$cssfileout=fwrite($cssfilein,$configcss);
		//fclose($cssfilein);
		
//if ($return) {$msg = 'CSS Saved';}
//else { $msg = 'Problem writing file';
//$mainframe->redirect('index.php?option=com_biblestudy&view=studieslist', JText::_('File Saved'));}
 // $link = 'index.php?option=com_biblestudy&view=studieslist';
		//$this->setRedirect($link, $msg);
}

function reset() {
	global $mainframe, $option;
		$savfilename = JPATH_ROOT.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.sav';
		$savcssfilein=fopen($savfilename,"r") or die("Can't open file $savfilename");
		$savfilecontent=fread($savcssfilein,filesize($savfilename));
		$replacecss=str_replace("[CR][NL]","\n",$savfilecontent);
		$replacecss=str_replace("[ES][SQ]","'",$replacecss);
		$replacecss=nl2br($replacecss);
		$replacecss=str_replace("<br />"," ",$replacecss);
		$filename=JPATH_ROOT.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css';
		$cssfilein=fopen($filename,"w+") or die("Can't open file $filename");
		$return = $cssfileout=fwrite($cssfilein,$replacecss);
		fclose($cssfilein);
		fclose($savcssfilein);
if ($return)
		{
			$task = JRequest::getCmd('task');
			switch($task)
			{
				case 'apply_css':
					$mainframe->redirect('index.php?option='.$option.'&view=cssedit',  JText::_('File Reset to Default'));
					break;

				case 'save_css':
				default:
					$mainframe->redirect('index.php?option='.$option.'&view=cssedit', JText::_('File Reset To Default'));
					break;
			}
		}
		else {
			$mainframe->redirect('index.php?option='.$option.'&view=cssedit', JText::_('Operation Failed').': '.JText::sprintf('Failed to open file for writing.', $filename));
		}
 // mosRedirect( "index2.php?option=$option&task=manage_css", "CSS has been reset to default settings." );
}

		

		function editCSS()
	{
		global $mainframe;

		// Initialize some variables
		$option		= JRequest::getCmd('option');
		$client		=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$template	= JRequest::getVar('id', '', 'method', 'cmd');
		$filename	= JRequest::getVar('filename', '', 'method', 'cmd');

		jimport('joomla.filesystem.file');

		if (JFile::getExt($filename) !== 'css') {
			$msg = JText::_('Wrong file type given, only CSS files can be edited.');
			$mainframe->redirect('index.php?option='.$option.'&client='.$client->id.'&task=choose_css', $msg, 'error');
		}

		$content = JFile::read($client->path.DS.'administrator'.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.$filename);

		if ($content !== false)
		{
			// Set FTP credentials, if given
			jimport('joomla.client.helper');
			$ftp =& JClientHelper::setCredentialsFromRequest('ftp');

			$content = htmlspecialchars($content, ENT_COMPAT, 'UTF-8');
			require_once (JPATH_COMPONENT.DS.'admin.templates.html.php');
			TemplatesView::editCSSSource($template, $filename, $content, $option, $client, $ftp);
		}
		else
		{
			$msg = JText::sprintf('Operation Failed Could not open', $client->path.$filename);
			$mainframe->redirect('index.php?option='.$option.'&client='.$client->id, $msg);
		}
	}

	function save()
	{
		global $mainframe;

		// Check for request forgeries
		//JRequest::checkToken() or jexit( 'Invalid Token' );

		// Initialize some variables
		$option			= JRequest::getCmd('option');
		$client			=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		//$template		= JRequest::getVar('id', '', 'post', 'cmd');
		//$filename		= JRequest::getVar('filename', '', 'post', 'cmd');
		$filename		= 'biblestudy.css';
		$filecontent	= JRequest::getVar('filecontent', '', 'post', 'string', JREQUEST_ALLOWRAW);

		//if (!$template) {
			//$mainframe->redirect('index.php?option='.$option.'&client='.$client->id, JText::_('Operation Failed').': '.JText::_('No template specified.'));
		//}

		if (!$filecontent) {
			$mainframe->redirect('index.php?option='.$option.'&view=cssedit', JText::_('Operation Failed').': '.JText::_('Content empty.'));
		}

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');

		//$file = $client->path.DS.'administrator'.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.$filename;
		$file = JPATH_ROOT.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.$filename;
		// Try to make the css file writeable
		//if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0755')) {
			//JError::raiseNotice('SOME_ERROR_CODE', JText::_('Could not make the css file writable'));
		//}

		jimport('joomla.filesystem.file');
		$return = JFile::write($file, $filecontent);

		// Try to make the css file unwriteable
		//if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0555')) {
			//JError::raiseNotice('SOME_ERROR_CODE', JText::_('Could not make the css file unwritable'));
		//}

		if ($return)
		{
			$task = JRequest::getCmd('task');
			switch($task)
			{
				case 'apply_css':
					$mainframe->redirect('index.php?option='.$option.'&view=cssedit',  JText::_('File Saved'));
					break;

				case 'save_css':
				default:
					$mainframe->redirect('index.php?option='.$option.'&view=cssedit', JText::_('File Saved'));
					break;
			}
		}
		else {
			$mainframe->redirect('index.php?option='.$option.'&view=cssedit', JText::_('Operation Failed').': '.JText::sprintf('Failed to open file for writing.', $file));
		}
	}
}
?>
