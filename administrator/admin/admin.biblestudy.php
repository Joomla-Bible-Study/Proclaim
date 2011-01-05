<?php

/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

define('JSTART', '$j(document).ready( function() {');
define('JSTOP', '});');

jimport('joomla.application.component.controller');

$controller = JController::getInstance('biblestudy');

$controller->execute(JRequest::getCmd('task'));
$controller->redirect();

?>
