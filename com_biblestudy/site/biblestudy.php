<?php

/**
 * Core BibleStudy Site File
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

// Include dependancies
require_once JPATH_COMPONENT.'/helpers/route.php';

/**
 * Joomla Core Toolbar
 */
require_once(JPATH_ADMINISTRATOR . '/includes/toolbar.php');

/**
 * Bible Study Core Difines
 */
require_once(JPATH_ADMINISTRATOR . '/components/com_biblestudy/lib/biblestudy.defines.php');

$controller = JControllerLegacy::getInstance('biblestudy');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();