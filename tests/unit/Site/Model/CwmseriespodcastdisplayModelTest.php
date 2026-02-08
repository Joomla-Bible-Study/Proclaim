<?php

/**
 * Unit tests for CwmseriespodcastdisplayModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Model;

use CWM\Component\Proclaim\Site\Model\CwmseriespodcastdisplayModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Database\QueryInterface;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmseriespodcastdisplayModel
 *
 * #[CoversClass(CwmseriespodcastdisplayModel::class)]
 * @since  10.0.0
 */
class CwmseriespodcastdisplayModelTest extends ProclaimTestCase
{
    /**
     * Test getItem method signature
     *
     * @return void
     * #[CoversClass(CwmseriespodcastdisplayModel::class)]::getItem
     */
    public function testGetItemMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmseriespodcastdisplayModel::class, 'getItem');

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
     * Test getStudiesQuery method signature
     *
     * @return void
     * #[CoversClass(CwmseriespodcastdisplayModel::class)]::getStudiesQuery
     */
    public function testGetStudiesQueryMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmseriespodcastdisplayModel::class, 'getStudiesQuery');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertEquals('Joomla\Database\QueryInterface', $reflection->getReturnType()->getName());
    }

    /**
     * Test getStudies method signature
     *
     * @return void
     * #[CoversClass(CwmseriespodcastdisplayModel::class)]::getStudies
     */
    public function testGetStudiesMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmseriespodcastdisplayModel::class, 'getStudies');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('array', $reflection->getReturnType()->getName());
    }

    /**
     * Test getTotal method signature
     *
     * @return void
     * #[CoversClass(CwmseriespodcastdisplayModel::class)]::getTotal
     */
    public function testGetTotalMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmseriespodcastdisplayModel::class, 'getTotal');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('int', $reflection->getReturnType()->getName());
    }

    /**
     * Test populateState method signature
     *
     * @return void
     * #[CoversClass(CwmseriespodcastdisplayModel::class)]::populateState
     */
    public function testPopulateStateMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmseriespodcastdisplayModel::class, 'populateState');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertEquals('void', $reflection->getReturnType()->getName());
    }
}
