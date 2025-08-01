<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

// No Direct Access
use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use CWM\Component\Proclaim\Administrator\Model\CwmassetsModel;
use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;

/**
 * Controller for Assets
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmassetsController extends BaseController
{
    /**
     * NOTE: This is needed to prevent Joomla 1.6's pluralization mechanism from kicking in
     *
     * @var  string
     *
     * @since 7.0
     */
    protected string $view_list = 'cwmassets';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  3.0
     */
    protected $default_view = 'cwmassets';

    /**
     * Constructor.
     *
     * @param   string  $task  An optional associative array of configuration settings.
     *
     * @return void
     *
     * @throws Exception
     * @since 1.5
     */
    public function execute($task): void
    {
        if ($task !== 'run' && $task !== 'checkassets' && $task !== 'clear') {
            $task = 'browse';
        }

        parent::execute($task);
    }

    /**
     * Check Assets
     *
     * @return void
     *
     * @throws Exception
     * @since 8.0.0
     */
    public function checkassets(): void
    {
        // Check for request forgeries.
        (Session::checkToken('get') || Session::checkToken()) or jexit(Text::_('JINVALID_TOKEN'));

        $model       = new CwmassetsModel();
        $checkassets = $model->checkAssets();
        $session     = Factory::getApplication()->getSession();
        $session->set('assat_stack', '', 'CWM');
        $session->set('checkassets', $checkassets, 'CWM');
        $this->input->set('view', 'Cwmassets');

        $this->display(false);
    }

    /**
     * Start of installer display hook.
     *
     * @return void
     *
     * @throws Exception
     * @since 8.0.0
     */
    public function browse(): void
    {
        // Check for request forgeries.
        (Session::checkToken('get') || Session::checkToken()) or jexit(Text::_('JINVALID_TOKEN'));

        $app     = Factory::getApplication();
        $session = $app->getSession();
        $stack   = $session->get('asset_stack', '', 'CWM');

        if (empty($stack) || !is_array($stack)) {
            Cwmhelper::clearCache();
            $session->set('asset_stack', '', 'CWM');

            $model = new CwmassetsModel();
            $state = $model->startScanning();
            $app->input->set('scanstate', $state);
            $app->input->set('view', 'Cwmassets');

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
     * @throws Exception
     * @since 8.0.0
     */
    public function run(): void
    {
        // Check for request forgeries.
        (Session::checkToken('get') || Session::checkToken()) or jexit(Text::_('JINVALID_TOKEN'));

        $app   = Factory::getApplication();
        $model = new CwmassetsModel();
        $state = $model->run();
        $app->input->set('scanstate', $state);
        $app->input->set('view', 'Cwmassets');

        $this->display(false);
    }

    /**
     * Clear and start of installer display hook.
     *
     * @return void
     *
     * @throws Exception
     * @since 9.0.2
     */
    public function clear(): void
    {
        // Check for request forgeries.
        (Session::checkToken('get') || Session::checkToken()) or jexit(Text::_('JINVALID_TOKEN'));

        Cwmhelper::clearCache();
        $session = Factory::getApplication()->getSession();
        $session->set('assat_stack', '', 'CWM');
        $app = Factory::getApplication();
        $app->input->set('view', 'Cwmassets');
        $this->display(false);
    }
}
