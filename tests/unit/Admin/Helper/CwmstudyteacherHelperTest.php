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
     * Verify the class exists and can be referenced.
     *
     * @return void
     * @since  10.1.0
     */
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(CwmstudyteacherHelper::class));
    }

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

    /**
     * Verify getTeachersForStudy method signature.
     *
     * @return void
     * @since  10.1.0
     */
    public function testGetTeachersForStudySignature(): void
    {
        $method = new \ReflectionMethod(CwmstudyteacherHelper::class, 'getTeachersForStudy');
        $this->assertReturnTypeName('array', $method);
        $this->assertParamTypeName('int', $method->getParameters()[0]);
    }

    /**
     * Verify getTeachersForStudies method signature.
     *
     * @return void
     * @since  10.1.0
     */
    public function testGetTeachersForStudiesSignature(): void
    {
        $method = new \ReflectionMethod(CwmstudyteacherHelper::class, 'getTeachersForStudies');
        $this->assertReturnTypeName('array', $method);
        $this->assertParamTypeName('array', $method->getParameters()[0]);
    }

    /**
     * Verify saveTeachers method signature.
     *
     * @return void
     * @since  10.1.0
     */
    public function testSaveTeachersSignature(): void
    {
        $method = new \ReflectionMethod(CwmstudyteacherHelper::class, 'saveTeachers');
        $this->assertReturnTypeName('void', $method);
        $params = $method->getParameters();
        $this->assertParamTypeName('int', $params[0]);
        $this->assertParamTypeName('array', $params[1]);
    }

    /**
     * Verify syncLegacyColumn method signature.
     *
     * @return void
     * @since  10.1.0
     */
    public function testSyncLegacyColumnSignature(): void
    {
        $method = new \ReflectionMethod(CwmstudyteacherHelper::class, 'syncLegacyColumn');
        $this->assertReturnTypeName('void', $method);
        $params = $method->getParameters();
        $this->assertParamTypeName('int', $params[0]);
        $this->assertParamTypeName('array', $params[1]);
    }

    /**
     * Verify deleteTeachers method signature.
     *
     * @return void
     * @since  10.1.0
     */
    public function testDeleteTeachersSignature(): void
    {
        $method = new \ReflectionMethod(CwmstudyteacherHelper::class, 'deleteTeachers');
        $this->assertReturnTypeName('void', $method);
        $this->assertParamTypeName('int', $method->getParameters()[0]);
    }

    /**
     * Verify resetCache method signature.
     *
     * @return void
     * @since  10.1.0
     */
    public function testResetCacheSignature(): void
    {
        $method = new \ReflectionMethod(CwmstudyteacherHelper::class, 'resetCache');
        $this->assertReturnTypeName('void', $method);
    }

    /**
     * Verify the teacherCache property is declared and static.
     *
     * @return void
     * @since  10.1.0
     */
    public function testTeacherCachePropertyExists(): void
    {
        $reflection = new \ReflectionClass(CwmstudyteacherHelper::class);
        $this->assertTrue($reflection->hasProperty('teacherCache'));

        $prop = $reflection->getProperty('teacherCache');
        $this->assertTrue($prop->isStatic());
        $this->assertTrue($prop->isPrivate());
    }

    /**
     * Verify all public methods are static.
     *
     * @return void
     * @since  10.1.0
     */
    public function testAllPublicMethodsAreStatic(): void
    {
        $reflection    = new \ReflectionClass(CwmstudyteacherHelper::class);
        $publicMethods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($publicMethods as $method) {
            if ($method->getDeclaringClass()->getName() === CwmstudyteacherHelper::class) {
                $this->assertTrue(
                    $method->isStatic(),
                    'Method ' . $method->getName() . ' should be static'
                );
            }
        }
    }
}
