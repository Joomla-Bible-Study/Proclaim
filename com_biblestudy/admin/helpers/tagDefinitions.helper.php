<?php
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

?>