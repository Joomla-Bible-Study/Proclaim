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
     * Prevents Joomla's pluralization mechanism from altering the view name.
     *
     * @var    string
     * @since  7.0.0
     */
    protected string $view_list = 'cwminstall';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  7.0.0
     */
    protected $default_view = 'cwminstall';

    /**
     * Route tasks to allowed methods or fall back to browse.
     *
     * @param   string  $task  The task to execute.
     *
     * @return mixed
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public function execute($task): mixed
    {
        if ($task !== 'run' && $task !== 'clear' && $task !== 'browse') {
            $task = 'browse';
        }

        return parent::execute($task);
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
            $this->setRedirect('index.php?option=com_proclaim&view=cwminstall', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        $app     = Factory::getApplication();
        $session = $app->getSession();
        $stack   = $session->get('migration_stack', '', 'CWM');

        if (empty($stack) || !\is_array($stack)) {
            Cwmhelper::clearCache();
            $session->set('migration_stack', '', 'CWM');

            /** @var \CWM\Component\Proclaim\Administrator\Model\CwminstallModel $model */
            $model = $this->getModel('Cwminstall');
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
            $this->setRedirect('index.php?option=com_proclaim&view=cwminstall', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        $app = Factory::getApplication();

        /** @var \CWM\Component\Proclaim\Administrator\Model\CwminstallModel $model */
        $model = $this->getModel('Cwminstall');
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
            $this->setRedirect('index.php?option=com_proclaim&view=cwminstall', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        Cwmhelper::clearCache();
        $app     = Factory::getApplication();
        $session = $app->getSession();
        $session->set('migration_stack', '', 'CWM');
        $app->input->set('view', 'cwminstall');

        $this->display(false);
    }
}
