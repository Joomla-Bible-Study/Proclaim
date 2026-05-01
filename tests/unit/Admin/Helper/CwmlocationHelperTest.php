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
    /**
     * Verify the class exists and is auto-loadable.
     *
     * @return void
     * @since  10.1.0
     */
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(CwmlocationHelper::class));
    }

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

    // -------------------------------------------------------------------------
    // Method signature / reflection checks
    // -------------------------------------------------------------------------

    /**
     * Verify all expected public static methods exist with correct signatures.
     *
     * @return void
     * @since  10.1.0
     */
    public function testPublicApiSurface(): void
    {
        $class   = new \ReflectionClass(CwmlocationHelper::class);
        $methods = array_map(
            static fn (\ReflectionMethod $m) => $m->getName(),
            $class->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_STATIC)
        );

        $expected = [
            'getUserLocations',
            'getUserAccessibleLocationsForEdit',
            'applyLocationFilter',
            'applySecurityFilter',
            'getTeacherLocations',
            'userIsTeacher',
            'getLocationUsage',
            'shouldShowWizard',
            'getPublishedLocationCount',
            'isEnabled',
            'resetCache',
        ];

        foreach ($expected as $name) {
            $this->assertContains($name, $methods, "Expected public static method {$name}() to exist");
        }
    }

    /**
     * getUserLocations() return type is array.
     *
     * @return void
     * @since  10.1.0
     */
    public function testGetUserLocationsReturnType(): void
    {
        $ref    = new \ReflectionMethod(CwmlocationHelper::class, 'getUserLocations');
        $return = $ref->getReturnType();
        $this->assertNotNull($return, 'getUserLocations() should declare a return type');
        $this->assertReturnTypeName('array', $ref);
    }

    /**
     * getTeacherLocations() return type is array.
     *
     * @return void
     * @since  10.1.0
     */
    public function testGetTeacherLocationsReturnType(): void
    {
        $ref = new \ReflectionMethod(CwmlocationHelper::class, 'getTeacherLocations');
        $this->assertReturnTypeName('array', $ref);
    }

    /**
     * userIsTeacher() return type is bool.
     *
     * @return void
     * @since  10.1.0
     */
    public function testUserIsTeacherReturnType(): void
    {
        $ref = new \ReflectionMethod(CwmlocationHelper::class, 'userIsTeacher');
        $this->assertReturnTypeName('bool', $ref);
    }

    /**
     * isEnabled() return type is bool.
     *
     * @return void
     * @since  10.1.0
     */
    public function testIsEnabledReturnType(): void
    {
        $ref = new \ReflectionMethod(CwmlocationHelper::class, 'isEnabled');
        $this->assertReturnTypeName('bool', $ref);
    }

    /**
     * shouldShowWizard() return type is bool.
     *
     * @return void
     * @since  10.1.0
     */
    public function testShouldShowWizardReturnType(): void
    {
        $ref = new \ReflectionMethod(CwmlocationHelper::class, 'shouldShowWizard');
        $this->assertReturnTypeName('bool', $ref);
    }

    /**
     * getLocationUsage() return type is array.
     *
     * @return void
     * @since  10.1.0
     */
    public function testGetLocationUsageReturnType(): void
    {
        $ref = new \ReflectionMethod(CwmlocationHelper::class, 'getLocationUsage');
        $this->assertReturnTypeName('array', $ref);
    }

    /**
     * resetCache() accepts an optional nullable int parameter.
     *
     * @return void
     * @since  10.1.0
     */
    public function testResetCacheSignature(): void
    {
        $ref    = new \ReflectionMethod(CwmlocationHelper::class, 'resetCache');
        $params = $ref->getParameters();
        $this->assertCount(1, $params, 'resetCache() should accept exactly one parameter');
        $this->assertTrue($params[0]->isOptional(), 'resetCache() parameter should be optional');
        $this->assertTrue($params[0]->allowsNull(), 'resetCache() parameter should allow null');
    }
}
