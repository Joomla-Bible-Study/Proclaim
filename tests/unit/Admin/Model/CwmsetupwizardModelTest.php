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

        // Verify it checks for existing servers before creating. Asserting
        // only the COUNT(*) query text stayed green if the gating
        // `if (loadResult() > 0) continue;` was removed while the query
        // remained -- which would duplicate default servers on every
        // wizard re-run.
        $this->assertStringContainsString('COUNT(*)', $source);
        $this->assertStringContainsString('#__bsms_servers', $source);
        $this->assertMatchesRegularExpression(
            '/if\s*\(\(int\)\s*\$db->loadResult\(\)\s*>\s*0\)\s*\{\s*continue;/s',
            $source,
            'The existing-server count must gate (skip) the insert, not just be queried'
        );
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
        $this->assertMatchesRegularExpression(
            '/if\s*\(\(int\)\s*\$db->loadResult\(\)\s*>\s*0\)\s*\{\s*continue;/s',
            $source,
            'The existing-task count must gate (skip) the insert, not just be queried'
        );
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
