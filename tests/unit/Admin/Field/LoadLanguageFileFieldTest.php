<?php

/**
 * Unit tests for LoadLanguageFileField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\LoadLanguageFileField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for LoadLanguageFileField
 *
 * #[CoversClass(LoadLanguageFileField::class)]
 * @since  10.0.0
 */
class LoadLanguageFileFieldTest extends ProclaimTestCase
{
    /**
     * Test getLabel method signature
     *
     * @return void
     * #[CoversClass(LoadLanguageFileField::class)]::getLabel
     */
    public function testGetLabelMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(LoadLanguageFileField::class, 'getLabel');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);
    }

    /**
     * Test getInput method signature
     *
     * @return void
     * #[CoversClass(LoadLanguageFileField::class)]::getInput
     */
    public function testGetInputMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(LoadLanguageFileField::class, 'getInput');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('string', $reflection);
    }
}
