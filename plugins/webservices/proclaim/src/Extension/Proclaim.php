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
 * Read endpoints (GET):
 *   /api/index.php/v1/proclaim/{sermons|teachers|series|podcasts|media}
 *   /api/index.php/v1/proclaim/{resource}/:id
 *
 * Write endpoints (always require API key):
 *   POST   /api/index.php/v1/proclaim/{resource}      — Create
 *   PATCH  /api/index.php/v1/proclaim/{resource}/:id  — Update
 *   DELETE /api/index.php/v1/proclaim/{resource}/:id  — Delete
 *
 * @since  10.3.0
 */
class Proclaim extends CMSPlugin implements SubscriberInterface
{
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
            return;
        }

        // 1 = Public (no auth), 2 = API key required
        $isPublic = ($apiAccess === 1);
        $router   = $event->getRouter();

        $resources = ['sermons', 'teachers', 'series', 'podcasts', 'media'];

        foreach ($resources as $resource) {
            $this->createReadOnlyRoutes($router, "v1/proclaim/$resource", $resource, $isPublic);
            $this->createWriteRoutes($router, "v1/proclaim/$resource", $resource);
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
        } catch (\Throwable) {
            // Table may not exist yet (fresh install before migration)
        }

        return 0;
    }
}
