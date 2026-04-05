<?php

/**
 * Unit tests for CwmsetupwizardModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmsetupwizardModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Test class for CwmsetupwizardModel
 *
 * @since  10.3.0
 */
class CwmsetupwizardModelTest extends ProclaimTestCase
{
    /**
     * Test that the class extends BaseDatabaseModel.
     */
    public function testExtendsBaseDatabaseModel(): void
    {
        $this->assertTrue(
            is_subclass_of(CwmsetupwizardModel::class, BaseDatabaseModel::class),
            'CwmsetupwizardModel must extend Joomla BaseDatabaseModel'
        );
    }

    /**
     * Test that getCurrentState method exists and is public.
     */
    public function testGetCurrentStateMethodExists(): void
    {
        $ref = new \ReflectionMethod(CwmsetupwizardModel::class, 'getCurrentState');

        $this->assertTrue($ref->isPublic());
    }

    /**
     * Test that getCurrentState returns an array type.
     */
    public function testGetCurrentStateReturnType(): void
    {
        $ref  = new \ReflectionMethod(CwmsetupwizardModel::class, 'getCurrentState');
        $type = $ref->getReturnType();

        $this->assertNotNull($type);
        $this->assertEquals('array', $type->getName());
    }

    /**
     * Test that applyWizard method exists and is public.
     */
    public function testApplyWizardMethodExists(): void
    {
        $ref = new \ReflectionMethod(CwmsetupwizardModel::class, 'applyWizard');

        $this->assertTrue($ref->isPublic());
    }

    /**
     * Test that applyWizard accepts an array parameter.
     */
    public function testApplyWizardAcceptsArray(): void
    {
        $ref    = new \ReflectionMethod(CwmsetupwizardModel::class, 'applyWizard');
        $params = $ref->getParameters();

        $this->assertCount(1, $params);
        $this->assertEquals('data', $params[0]->getName());
        $this->assertEquals('array', $params[0]->getType()->getName());
    }

    /**
     * Test that dismiss method exists and is public.
     */
    public function testDismissMethodExists(): void
    {
        $ref = new \ReflectionMethod(CwmsetupwizardModel::class, 'dismiss');

        $this->assertTrue($ref->isPublic());
    }

    /**
     * Test that createDefaultServers is private.
     */
    public function testCreateDefaultServersIsPrivate(): void
    {
        $ref = new \ReflectionMethod(CwmsetupwizardModel::class, 'createDefaultServers');

        $this->assertTrue($ref->isPrivate());
    }

    /**
     * Test that createSampleContent is private.
     */
    public function testCreateSampleContentIsPrivate(): void
    {
        $ref = new \ReflectionMethod(CwmsetupwizardModel::class, 'createSampleContent');

        $this->assertTrue($ref->isPrivate());
    }

    /**
     * Test that registerScheduledTasks is private.
     */
    public function testRegisterScheduledTasksIsPrivate(): void
    {
        $ref = new \ReflectionMethod(CwmsetupwizardModel::class, 'registerScheduledTasks');

        $this->assertTrue($ref->isPrivate());
    }

    /**
     * Test that loadAdminParams and saveAdminParams are private.
     */
    public function testAdminParamsMethodsArePrivate(): void
    {
        $load = new \ReflectionMethod(CwmsetupwizardModel::class, 'loadAdminParams');
        $save = new \ReflectionMethod(CwmsetupwizardModel::class, 'saveAdminParams');

        $this->assertTrue($load->isPrivate());
        $this->assertTrue($save->isPrivate());
    }

    /**
     * Test that createDefaultServers checks for existing servers.
     */
    public function testCreateDefaultServersChecksExisting(): void
    {
        $ref    = new \ReflectionMethod(CwmsetupwizardModel::class, 'createDefaultServers');
        $lines  = \array_slice(file($ref->getFileName()), $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1);
        $source = implode('', $lines);

        // Verify it checks for existing servers before creating
        $this->assertStringContainsString('COUNT(*)', $source);
        $this->assertStringContainsString('#__bsms_servers', $source);
    }

    /**
     * Test that registerScheduledTasks checks for existing tasks.
     */
    public function testRegisterScheduledTasksChecksExisting(): void
    {
        $ref    = new \ReflectionMethod(CwmsetupwizardModel::class, 'registerScheduledTasks');
        $lines  = \array_slice(file($ref->getFileName()), $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1);
        $source = implode('', $lines);

        $this->assertStringContainsString('COUNT(*)', $source);
        $this->assertStringContainsString('#__scheduler_tasks', $source);
    }

    /**
     * Test that applyWizard sets setup_wizard_complete flag.
     */
    public function testApplyWizardSetsCompletionFlag(): void
    {
        $ref    = new \ReflectionMethod(CwmsetupwizardModel::class, 'applyWizard');
        $lines  = \array_slice(file($ref->getFileName()), $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1);
        $source = implode('', $lines);

        $this->assertStringContainsString("'setup_wizard_complete', 1", $source);
    }

    /**
     * Test that dismiss sets completion flag.
     */
    public function testDismissSetsCompletionFlag(): void
    {
        $ref    = new \ReflectionMethod(CwmsetupwizardModel::class, 'dismiss');
        $lines  = \array_slice(file($ref->getFileName()), $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1);
        $source = implode('', $lines);

        $this->assertStringContainsString("'setup_wizard_complete', 1", $source);
    }
}
