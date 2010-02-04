<?php
defined('_JEXEC') or die();
/**
 * @author Joomla Bible Study
 * @copyright 2010
 */
//Check to see if the css file exists. If it does, don't do anything. If not, install the css file
jimport('joomla.filesystem.file');
$src = JPATH_SITE.DS.'components/com_biblestudy/assets/css/biblestudy.css.dist';
$dest = JPATH_SITE.DS.'components/com_biblestudy/assets/css/biblestudy.css';
$cssexists = JFile::exists($dest);
if (!$cssexists)
	{
		JFile::copy($src, $dest);
		print 'CSS data installed';
	}


?>