<?php

/**
 * @version     $Id: teachers.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

class BiblestudyControllerTeachers extends JControllerAdmin {
	/**
	 * Proxy for getModel
	 *
	 * @param <String> $name    The name of the model
	 * @param <String> $prefix  The prefix for the PHP class name
	 * @return JModel
	 *
	 * @since 7.0
	 */
	public function &getModel($name = 'Teacher', $prefix = 'BiblestudyModel', $config = array('ignore_request' => true)) {
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
}