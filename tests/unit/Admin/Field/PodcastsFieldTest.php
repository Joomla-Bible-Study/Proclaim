<?php

/**
 * Unit tests for PodcastsField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\PodcastsField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for PodcastsField
 *
 * #[CoversClass(PodcastsField::class)]
 * @since  10.0.0
 */
class PodcastsFieldTest extends ProclaimTestCase
{
    /**
     * Test getOptions method signature
     *
     * @return void
     * #[CoversClass(PodcastsField::class)]::getOptions
     */
    public function testGetOptionsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(PodcastsField::class, 'getOptions');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('array', $reflection);
    }
}
