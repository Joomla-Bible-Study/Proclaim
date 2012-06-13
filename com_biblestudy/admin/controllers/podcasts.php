<?php

/**
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Controler for Podcasts
 * 
 * @package BibleStudy.Admin
 */
class BiblestudyControllerPodcasts extends JControllerAdmin {

	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct() {
		parent::__construct();

		// Register Extra tasks
	}

	/**
	 * Proxy for getModel
	 *
	 * @param <String> $name    The name of the model
	 * @param <String> $prefix  The prefix for the PHP class name
	 * @return JModel
	 *
	 * @since 7.0
	 */
	public function &getModel($name = 'Podcast', $prefix = 'BiblestudyModel') {
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

}