<?php

/**
 * Controller Messages
 *
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Base this model on the backend version.
JLoader::register('BiblestudyControllerMessages', JPATH_ADMINISTRATOR . '/components/com_biblestudy/controllers/messages.php');

/**
 * Controller class for Messages
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyControllerMessagelist extends BiblestudyControllerMessages
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
