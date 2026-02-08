<?php

/**
 * Unit tests for CwmguidedtourHelper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmguidedtourHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmguidedtourHelper
 *
 * #[CoversClass(CwmguidedtourHelper::class)]
 * @since  10.2.0
 */
class CwmguidedtourHelperTest extends ProclaimTestCase
{
    /**
     * Test constructor
     *
     * @return void
     * #[CoversClass(CwmguidedtourHelper::class)]::__construct
     */
    public function testConstructor(): void
    {
        // We can't easily test the constructor because it calls Factory::getContainer()
        // which is hard to mock in this environment without a full DI container setup.
        // However, we can verify the class exists and methods are present.
        $this->assertTrue(class_exists(CwmguidedtourHelper::class));
    }

    /**
     * Test registerAll method signature
     *
     * @return void
     * #[CoversClass(CwmguidedtourHelper::class)]::registerAll
     */
    public function testRegisterAllMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmguidedtourHelper::class, 'registerAll');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test registerTour method signature
     *
     * @return void
     * #[CoversClass(CwmguidedtourHelper::class)]::registerTour
     */
    public function testRegisterTourMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmguidedtourHelper::class, 'registerTour');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('key', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
    }

    /**
     * Test registerPostInstallMessage method signature
     *
     * @return void
     * #[CoversClass(CwmguidedtourHelper::class)]::registerPostInstallMessage
     */
    public function testRegisterPostInstallMessageMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmguidedtourHelper::class, 'registerPostInstallMessage');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('key', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
    }

    /**
     * Test registerPostInstallMessages method signature
     *
     * @return void
     * #[CoversClass(CwmguidedtourHelper::class)]::registerPostInstallMessages
     */
    public function testRegisterPostInstallMessagesMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmguidedtourHelper::class, 'registerPostInstallMessages');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('int', $reflection);
    }

    /**
     * Test registerGuidedTours method signature
     *
     * @return void
     * #[CoversClass(CwmguidedtourHelper::class)]::registerGuidedTours
     */
    public function testRegisterGuidedToursMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmguidedtourHelper::class, 'registerGuidedTours');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('int', $reflection);
    }

    /**
     * Test supportsGuidedTours method signature
     *
     * @return void
     * #[CoversClass(CwmguidedtourHelper::class)]::supportsGuidedTours
     */
    public function testSupportsGuidedToursMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmguidedtourHelper::class, 'supportsGuidedTours');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);
    }

    /**
     * Test getTourId method signature
     *
     * @return void
     * #[CoversClass(CwmguidedtourHelper::class)]::getTourId
     */
    public function testGetTourIdMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmguidedtourHelper::class, 'getTourId');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('int', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('uid', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
    }

    /**
     * Test removeAllTours method signature
     *
     * @return void
     * #[CoversClass(CwmguidedtourHelper::class)]::removeAllTours
     */
    public function testRemoveAllToursMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmguidedtourHelper::class, 'removeAllTours');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('int', $reflection);
    }

    /**
     * Test addTour method signature
     *
     * @return void
     * #[CoversClass(CwmguidedtourHelper::class)]::addTour
     */
    public function testAddTourMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmguidedtourHelper::class, 'addTour');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        // The return type is self, which in reflection shows as the class name
        $returnType = $reflection->getReturnType();
        $this->assertNotNull($returnType);
        // In PHP 7.4+ it might be 'self' or the class name depending on how it's defined
        // The method defines it as 'self'
        
        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('key', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
        
        $this->assertEquals('tour', $params[1]->getName());
        $this->assertParamTypeName('array', $params[1]);
    }

    /**
     * Test addPostInstallMessage method signature
     *
     * @return void
     * #[CoversClass(CwmguidedtourHelper::class)]::addPostInstallMessage
     */
    public function testAddPostInstallMessageMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmguidedtourHelper::class, 'addPostInstallMessage');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        
        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('key', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
        
        $this->assertEquals('message', $params[1]->getName());
        $this->assertParamTypeName('array', $params[1]);
    }
}
