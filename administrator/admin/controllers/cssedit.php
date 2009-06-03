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
		$this->registerTask( 'saveCSS'  , 	'editCSS' );
	}

	/**
	 * display the edit form
	 * @return void
	 */
	function edit()
	{
		JRequest::setVar( 'view', 'cssedit' );
		JRequest::setVar( 'layout', 'form'  );
		JRequest::setVar('hidemainmenu', 1);

		parent::display();
	}

	/**
	 * save a record (and redirect to main page)
	 * @return void
	 */
	function save()
	{
	// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_biblestudy&view=serieslist';
		$this->setRedirect($link, $msg);
	}
	function saveCss($option) {
	$config_css = mosGetParam( $_POST, 'config_css' );
		$configcss=str_replace("[CR][NL]","\n",$config_css);
		$configcss=str_replace("[ES][SQ]","'",$configcss);
		$configcss=nl2br($configcss);
		$configcss=str_replace("<br />"," ",$configcss);
		$filename=dirname(__FILE__)."/../../../components/com_prayercenter/css/prayercenter.css";
		$cssfilein=fopen($filename,"w+") or die("Can't open file $filename");
		$filecontent=fread($cssfilein,filesize($filename));
		$cssfileout=fwrite($cssfilein,$configcss);
		fclose($cssfilein);

  mosRedirect( "index2.php?option=$option&task=manage_css", "Changes in CSS have been saved." );
}

function resetCss($option) {
		$savfilename=dirname(__FILE__)."/../../../components/com_prayercenter/css/prayercenter.sav";
		$savcssfilein=fopen($savfilename,"r") or die("Can't open file $savfilename");
		$savfilecontent=fread($savcssfilein,filesize($savfilename));
		$replacecss=str_replace("[CR][NL]","\n",$savfilecontent);
		$replacecss=str_replace("[ES][SQ]","'",$replacecss);
		$replacecss=nl2br($replacecss);
		$replacecss=str_replace("<br />"," ",$replacecss);
		$filename=dirname(__FILE__)."/../../../components/com_prayercenter/css/prayercenter.css";
		$cssfilein=fopen($filename,"w+") or die("Can't open file $filename");
		$cssfileout=fwrite($cssfilein,$replacecss);
		fclose($cssfilein);
		fclose($savcssfilein);

  mosRedirect( "index2.php?option=$option&task=manage_css", "CSS has been reset to default settings." );
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

	function saveCSS()
	{
		global $mainframe;

		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		// Initialize some variables
		$option			= JRequest::getCmd('option');
		$client			=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$template		= JRequest::getVar('id', '', 'post', 'cmd');
		$filename		= JRequest::getVar('filename', '', 'post', 'cmd');
		$filename		= 'biblestudy.css';
		$filecontent	= JRequest::getVar('filecontent', '', 'post', 'string', JREQUEST_ALLOWRAW);

		if (!$template) {
			$mainframe->redirect('index.php?option='.$option.'&client='.$client->id, JText::_('Operation Failed').': '.JText::_('No template specified.'));
		}

		if (!$filecontent) {
			$mainframe->redirect('index.php?option='.$option.'&client='.$client->id, JText::_('Operation Failed').': '.JText::_('Content empty.'));
		}

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');

		$file = $client->path.DS.'administrator'.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.$filename;

		// Try to make the css file writeable
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0755')) {
			JError::raiseNotice('SOME_ERROR_CODE', JText::_('Could not make the css file writable'));
		}

		jimport('joomla.filesystem.file');
		$return = JFile::write($file, $filecontent);

		// Try to make the css file unwriteable
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0555')) {
			JError::raiseNotice('SOME_ERROR_CODE', JText::_('Could not make the css file unwritable'));
		}

		if ($return)
		{
			$task = JRequest::getCmd('task');
			switch($task)
			{
				case 'apply_css':
					$mainframe->redirect('index.php?option='.$option.'&client='.$client->id.'&task=edit_css&filename='.$filename,  JText::_('File Saved'));
					break;

				case 'save_css':
				default:
					$mainframe->redirect('index.php?option='.$option.'&client='.$client->id.'&task=edit', JText::_('File Saved'));
					break;
			}
		}
		else {
			$mainframe->redirect('index.php?option='.$option.'&client='.$client->id.'&task=choose_css', JText::_('Operation Failed').': '.JText::sprintf('Failed to open file for writing.', $file));
		}
	}
}
?>
