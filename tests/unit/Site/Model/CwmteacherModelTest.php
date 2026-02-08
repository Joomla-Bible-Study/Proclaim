<?php

/**
 * Unit tests for CwmteacherModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Model;

use CWM\Component\Proclaim\Site\Model\CwmteacherModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmteacherModel
 *
 * #[CoversClass(CwmteacherModel::class)]
 * @since  10.0.0
 */
class CwmteacherModelTest extends ProclaimTestCase
{
    /**
     * Test getItem method signature
     *
     * @return void
     * #[CoversClass(CwmteacherModel::class)]::getItem
     */
    public function testGetItemMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmteacherModel::class, 'getItem');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertTrue($reflection->returnsReference());
        // Return type is mixed, which reflection might show differently
        
        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('pk', $params[0]->getName());
        // No type hint in method signature for pk
        $this->assertTrue($params[0]->isOptional());
    }

    /**
     * Test populateState method signature
     *
     * @return void
     * #[CoversClass(CwmteacherModel::class)]::populateState
     */
    public function testPopulateStateMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmteacherModel::class, 'populateState');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('void', $reflection);
    }
}
