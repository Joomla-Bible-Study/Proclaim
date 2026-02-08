<?php

/**
 * Unit tests for CwmseriespodcastlistModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Model;

use CWM\Component\Proclaim\Site\Model\CwmseriespodcastlistModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Database\DatabaseQuery;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmseriespodcastlistModel
 *
 * #[CoversClass(CwmseriespodcastlistModel::class)]
 * @since  10.0.0
 */
class CwmseriespodcastlistModelTest extends ProclaimTestCase
{
    /**
     * Test getItems method signature
     *
     * @return void
     * #[CoversClass(CwmseriespodcastlistModel::class)]::getItems
     */
    public function testGetItemsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmseriespodcastlistModel::class, 'getItems');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('mixed', $reflection);
    }

    /**
     * Test populateState method signature
     *
     * @return void
     * #[CoversClass(CwmseriespodcastlistModel::class)]::populateState
     */
    public function testPopulateStateMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmseriespodcastlistModel::class, 'populateState');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('void', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('ordering', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
        $this->assertTrue($params[0]->isOptional());
        
        $this->assertEquals('direction', $params[1]->getName());
        $this->assertParamTypeName('string', $params[1]);
        $this->assertTrue($params[1]->isOptional());
    }

    /**
     * Test getListQuery method signature
     *
     * @return void
     * #[CoversClass(CwmseriespodcastlistModel::class)]::getListQuery
     */
    public function testGetListQueryMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmseriespodcastlistModel::class, 'getListQuery');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('Joomla\Database\DatabaseQuery', $reflection);
    }
}
