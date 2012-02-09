<?php

/**
 * @version     $Id: shareedit.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

abstract class controllerClass extends JControllerForm {

}


class biblestudyControllershareedit extends controllerClass
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	protected $view_list = 'sharelist';

	function __construct()
	{
		parent::__construct();
	}
}