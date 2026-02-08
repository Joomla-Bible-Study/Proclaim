<?php

/**
 * Unit tests for MediaFileField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\MediaFileField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for MediaFileField
 *
 * #[CoversClass(MediaFileField::class)]
 * @since  10.0.0
 */
class MediaFileFieldTest extends ProclaimTestCase
{
    /**
     * Test getOptions method signature
     *
     * @return void
     * #[CoversClass(MediaFileField::class)]::getOptions
     */
    public function testGetOptionsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(MediaFileField::class, 'getOptions');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('array', $reflection);
    }
}
