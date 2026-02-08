<?php

/**
 * Unit tests for CWMHtml5Inline
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Service\HTML;

use CWM\Component\Proclaim\Administrator\Service\HTML\CWMHtml5Inline;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Registry\Registry;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CWMHtml5Inline
 *
 * #[CoversClass(CWMHtml5Inline::class)]
 * @since  10.0.0
 */
class CWMHtml5InlineTest extends ProclaimTestCase
{
    /**
     * Test render method signature
     *
     * @return void
     * #[CoversClass(CWMHtml5Inline::class)]::render
     */
    public function testRenderMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CWMHtml5Inline::class, 'render');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(5, $params);
        $this->assertEquals('media', $params[0]->getName());
        $this->assertParamTypeName('object', $params[0]);
        
        $this->assertEquals('params', $params[1]->getName());
        // No type hint in method signature for params
        
        $this->assertEquals('player', $params[2]->getName());
        $this->assertParamTypeName('object', $params[2]);
        
        $this->assertEquals('popup', $params[3]->getName());
        $this->assertParamTypeName('bool', $params[3]);
        $this->assertTrue($params[3]->isOptional());
        
        $this->assertEquals('t', $params[4]->getName());
        $this->assertParamTypeName('int', $params[4]);
        $this->assertTrue($params[4]->allowsNull());
        $this->assertTrue($params[4]->isOptional());
    }

    /**
     * Test isMimeTypeAllowed method signature
     *
     * @return void
     * #[CoversClass(CWMHtml5Inline::class)]::isMimeTypeAllowed
     */
    public function testIsMimeTypeAllowedMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CWMHtml5Inline::class, 'isMimeTypeAllowed');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPrivate());
        $this->assertReturnTypeName('bool', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('mimeType', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
        
        $this->assertEquals('mimeArray', $params[1]->getName());
        $this->assertParamTypeName('array', $params[1]);
    }
}
