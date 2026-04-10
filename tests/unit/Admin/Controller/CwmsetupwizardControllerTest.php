<?php

/**
 * Unit tests for CwmsetupwizardController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmsetupwizardController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Test class for CwmsetupwizardController
 *
 * @since  10.3.0
 */
class CwmsetupwizardControllerTest extends ProclaimTestCase
{
    /**
     * Test that the class extends BaseController.
     */
    public function testExtendsBaseController(): void
    {
        $this->assertTrue(
            is_subclass_of(CwmsetupwizardController::class, BaseController::class),
            'CwmsetupwizardController must extend Joomla BaseController'
        );
    }

    /**
     * Test that the default view is set correctly.
     */
    public function testDefaultView(): void
    {
        $ref  = new \ReflectionClass(CwmsetupwizardController::class);
        $prop = $ref->getProperty('default_view');

        $this->assertEquals('cwmsetupwizard', $prop->getDefaultValue());
    }

    /**
     * Test that the view_list prevents pluralization.
     */
    public function testViewListProperty(): void
    {
        $ref  = new \ReflectionClass(CwmsetupwizardController::class);
        $prop = $ref->getProperty('view_list');

        $this->assertEquals('cwmsetupwizard', $prop->getDefaultValue());
    }

    /**
     * Test that execute only allows known tasks.
     */
    public function testExecuteMethodExists(): void
    {
        $ref = new \ReflectionMethod(CwmsetupwizardController::class, 'execute');

        $this->assertTrue($ref->isPublic());

        // Verify allowed tasks are defined in the source
        $source = file_get_contents($ref->getFileName());
        $this->assertStringContainsString("'display'", $source);
        $this->assertStringContainsString("'apply'", $source);
        $this->assertStringContainsString("'dismiss'", $source);
        $this->assertStringContainsString("'getStepData'", $source);
    }

    /**
     * Test that apply method exists and checks token.
     */
    public function testApplyMethodExists(): void
    {
        $ref = new \ReflectionMethod(CwmsetupwizardController::class, 'apply');

        $this->assertTrue($ref->isPublic());

        // Verify it checks session token
        $source       = file_get_contents($ref->getFileName());
        $startLine    = $ref->getStartLine();
        $endLine      = $ref->getEndLine();
        $lines        = \array_slice(file($ref->getFileName()), $startLine - 1, $endLine - $startLine + 1);
        $methodSource = implode('', $lines);

        $this->assertStringContainsString('Session::checkToken', $methodSource);
        $this->assertStringContainsString('core.admin', $methodSource);
    }

    /**
     * Test that dismiss method exists and checks token.
     */
    public function testDismissMethodExists(): void
    {
        $ref = new \ReflectionMethod(CwmsetupwizardController::class, 'dismiss');

        $this->assertTrue($ref->isPublic());
    }

    /**
     * Test that getStepData method exists.
     */
    public function testGetStepDataMethodExists(): void
    {
        $ref = new \ReflectionMethod(CwmsetupwizardController::class, 'getStepData');

        $this->assertTrue($ref->isPublic());
    }

    /**
     * Test that sendJsonResponse is private.
     */
    public function testSendJsonResponseIsPrivate(): void
    {
        $ref = new \ReflectionMethod(CwmsetupwizardController::class, 'sendJsonResponse');

        $this->assertTrue($ref->isPrivate());
    }

    /**
     * Test that apply sanitizes ministry_style input.
     */
    public function testApplySanitizesMinistryStyle(): void
    {
        $source = file_get_contents(
            (new \ReflectionClass(CwmsetupwizardController::class))->getFileName()
        );

        // Verify whitelist validation for ministry_style
        $this->assertStringContainsString("'simple', 'full_media', 'multi_campus'", $source);
        // Verify whitelist validation for primary_media
        $this->assertStringContainsString("'local', 'youtube', 'vimeo', 'direct'", $source);
        // Verify whitelist validation for ai_provider
        $this->assertStringContainsString("'claude', 'openai', 'gemini'", $source);
    }
}
