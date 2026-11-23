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

use CWM\Component\Proclaim\Administrator\Helper\CwmstudytopicHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Tests for the CwmstudytopicHelper class, extracted from
 * CwmmessageController::save() as part of #1444.
 *
 * @since  10.5.6
 */
class CwmstudytopicHelperTest extends ProclaimTestCase
{
    /**
     * The CwmstudytopicHelper class exists.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(CwmstudytopicHelper::class));
    }

    /**
     * findTopicIdByText() takes a string and returns an int.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    public function testFindTopicIdByTextSignature(): void
    {
        $method = new \ReflectionMethod(CwmstudytopicHelper::class, 'findTopicIdByText');
        $this->assertReturnTypeName('int', $method);
        $this->assertParamTypeName('string', $method->getParameters()[0]);
    }

    /**
     * findTopicIdByText() returns 0 for text no topic has.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    public function testFindTopicIdByTextReturnsZeroForUnknownText(): void
    {
        $result = CwmstudytopicHelper::findTopicIdByText('a topic name that certainly does not exist ' . uniqid());
        $this->assertSame(0, $result);
    }

    /**
     * saveTopics() takes an int and an array, and returns a bool.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    public function testSaveTopicsSignature(): void
    {
        $method = new \ReflectionMethod(CwmstudytopicHelper::class, 'saveTopics');
        $this->assertReturnTypeName('bool', $method);
        $params = $method->getParameters();
        $this->assertParamTypeName('int', $params[0]);
        $this->assertParamTypeName('array', $params[1]);
    }

    /**
     * Every public method the helper declares is static.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    public function testAllPublicMethodsAreStatic(): void
    {
        $reflection    = new \ReflectionClass(CwmstudytopicHelper::class);
        $publicMethods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($publicMethods as $method) {
            if ($method->getDeclaringClass()->getName() === CwmstudytopicHelper::class) {
                $this->assertTrue(
                    $method->isStatic(),
                    'Method ' . $method->getName() . ' should be static'
                );
            }
        }
    }
}
