<?php

/**
 * Unit tests for FilesizeField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\FilesizeField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for FilesizeField
 *
 * #[CoversClass(FilesizeField::class)]
 * @since  10.0.0
 */
class FilesizeFieldTest extends ProclaimTestCase
{
    /**
     * Test getInput method signature
     *
     * @return void
     * #[CoversClass(FilesizeField::class)]::getInput
     */
    public function testGetInputMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(FilesizeField::class, 'getInput');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('string', $reflection);
    }

    /**
     * Test sizeConverter method signature
     *
     * @return void
     * #[CoversClass(FilesizeField::class)]::sizeConverter
     */
    public function testSizeConverterMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(FilesizeField::class, 'sizeConverter');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPrivate());
        $this->assertReturnTypeName('string', $reflection);
    }
}
