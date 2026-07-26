<?php

/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Api\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmlogHelper;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\CMS\Router\Exception\RouteNotFoundException;

/**
 * Base controller for resources the API exposes read-only.
 *
 * Some Proclaim entities are safe to publish but must never be writable over
 * HTTP — either because they carry secrets (server credentials), because they
 * are site markup rather than content (templates, template code), or because a
 * write would trigger real side effects or bypass moderation (playlists sync,
 * comments).
 *
 * The webservices plugin already declines to register write routes for these,
 * so this class is defence in depth: it means a resource cannot become writable
 * by accident if a route is ever added elsewhere, or if this controller is
 * dispatched directly. `RouteNotFoundException` is what ApiApplication turns
 * into a 404, which is the honest answer — for these resources the write route
 * genuinely does not exist.
 *
 * @since  10.3.4
 */
abstract class AbstractReadOnlyController extends ApiController
{
    /**
     * Creating is not supported for this resource.
     *
     * @return  void
     *
     * @throws  RouteNotFoundException  Always.
     *
     * @since   10.3.4
     */
    public function add()
    {
        throw new RouteNotFoundException($this->readOnlyMessage('created'));
    }

    /**
     * Updating is not supported for this resource.
     *
     * @return  void
     *
     * @throws  RouteNotFoundException  Always.
     *
     * @since   10.3.4
     */
    public function edit()
    {
        throw new RouteNotFoundException($this->readOnlyMessage('updated'));
    }

    /**
     * Deleting is not supported for this resource.
     *
     * @param   integer|null  $id  Ignored.
     *
     * @return  void
     *
     * @throws  RouteNotFoundException  Always.
     *
     * @since   10.3.4
     */
    public function delete($id = null)
    {
        throw new RouteNotFoundException($this->readOnlyMessage('deleted'));
    }

    /**
     * Build the refusal message for a blocked write verb.
     *
     * @param   string  $verb  Past-tense verb, e.g. 'created'.
     *
     * @return  string
     *
     * @since   10.3.4
     */
    private function readOnlyMessage(string $verb): string
    {
        $message = \sprintf(
            'The %s resource is read-only and cannot be %s through the API.',
            $this->contentType,
            $verb
        );

        // Record the attempt as a WARNING, not an action-log entry. Nothing
        // changed, so there is nothing to audit — but a write aimed at a resource
        // that has no write route is either a probe or a client built on wrong
        // assumptions, and an administrator should be able to see it rather than
        // only an unexplained 404. The action log would also miss it entirely
        // when the caller is unauthenticated, since it records nothing without a
        // user id.
        try {
            $user     = $this->app->getIdentity();
            $identity = $user && $user->id ? $user->username . ' (#' . $user->id . ')' : 'unauthenticated caller';

            CwmlogHelper::warning(
                \sprintf(
                    'Blocked write to read-only resource "%s" by %s from %s',
                    $this->contentType,
                    $identity,
                    $this->app->getInput()->server->getString('REMOTE_ADDR', 'unknown address')
                ),
                CwmlogHelper::CATEGORY_API
            );
        } catch (\Throwable) {
            // Logging must never be the reason a refusal fails to reach the caller.
        }

        return $message;
    }
}
