<?php

/**
 * Integration tests for CwmschemaorgHelper's DB-touching methods.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmschemaorgHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1562:
 *
 * - isManuallyEdited() only stripped `_autoHash` before re-hashing, while
 *   computeAutoHash() (used to stamp the hash in the first place) strips
 *   `_autoHash`, `_customFields`, `_fieldHashes`, and `_editedFields`. Any
 *   row carrying a non-empty `_customFields`/`_fieldHashes` -- which the
 *   schemaorg plugin's per-field Smart Sync writes on every admin save --
 *   permanently mismatched and could never be Smart Synced again.
 * - syncOne()'s sermon/teacher builders diverged from the bulk syncMessages()/
 *   syncTeachers() field sets (missing genericField / worksFor respectively),
 *   so alternating "Reset Schema" and "Sync All" silently dropped fields.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmschemaorgHelper::class)]
class CwmschemaorgHelperIntegrationTest extends IntegrationTestCase
{
    private ?DatabaseDriver $db = null;

    /**
     * @return  void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart();
    }

    /**
     * @return  void
     */
    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback();
            } catch (\Throwable) {
                // Connection may have been lost -- nothing to roll back.
            }
        }

        parent::tearDown();
    }

    #[TestDox('isManuallyEdited() does not misclassify a row hash-stamped with _customFields/_fieldHashes present')]
    public function testIsManuallyEditedFalseWhenStoredDataCarriesTrackingKeys(): void
    {
        // Mirrors what the schemaorg plugin's onSchemaPrepareSave() persists:
        // fresh auto-generated fields plus non-null _customFields/_fieldHashes,
        // hashed via the same 4-key strip computeAutoHash() uses.
        $schema = [
            'headline'      => 'Test Sermon',
            '_customFields' => ['headline'],
            '_fieldHashes'  => ['headline' => 'abc12345'],
        ];
        $schema['_autoHash'] = CwmschemaorgHelper::computeAutoHash($schema);

        $itemId = $this->insertSchemaRow('com_proclaim.cwmmessage', 'Sermon', $schema);

        $this->assertFalse(
            $this->invokeIsManuallyEdited($itemId, 'com_proclaim.cwmmessage'),
            'A row hash-stamped with _customFields/_fieldHashes present must not be treated as manually edited'
        );
    }

    #[TestDox('isManuallyEdited() still returns true when a stored field genuinely diverges from its auto-hash')]
    public function testIsManuallyEditedTrueWhenStoredDataDiverged(): void
    {
        $schema               = ['headline' => 'Original'];
        $schema['_autoHash']  = CwmschemaorgHelper::computeAutoHash($schema);
        // Simulate a hand-edit after the hash was stamped (e.g. a direct DB
        // edit, or a row from before Smart Sync existed).
        $schema['headline'] = 'Admin-customized headline';

        $itemId = $this->insertSchemaRow('com_proclaim.cwmmessage', 'Sermon', $schema);

        $this->assertTrue(
            $this->invokeIsManuallyEdited($itemId, 'com_proclaim.cwmmessage'),
            'A row whose stored fields no longer hash to _autoHash must still be flagged as manually edited'
        );
    }

    #[TestDox('syncOne() for a sermon builds the same genericField set as the bulk sync path (series/genre/location/topics)')]
    public function testSyncOneSermonIncludesGenericFieldFromSeriesGenreLocationTopics(): void
    {
        $locationId = $this->insertLocation('Main Campus');
        $seriesId   = $this->insertSeries('Parables of Jesus');
        $typeId     = $this->insertMessageType('Sermon');
        $topicId    = $this->insertTopic('Compassion');
        $studyId    = $this->insertStudy($locationId, $seriesId, $typeId);

        $this->insertStudyTopic($studyId, $topicId);

        $synced = CwmschemaorgHelper::syncOne($studyId, 'com_proclaim.cwmmessage');
        $this->assertTrue($synced);

        $schema = $this->loadStoredSchema($studyId, 'com_proclaim.cwmmessage');

        $this->assertArrayHasKey('genericField', $schema);

        $titles = array_column($schema['genericField'], 'genericTitle');

        $this->assertContains('isPartOf', $titles);
        $this->assertContains('genre', $titles);
        $this->assertContains('locationCreated', $titles);
        $this->assertContains('about', $titles);
    }

    #[TestDox('Bulk sync (syncTeachers) includes worksFor, matching the syncOne()/buildTeacherSchemaFromRow() path')]
    public function testBulkSyncTeacherIncludesWorksFor(): void
    {
        // syncOne() already routed through buildTeacherSchemaFromRow() (which
        // has always included worksFor) even before this fix -- the actual
        // divergence was in syncTeachers()'s separate inline builder used by
        // the "Sync All" bulk path, so this must exercise syncTeachers()
        // directly (private, reached via reflection) rather than syncOne().
        $teacherId = $this->insertTeacher('Pastor Jane');

        $method = new \ReflectionMethod(CwmschemaorgHelper::class, 'syncTeachers');
        $result = $method->invoke(null, $this->db, CwmschemaorgHelper::SYNC_SMART);

        $this->assertGreaterThan(0, $result['synced']);

        $schema = $this->loadStoredSchema($teacherId, 'com_proclaim.teacher');

        $this->assertArrayHasKey('worksFor', $schema);
        $this->assertEquals('Organization', $schema['worksFor']['@type']);
    }

    /**
     * @param   int     $itemId   Item ID.
     * @param   string  $context  Context string.
     *
     * @return  bool|null
     */
    private function invokeIsManuallyEdited(int $itemId, string $context): ?bool
    {
        $method = new \ReflectionMethod(CwmschemaorgHelper::class, 'isManuallyEdited');

        return $method->invoke(null, $this->db, $itemId, $context);
    }

    /**
     * @param   string  $context     Context string.
     * @param   string  $schemaType  Schema type name.
     * @param   array   $schema      Schema data (already hash-stamped).
     *
     * @return  int  itemId used for the inserted row.
     */
    private function insertSchemaRow(string $context, string $schemaType, array $schema): int
    {
        static $nextItemId = 91000;

        $itemId = $nextItemId++;

        $row             = new \stdClass();
        $row->itemId     = $itemId;
        $row->context    = $context;
        $row->schemaType = $schemaType;
        $row->schema     = json_encode($schema, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        $this->db->insertObject('#__schemaorg', $row);

        return $itemId;
    }

    /**
     * @param   int     $itemId   Item ID.
     * @param   string  $context  Context string.
     *
     * @return  array
     */
    private function loadStoredSchema(int $itemId, string $context): array
    {
        $query = $this->db->createQuery()
            ->select($this->db->quoteName('schema'))
            ->from($this->db->quoteName('#__schemaorg'))
            ->where($this->db->quoteName('itemId') . ' = ' . $itemId)
            ->where($this->db->quoteName('context') . ' = ' . $this->db->quote($context));
        $stored = $this->db->setQuery($query)->loadResult();

        $this->assertNotNull($stored, 'Expected a stored schema row after syncOne()');

        return json_decode($stored, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param   string  $name  Location display name.
     *
     * @return  int  Location ID.
     */
    private function insertLocation(string $name): int
    {
        $row = (object) [
            'location_text' => $name,
            'published'     => 1,
            'params'        => '',
            'sortname1'     => '',
            'sortname2'     => '',
            'sortname3'     => '',
            'language'      => '*',
            'metakey'       => '',
            'metadesc'      => '',
            'metadata'      => '',
            'xreference'    => '',
        ];

        $this->db->insertObject('#__bsms_locations', $row);

        return (int) $this->db->insertid();
    }

    /**
     * @param   string  $name  Series name.
     *
     * @return  int  Series ID.
     */
    private function insertSeries(string $name): int
    {
        $row = (object) [
            'series_text' => $name,
            'alias'       => 'test-series-' . uniqid('', true),
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
        ];

        $this->db->insertObject('#__bsms_series', $row);

        return (int) $this->db->insertid();
    }

    /**
     * @param   string  $name  Message type name.
     *
     * @return  int  Message type ID.
     */
    private function insertMessageType(string $name): int
    {
        $row = (object) [
            'message_type' => $name,
            'alias'        => 'test-type-' . uniqid('', true),
            'published'    => 1,
            'access'       => 1,
        ];

        $this->db->insertObject('#__bsms_message_type', $row);

        return (int) $this->db->insertid();
    }

    /**
     * @param   string  $name  Topic text.
     *
     * @return  int  Topic ID.
     */
    private function insertTopic(string $name): int
    {
        $row = (object) [
            'topic_text' => $name,
            'published'  => 1,
            'access'     => 1,
            'language'   => '*',
        ];

        $this->db->insertObject('#__bsms_topics', $row);

        return (int) $this->db->insertid();
    }

    /**
     * @param   int  $studyId  Study ID.
     * @param   int  $topicId  Topic ID.
     *
     * @return  void
     */
    private function insertStudyTopic(int $studyId, int $topicId): void
    {
        $row = (object) [
            'study_id' => $studyId,
            'topic_id' => $topicId,
            'access'   => 1,
        ];

        $this->db->insertObject('#__bsms_studytopics', $row);
    }

    /**
     * @param   int  $locationId  Location ID.
     * @param   int  $seriesId    Series ID.
     * @param   int  $typeId      Message type ID.
     *
     * @return  int  Study ID.
     */
    private function insertStudy(int $locationId, int $seriesId, int $typeId): int
    {
        $row = (object) [
            'studytitle'  => 'Test Study ' . uniqid('', true),
            'alias'       => 'test-study-' . uniqid('', true),
            'studydate'   => '2026-01-15 10:00:00',
            'messagetype' => (string) $typeId,
            'booknumber'  => 101,
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 0,
            'location_id' => $locationId,
            'series_id'   => $seriesId,
            'params'      => '{}',
        ];

        $this->db->insertObject('#__bsms_studies', $row);

        return (int) $this->db->insertid();
    }

    /**
     * @param   string  $name  Teacher display name.
     *
     * @return  int  Teacher ID.
     */
    private function insertTeacher(string $name): int
    {
        $row = (object) [
            'teachername' => $name,
            'alias'       => 'test-teacher-' . uniqid('', true),
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'address'     => '',
        ];

        $this->db->insertObject('#__bsms_teachers', $row);

        return (int) $this->db->insertid();
    }
}
