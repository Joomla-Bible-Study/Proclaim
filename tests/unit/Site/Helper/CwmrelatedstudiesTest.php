<?php

/**
 * Unit tests for Cwmrelatedstudies Helper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmrelatedstudies;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Registry\Registry;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for Cwmrelatedstudies helper
 *
 * #[CoversClass(Cwmrelatedstudies::class)]
 * @since  10.0.0
 */
class CwmrelatedstudiesTest extends ProclaimTestCase
{
    /**
     * Test getRelated method signature
     *
     * @return void
     * #[CoversClass(Cwmrelatedstudies::class)]::getRelated
     */
    public function testGetRelatedMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmrelatedstudies::class, 'getRelated');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        // Return type is string|bool, which reflection might show differently
        
        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('row', $params[0]->getName());
        $this->assertParamTypeName('object', $params[0]);
        
        $this->assertEquals('params', $params[1]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[1]);
    }

    /**
     * Test getStudies method signature
     *
     * @return void
     * #[CoversClass(Cwmrelatedstudies::class)]::getStudies
     */
    public function testGetStudiesMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmrelatedstudies::class, 'getStudies');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('array', $reflection);
    }

    /**
     * Test parseKeys method signature
     *
     * @return void
     * #[CoversClass(Cwmrelatedstudies::class)]::parseKeys
     */
    public function testParseKeysMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmrelatedstudies::class, 'parseKeys');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('source', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
        
        $this->assertEquals('compare', $params[1]->getName());
        $this->assertParamTypeName('string', $params[1]);
        
        $this->assertEquals('id', $params[2]->getName());
        $this->assertParamTypeName('int', $params[2]);
    }

    /**
     * Test getRelatedLinks method signature
     *
     * @return void
     * #[CoversClass(Cwmrelatedstudies::class)]::getRelatedLinks
     */
    public function testGetRelatedLinksMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmrelatedstudies::class, 'getRelatedLinks');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('id', $params[0]->getName());
        $this->assertParamTypeName('int', $params[0]);
    }
}
