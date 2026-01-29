<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use CWM\Component\Proclaim\Administrator\Model\CwminstallModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;

/**
 * Controller for Admin
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwminstallController extends BaseController
{
    /**
     * @var string
     * @since 7.0.0
     */
    public string $modelName;

    /**
     * The URL view list variable.
     *
     * @var    string
     * @since  12.2
     */
    protected string $view_list = 'cwminstall';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  3.0
     */
    protected $default_view = 'cwminstall';

    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @return void
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    /**
     * Execute the task.
     *
     * @param   string  $task  The task to execute.
     *
     * @return void
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public function execute($task): void
    {
        if ($task !== 'run' && $task !== 'clear' && $task !== 'browse') {
            $task = 'browse';
        }

        parent::execute($task);
    }

    /**
     * Start of installer display hook.
     *
     * @return void
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public function browse(): void
    {
        // Check for request forgeries.
        if (!Session::checkToken('get') && !Session::checkToken()) {
            throw new \Exception(Text::_('JINVALID_TOKEN'));
        }

        $app     = Factory::getApplication();
        $session = $app->getSession();
        $stack   = $session->get('migration_stack', '', 'CWM');

        if (empty($stack) || !\is_array($stack)) {
            Cwmhelper::clearCache();
            $session->set('migration_stack', '', 'CWM');

            $model = new CwminstallModel();
            $state = $model->startScanning();
            $app->input->set('scanstate', $state);
            $app->input->set('view', 'cwminstall');

            $this->display(false);
        } else {
            $this->run();
        }
    }

    /**
     * Run function loop
     *
     * @return void
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public function run(): void
    {
        // Check for request forgeries.
        if (!Session::checkToken('get') && !Session::checkToken()) {
            throw new \Exception(Text::_('JINVALID_TOKEN'));
        }

        $app   = Factory::getApplication();
        $model = new CwminstallModel();
        $state = $model->run();
        $app->input->set('scanstate', $state);
        $app->input->set('view', 'cwminstall');

        $this->display(false);
    }

    /**
     * Clear and start of installer display hook.
     *
     * @return void
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public function clear(): void
    {
        // Check for request forgeries.
        if (!Session::checkToken('get') && !Session::checkToken()) {
            throw new \Exception(Text::_('JINVALID_TOKEN'));
        }

        Cwmhelper::clearCache();
        $session = Factory::getApplication()->getSession();
        $session->set('migration_stack', '', 'CWM');
        $app = Factory::getApplication();
        $app->input->set('view', 'cwminstall');

        $this->display(false);
    }
}
