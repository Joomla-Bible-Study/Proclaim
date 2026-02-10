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

use CWM\Component\Proclaim\Site\Bible\BiblePassageResult;
use CWM\Component\Proclaim\Site\Helper\Cwmshowscripture;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
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
     */
    public function testBuildPassageMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmshowscripture::class, 'buildPassage');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('row', $params[0]->getName());
        $this->assertEquals('params', $params[1]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[1]);
    }

    /**
     * Test formReference method signature
     *
     * @return void
     */
    public function testFormReferenceMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmshowscripture::class, 'formReference');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('row', $params[0]->getName());
        $this->assertParamTypeName('object', $params[0]);
    }

    /**
     * Test renderTextPassage method signature
     *
     * @return void
     */
    public function testRenderTextPassageMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmshowscripture::class, 'renderTextPassage');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(4, $params);
        $this->assertEquals('result', $params[0]->getName());
        $this->assertParamTypeName(BiblePassageResult::class, $params[0]);

        $this->assertEquals('choice', $params[1]->getName());
        $this->assertParamTypeName('int', $params[1]);

        $this->assertEquals('params', $params[2]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[2]);

        $this->assertEquals('switcherHtml', $params[3]->getName());
        $this->assertParamTypeName('string', $params[3]);
        $this->assertTrue($params[3]->isDefaultValueAvailable());
        $this->assertEquals('', $params[3]->getDefaultValue());
    }

    /**
     * Test renderVersionSwitcher method signature
     *
     * @return void
     */
    public function testRenderVersionSwitcherMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmshowscripture::class, 'renderVersionSwitcher');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('row', $params[0]->getName());
        $this->assertParamTypeName('object', $params[0]);

        $this->assertEquals('version', $params[1]->getName());
        $this->assertParamTypeName('string', $params[1]);

        $this->assertEquals('adminParams', $params[2]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[2]);
    }
}
