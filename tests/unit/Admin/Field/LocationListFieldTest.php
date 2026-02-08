<?php

/**
 * Unit tests for LocationListField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\LocationListField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for LocationListField
 *
 * #[CoversClass(LocationListField::class)]
 * @since  10.0.0
 */
class LocationListFieldTest extends ProclaimTestCase
{
    /**
     * Test getOptions method signature
     *
     * @return void
     * #[CoversClass(LocationListField::class)]::getOptions
     */
    public function testGetOptionsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(LocationListField::class, 'getOptions');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('array', $reflection);
    }
}
