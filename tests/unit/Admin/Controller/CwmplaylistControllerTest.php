<?php

/**
 * Unit tests for CwmplaylistController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmplaylistController;
use CWM\Component\Proclaim\Administrator\Controller\Trait\MultiCampusAccessTrait;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for CwmplaylistController
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmplaylistControllerTest extends ProclaimTestCase
{
    /**
     * Regression test for #1438: this controller was the only ACL-scoped
     * singular FormController not using MultiCampusAccessTrait, letting a
     * non-admin editor in a multi-campus install edit any playlist regardless
     * of its access level.
     *
     * @return void
     */
    public function testUsesMultiCampusAccessTrait(): void
    {
        $this->assertContains(
            MultiCampusAccessTrait::class,
            class_uses(CwmplaylistController::class),
            'CwmplaylistController must use MultiCampusAccessTrait — see #1438'
        );
    }

    /**
     * @return void
     */
    public function testAccessTableIsSet(): void
    {
        $reflection = new \ReflectionProperty(CwmplaylistController::class, 'accessTable');
        $reflection->setAccessible(true);

        $instance = $reflection->getDeclaringClass()->newInstanceWithoutConstructor();

        $this->assertSame('#__bsms_playlists', $reflection->getValue($instance));
    }

    /**
     * allowEdit() must actually call into the trait's access-level check, not
     * just declare `use MultiCampusAccessTrait;` without wiring it up.
     *
     * @return void
     */
    public function testAllowEditCallsAccessLevelCheck(): void
    {
        $reflection = new \ReflectionMethod(CwmplaylistController::class, 'allowEdit');
        $lines      = file($reflection->getFileName());
        $body       = implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );

        $this->assertStringContainsString(
            'checkRecordAccessLevel(',
            $body,
            'allowEdit() must call checkRecordAccessLevel() — see #1438'
        );
    }
}
