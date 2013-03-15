<?php
/**
 * Default
 *
 * @package   BibleStudy.Admin
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link      http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

include_once BIBLESTUDY_PATH_ADMIN_HELPERS . '/params.php';
$admin_params = JBSMParams::getAdmin();
var_dump($admin_params->debug);
