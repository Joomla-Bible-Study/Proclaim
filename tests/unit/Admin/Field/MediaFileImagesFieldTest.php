<?php

/**
 * Unit tests for MediaFileImagesField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\MediaFileImagesField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for MediaFileImagesField
 *
 * #[CoversClass(MediaFileImagesField::class)]
 * @since  10.0.0
 */
class MediaFileImagesFieldTest extends ProclaimTestCase
{
    /**
     * Test getOptions method signature
     *
     * @return void
     * #[CoversClass(MediaFileImagesField::class)]::getOptions
     */
    public function testGetOptionsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(MediaFileImagesField::class, 'getOptions');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('array', $reflection);
    }

    /**
     * Test getButton method signature
     *
     * @return void
     * #[CoversClass(MediaFileImagesField::class)]::getButton
     */
    public function testGetButtonMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(MediaFileImagesField::class, 'getButton');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);
        $this->assertTrue($reflection->getReturnType()->allowsNull());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('media', $params[0]->getName());
        $this->assertParamTypeName('object', $params[0]);
    }

    /**
     * Test getIcon method signature
     *
     * @return void
     * #[CoversClass(MediaFileImagesField::class)]::getIcon
     */
    public function testGetIconMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(MediaFileImagesField::class, 'getIcon');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('media', $params[0]->getName());
        $this->assertParamTypeName('object', $params[0]);
    }
}
