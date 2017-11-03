<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2017 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Controller for Podcasts
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class BiblestudyControllerPodcasts extends JControllerAdmin
{
	/**
	 * Proxy for getModel
	 *
	 * @param   string  $name    The name of the model
	 * @param   string  $prefix  The prefix for the PHP class name
	 *
	 * @return JModel
	 *
	 * @since 7.0
	 */
	public function getModel($name = 'Podcast', $prefix = 'BiblestudyModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}
}
