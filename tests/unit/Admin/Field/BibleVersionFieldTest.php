<?php

/**
 * Unit tests for BibleVersionField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\BibleVersionField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for BibleVersionField
 *
 * @since  10.1.0
 */
class BibleVersionFieldTest extends ProclaimTestCase
{
    /**
     * Test that BibleVersionField class exists and extends ListField
     *
     * @return void
     */
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(BibleVersionField::class));
    }

    /**
     * Test that BibleVersionField extends Joomla ListField
     *
     * @return void
     */
    public function testExtendsListField(): void
    {
        $reflection = new \ReflectionClass(BibleVersionField::class);
        $this->assertTrue($reflection->isSubclassOf(\Joomla\CMS\Form\Field\ListField::class));
    }

    /**
     * Test that the field type is set correctly
     *
     * @return void
     */
    public function testFieldType(): void
    {
        $reflection = new \ReflectionClass(BibleVersionField::class);
        $prop       = $reflection->getProperty('type');
        $prop->setAccessible(true);

        $field = $reflection->newInstanceWithoutConstructor();
        $this->assertSame('BibleVersion', $prop->getValue($field));
    }

    /**
     * Test that getOptions method exists and is protected
     *
     * @return void
     */
    public function testGetOptionsMethodExists(): void
    {
        $reflection = new \ReflectionClass(BibleVersionField::class);
        $this->assertTrue($reflection->hasMethod('getOptions'));

        $method = $reflection->getMethod('getOptions');
        $this->assertTrue($method->isProtected());
    }
}
