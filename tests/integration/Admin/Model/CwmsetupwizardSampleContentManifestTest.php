<?php

/**
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Model;

use CWM\Component\Proclaim\Administrator\Lib\Cwmimportmanifest;
use CWM\Component\Proclaim\Administrator\Model\CwmsetupwizardModel;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The setup wizard's own opt-in sample content (a teacher, a series, and the
 * 'welcome-to-proclaim' message) is a second source of demo content, distinct
 * from #2145's imported set but subject to the same problem: the checklist
 * (#2175) now excludes "real content" by manifest membership rather than by
 * matching that one alias, so createSampleContent() has to record what it
 * creates or its own output would start counting as real.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmsetupwizardModel::class)]
class CwmsetupwizardSampleContentManifestTest extends IntegrationTestCase
{
    private ?DatabaseInterface $db = null;

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

    /**
     * Neither this method nor recordRow() touches $this, so an unconstructed
     * instance is enough — the same pattern CwmsetupwizardModelBindTest uses.
     */
    #[TestDox('createSampleContent() records its series and message in the import manifest')]
    public function testCreateSampleContentRecordsItsRowsInTheManifest(): void
    {
        $model = (new \ReflectionClass(CwmsetupwizardModel::class))->newInstanceWithoutConstructor();
        $ref   = new \ReflectionMethod(CwmsetupwizardModel::class, 'createSampleContent');

        // A teacher is required for the message; not exercising
        // createEssentialDefaults() here, so seed one directly.
        $teacher = (object) [
            'teachername' => 'cwm2175-' . bin2hex(random_bytes(4)),
            'alias'       => 'cwm2175-' . bin2hex(random_bytes(4)),
            'published'   => 1,
            'language'    => '*',
            'address'     => '',
        ];
        $this->db->insertObject('#__bsms_teachers', $teacher, 'id');

        $ids = $ref->invoke($model, [], ['teacher_id' => (int) $this->db->insertid()]);

        $this->assertContains(
            $ids['series_id'],
            Cwmimportmanifest::allRowIds('#__bsms_series'),
            'The sample series must be recorded so the checklist and #2174 removal can find it'
        );
        $this->assertContains(
            $ids['message_id'],
            Cwmimportmanifest::allRowIds('#__bsms_studies'),
            'The sample message must be recorded so the checklist and #2174 removal can find it'
        );

        // The reused/looked-up teacher is not created by this method and must
        // not be claimed as demo content — it could be a real, pre-existing one.
        $this->assertNotContains(
            $ids['teacher_id'],
            Cwmimportmanifest::allRowIds('#__bsms_teachers'),
            'createSampleContent() does not create the teacher, so it must not tag it'
        );
    }

    /**
     * A freshly created row must not already look "edited since import" —
     * otherwise #2174's removal would keep it forever. This mirrors the
     * insert-vs-update branch every entity's own prepareTable() uses.
     */
    #[TestDox('createSampleContent() does not set modified_by, so #2174 can still remove its rows')]
    public function testCreateSampleContentLeavesModifiedByUnset(): void
    {
        $model = (new \ReflectionClass(CwmsetupwizardModel::class))->newInstanceWithoutConstructor();
        $ref   = new \ReflectionMethod(CwmsetupwizardModel::class, 'createSampleContent');

        $teacher = (object) [
            'teachername' => 'cwm2175-' . bin2hex(random_bytes(4)),
            'alias'       => 'cwm2175-' . bin2hex(random_bytes(4)),
            'published'   => 1,
            'language'    => '*',
            'address'     => '',
        ];
        $this->db->insertObject('#__bsms_teachers', $teacher, 'id');

        $ids = $ref->invoke($model, [], ['teacher_id' => (int) $this->db->insertid()]);

        $seriesModifiedBy = $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('modified_by'))
                ->from($this->db->quoteName('#__bsms_series'))
                ->where($this->db->quoteName('id') . ' = ' . (int) $ids['series_id'])
        )->loadResult();

        $studyModifiedBy = $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('modified_by'))
                ->from($this->db->quoteName('#__bsms_studies'))
                ->where($this->db->quoteName('id') . ' = ' . (int) $ids['message_id'])
        )->loadResult();

        $this->assertSame(0, (int) $seriesModifiedBy);
        $this->assertSame(0, (int) $studyModifiedBy);
    }
}
