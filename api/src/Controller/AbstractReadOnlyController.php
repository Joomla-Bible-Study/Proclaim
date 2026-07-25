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
 * @since  __DEPLOY_VERSION__
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
     * @since   __DEPLOY_VERSION__
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
     * @since   __DEPLOY_VERSION__
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
     * @since   __DEPLOY_VERSION__
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
     * @since   __DEPLOY_VERSION__
     */
    private function readOnlyMessage(string $verb): string
    {
        return \sprintf(
            'The %s resource is read-only and cannot be %s through the API.',
            $this->contentType,
            $verb
        );
    }
}
