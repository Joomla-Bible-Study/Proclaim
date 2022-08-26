<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

// No Direct Access
defined('_JEXEC') or die;

// No Direct Access
use CWM\Component\Proclaim\Administrator\Helper\CWMHelper;
use CWM\Component\Proclaim\Administrator\Model\CWMAssetsModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/**
 * Controller for Assets
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMAssetsController extends BaseController
{
	/**
	 * NOTE: This is needed to prevent Joomla 1.6's pluralization mechanism from kicking in
	 *
	 * @var  string
	 *
	 * @since 7.0
	 */
	protected $view_list = 'CWMAdmin';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $default_view = 'CWMAssets';

	/**
	 * Constructor.
	 *
	 * @param   string  $task  An optional associative array of configuration settings.
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.5
	 */
	public function execute($task)
	{
		if ($task !== 'run' && $task !== 'checkassets' && $task !== 'clear')
		{
			$task = 'browse';
		}

		parent::execute($task);
	}

	/**
	 * Check Assets
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 8.0.0
	 */
	public function checkassets(): void
	{
		// Check for request forgeries.
		Session::checkToken('get') || Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$model = new CWMAssetsModel;
		$checkassets = $model->checkAssets();
		$session = Factory::getApplication()->getSession();
		$session->set('assat_stack', '', 'JBSM');
		$session->set('checkassets', $checkassets, 'JBSM');

		$this->display(false);
	}

	/**
	 * Start of installer display hook.
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 8.0.0
	 */
	public function browse()
	{
		// Check for request forgeries.
		Session::checkToken('get') or jexit(Text::_('JINVALID_TOKEN'));

		$app = Factory::getApplication();
		$session = $app->getSession();
		$stack = $session->get('asset_stack', '', 'JBSM');

		if (empty($stack) || !is_array($stack))
		{
			CWMHelper::clearcache('site');
			CWMHelper::clearcache('administrator');
			$session->set('asset_stack', '', 'JBSM');

			$model = new CWMAssetsModel;
			$state = $model->startScanning();
			$app->input->set('scanstate', $state);
			$app->input->set('view', 'cwmassets');

			$this->display(false);
		}
		else
		{
			$this->run();
		}
	}

	/**
	 * Clear and start of installer display hook.
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 9.0.2
	 */
	public function clear()
	{
		// Check for request forgeries.
		Session::checkToken('get') or jexit(Text::_('JINVALID_TOKEN'));

		CWMHelper::clearcache('administrator');
		CWMHelper::clearcache('site');
		$session = Factory::getSession();
		$session->set('assat_stack', '', 'JBSM');
		$app = Factory::getApplication();
		$app->input->set('view', 'cwmassets');
		$this->display(false);
	}

	/**
	 * Run function loop
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 8.0.0
	 */
	public function run()
	{
		// Check for request forgeries.
		Session::checkToken('get') || Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app   = Factory::getApplication();
		$model = new CWMAssetsModel;
		$state = $model->run();
		$app->input->set('scanstate', $state);
		$app->input->set('view', 'cwmassets');

		$this->display(false);
	}
}