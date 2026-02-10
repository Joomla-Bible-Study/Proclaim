<?php

/**
 * Unit tests for CwmscriptureController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Controller;

use CWM\Component\Proclaim\Site\Controller\CwmscriptureController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for CwmscriptureController
 *
 * @since  10.1.0
 */
class CwmscriptureControllerTest extends ProclaimTestCase
{
    /**
     * Test that controller class exists
     *
     * @return void
     */
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(CwmscriptureController::class));
    }

    /**
     * Test that controller extends BaseController
     *
     * @return void
     */
    public function testExtendsBaseController(): void
    {
        $reflection = new \ReflectionClass(CwmscriptureController::class);
        $this->assertTrue($reflection->isSubclassOf(\Joomla\CMS\MVC\Controller\BaseController::class));
    }

    /**
     * Test that getPassageXHR method exists
     *
     * @return void
     */
    public function testGetPassageXHRMethodExists(): void
    {
        $reflection = new \ReflectionClass(CwmscriptureController::class);
        $this->assertTrue($reflection->hasMethod('getPassageXHR'));

        $method = $reflection->getMethod('getPassageXHR');
        $this->assertTrue($method->isPublic());
        $this->assertSame('void', $this->getReturnTypeName($method));
    }

    /**
     * Get a return type name safely.
     *
     * @param   \ReflectionMethod  $method  The reflection method
     *
     * @return  string  Return type name or empty string
     */
    private function getReturnTypeName(\ReflectionMethod $method): string
    {
        $type = $method->getReturnType();

        if ($type instanceof \ReflectionNamedType) {
            return $type->getName();
        }

        return '';
    }
}
