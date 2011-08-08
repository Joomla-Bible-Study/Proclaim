<?php

/**
 * @version     $Id: mediafilesedit.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/
//No Direct Access
defined('_JEXEC') or die();

    jimport('joomla.application.component.controllerform');

    abstract class controllerClass extends JControllerForm {

    }

class biblestudyControllermediafilesedit extends controllerClass {

    /*
     * NOTE: This is needed to prevent Joomla 1.6's pluralization mechanisim from kicking in
     *
     * @todo bcc   We should rename this controler to "mediafile" and the list view controller
     * to "mediafiles" so that the pluralization in 1.6 would work properly
     *
     * @since 7.0
     */
    protected $view_list = 'mediafileslist';

    /**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();
		// Register Extra tasks
	}

	function docmanCategoryItems() {
		//hide errors and warnings
		error_reporting(0);
		$catId = JRequest::getVar('catId');

		$model =& $this->getModel('mediafilesedit');
		$items =& $model->getdocManCategoryItems($catId);
		echo $items;
	}

	function articlesSectionCategories() {
		error_reporting(0);
		$secId = JRequest::getVar('secId');

		$model =& $this->getModel('mediafilesedit');
		$items =& $model->getArticlesSectionCategories($secId);
		echo $items;

	}

	function articlesCategoryItems() {
		error_reporting(0);
		$catId = JRequest::getVar('catId');

		$model =& $this->getModel('mediafilesedit');
		$items =& $model->getCategoryItems($catId);
		echo $items;
	}
	function virtueMartItems(){
		error_reporting(0);
		$catId = JRequest::getVar('catId');

		$model =& $this->getModel('mediafilesedit');
		$items =& $model->getVirtueMartItems($catId);
		echo $items;
	}
	

	function resetDownloads()
	{
		$msg = null;
		$id 	= JRequest::getInt( 'id', 0, 'post');
		$db = JFactory::getDBO();
		$db->setQuery("UPDATE #__bsms_mediafiles SET downloads='0' WHERE id = ".$id);
		$reset = $db->query();
        if ($db->getErrorNum() > 0)
				{
					$error = $db->getErrorMsg();
                    $msg = JText::_('JBS_CMN_ERROR_RESETTING_DOWNLOADS').' '.$error;
					$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafilesedit&controller=admin&layout=form&cid[]='.$id, $msg );
				}
		else
			{
				$updated = $db->getAffectedRows();
                $msg = JText::_('JBS_CMN_RESET_SUCCESSFUL').' '.$updated.' '.JText::_('JBS_CMN_ROWS_RESET');
				$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafilesedit&controller=studiesedit&layout=form&cid[]='.$id, $msg );
			}
	}

	function resetPlays()
	{
		$msg = null;
		$id 	= JRequest::getInt( 'id', 0, 'post');
		$db = JFactory::getDBO();
		$db->setQuery("UPDATE #__bsms_mediafiles SET plays='0' WHERE id = ".$id);
		$reset = $db->query();
        if ($db->getErrorNum() > 0)
				{
					$error = $db->getErrorMsg();
                    $msg = JText::_('JBS_CMN_ERROR_RESETTING_PLAYS').' '.$error;
					$this->setRedirect( 'index.php?option=com_biblestudy&view=mediafilesedit&controller=admin&layout=form&cid[]='.$id, $msg );
				}
		else
			{
				$updated = $db->getAffectedRows();
                $msg = JText::_('JBS_CMN_RESET_SUCCESSFUL').' '.$updated.' '.JText::_('JBS_CMN_ROWS_RESET');
                $this->setRedirect( 'index.php?option=com_biblestudy&view=mediafilesedit&controller=studiesedit&layout=form&cid[]='.$id, $msg );
			}
	}
}

	//New File Size System Should work on all server now.
	function getSizeFile ($url){ 
	$head = ""; 
	$url_p = @parse_url($url); 
	$host = $url_p["host"]; 
	if(!preg_match("/[0-9]*\.[0-9]*\.[0-9]*\.[0-9]*/",$host)){
		// a domain name was given, not an IP
		$ip=gethostbyname($host);
		if(!preg_match("/[0-9]*\.[0-9]*\.[0-9]*\.[0-9]*/",$ip)){
			//domain could not be resolved
			return -1;
		}
	}
	$port = intval($url_p["port"]); 
	if(!$port) $port=80;
	$path = $url_p["path"];

	$fp = fsockopen($host, $port, $errno, $errstr, 20); 
	if(!$fp) { 
		return false; 
		} else { 
		fputs($fp, "HEAD "  . $url  . " HTTP/1.1\r\n"); 
		fputs($fp, "HOST: " . $host . "\r\n"); 
		fputs($fp, "User-Agent: http://www.example.com/my_application\r\n");
		fputs($fp, "Connection: close\r\n\r\n"); 
		$headers = ""; 
		while (!feof($fp)) { 
			$headers .= fgets ($fp, 128); 
			} 
		} 
	fclose ($fp);
	$return = -2; 
	$arr_headers = explode("\n", $headers);
	foreach($arr_headers as $header) {
		$s1 = "HTTP/1.1"; 
		$s2 = "Content-Length: "; 
		$s3 = "Location: "; 
		if(substr(strtolower ($header), 0, strlen($s1)) == strtolower($s1)) $status = substr($header, strlen($s1)); 
		if(substr(strtolower ($header), 0, strlen($s2)) == strtolower($s2)) $size   = substr($header, strlen($s2));  
		if(substr(strtolower ($header), 0, strlen($s3)) == strtolower($s3)) $newurl = substr($header, strlen($s3));  
		}
	if(intval($size) > 0) {
		$return=strval($size);
	} else {
		$return=$status;
	}
	if (intval($status)==302 && strlen($newurl) > 0) {
		// 302 redirect: get HTTP HEAD of new URL
		$return=getSizeFile($newurl);
	}
	return $return; 
} 
