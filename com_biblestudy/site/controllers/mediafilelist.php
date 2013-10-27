<?php

/**
 * Controller for MediaFiles
 *
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Controller for MediaFiles
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyControllerMediafilelist extends JControllerAdmin
{

	/**
	 * Proxy for getModel
	 *
	 * @param   string $name    The name of the model
	 * @param   string $prefix  The prefix for the PHP class name
	 * @param   array  $config  Set ignore request
	 *
	 * @return JModel
	 *
	 * @since 7.0
	 */
	public function &getModel(
		$name = 'Mediafileform',
		$prefix = 'BiblestudyModel',
		$config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

}
