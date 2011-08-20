<?php

/**
 * @version     $Id: teacheredit.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

//No Direct Access
defined('_JEXEC') or die();

jimport('joomla.application.component.controllerform');

abstract class controllerClass extends JControllerForm {

}

class biblestudyControllerteacheredit extends controllerClass {

	protected $view_list = 'teacherlist';

	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct() {
		parent::__construct();

		// Register Extra tasks
	}

}