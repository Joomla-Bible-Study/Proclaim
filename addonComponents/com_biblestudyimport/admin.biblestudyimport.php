<?php
/*
 Program to Import Mp3s into Bible Study Component tables.
 Version 1.1
 Jesus Loves You!
 */
defined('_JEXEC') or die('Restricted access');

$controller = JRequest::getVar('controller', 'mp3');
require_once (JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php');


$classname	= 'biblestudyImportController'.$controller;
$controller = new $classname();
$controller->execute(JRequest::getVar('task', 'main'));
$controller->redirect();