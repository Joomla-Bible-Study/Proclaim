<?php

/**
 * @version $Id: version.php 1 $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

defined('_JEXEC') or die();
function latestVersion(){

	include_once( JPATH_ADMINISTRATOR.'/components/com_biblestudy/Snoopy.class.php' );

	$s = new Snoopy();
	$s->read_timeout = 90;
	$s->referer = JPATH_SITE;
	@$s->fetch('http://www.joomlabiblestudy.org/bsmsversion.php');
	$version = $s->results;

	if($s->error || $s->status != 200){
		$version = '<font color="red">Connection to update server failed: ERROR: ' . $s->error . ($s->status == -100 ? 'Timeout' : $s->status).'</font>';
	}
	return $version;
}
