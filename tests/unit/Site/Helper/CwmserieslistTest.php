<?php

/**
 * Unit tests for Cwmserieslist Helper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmserieslist;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for Cwmserieslist helper
 *
 * #[CoversClass(Cwmserieslist::class)]
 * @since  10.0.0
 */
class CwmserieslistTest extends ProclaimTestCase
{
    /**
     * Test getseriesElementnumber method signature
     *
     * @return void
     * #[CoversClass(Cwmserieslist::class)]::getseriesElementnumber
     */
    public function testGetseriesElementnumberMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmserieslist::class, 'getseriesElementnumber');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('int', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('subcustom', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
        $this->assertTrue($params[0]->allowsNull());
    }

    /**
     * Test getSerieslistExp method signature
     *
     * @return void
     * #[CoversClass(Cwmserieslist::class)]::getSerieslistExp
     */
    public function testGetSerieslistExpMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmserieslist::class, 'getSerieslistExp');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('row', $params[0]->getName());
        $this->assertParamTypeName('object', $params[0]);

        $this->assertEquals('params', $params[1]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[1]);

        $this->assertEquals('template', $params[2]->getName());
        $this->assertParamTypeName('object', $params[2]);
    }

    /**
     * Test getSeriesDetailsExp method signature
     *
     * @return void
     * #[CoversClass(Cwmserieslist::class)]::getSeriesDetailsExp
     */
    public function testGetSeriesDetailsExpMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmserieslist::class, 'getSeriesDetailsExp');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('row', $params[0]->getName());
        $this->assertParamTypeName('object', $params[0]);

        $this->assertEquals('params', $params[1]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[1]);
    }

    /**
     * Test getSeriesstudiesExp method signature
     *
     * @return void
     * #[CoversClass(Cwmserieslist::class)]::getSeriesstudiesExp
     */
    public function testGetSeriesstudiesExpMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmserieslist::class, 'getSeriesstudiesExp');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('id', $params[0]->getName());
        $this->assertParamTypeName('int', $params[0]);

        $this->assertEquals('params', $params[1]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[1]);

        $this->assertEquals('template', $params[2]->getName());
        $this->assertParamTypeName('object', $params[2]);
    }

    /**
     * Test getSeriesstudiesDBO method signature
     *
     * @return void
     * #[CoversClass(Cwmserieslist::class)]::getSeriesstudiesDBO
     */
    public function testGetSeriesstudiesDBOMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmserieslist::class, 'getSeriesstudiesDBO');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('array', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('id', $params[0]->getName());
        $this->assertParamTypeName('int', $params[0]);

        $this->assertEquals('params', $params[1]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[1]);

        $this->assertEquals('limit', $params[2]->getName());
        $this->assertParamTypeName('int', $params[2]);
        $this->assertTrue($params[2]->isOptional());
    }
}
