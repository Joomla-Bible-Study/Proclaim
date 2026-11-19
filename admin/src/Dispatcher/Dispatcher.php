<?php

/**
 * @package        Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Dispatcher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\ComponentDispatcher;

/**
 * Component dispatcher for com_proclaim.
 *
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
{
    /**
     * @var string
     * @since 10.0.0
     */
    protected string $defaultController = 'cwmcpanel';

    /**
     * @return void
     *
     * @throws \Throwable
     * @since 10.0.0
     */
    #[\Override]
    public function dispatch(): void
    {
        // Always load Proclaim API if it exists.
        $api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

        if (!\defined('CWM_LOADED')) {
            // Guard against running on unsupported PHP versions (system plugin
            // normally catches this earlier, but keep as a safety net here).
            if (PHP_VERSION_ID < 80300) {
                throw new \RuntimeException(
                    'Proclaim requires PHP 8.3.0 or later.',
                    502
                );
            }

            require_once $api;
        }

        // Gate: require license acceptance before any admin access.
        $view = $this->input->getCmd('view', '');
        $task = $this->input->getCmd('task', '');

        if (
            $view !== 'cwmlicense'
            && !str_starts_with($task, 'cwmlicense.')
            && !ComponentHelper::getParams('com_proclaim')->get('license_accepted', '0')
        ) {
            $this->app->redirect('index.php?option=com_proclaim&view=cwmlicense');

            return;
        }

        parent::dispatch();
    }
}
