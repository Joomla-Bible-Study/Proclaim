<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmstudyteacherHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Tests for the CwmstudyteacherHelper class.
 *
 * @since  10.1.0
 */
class CwmstudyteacherHelperTest extends ProclaimTestCase
{
    /**
     * Test getTeachersForStudy returns empty array for zero/negative IDs.
     *
     * @return void
     * @since  10.1.0
     */
    public function testGetTeachersForStudyWithZeroId(): void
    {
        $result = CwmstudyteacherHelper::getTeachersForStudy(0);
        $this->assertSame([], $result);

        $result = CwmstudyteacherHelper::getTeachersForStudy(-1);
        $this->assertSame([], $result);
    }

    /**
     * Test getTeachersForStudies returns empty array for empty input.
     *
     * @return void
     * @since  10.1.0
     */
    public function testGetTeachersForStudiesWithEmptyInput(): void
    {
        $result = CwmstudyteacherHelper::getTeachersForStudies([]);
        $this->assertSame([], $result);
    }

    /**
     * Test getTeachersForStudies filters out zero/negative IDs.
     *
     * @return void
     * @since  10.1.0
     */
    public function testGetTeachersForStudiesFiltersInvalidIds(): void
    {
        $result = CwmstudyteacherHelper::getTeachersForStudies([0, -1, -5]);
        $this->assertSame([], $result);
    }

    /**
     * Test resetCache can be called without errors.
     *
     * @return void
     * @since  10.1.0
     */
    public function testResetCacheDoesNotThrow(): void
    {
        CwmstudyteacherHelper::resetCache();
        CwmstudyteacherHelper::resetCache(123);

        // If we reach here, no exception was thrown
        $this->assertTrue(true);
    }
}
