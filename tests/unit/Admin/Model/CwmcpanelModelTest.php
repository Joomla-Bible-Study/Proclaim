<?php

/**
 * Unit tests for CwmcpanelModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmcpanelModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmcpanelModel
 *
 * #[CoversClass(CwmcpanelModel::class)]
 * @since  10.0.0
 */
class CwmcpanelModelTest extends ProclaimTestCase
{
    /**
     * Test getData method signature
     *
     * @return void
     * #[CoversClass(CwmcpanelModel::class)]::getData
     */
    public function testGetDataMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmcpanelModel::class, 'getData');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('object', $reflection);
        $this->assertTrue($reflection->getReturnType()->allowsNull());
    }

    /**
     * Test hasPostInstallMessages method signature
     *
     * @return void
     * #[CoversClass(CwmcpanelModel::class)]::hasPostInstallMessages
     */
    public function testHasPostInstallMessagesMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmcpanelModel::class, 'hasPostInstallMessages');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);
    }
}
