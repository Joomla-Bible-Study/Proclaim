<?php

/**
 * @package        Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Site\Dispatcher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// Always load Proclaim API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

if (!\defined('CWM_LOADED')) {
    require_once $api;
}
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmproclaimHelper;
use CWM\Component\Proclaim\Site\Helper\CwmcustomcssHelper;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Language\Text;

/**
 * ComponentDispatcher class for com_proclaim
 *
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
{
    /**
     * @var string
     * @since 10.0.0
     */
    protected string $defaultController = 'display';

    /**
     * The view shown when a request names neither a view nor a controller.
     *
     * Separate from the controller because no CwmlandingpageController exists —
     * the generic display controller renders it.
     *
     * @var string
     * @since __DEPLOY_VERSION__
     */
    protected string $defaultView = 'cwmlandingpage';

    /**
     * Dispatch a controller task. Redirecting the user if appropriate.
     *
     * @return  void
     *
     * @throws \Exception
     * @since   4.0.0
     */
    #[\Override]
    public function dispatch(): void
    {
        CwmproclaimHelper::applyViewAndController($this->defaultController, $this->defaultView);

        // Once per component request, ahead of the views, so a rule can reach
        // anything Proclaim renders.
        CwmcustomcssHelper::apply();

        if ($this->input->get('view') === 'cwmlandingpage' && $this->input->get('layout') === 'modal') {
            if (!$this->app->getIdentity()->authorise('core.create', 'com_proclaim')) {
                $this->app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');

                return;
            }

            $this->app->getLanguage()->load('com_proclaim', JPATH_ADMINISTRATOR);
        }

        parent::dispatch();
    }
}
