<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Site\Controller;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
// No Direct Access
defined('_JEXEC') or die;

/**
 * Controller class for Teacher
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class CWMTeacherController extends BaseController
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
		$input->set('view', 'CWMTeacher');
		$input->set('layout', 'default');

		parent::display();
	}
}