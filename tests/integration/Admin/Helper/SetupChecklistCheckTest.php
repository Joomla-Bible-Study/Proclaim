<?php

/**
 * Integration tests for the setup checklist health check.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Health\Check\SetupChecklistCheck;
use CWM\Component\Proclaim\Administrator\Health\HealthStatus;
use CWM\Component\Proclaim\Administrator\Helper\CwmsetupwizardHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * ⚠️ `getChecklistItems()` consults neither setup flag. It builds its list
 * unconditionally, so on a site that has never run the wizard it returns every
 * step marked as not done — against records the installer seeded, not against
 * anything the administrator did or failed to do.
 *
 * Anything reporting on that list therefore has to ask whether the wizard has
 * run before it says anything, or it describes an install that has not started
 * as one that has fallen behind, and puts a notice on every fresh install.
 *
 * Exercised against the real settings row because the flag lives in
 * `#__bsms_admin` and the failure is entirely about reading it.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(SetupChecklistCheck::class)]
class SetupChecklistCheckTest extends IntegrationTestCase
{
    private ?DatabaseDriver $db = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart(true);
    }

    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback(true);
            } catch (\Throwable) {
                // Connection lost; nothing to roll back.
            }
        }

        parent::tearDown();
    }

    /**
     * @param   int  $complete  Value for setup_wizard_complete.
     *
     * @return  void
     */
    private function setWizardComplete(int $complete): void
    {
        $this->setParam('setup_wizard_complete', $complete);
    }

    /**
     * @param   string  $key    Param name.
     * @param   int     $value  Param value.
     *
     * @return  void
     */
    private function setParam(string $key, int $value): void
    {
        $json   = (string) $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('params'))
                ->from($this->db->quoteName('#__bsms_admin'))
                ->where($this->db->quoteName('id') . ' = 1')
        )->loadResult();

        $params = new Registry($json ?: '{}');
        $params->set($key, $value);

        $this->db->setQuery(
            $this->db->createQuery()
                ->update($this->db->quoteName('#__bsms_admin'))
                ->set($this->db->quoteName('params') . ' = ' . $this->db->quote($params->toString()))
                ->where($this->db->quoteName('id') . ' = 1')
        )->execute();
    }

    #[TestDox('The checklist helper ignores the wizard flag, so the caller must not')]
    public function testHelperDoesNotGateOnTheWizardFlag(): void
    {
        // ⚠️ This is the defect, stated as a property rather than as a symptom.
        // Asserting instead that the list "contains unfinished steps" made the
        // test depend on how the site happened to be set up — true locally,
        // false on a clean CI database, where it failed. The property holds
        // whatever the site contains.
        $this->setWizardComplete(0);
        $before = CwmsetupwizardHelper::getChecklistItems();

        $this->setWizardComplete(1);
        $after = CwmsetupwizardHelper::getChecklistItems();

        $this->assertNotSame([], $after, 'The checklist is empty in every state; this test would prove nothing.');
        $this->assertEquals(
            $before,
            $after,
            'getChecklistItems() now consults the wizard flag. If it gates itself, the check no longer has to — '
            . 'but until it does, anything reporting on this list must ask first.'
        );
    }

    #[TestDox('A site that never ran the wizard is not told it is behind')]
    public function testSilentBeforeTheWizardHasRun(): void
    {
        $this->setWizardComplete(0);

        $this->assertFalse(CwmsetupwizardHelper::wizardComplete());

        // Meaningful because of the test above: the helper hands over a full
        // list in this state regardless, so an Ok here is the check declining
        // to report it rather than there being nothing to report.
        $this->assertSame(
            HealthStatus::Ok,
            (new SetupChecklistCheck())->run()->status,
            'A fresh install was told it had unfinished setup steps, on the strength of seeded records.'
        );
    }

    #[TestDox('Once the wizard has run, outstanding steps are reported')]
    public function testReportsOutstandingStepsAfterTheWizard(): void
    {
        $this->setWizardComplete(1);

        $this->assertTrue(CwmsetupwizardHelper::wizardComplete());

        $undone = array_filter(
            CwmsetupwizardHelper::getChecklistItems(),
            static fn (array $item): bool => empty($item['done'])
        );

        $result = (new SetupChecklistCheck())->run();

        if ($undone === []) {
            // A site with nothing outstanding is a legitimate state; assert the
            // matching answer rather than skipping and covering nothing.
            $this->assertSame(HealthStatus::Ok, $result->status);

            return;
        }

        $this->assertSame(
            HealthStatus::Notice,
            $result->status,
            'Outstanding setup steps were not reported after the wizard completed.'
        );

        // Never a Warning: an unfinished checklist is information, not a fault.
        $this->assertNotSame(HealthStatus::Warning, $result->status);

        // The fingerprint carries which steps are outstanding, so finishing one
        // is a different statement and is not silenced by an earlier quieten.
        $this->assertNotSame('', $result->fingerprint);
    }

    #[TestDox('Dismissing the dashboard banner does not change what the check reports')]
    public function testDismissalDoesNotSilenceIt(): void
    {
        $this->setWizardComplete(1);

        $before = (new SetupChecklistCheck())->run();

        $this->setParam('setup_checklist_dismissed', 1);

        // The entire point of the issue. Assert the banner really is gone,
        // otherwise the comparison below proves nothing.
        $this->assertFalse(
            CwmsetupwizardHelper::shouldShowChecklist(),
            'Setup did not actually dismiss the banner, so this proves nothing.'
        );

        $after = (new SetupChecklistCheck())->run();

        // ⚠️ Compared rather than asserted against a fixed status, so this holds
        // on a site with outstanding steps and on one without. Either way the
        // dismissal must make no difference — that is the bug.
        $this->assertSame(
            $before->status,
            $after->status,
            'Dismissing the dashboard banner changed what System Health reports, which is the bug.'
        );
        $this->assertSame($before->detail, $after->detail);
    }

}
