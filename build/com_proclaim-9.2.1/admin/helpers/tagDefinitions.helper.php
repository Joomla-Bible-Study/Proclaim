<?php
/**
 * Set Definition for tags
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
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
