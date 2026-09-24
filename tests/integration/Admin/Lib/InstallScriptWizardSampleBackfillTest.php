<?php

/**
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Lib;

use CWM\Component\Proclaim\Administrator\Lib\Cwmimportmanifest;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The install script's backfill for pre-#2175 wizard-sample content.
 *
 * #2175 replaced the setup checklist's `alias != 'welcome-to-proclaim'`
 * string match with manifest-based exclusion. A site that used the wizard's
 * opt-in sample-content step on an earlier release has a study and series
 * wearing those aliases with no manifest row (the manifest table did not
 * exist yet), so without this backfill the checklist would start counting
 * them as real content the moment the update runs — the exact regression
 * #2175's own review caught.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversNothing]
class InstallScriptWizardSampleBackfillTest extends IntegrationTestCase
{
    private ?DatabaseInterface $db = null;

    private int $seq = 0;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        if (!class_exists('com_proclaimInstallerScript', false)) {
            require_once \dirname(__DIR__, 4) . '/proclaim.script.php';
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseInterface::class);
        $this->db->transactionStart(true);
    }

    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback(true);
            } catch (\Throwable) {
                // Connection may have gone away.
            }
        }

        parent::tearDown();
    }

    private function backfill(): void
    {
        $script = (new \ReflectionClass('com_proclaimInstallerScript'))->newInstanceWithoutConstructor();
        (new \ReflectionMethod('com_proclaimInstallerScript', 'backfillWizardSampleManifest'))->invoke($script);
    }

    /**
     * A unique-enough alias suffix so parallel runs, or a prior failed
     * rollback, never collide with an existing row.
     */
    private function alias(string $base): string
    {
        return $base . '-cwm2182-' . bin2hex(random_bytes(4)) . '-' . $this->seq++;
    }

    #[TestDox('Backfills a pre-existing welcome-to-proclaim study with no manifest row')]
    public function testBackfillsThePreExistingStudy(): void
    {
        // Real alias, not a unique-suffixed one — the backfill matches on it
        // literally, so the fixture has to use the exact string.
        $study = (object) ['studytitle' => 'Welcome to Proclaim', 'alias' => 'welcome-to-proclaim', 'language' => '*'];
        $this->db->insertObject('#__bsms_studies', $study, 'id');
        $studyId = (int) $this->db->insertid();

        $this->backfill();

        $this->assertContains($studyId, Cwmimportmanifest::allRowIds('#__bsms_studies'));
    }

    #[TestDox('Backfills a pre-existing sample-series with no manifest row')]
    public function testBackfillsThePreExistingSeries(): void
    {
        $series = (object) ['series_text' => 'Sample Series', 'alias' => 'sample-series', 'language' => '*'];
        $this->db->insertObject('#__bsms_series', $series, 'id');
        $seriesId = (int) $this->db->insertid();

        $this->backfill();

        $this->assertContains($seriesId, Cwmimportmanifest::allRowIds('#__bsms_series'));
    }

    #[TestDox('Running the backfill twice does not duplicate the manifest row or error')]
    public function testIdempotent(): void
    {
        $study = (object) ['studytitle' => 'Welcome to Proclaim', 'alias' => 'welcome-to-proclaim', 'language' => '*'];
        $this->db->insertObject('#__bsms_studies', $study, 'id');
        $studyId = (int) $this->db->insertid();

        $this->backfill();
        $this->backfill();

        $matches = array_filter(Cwmimportmanifest::allRowIds('#__bsms_studies'), static fn (int $id) => $id === $studyId);

        $this->assertCount(1, $matches, 'A second run must not record the same row twice.');
    }

    #[TestDox('A site with no such alias is a no-op')]
    public function testNoOpWhenNeitherAliasExists(): void
    {
        // Nothing seeded — this only proves the method runs cleanly to
        // completion when the SELECTs find nothing, which the harness's
        // shared dev database cannot otherwise guarantee is the case.
        $this->backfill();

        $this->addToAssertionCount(1);
    }

    #[TestDox('More than one row wearing the alias is backfilled, not just the first')]
    public function testBackfillsEveryMatchingRowNotJustTheFirst(): void
    {
        $first  = (object) ['studytitle' => 'Welcome to Proclaim', 'alias' => 'welcome-to-proclaim', 'language' => '*'];
        $second = (object) ['studytitle' => 'Welcome to Proclaim', 'alias' => 'welcome-to-proclaim', 'language' => '*'];
        $this->db->insertObject('#__bsms_studies', $first, 'id');
        $firstId = (int) $this->db->insertid();
        $this->db->insertObject('#__bsms_studies', $second, 'id');
        $secondId = (int) $this->db->insertid();

        $this->backfill();

        $manifestIds = Cwmimportmanifest::allRowIds('#__bsms_studies');

        $this->assertContains($firstId, $manifestIds);
        $this->assertContains($secondId, $manifestIds);
    }
}
