<?php
/**
 * Set Definition for tags
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */

// No Direct Access
defined('_JEXEC') or die();

$BsmTmplTags = array(
	'[studyDate]'     => array(
		'method' => 'studyDate', 'type' => 'data', 'db' => 'studydate'
	), '[filterBook]' => array(
		'method' => 'filterBook', 'type' => 'generic', 'db' => null
	)
);
