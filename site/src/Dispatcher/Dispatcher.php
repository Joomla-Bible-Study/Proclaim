<?php

/**
 * @package        Proclaim.Site
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Site\Dispatcher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// Always load Proclaim API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

if (!\defined('BIBLESTUDY_COMPONENT_NAME')) {
    require_once $api;
}
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmproclaimHelper;
use Exception;
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
    protected string $defaultController = 'DisplayController';

    /**
     * Dispatch a controller task. Redirecting the user if appropriate.
     *
     * @return  void
     *
     * @throws Exception
     * @since   4.0.0
     */
    public function dispatch(): void
    {
        CwmproclaimHelper::applyViewAndController($this->defaultController);

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
