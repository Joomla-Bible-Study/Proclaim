<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * MediaImages list controller class
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyControllerMediaimages extends JControllerAdmin
{

	/**
	 * Proxy for getModel
	 *
	 * @param   string $name    The name of the model
	 * @param   string $prefix  The prefix for the PHP class name
	 *
	 * @return JModel
	 *
	 * @since 7.0
	 */
	public function getModel($name = 'mediaimage', $prefix = 'biblestudyModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

}
