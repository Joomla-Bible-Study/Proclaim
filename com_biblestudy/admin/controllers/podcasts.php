<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Controller for Podcasts
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyControllerPodcasts extends JControllerAdmin
{

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      7.0.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Register Extra tasks
	}

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
