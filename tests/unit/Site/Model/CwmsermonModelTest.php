<?php

/**
 * Unit tests for CwmsermonModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Model;

use CWM\Component\Proclaim\Site\Model\CwmsermonModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Form\Form;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmsermonModel
 *
 * #[CoversClass(CwmsermonModel::class)]
 * @since  10.0.0
 */
class CwmsermonModelTest extends ProclaimTestCase
{
    /**
     * Test hit method signature
     *
     * @return void
     * #[CoversClass(CwmsermonModel::class)]::hit
     */
    public function testHitMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmsermonModel::class, 'hit');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('pk', $params[0]->getName());
        // No type hint in method signature for pk
        $this->assertTrue($params[0]->isOptional());
    }

    /**
     * Test getItem method signature
     *
     * @return void
     * #[CoversClass(CwmsermonModel::class)]::getItem
     */
    public function testGetItemMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmsermonModel::class, 'getItem');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('mixed', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('pk', $params[0]->getName());
        // No type hint in method signature for pk
        $this->assertTrue($params[0]->isOptional());
    }

    /**
     * Test getMediaFiles method signature
     *
     * @return void
     * #[CoversClass(CwmsermonModel::class)]::getMediaFiles
     */
    public function testGetMediaFilesMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmsermonModel::class, 'getMediaFiles');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('array', $reflection->getReturnType()->getName());
    }

    /**
     * Test getComments method signature
     *
     * @return void
     * #[CoversClass(CwmsermonModel::class)]::getComments
     */
    public function testGetCommentsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmsermonModel::class, 'getComments');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('array', $reflection->getReturnType()->getName());
    }

    /**
     * Test storecomment method signature
     *
     * @return void
     * #[CoversClass(CwmsermonModel::class)]::storecomment
     */
    public function testStorecommentMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmsermonModel::class, 'storecomment');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());
    }

    /**
     * Test getForm method signature
     *
     * @return void
     * #[CoversClass(CwmsermonModel::class)]::getForm
     */
    public function testGetFormMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmsermonModel::class, 'getForm');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        // Return type is bool|Form, which reflection might show differently
        
        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('data', $params[0]->getName());
        $this->assertEquals('array', $params[0]->getType()->getName());
        $this->assertTrue($params[0]->isOptional());
        
        $this->assertEquals('loadData', $params[1]->getName());
        $this->assertEquals('bool', $params[1]->getType()->getName());
        $this->assertTrue($params[1]->isOptional());
    }

    /**
     * Test populateState method signature
     *
     * @return void
     * #[CoversClass(CwmsermonModel::class)]::populateState
     */
    public function testPopulateStateMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmsermonModel::class, 'populateState');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertEquals('void', $reflection->getReturnType()->getName());
    }
}
