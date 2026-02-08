<?php

/**
 * Unit tests for MimeTypeField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\MimeTypeField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for MimeTypeField
 *
 * #[CoversClass(MimeTypeField::class)]
 * @since  10.0.0
 */
class MimeTypeFieldTest extends ProclaimTestCase
{
    /**
     * Test getOptions method signature
     *
     * @return void
     * #[CoversClass(MimeTypeField::class)]::getOptions
     */
    public function testGetOptionsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(MimeTypeField::class, 'getOptions');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('array', $reflection);
    }
}
