<?php

/**
 * @package    Proclaim.Plugin
 * @subpackage WebServices.Proclaim
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Plugin\WebServices\Proclaim\Extension;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmlogHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use Joomla\Router\Route;

/**
 * Web Services plugin for Proclaim REST API.
 *
 * Controlled by the `api_access` admin setting:
 *   0 = Disabled (no routes registered)
 *   1 = Public reads (GET open, writes require API key)
 *   2 = API Key Required (all operations require Bearer token)
 *
 * Read endpoints (GET) — every resource in self::RESOURCES:
 *   /api/index.php/v1/proclaim/{resource}
 *   /api/index.php/v1/proclaim/{resource}/:id
 *
 * Write endpoints (always require an API key) — only resources flagged writable
 * in self::RESOURCES; see that constant for why the rest are read-only:
 *   POST   /api/index.php/v1/proclaim/{resource}      — Create
 *   PATCH  /api/index.php/v1/proclaim/{resource}/:id  — Update
 *   DELETE /api/index.php/v1/proclaim/{resource}/:id  — Delete
 *
 * Regardless of routing, every list query is filtered to the caller's authorised
 * view levels by the underlying model, so a public read never returns rows a
 * user could not see in the site itself.
 *
 * @since  10.3.0
 */
class Proclaim extends CMSPlugin implements SubscriberInterface
{
    /**
     * Exposed resources, mapped to whether write routes are registered.
     *
     * Read-only entries are not merely unimplemented — each has a concrete
     * write-side hazard, and the matching controller extends
     * AbstractReadOnlyController so the verbs are refused even if a route were
     * added elsewhere:
     *
     *   servers        credentials live in params (api_key, client_secret,
     *                  access_token); a write would overwrite or exfiltrate them
     *   templates      site markup — an HTTP write is a defacement vector
     *   comments       a write would bypass the front-end moderation gate
     *   playlists      sync_enabled / writeback_enabled arm real mutations
     *                  against a church's live YouTube account
     *
     * Deliberately absent: templatecodes. #__bsms_templatecode has no `access`
     * column, so unlike every other entity here it cannot honour view levels —
     * and the field worth serving holds PHP source. Exposing it would mean
     * publishing code that no ACL could scope. Adding an `access` column to that
     * table is the prerequisite for reconsidering it.
     *
     * @var    array<string, bool>
     * @since  __DEPLOY_VERSION__
     */
    private const RESOURCES = [
        'sermons'      => true,
        'teachers'     => true,
        'series'       => true,
        'podcasts'     => true,
        'media'        => true,
        'topics'       => true,
        'locations'    => true,
        'messagetypes' => true,
        'playlists'    => false,
        'comments'     => false,
        'servers'      => false,
        'templates'    => false,
    ];

    /**
     * @since   10.3.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onBeforeApiRoute' => 'onBeforeApiRoute',
        ];
    }

    /**
     * Register Proclaim API routes based on the admin api_access setting.
     *
     * @param   mixed  $event  The API route event
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function onBeforeApiRoute($event): void
    {
        $apiAccess = $this->getApiAccessSetting();

        // 0 = Disabled — do not register any routes
        if ($apiAccess === 0) {
            // Every Proclaim endpoint will 404 from here. Said plainly at DEBUG,
            // because "the API returns 404" is otherwise indistinguishable from a
            // routing bug, and this is the most common cause.
            CwmlogHelper::debug(
                'api_access is disabled — no Proclaim API routes registered.',
                CwmlogHelper::CATEGORY_API
            );

            return;
        }

        // 1 = Public (no auth), 2 = API key required
        $isPublic = ($apiAccess === 1);
        $router   = $event->getRouter();

        CwmlogHelper::debug(
            \sprintf(
                'Registering %d Proclaim API resources (reads %s).',
                \count(self::RESOURCES),
                $isPublic ? 'public' : 'require an API key'
            ),
            CwmlogHelper::CATEGORY_API
        );

        foreach (self::RESOURCES as $resource => $writable) {
            $this->createReadOnlyRoutes($router, "v1/proclaim/$resource", $resource, $isPublic);

            if ($writable) {
                $this->createWriteRoutes($router, "v1/proclaim/$resource", $resource);
            }
        }
    }

    /**
     * Register read-only (GET) routes for a resource.
     *
     * @param   ApiRouter  $router      The API router
     * @param   string     $baseName    Route base path
     * @param   string     $controller  Controller name
     * @param   bool       $isPublic    Whether routes are publicly accessible
     *
     * @return  void
     *
     * @since   10.3.0
     */
    private function createReadOnlyRoutes(
        ApiRouter $router,
        string $baseName,
        string $controller,
        bool $isPublic
    ): void {
        $defaults = [
            'component' => 'com_proclaim',
            'public'    => $isPublic,
        ];

        $routes = [
            new Route(['GET'], $baseName, $controller . '.displayList', [], $defaults),
            new Route(['GET'], $baseName . '/:id', $controller . '.displayItem', ['id' => '(\d+)'], $defaults),
        ];

        $router->addRoutes($routes);
    }

    /**
     * Register write (POST/PATCH/DELETE) routes for a resource.
     *
     * Write routes always require authentication (public=false),
     * even when read routes are set to public access.
     *
     * @param   ApiRouter  $router      The API router
     * @param   string     $baseName    Route base path
     * @param   string     $controller  Controller name
     *
     * @return  void
     *
     * @since   10.3.0
     */
    private function createWriteRoutes(ApiRouter $router, string $baseName, string $controller): void
    {
        $defaults = [
            'component' => 'com_proclaim',
            'public'    => false,
        ];

        $routes = [
            new Route(['POST'], $baseName, $controller . '.add', [], $defaults),
            new Route(['PATCH'], $baseName . '/:id', $controller . '.edit', ['id' => '(\d+)'], $defaults),
            new Route(['DELETE'], $baseName . '/:id', $controller . '.delete', ['id' => '(\d+)'], $defaults),
        ];

        $router->addRoutes($routes);
    }

    /**
     * Read the api_access setting from the Proclaim admin params.
     *
     * @return  int  0 = disabled, 1 = public, 2 = API key required
     *
     * @since   10.3.0
     */
    private function getApiAccessSetting(): int
    {
        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName('params'))
                ->from($db->quoteName('#__bsms_admin'))
                ->where($db->quoteName('id') . ' = 1');
            $db->setQuery($query, 0, 1);
            $json = $db->loadResult();

            if ($json) {
                $params = new Registry($json);

                return (int) $params->get('api_access', 0);
            }
        } catch (\Throwable $e) {
            // The table may genuinely not exist yet (fresh install, before the
            // migration runs). Either way this returns 0, which unregisters every
            // route and makes the whole API 404 — a failure mode indistinguishable
            // from "the administrator disabled it". ERROR rather than WARNING:
            // the API is entirely unavailable and somebody has to act.
            CwmlogHelper::error(
                'Could not read the api_access setting, so no API routes were registered: ' . $e->getMessage(),
                CwmlogHelper::CATEGORY_API
            );
        }

        return 0;
    }
}
