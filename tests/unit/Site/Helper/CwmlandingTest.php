<?php

/**
 * Unit tests for Cwmlanding Helper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmlanding;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Registry\Registry;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for Cwmlanding helper
 *
 * #[CoversClass(Cwmlanding::class)]
 * @since  10.0.0
 */
class CwmlandingTest extends ProclaimTestCase
{
    /**
     * Test getSectionOrder method signature
     *
     * @return void
     * #[CoversClass(Cwmlanding::class)]::getSectionOrder
     */
    public function testGetSectionOrderMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlanding::class, 'getSectionOrder');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('array', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('params', $params[0]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[0]);
    }

    /**
     * Test getLandingData method signature
     *
     * @return void
     * #[CoversClass(Cwmlanding::class)]::getLandingData
     */
    public function testGetLandingDataMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlanding::class, 'getLandingData');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('array', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('params', $params[0]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[0]);
    }

    /**
     * Test getLocationsLandingPage method signature
     *
     * @return void
     * #[CoversClass(Cwmlanding::class)]::getLocationsLandingPage
     */
    public function testGetLocationsLandingPageMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlanding::class, 'getLocationsLandingPage');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('params', $params[0]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[0]);
        
        $this->assertEquals('id', $params[1]->getName());
        $this->assertParamTypeName('int', $params[1]);
        $this->assertTrue($params[1]->isOptional());
        
        $this->assertEquals('items', $params[2]->getName());
        $this->assertParamTypeName('array', $params[2]);
        $this->assertTrue($params[2]->allowsNull());
        $this->assertTrue($params[2]->isOptional());
    }

    /**
     * Test getTeacherLandingPage method signature
     *
     * @return void
     * #[CoversClass(Cwmlanding::class)]::getTeacherLandingPage
     */
    public function testGetTeacherLandingPageMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlanding::class, 'getTeacherLandingPage');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('params', $params[0]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[0]);
        
        $this->assertEquals('id', $params[1]->getName());
        $this->assertParamTypeName('int', $params[1]);
        $this->assertTrue($params[1]->isOptional());
        
        $this->assertEquals('items', $params[2]->getName());
        $this->assertParamTypeName('array', $params[2]);
        $this->assertTrue($params[2]->allowsNull());
        $this->assertTrue($params[2]->isOptional());
    }

    /**
     * Test getSeriesLandingPage method signature
     *
     * @return void
     * #[CoversClass(Cwmlanding::class)]::getSeriesLandingPage
     */
    public function testGetSeriesLandingPageMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlanding::class, 'getSeriesLandingPage');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('params', $params[0]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[0]);
        
        $this->assertEquals('id', $params[1]->getName());
        $this->assertParamTypeName('int', $params[1]);
        $this->assertTrue($params[1]->isOptional());
        
        $this->assertEquals('items', $params[2]->getName());
        $this->assertParamTypeName('array', $params[2]);
        $this->assertTrue($params[2]->allowsNull());
        $this->assertTrue($params[2]->isOptional());
    }

    /**
     * Test getYearsLandingPage method signature
     *
     * @return void
     * #[CoversClass(Cwmlanding::class)]::getYearsLandingPage
     */
    public function testGetYearsLandingPageMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlanding::class, 'getYearsLandingPage');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('params', $params[0]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[0]);
        
        $this->assertEquals('id', $params[1]->getName());
        $this->assertParamTypeName('int', $params[1]);
        $this->assertTrue($params[1]->isOptional());
        
        $this->assertEquals('items', $params[2]->getName());
        $this->assertParamTypeName('array', $params[2]);
        $this->assertTrue($params[2]->allowsNull());
        $this->assertTrue($params[2]->isOptional());
    }

    /**
     * Test getTopicsLandingPage method signature
     *
     * @return void
     * #[CoversClass(Cwmlanding::class)]::getTopicsLandingPage
     */
    public function testGetTopicsLandingPageMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlanding::class, 'getTopicsLandingPage');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('params', $params[0]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[0]);
        
        $this->assertEquals('id', $params[1]->getName());
        $this->assertParamTypeName('int', $params[1]);
        $this->assertTrue($params[1]->isOptional());
        
        $this->assertEquals('items', $params[2]->getName());
        $this->assertParamTypeName('array', $params[2]);
        $this->assertTrue($params[2]->allowsNull());
        $this->assertTrue($params[2]->isOptional());
    }

    /**
     * Test getMessageTypesLandingPage method signature
     *
     * @return void
     * #[CoversClass(Cwmlanding::class)]::getMessageTypesLandingPage
     */
    public function testGetMessageTypesLandingPageMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlanding::class, 'getMessageTypesLandingPage');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('params', $params[0]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[0]);
        
        $this->assertEquals('id', $params[1]->getName());
        $this->assertParamTypeName('int', $params[1]);
        $this->assertTrue($params[1]->isOptional());
        
        $this->assertEquals('items', $params[2]->getName());
        $this->assertParamTypeName('array', $params[2]);
        $this->assertTrue($params[2]->allowsNull());
        $this->assertTrue($params[2]->isOptional());
    }

    /**
     * Test getBooksLandingPage method signature
     *
     * @return void
     * #[CoversClass(Cwmlanding::class)]::getBooksLandingPage
     */
    public function testGetBooksLandingPageMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlanding::class, 'getBooksLandingPage');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('params', $params[0]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[0]);
        
        $this->assertEquals('id', $params[1]->getName());
        $this->assertParamTypeName('int', $params[1]);
        $this->assertTrue($params[1]->isOptional());
        
        $this->assertEquals('items', $params[2]->getName());
        $this->assertParamTypeName('array', $params[2]);
        $this->assertTrue($params[2]->allowsNull());
        $this->assertTrue($params[2]->isOptional());
    }
}
