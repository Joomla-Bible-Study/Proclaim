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
        $json   = (string) $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('params'))
                ->from($this->db->quoteName('#__bsms_admin'))
                ->where($this->db->quoteName('id') . ' = 1')
        )->loadResult();

        $params = new Registry($json ?: '{}');
        $params->set('setup_wizard_complete', $complete);

        $this->db->setQuery(
            $this->db->createQuery()
                ->update($this->db->quoteName('#__bsms_admin'))
                ->set($this->db->quoteName('params') . ' = ' . $this->db->quote($params->toString()))
                ->where($this->db->quoteName('id') . ' = 1')
        )->execute();
    }

    #[TestDox('A site that never ran the wizard is not told it is behind')]
    public function testSilentBeforeTheWizardHasRun(): void
    {
        $this->setWizardComplete(0);

        // ⚠️ Positive control. The assertion below is only meaningful if the
        // list genuinely contains unfinished steps in this state — which is
        // the whole defect. Without this, a helper that returned nothing would
        // make the check silent for the wrong reason and the test would agree.
        $undone = array_filter(
            CwmsetupwizardHelper::getChecklistItems(),
            static fn (array $item): bool => empty($item['done'])
        );

        $this->assertNotEmpty(
            $undone,
            'getChecklistItems() reported nothing outstanding, so this test cannot show the check ignoring it.'
        );

        $this->assertFalse(CwmsetupwizardHelper::wizardComplete());

        $result = (new SetupChecklistCheck())->run();

        $this->assertSame(
            HealthStatus::Ok,
            $result->status,
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

    #[TestDox('Dismissing the dashboard banner does not silence the check')]
    public function testDismissalDoesNotSilenceIt(): void
    {
        $this->setWizardComplete(1);

        $json   = (string) $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('params'))
                ->from($this->db->quoteName('#__bsms_admin'))
                ->where($this->db->quoteName('id') . ' = 1')
        )->loadResult();
        $params = new Registry($json ?: '{}');
        $params->set('setup_checklist_dismissed', 1);

        $this->db->setQuery(
            $this->db->createQuery()
                ->update($this->db->quoteName('#__bsms_admin'))
                ->set($this->db->quoteName('params') . ' = ' . $this->db->quote($params->toString()))
                ->where($this->db->quoteName('id') . ' = 1')
        )->execute();

        // The entire point of the check. The banner is gone for good; the
        // information behind it must not be.
        $this->assertFalse(
            CwmsetupwizardHelper::shouldShowChecklist(),
            'Setup did not actually dismiss the banner, so this proves nothing.'
        );

        $undone = array_filter(
            CwmsetupwizardHelper::getChecklistItems(),
            static fn (array $item): bool => empty($item['done'])
        );

        if ($undone === []) {
            $this->markTestSkipped('Nothing outstanding on this site, so dismissal cannot hide anything.');
        }

        $this->assertSame(
            HealthStatus::Notice,
            (new SetupChecklistCheck())->run()->status,
            'Dismissing the dashboard banner also silenced System Health, which is the bug.'
        );
    }
}
