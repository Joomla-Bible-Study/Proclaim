<?php

/**
 * Unit tests for Cwmshowscripture Helper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmshowscripture;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Registry\Registry;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for Cwmshowscripture helper
 *
 * #[CoversClass(Cwmshowscripture::class)]
 * @since  10.0.0
 */
class CwmshowscriptureTest extends ProclaimTestCase
{
    /**
     * Test buildPassage method signature
     *
     * @return void
     * #[CoversClass(Cwmshowscripture::class)]::buildPassage
     */
    public function testBuildPassageMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmshowscripture::class, 'buildPassage');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        // Return type is string|bool, which reflection might show differently
        
        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('row', $params[0]->getName());
        // No type hint in method signature for row
        
        $this->assertEquals('params', $params[1]->getName());
        $this->assertEquals('Joomla\Registry\Registry', $params[1]->getType()->getName());
    }

    /**
     * Test formReference method signature
     *
     * @return void
     * #[CoversClass(Cwmshowscripture::class)]::formReference
     */
    public function testFormReferenceMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmshowscripture::class, 'formReference');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('row', $params[0]->getName());
        $this->assertEquals('object', $params[0]->getType()->getName());
    }

    /**
     * Test getBiblegateway method signature
     *
     * @return void
     * #[CoversClass(Cwmshowscripture::class)]::getBiblegateway
     */
    public function testGetBiblegatewayMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmshowscripture::class, 'getBiblegateway');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('reference', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
        
        $this->assertEquals('version', $params[1]->getName());
        $this->assertEquals('string', $params[1]->getType()->getName());
        
        $this->assertEquals('params', $params[2]->getName());
        $this->assertEquals('Joomla\Registry\Registry', $params[2]->getType()->getName());
    }

    /**
     * Test getHideShow method signature
     *
     * @return void
     * #[CoversClass(Cwmshowscripture::class)]::getHideShow
     */
    public function testGetHideShowMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmshowscripture::class, 'getHideShow');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('params', $params[0]->getName());
        $this->assertEquals('Joomla\Registry\Registry', $params[0]->getType()->getName());
    }

    /**
     * Test getShow method signature
     *
     * @return void
     * #[CoversClass(Cwmshowscripture::class)]::getShow
     */
    public function testGetShowMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmshowscripture::class, 'getShow');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());
    }

    /**
     * Test getLink method signature
     *
     * @return void
     * #[CoversClass(Cwmshowscripture::class)]::getLink
     */
    public function testGetLinkMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmshowscripture::class, 'getLink');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('params', $params[0]->getName());
        $this->assertEquals('Joomla\Registry\Registry', $params[0]->getType()->getName());
    }

    /**
     * Test bodyOnly method signature
     *
     * @return void
     * #[CoversClass(Cwmshowscripture::class)]::bodyOnly
     */
    public function testBodyOnlyMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmshowscripture::class, 'bodyOnly');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('html', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
    }
}
