<?php
/**
 * @package         Proclaim.Admin
 * @subpackage      com_proclaim
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Administrator\Dispatcher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcher;

/**
 * ComponentDispatcher class for com_users
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
     * @var string
     * @since version
     */
    private string $redirect;

    /**
     * @return void
     *
     * @throws \Throwable
     * @since 10.0.0
     */
    public function dispatch(): void
    {
        // Always load Proclaim API if it exists.
        $api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

        if (file_exists($api)) {
            require_once $api;
        }

        parent::dispatch();
    }

    /**
     * Redirects the browser or returns false if no redirect is set.
     *
     * @return  boolean  False if no redirect exists.
     *
     * @throws  \Exception
     * @since   3.0
     */
    public function redirect(): bool
    {
        if (!($this->app instanceof CMSWebApplicationInterface)) {
            throw new \Exception(
                sprintf(
                    'The %s method requires an instance of %s but instead %s was supplied',
                    __METHOD__,
                    CMSWebApplicationInterface::class,
                    \get_class($this->app)
                )
            );
        }

        if ($this->redirect) {
            // Enqueue the redirect message
            // $this->app->enqueueMessage($this->message, $this->messageType);

            // Execute the redirect
            $this->app->redirect($this->redirect);
        }

        return false;
    }

    /**
     * Override checkAccess to allow users edit profile without having to have core.manager permission
     *
     * @return  void
     *
     * @since  4.0.0
     */
    protected function checkAccess(): void
    {
        $task         = $this->input->getCmd('task');
        $view         = $this->input->getCmd('view');
        $layout       = $this->input->getCmd('layout');
        $allowedTasks = ['user.edit', 'user.apply', 'user.save', 'user.cancel'];

        // Allow users to edit their own account
        if (in_array($task, $allowedTasks, true) || ($view === 'user' && $layout === 'edit')) {
            $user = $this->app->getIdentity();
            $id   = $this->input->getInt('id');

            if ((int)$user->id === $id) {
                return;
            }
        }

        parent::checkAccess();
    }
}
