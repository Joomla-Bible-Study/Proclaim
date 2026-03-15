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

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;
use Joomla\Event\SubscriberInterface;
use Joomla\Router\Route;

/**
 * Web Services plugin for Proclaim — read-only REST API.
 *
 * Registers GET routes for sermons, teachers, series, and podcasts.
 * All endpoints respect Joomla access levels (multi-campus isolation).
 *
 * Endpoints:
 *   GET /api/index.php/v1/proclaim/sermons          — Sermon list
 *   GET /api/index.php/v1/proclaim/sermons/:id       — Sermon detail
 *   GET /api/index.php/v1/proclaim/teachers          — Teacher list
 *   GET /api/index.php/v1/proclaim/teachers/:id      — Teacher detail
 *   GET /api/index.php/v1/proclaim/series             — Series list
 *   GET /api/index.php/v1/proclaim/series/:id         — Series detail
 *   GET /api/index.php/v1/proclaim/podcasts           — Podcast list
 *   GET /api/index.php/v1/proclaim/podcasts/:id       — Podcast detail
 *
 * @since  10.3.0
 */
class Proclaim extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns the events this subscriber listens to.
     *
     * @return  array
     *
     * @since   10.3.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onBeforeApiRoute' => 'onBeforeApiRoute',
        ];
    }

    /**
     * Register Proclaim API routes.
     *
     * @param   \Joomla\CMS\Event\WebAsset\BeforeApiRouteEvent|mixed  $event  The API route event
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function onBeforeApiRoute($event): void
    {
        $router = $event->getRouter();

        $this->createReadOnlyRoutes($router, 'v1/proclaim/sermons', 'sermons');
        $this->createReadOnlyRoutes($router, 'v1/proclaim/teachers', 'teachers');
        $this->createReadOnlyRoutes($router, 'v1/proclaim/series', 'series');
        $this->createReadOnlyRoutes($router, 'v1/proclaim/podcasts', 'podcasts');
    }

    /**
     * Register read-only (GET) routes for a resource.
     *
     * @param   ApiRouter  $router      The API router
     * @param   string     $baseName    Route base path (e.g. 'v1/proclaim/sermons')
     * @param   string     $controller  Controller name (maps to displayList/displayItem tasks)
     *
     * @return  void
     *
     * @since   10.3.0
     */
    private function createReadOnlyRoutes(ApiRouter $router, string $baseName, string $controller): void
    {
        $defaults = [
            'component' => 'com_proclaim',
            'public'    => true,
            'format'    => ['application/json'],
        ];

        $routes = [
            new Route(['GET'], $baseName, $controller . '.displayList', [], $defaults),
            new Route(['GET'], $baseName . '/:id', $controller . '.displayItem', ['id' => '(\d+)'], $defaults),
        ];

        $router->addRoutes($routes);
    }
}
