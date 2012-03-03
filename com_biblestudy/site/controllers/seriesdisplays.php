<?php

/**
 * @version $Id: seriesdisplays.php 1 $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

//No Direct Access
defined('_JEXEC') or die;
jimport('joomla.application.component.controller');

class BiblestudyControllerSeriesdisplays extends JController
{
	var $mediaCode;

	/**
	 *@desc Method to display the view
	 *@access public
	 */
	function display()
	{
		parent::display();
	}

	function download() {
		$abspath    = JPATH_SITE;
		require_once($abspath.DIRECTORY_SEPARATOR.'components/com_biblestudy/lib/biblestudy.download.class.php');
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
		echo('{m4vremote}http://www.livingwatersweb.com/video/John_14_15-31.m4v{/m4vremote}');
	}

}