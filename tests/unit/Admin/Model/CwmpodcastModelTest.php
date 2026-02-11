<?php

/**
 * Unit tests for CwmpodcastModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmpodcastModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmpodcastModel
 *
 * #[CoversClass(CwmpodcastModel::class)]
 * @since  10.0.0
 */
class CwmpodcastModelTest extends ProclaimTestCase
{
    /**
     * Test getForm method signature
     *
     * @return void
     * #[CoversClass(CwmpodcastModel::class)]::getForm
     */
    public function testGetFormMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmpodcastModel::class, 'getForm');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('Joomla\CMS\Form\Form', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('data', $params[0]->getName());
        $this->assertParamTypeName('array', $params[0]);
        $this->assertTrue($params[0]->isOptional());

        $this->assertEquals('loadData', $params[1]->getName());
        $this->assertParamTypeName('bool', $params[1]);
        $this->assertTrue($params[1]->isOptional());
    }

    /**
     * Test getTable method signature
     *
     * @return void
     * #[CoversClass(CwmpodcastModel::class)]::getTable
     */
    public function testGetTableMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmpodcastModel::class, 'getTable');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('Joomla\CMS\Table\Table', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('name', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
        $this->assertTrue($params[0]->isOptional());

        $this->assertEquals('prefix', $params[1]->getName());
        $this->assertParamTypeName('string', $params[1]);
        $this->assertTrue($params[1]->isOptional());

        $this->assertEquals('options', $params[2]->getName());
        $this->assertParamTypeName('array', $params[2]);
        $this->assertTrue($params[2]->isOptional());
    }

    /**
     * Test batchLinkType method signature
     *
     * @return void
     * #[CoversClass(CwmpodcastModel::class)]::batchLinkType
     */
    public function testBatchLinkTypeMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmpodcastModel::class, 'batchLinkType');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('bool', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('value', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);

        $this->assertEquals('pks', $params[1]->getName());
        $this->assertParamTypeName('array', $params[1]);

        $this->assertEquals('contexts', $params[2]->getName());
        $this->assertParamTypeName('array', $params[2]);
    }
}
