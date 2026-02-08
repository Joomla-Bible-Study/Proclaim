<?php

/**
 * Unit tests for CwmlatestModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Model;

use CWM\Component\Proclaim\Site\Model\CwmlatestModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmlatestModel
 *
 * #[CoversClass(CwmlatestModel::class)]
 * @since  10.0.0
 */
class CwmlatestModelTest extends ProclaimTestCase
{
    /**
     * Test getLatestStudyId method signature
     *
     * @return void
     * #[CoversClass(CwmlatestModel::class)]::getLatestStudyId
     */
    public function testGetLatestStudyIdMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmlatestModel::class, 'getLatestStudyId');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('int', $reflection->getReturnType()->getName());
        $this->assertTrue($reflection->getReturnType()->allowsNull());
    }
}
