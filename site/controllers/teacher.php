<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
defined('_JEXEC') or die;

/**
 * Controller class for Teacher
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class Teacher extends BaseController
{
	/**
	 * Display the edit form
	 *
	 * @return void
	 *
	 * @since 7.0
	 */
	public function view()
	{
		$input = Factory::getApplication('site');
		$input->set('view', 'teacher');
		$input->set('layout', 'default');

		parent::display();
	}
}
