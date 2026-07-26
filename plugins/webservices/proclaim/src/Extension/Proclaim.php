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
use Joomla\CMS\Uri\Uri;
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
     * Deliberately absent, and not merely pending: templatecodes.
     *
     * CwmtemplatecodeTable::store() writes the `templatecode` column to a real
     * PHP file under components/com_proclaim/tmpl/ via File::write(), which the
     * front end then executes as a template. A write endpoint would therefore let
     * a caller put arbitrary PHP in the web root — remote code execution, not a
     * content problem, and no ACL makes that acceptable.
     *
     * Reads are separately unsafe: the column holds PHP source, and
     * #__bsms_templatecode has no `access` column, so unlike every other entity
     * here it cannot honour view levels at all.
     *
     * Adding an `access` column would address the read side ONLY. It would not
     * make writes safe. Do not treat that column as the single prerequisite for
     * exposing this resource.
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

        $this->explainUnroutableRequest();
    }

    /**
     * Say why the current request will not match a route, if it will not.
     *
     * A request the router cannot match gets a bare 404 with nothing indicating
     * whether the endpoint was misspelled, deliberately absent, or read-only.
     * That is the hardest kind of API problem to diagnose from the outside, and
     * the answer is known right here.
     *
     * DEBUG level, so it costs nothing in production and appears as soon as an
     * administrator turns debug on to find out why their client is failing. This
     * deliberately does not care who is calling — it is diagnosing the API, not
     * policing it, and it runs before authentication anyway.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function explainUnroutableRequest(): void
    {
        $method = strtoupper($this->getApplication()->getInput()->getMethod());

        if (!\in_array($method, ['POST', 'PATCH', 'PUT', 'DELETE'], true)) {
            return;
        }

        // Match the resource segment of a Proclaim API path, whatever the site's
        // base URI or whether index.php is present.
        if (preg_match('#/v1/proclaim/([a-z0-9_-]+)#i', $this->currentPath(), $m) !== 1) {
            return;
        }

        $resource = strtolower($m[1]);

        if (!\array_key_exists($resource, self::RESOURCES)) {
            CwmlogHelper::debug(
                \sprintf(
                    '%s /v1/proclaim/%s will 404: no such Proclaim API resource. Available: %s.',
                    $method,
                    $resource,
                    implode(', ', array_keys(self::RESOURCES))
                ),
                CwmlogHelper::CATEGORY_API
            );

            return;
        }

        if (self::RESOURCES[$resource] === false) {
            CwmlogHelper::debug(
                \sprintf(
                    '%s /v1/proclaim/%s will 404: this resource is read-only by design, so no write'
                    . ' route is registered. See the RESOURCES constant for why.',
                    $method,
                    $resource
                ),
                CwmlogHelper::CATEGORY_API
            );
        }
    }

    /**
     * The requested path, or an empty string if it cannot be determined.
     *
     * Uri::getInstance() is what ApiRouter itself uses to resolve the route, so
     * this sees the same value the router will.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function currentPath(): string
    {
        try {
            return (string) urldecode(Uri::getInstance()->getPath());
        } catch (\Throwable) {
            return '';
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
