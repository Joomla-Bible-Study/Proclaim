<?php

/**
 * @version $Id: biblestudy.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//This function is designed to save the template params to the database
//No Direct Access
defined('_JEXEC') or die;
//@todo not sure why this file is hear????
function bind($array, $ignore = '') {
    if (key_exists('params', $array) && is_array($array['params'])) {
        $registry = new JRegistry();
        $registry->loadArray($array['params']);
        $array['params'] = $registry->toString();
    }
    return parent::bind($array, $ignore);
}
