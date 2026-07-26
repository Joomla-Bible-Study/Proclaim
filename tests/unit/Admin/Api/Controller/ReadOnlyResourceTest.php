<?php

/**
 * Unit tests for the read-only API resources and their withheld fields.
 *
 * These assert security properties, not conveniences. Each assertion here stands
 * between an HTTP caller and either a credential, someone's personal data, or a
 * write that would have real side effects.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Api\Controller;

use CWM\Component\Proclaim\Api\Controller\AbstractReadOnlyController;
use CWM\Component\Proclaim\Api\Controller\ServersController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Router\Exception\RouteNotFoundException;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @since  __DEPLOY_VERSION__
 */
class ReadOnlyResourceTest extends ProclaimTestCase
{
    /**
     * Controllers that must never accept writes.
     *
     * @return array<string, array{0: string}>
     */
    public static function readOnlyControllerProvider(): array
    {
        return [
            'servers'   => ['ServersController'],
            'templates' => ['TemplatesController'],
            'comments'  => ['CommentsController'],
            'playlists' => ['PlaylistsController'],
        ];
    }

    /**
     * Each read-only resource extends the base that refuses writes.
     *
     */
    #[DataProvider('readOnlyControllerProvider')]
    public function testReadOnlyControllerExtendsReadOnlyBase(string $class): void
    {
        $fqcn = 'CWM\\Component\\Proclaim\\Api\\Controller\\' . $class;

        $this->assertTrue(class_exists($fqcn), "$class should exist");
        $this->assertTrue(
            is_subclass_of($fqcn, AbstractReadOnlyController::class),
            "$class must extend AbstractReadOnlyController so writes are refused"
        );
    }

    /**
     * The writable controllers must NOT extend the read-only base, or their
     * legitimate write endpoints would 404.
     */
    public function testWritableControllersAreNotReadOnly(): void
    {
        foreach (['TopicsController', 'LocationsController', 'MessagetypesController'] as $class) {
            $fqcn = 'CWM\\Component\\Proclaim\\Api\\Controller\\' . $class;

            $this->assertTrue(class_exists($fqcn), "$class should exist");
            $this->assertFalse(
                is_subclass_of($fqcn, AbstractReadOnlyController::class),
                "$class is meant to be writable and must not extend AbstractReadOnlyController"
            );
        }
    }

    /**
     * Write verbs on the read-only base throw rather than falling through to the
     * parent implementation.
     *
     */
    #[DataProvider('writeVerbProvider')]
    public function testWriteVerbsThrow(string $method): void
    {
        $controller = $this->newReadOnlyController();

        $this->expectException(RouteNotFoundException::class);

        $controller->$method();
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function writeVerbProvider(): array
    {
        return [
            'add'    => ['add'],
            'edit'   => ['edit'],
            'delete' => ['delete'],
        ];
    }

    /**
     * The servers view must never render `params` — it holds api_key,
     * client_secret and access_token for every server addon, and the item query
     * loads the full row.
     */
    public function testServersViewWithholdsCredentialParams(): void
    {
        foreach (['fieldsToRenderItem', 'fieldsToRenderList'] as $property) {
            $fields = $this->viewFields('Servers', $property);

            $this->assertNotContains(
                'params',
                $fields,
                "Servers $property must not expose params — it contains server credentials"
            );
        }
    }

    /**
     * The comments view must never render `user_email` or `user_id`. The comments
     * list query does select user_email, so these lists are the only thing
     * keeping a commenter's address out of the response.
     */
    public function testCommentsViewWithholdsPersonalData(): void
    {
        foreach (['fieldsToRenderItem', 'fieldsToRenderList'] as $property) {
            $fields = $this->viewFields('Comments', $property);

            foreach (['user_email', 'user_id'] as $forbidden) {
                $this->assertNotContains(
                    $forbidden,
                    $fields,
                    "Comments $property must not expose $forbidden"
                );
            }
        }
    }

    /**
     * Sanity check that the views expose something — a typo'd property name would
     * otherwise make the assertions above pass against an empty array.
     */
    public function testWithholdingTestsAreMeaningful(): void
    {
        foreach (['Servers', 'Comments'] as $view) {
            foreach (['fieldsToRenderItem', 'fieldsToRenderList'] as $property) {
                $this->assertNotEmpty(
                    $this->viewFields($view, $property),
                    "$view::$property should not be empty"
                );
            }
        }
    }

    /**
     * Read a JSON:API view's field list without booting the view.
     *
     * @return array<int, string>
     */
    private function viewFields(string $view, string $property): array
    {
        $fqcn = 'CWM\\Component\\Proclaim\\Api\\View\\' . $view . '\\JsonapiView';

        $this->assertTrue(class_exists($fqcn), "$view JsonapiView should exist");

        $defaults = (new \ReflectionClass($fqcn))->getDefaultProperties();

        return $defaults[$property] ?? [];
    }

    /**
     * A real read-only controller, constructed without running the parent
     * constructor so no application or MVC factory is required. ServersController
     * stands in for the family — it already declares its own contentType, which
     * feeds the refusal message.
     */
    private function newReadOnlyController(): AbstractReadOnlyController
    {
        return (new \ReflectionClass(ServersController::class))
            ->newInstanceWithoutConstructor();
    }
}
