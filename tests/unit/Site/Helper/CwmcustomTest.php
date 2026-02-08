<?php

/**
 * Unit tests for Cwmcustom Helper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmcustom;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for Cwmcustom helper
 *
 * #[CoversClass(Cwmcustom::class)]
 * @since  10.0.0
 */
class CwmcustomTest extends ProclaimTestCase
{
    /**
     * Test getCustom method signature
     *
     * @return void
     * #[CoversClass(Cwmcustom::class)]::getCustom
     */
    public function testGetCustomMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmcustom::class, 'getCustom');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(5, $params);
        $this->assertEquals('rowid', $params[0]->getName());
        // No type hint in method signature for rowid
        
        $this->assertEquals('custom', $params[1]->getName());
        // No type hint in method signature for custom
        
        $this->assertEquals('row', $params[2]->getName());
        // No type hint in method signature for row
        
        $this->assertEquals('params', $params[3]->getName());
        // No type hint in method signature for params
        
        $this->assertEquals('template', $params[4]->getName());
        // No type hint in method signature for template
    }

    /**
     * Test getElementnumber method signature
     *
     * @return void
     * #[CoversClass(Cwmcustom::class)]::getElementnumber
     */
    public function testGetElementnumberMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmcustom::class, 'getElementnumber');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('int', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('row', $params[0]->getName());
        // No type hint in method signature for row
    }

    /**
     * Test getElementnumber returns correct values
     *
     * @return void
     * #[CoversClass(Cwmcustom::class)]::getElementnumber
     */
    public function testGetElementnumberValues(): void
    {
        $this->assertEquals(1, Cwmcustom::getElementnumber('scripture1'));
        $this->assertEquals(5, Cwmcustom::getElementnumber('studytitle'));
        $this->assertEquals(7, Cwmcustom::getElementnumber('teachername'));
        $this->assertEquals(0, Cwmcustom::getElementnumber('nonexistent'));
    }
}
