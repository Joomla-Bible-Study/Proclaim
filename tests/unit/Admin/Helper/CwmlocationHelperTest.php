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

use CWM\Component\Proclaim\Administrator\Helper\CwmlocationHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Tests for the CwmlocationHelper class (Phase 1 — pure-logic methods only).
 *
 * Methods that require a database connection or a live Joomla application are
 * not covered here; they are exercised by integration tests.
 *
 * @since  10.1.0
 */
class CwmlocationHelperTest extends ProclaimTestCase
{
    // -------------------------------------------------------------------------
    // resetCache / cache management
    // -------------------------------------------------------------------------

    /**
     * resetCache(null) should clear the entire per-request cache without error.
     *
     * @return void
     * @since  10.1.0
     */
    public function testResetCacheAll(): void
    {
        CwmlocationHelper::resetCache();
        $this->assertTrue(true); // no exception = pass
    }

    /**
     * resetCache(int) should clear only the specified user's cache entry.
     *
     * @return void
     * @since  10.1.0
     */
    public function testResetCacheForUser(): void
    {
        CwmlocationHelper::resetCache(42);
        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // getTeacherLocations / userIsTeacher edge cases (no DB needed)
    // -------------------------------------------------------------------------

    /**
     * getTeacherLocations() returns empty for zero or negative user IDs
     * without hitting the database.
     *
     * @return void
     * @since  10.3.0
     */
    public function testGetTeacherLocationsReturnsEmptyForInvalidUserId(): void
    {
        $this->assertSame([], CwmlocationHelper::getTeacherLocations(0));
        $this->assertSame([], CwmlocationHelper::getTeacherLocations(-1));
    }

    /**
     * userIsTeacher() returns false for zero or negative IDs without
     * hitting the database.
     *
     * @return void
     * @since  10.3.0
     */
    public function testUserIsTeacherReturnsFalseForInvalidIds(): void
    {
        $this->assertFalse(CwmlocationHelper::userIsTeacher(0, 0));
        $this->assertFalse(CwmlocationHelper::userIsTeacher(-1, 1));
        $this->assertFalse(CwmlocationHelper::userIsTeacher(1, 0));
        $this->assertFalse(CwmlocationHelper::userIsTeacher(1, -1));
    }
}
