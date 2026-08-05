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

/**
 * Test class for CwmsetupwizardModel
 *
 * @since  10.3.0
 */
class CwmsetupwizardModelTest extends ProclaimTestCase
{
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
