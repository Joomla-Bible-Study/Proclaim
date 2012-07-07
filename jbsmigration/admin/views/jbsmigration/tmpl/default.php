<?php

/**
 * @package BibleStudy
 * @subpackage JBSMigration
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
$done = JRequest::getInt('migrationdone', '', 'get');
if ($done > 0) {
    echo $this->loadTemplate('messages');
}
echo $this->loadTemplate('main');

