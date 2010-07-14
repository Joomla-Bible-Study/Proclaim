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
		//$this->registerTask( 'save'  , 	'apply' );
	}

	/**
	 * display the edit form
	 * @return void
	 */
	function edit()
	{
		JRequest::setVar( 'view', 'cssedit' );
		JRequest::setVar( 'layout', 'default'  );
		JRequest::setVar('hidemainmenu', 1);

		parent::display();
	}

	/**
	 * save a record (and redirect to main page)
	 * @return void
	 */




function resetcss() {
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


                        $mainframe->redirect('index.php?option='.$option.'&view=cssedit', JText::_('Operation Failed').': '.JText::_('Failed to open file for writing').': '.$filename));
		}
 // mosRedirect( "index2.php?option=$option&task=manage_css", "CSS has been reset to default settings." );
}





	function save()
	{
		global $mainframe;


		// Initialize some variables
		$option			= JRequest::getCmd('option');
		$client			=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$filename		= 'biblestudy.css';
		$filecontent	= JRequest::getVar('filecontent', '', 'post', 'string', JREQUEST_ALLOWRAW);

		if (!$filecontent) {
			$mainframe->redirect('index.php?option='.$option.'&view=cssedit', JText::_('Operation Failed').': '.JText::_('Content empty.'));
		}

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');

		$file = JPATH_ROOT.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.$filename;
		// Try to make the css file writeable

		jimport('joomla.filesystem.file');
		$return = JFile::write($file, $filecontent);


		if ($return)
		{

		$mainframe->redirect('index.php?option='.$option.'&view=cssedit',  JText::_('File Saved'));
		}
		else {
                        $mainframe->redirect('index.php?option='.$option.'&view=cssedit', JText::_('Operation Failed').': '.JText::_('Failed to open file for writing').': '.$file));
		}
	}
    function backup()
    {
        	global $mainframe;
            // Set FTP credentials, if given
    		jimport('joomla.client.helper');
    		JClientHelper::setCredentialsFromRequest('ftp');
    		$ftp = JClientHelper::getCredentials('ftp');
            $filename		= 'biblestudy.css';
    		$src = JPATH_ROOT.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.$filename;
            $dest = JPATH_ROOT.DS.'images'.DS.'biblestudy.css';

    		// Try to make the css file writeable

    		jimport('joomla.filesystem.file');
            $return = JFile::copy($src, $dest);
    		if ($return)
    		{

    		$mainframe->redirect('index.php?option=com_biblestudy&view=cssedit',  JText::_('Backup Saved to /images folder'));
    		}
    		else {
                        $mainframe->redirect('index.php?option=com_biblestudy&view=cssedit', JText::_('Operation Failed').': '.JText::_('Failed to open file for writing').': '.$file));
    		}
    }

    function copycss()
    {
        global $mainframe;
            // Set FTP credentials, if given
    		jimport('joomla.client.helper');
    		JClientHelper::setCredentialsFromRequest('ftp');
    		$ftp = JClientHelper::getCredentials('ftp');
            $filename		= 'biblestudy.css';
    		$dest = JPATH_ROOT.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.$filename;
            $src = JPATH_ROOT.DS.'images'.DS.'biblestudy.css';

    		// Try to make the css file writeable

    		jimport('joomla.filesystem.file');
            $return = JFile::copy($src, $dest);
    		if ($return)
    		{

    		$mainframe->redirect('index.php?option=com_biblestudy&view=cssedit',  JText::_('Backup restored from /images folder'));
    		}
    		else {
                        $mainframe->redirect('index.php?option=com_biblestudy&view=cssedit', JText::_('Operation Failed').': '.JText::_('Failed to open file for writing').': '.$file));
    		}
    }
}
?>
