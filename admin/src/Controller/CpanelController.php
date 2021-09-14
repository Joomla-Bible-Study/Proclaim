<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\BibleStudy\Administrator\Controller;

// No Direct Access
defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Controller for the cPanel
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CpanelController extends FormController
{
	// Holder for new controllers.
	/**
	 * Constructor.
	 *
	 * @param   array                                                        $config       An optional associative array of configuration settings.
	 *                                                                                     Recognized key values include 'name', 'default_task', 'model_path', and
	 *                                                                                     'view_path' (this list is not meant to be comprehensive).
	 * @param   \Joomla\CMS\MVC\Factory\MVCFactoryInterface|null             $factory      The factory.
	 * @param   \Joomla\CMS\Application\CMSApplication|null                  $app          The JApplication for the dispatcher
	 * @param   \CWM\Component\BibleStudy\Administrator\Controller\Input|null  $input        Input
	 * @param   \Joomla\CMS\Form\FormFactoryInterface|null                   $formFactory  The form factory.
	 *
	 * @throws \Exception
	 * @since   3.0
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, ?CMSApplication $app = null, ?Input $input = null,
	                            FormFactoryInterface $formFactory = null
	)
	{
		die('contruct');
		parent::__construct($config, $factory, $app, $input);
	}
}
