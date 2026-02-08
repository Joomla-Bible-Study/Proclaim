<?php

/**
 * Unit tests for CwmassetsModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmassetsModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmassetsModel
 *
 * #[CoversClass(CwmassetsModel::class)]
 * @since  10.0.0
 */
class CwmassetsModelTest extends ProclaimTestCase
{
    /**
     * Test constructor
     *
     * @return void
     * #[CoversClass(CwmassetsModel::class)]::__construct
     */
    public function testConstructor(): void
    {
        $model = new CwmassetsModel();
        $this->assertInstanceOf(CwmassetsModel::class, $model);
    }

    /**
     * Test startScanning method signature
     *
     * @return void
     * #[CoversClass(CwmassetsModel::class)]::startScanning
     */
    public function testStartScanningMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmassetsModel::class, 'startScanning');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());
    }

    /**
     * Test run method signature
     *
     * @return void
     * #[CoversClass(CwmassetsModel::class)]::run
     */
    public function testRunMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmassetsModel::class, 'run');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('resetTimer', $params[0]->getName());
        $this->assertEquals('bool', $params[0]->getType()->getName());
        $this->assertTrue($params[0]->isOptional());
    }

    /**
     * Test checkAssets method signature
     *
     * @return void
     * #[CoversClass(CwmassetsModel::class)]::checkAssets
     */
    public function testCheckAssetsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmassetsModel::class, 'checkAssets');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('array', $reflection->getReturnType()->getName());
    }

    /**
     * Test parentId method signature
     *
     * @return void
     * #[CoversClass(CwmassetsModel::class)]::parentId
     */
    public function testParentIdMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmassetsModel::class, 'parentId');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('void', $reflection->getReturnType()->getName());
    }
}
