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
defined('_JEXEC') or die;

/**
 * Controller class for Teacher
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
namespace CWM\Component\biblestudy\site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;


class TeacherController extends BaseController
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

        $app = Factory::getApplication('site');
		$app->set('view', 'teacher');
		$app->set('layout', 'default');

		parent::display();
	}
}
