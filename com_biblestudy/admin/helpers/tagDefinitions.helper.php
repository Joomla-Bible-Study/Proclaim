<?php

/**
 * @version $Id$
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/


defined('_JEXEC') or die('Restriced Access');

$BsmTmplTags = array(
	'[studyDate]' => array(
		'method' => 'studyDate',
		'type' => 'data',
		'db' => 'studydate'
),
	'[filterBook]' => array(
		'method' => 'filterBook',
		'type' => 'generic',
		'db' => null
)
);