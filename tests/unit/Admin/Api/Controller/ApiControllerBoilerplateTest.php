<?php

/**
 * Shared boilerplate tests for the REST API entity controllers.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Api\Controller;

use CWM\Component\Proclaim\Api\Controller\MediaController;
use CWM\Component\Proclaim\Api\Controller\PodcastsController;
use CWM\Component\Proclaim\Api\Controller\SeriesController;
use CWM\Component\Proclaim\Api\Controller\SermonsController;
use CWM\Component\Proclaim\Api\Controller\TeachersController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\MVC\Controller\ApiController;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * The five REST API entity controllers (Media/Podcasts/Series/Sermons/
 * Teachers) share the same base-class shape: extend ApiController, declare
 * matching contentType/default_view, expose a public displayList(), and
 * filter it to published+archived. Consolidates what were five copies of
 * this same five-test boilerplate block into one data-provider test.
 * Each controller's OWN test file keeps whatever is unique to it
 * (getModel() name mapping, preprocessSaveData() logic, etc.).
 *
 * @since  10.5.6
 */
class ApiControllerBoilerplateTest extends ProclaimTestCase
{
    /**
     * The API controllers checked here, each with its content type.
     *
     * @return  array<string, array{0: class-string, 1: string}>  The controller class and its content type
     *
     * @since   10.5.6
     */
    public static function controllerProvider(): array
    {
        return [
            'MediaController'    => [MediaController::class, 'media'],
            'PodcastsController' => [PodcastsController::class, 'podcasts'],
            'SeriesController'   => [SeriesController::class, 'series'],
            'SermonsController'  => [SermonsController::class, 'sermons'],
            'TeachersController' => [TeachersController::class, 'teachers'],
        ];
    }

    /**
     * Each controller extends Joomla's ApiController.
     *
     * @param   string  $class        The controller's class name
     * @param   string  $contentType  Its content type
     *
     * @return  void
     *
     * @since   10.5.6
     */
    #[DataProvider('controllerProvider')]
    public function testExtendsApiController(string $class, string $contentType): void
    {
        $this->assertTrue(
            is_subclass_of($class, ApiController::class),
            $class . ' must extend Joomla ApiController'
        );
    }

    /**
     * Each controller declares its content type.
     *
     * @param   string  $class        The controller's class name
     * @param   string  $contentType  Its content type
     *
     * @return  void
     *
     * @since   10.5.6
     * @throws  \ReflectionException
     */
    #[DataProvider('controllerProvider')]
    public function testContentType(string $class, string $contentType): void
    {
        $ref  = new \ReflectionClass($class);
        $prop = $ref->getProperty('contentType');
        $this->assertEquals($contentType, $prop->getDefaultValue());
    }

    /**
     * Each controller's default view is its content type.
     *
     * @param   string  $class        The controller's class name
     * @param   string  $contentType  Its content type
     *
     * @return  void
     *
     * @since   10.5.6
     * @throws  \ReflectionException
     */
    #[DataProvider('controllerProvider')]
    public function testDefaultView(string $class, string $contentType): void
    {
        $ref  = new \ReflectionClass($class);
        $prop = $ref->getProperty('default_view');
        $this->assertEquals($contentType, $prop->getDefaultValue());
    }

    /**
     * Each controller has a public displayList().
     *
     * @param   string  $class        The controller's class name
     * @param   string  $contentType  Its content type
     *
     * @return  void
     *
     * @since   10.5.6
     * @throws  \ReflectionException
     */
    #[DataProvider('controllerProvider')]
    public function testDisplayListMethodExists(string $class, string $contentType): void
    {
        $ref = new \ReflectionMethod($class, 'displayList');
        $this->assertTrue($ref->isPublic());
    }

    /**
     * Each controller's displayList() limits the list to published and archived
     * items.
     *
     * @param   string  $class        The controller's class name
     * @param   string  $contentType  Its content type
     *
     * @return  void
     *
     * @since   10.5.6
     * @throws  \ReflectionException
     */
    #[DataProvider('controllerProvider')]
    public function testDisplayListSetsPublishedFilter(string $class, string $contentType): void
    {
        $ref    = new \ReflectionMethod($class, 'displayList');
        $source = file_get_contents($ref->getFileName());

        $this->assertStringContainsString("'filter.published', [1, 2]", $source);
    }
}
