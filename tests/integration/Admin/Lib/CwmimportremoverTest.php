<?php

/**
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Lib;

use CWM\Component\Proclaim\Administrator\Lib\Cwmimportmanifest;
use CWM\Component\Proclaim\Administrator\Lib\Cwmimportremover;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Integration coverage for {@see Cwmimportremover::plan()} (#2145, #2174).
 *
 * `plan()` is read-only — no models, no plugins, no writes beyond the fixture
 * setup — so this suite runs green in the bare harness that #2173's model-save
 * tests cannot (see that test's skip reason). `execute()` needs the same full
 * model/plugin path #2173 needed, and is verified live instead; see the PR.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(Cwmimportremover::class)]
class CwmimportremoverTest extends IntegrationTestCase
{
    private ?DatabaseDriver $db = null;

    private int $seq = 0;

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
     * A unique-enough suffix per row this test seeds, so parallel runs (or a
     * failed prior rollback) never collide on a UNIQUE alias.
     *
     * @return  string
     */
    private function unique(): string
    {
        return 'cwm2174-' . bin2hex(random_bytes(4)) . '-' . $this->seq++;
    }

    private function seedTeacher(bool $modified = false): int
    {
        $alias = $this->unique();
        $row   = (object) [
            'teachername' => $alias,
            'alias'       => $alias,
            'language'    => '*',
            'address'     => '',
            'modified_by' => $modified ? 99 : 0,
        ];
        $this->db->insertObject('#__bsms_teachers', $row, 'id');

        return (int) $this->db->insertid();
    }

    private function seedSeries(int $teacherId, bool $modified = false): int
    {
        $alias = $this->unique();
        $row   = (object) [
            'series_text' => $alias,
            'alias'       => $alias,
            'teacher'     => $teacherId,
            'language'    => '*',
            'modified_by' => $modified ? 99 : 0,
        ];
        $this->db->insertObject('#__bsms_series', $row, 'id');

        return (int) $this->db->insertid();
    }

    private function seedStudy(int $seriesId, bool $modified = false): int
    {
        $alias = $this->unique();
        $row   = (object) [
            'studytitle'  => $alias,
            'alias'       => $alias,
            'series_id'   => $seriesId,
            'language'    => '*',
            'modified_by' => $modified ? 99 : 0,
        ];
        $this->db->insertObject('#__bsms_studies', $row, 'id');

        return (int) $this->db->insertid();
    }

    private function seedStudyTeacher(int $studyId, int $teacherId): void
    {
        $row = (object) ['study_id' => $studyId, 'teacher_id' => $teacherId, 'ordering' => 0];
        $this->db->insertObject('#__bsms_study_teachers', $row);
    }

    private function seedMediafile(int $studyId): void
    {
        $row = (object) ['study_id' => $studyId, 'metadata' => '{}', 'language' => '*'];
        $this->db->insertObject('#__bsms_mediafiles', $row, 'id');
    }

    /**
     * @return  string  A fresh, manifest-recorded tag for teacher/series/study
     *                   ids created together, with the study crediting the teacher.
     */
    private function seedCleanSet(int $teacherId, int $seriesId, int $studyId): string
    {
        $tag = 'cwm2174-tag-' . bin2hex(random_bytes(4));

        $this->seedStudyTeacher($studyId, $teacherId);

        Cwmimportmanifest::recordRow($tag, '#__bsms_teachers', $teacherId);
        Cwmimportmanifest::recordRow($tag, '#__bsms_series', $seriesId);
        Cwmimportmanifest::recordRow($tag, '#__bsms_studies', $studyId);

        return $tag;
    }

    /**
     * @param   array  $plan  As returned by {@see Cwmimportremover::plan()}.
     */
    private function actionFor(array $plan, string $table, int $id): ?string
    {
        foreach ($plan as $entry) {
            if ($entry['table'] === $table && $entry['id'] === $id) {
                return $entry['action'];
            }
        }

        return null;
    }

    public function testPlanRejectsAnUnknownTag(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/No import tagged/');

        (new Cwmimportremover())->plan('cwm2174-does-not-exist');
    }

    public function testPlanDeletesACleanSet(): void
    {
        $teacherId = $this->seedTeacher();
        $seriesId  = $this->seedSeries($teacherId);
        $studyId   = $this->seedStudy($seriesId);
        $tag       = $this->seedCleanSet($teacherId, $seriesId, $studyId);

        $plan = (new Cwmimportremover())->plan($tag);

        $this->assertSame('delete', $this->actionFor($plan, '#__bsms_teachers', $teacherId));
        $this->assertSame('delete', $this->actionFor($plan, '#__bsms_series', $seriesId));
        $this->assertSame('delete', $this->actionFor($plan, '#__bsms_studies', $studyId));
    }

    public function testPlanKeepsAnEditedSeriesButDeletesItsUntouchedMessage(): void
    {
        $teacherId = $this->seedTeacher();
        $seriesId  = $this->seedSeries($teacherId, modified: true);
        $studyId   = $this->seedStudy($seriesId);
        $tag       = $this->seedCleanSet($teacherId, $seriesId, $studyId);

        $plan = (new Cwmimportremover())->plan($tag);

        $seriesEntry = null;

        foreach ($plan as $entry) {
            if ($entry['table'] === '#__bsms_series' && $entry['id'] === $seriesId) {
                $seriesEntry = $entry;
            }
        }

        $this->assertNotNull($seriesEntry);
        $this->assertSame('keep', $seriesEntry['action']);
        $this->assertSame('edited since import', $seriesEntry['reason']);

        // The message under the kept series was itself never touched.
        $this->assertSame('delete', $this->actionFor($plan, '#__bsms_studies', $studyId));
    }

    public function testPlanKeepsATeacherPinnedByAKeptSeries(): void
    {
        $teacherId = $this->seedTeacher();
        $seriesId  = $this->seedSeries($teacherId, modified: true);
        $studyId   = $this->seedStudy($seriesId);
        $tag       = $this->seedCleanSet($teacherId, $seriesId, $studyId);

        $plan = (new Cwmimportremover())->plan($tag);

        $teacherEntry = null;

        foreach ($plan as $entry) {
            if ($entry['table'] === '#__bsms_teachers' && $entry['id'] === $teacherId) {
                $teacherEntry = $entry;
            }
        }

        $this->assertNotNull($teacherEntry);
        $this->assertSame('keep', $teacherEntry['action']);
        $this->assertSame('used by a series outside this import', $teacherEntry['reason']);
    }

    public function testPlanKeepsAStudyAndItsAncestorsWhenAMediaFileIsAttached(): void
    {
        $teacherId = $this->seedTeacher();
        $seriesId  = $this->seedSeries($teacherId);
        $studyId   = $this->seedStudy($seriesId);
        $tag       = $this->seedCleanSet($teacherId, $seriesId, $studyId);

        $this->seedMediafile($studyId);

        $plan = (new Cwmimportremover())->plan($tag);

        $studyEntry = null;

        foreach ($plan as $entry) {
            if ($entry['table'] === '#__bsms_studies' && $entry['id'] === $studyId) {
                $studyEntry = $entry;
            }
        }

        $this->assertNotNull($studyEntry);
        $this->assertSame('keep', $studyEntry['action']);
        $this->assertSame('has media files', $studyEntry['reason']);

        // A study kept for its own reason still pins the series and teacher
        // it credits — deleting either out from under it would strand it.
        $this->assertSame('keep', $this->actionFor($plan, '#__bsms_series', $seriesId));
        $this->assertSame('keep', $this->actionFor($plan, '#__bsms_teachers', $teacherId));
    }

    public function testPlanKeepsAStudyWithAComment(): void
    {
        $teacherId = $this->seedTeacher();
        $seriesId  = $this->seedSeries($teacherId);
        $studyId   = $this->seedStudy($seriesId);
        $tag       = $this->seedCleanSet($teacherId, $seriesId, $studyId);

        $comment = (object) [
            'study_id'     => $studyId,
            'user_id'      => 0,
            'full_name'    => 'A Real Visitor',
            'user_email'   => 'visitor@example.com',
            'comment_date' => '2026-01-01 00:00:00',
            'comment_text' => 'A real comment from a real visitor.',
            'language'     => '*',
        ];
        $this->db->insertObject('#__bsms_comments', $comment, 'id');

        $plan = (new Cwmimportremover())->plan($tag);

        $studyEntry = null;

        foreach ($plan as $entry) {
            if ($entry['table'] === '#__bsms_studies' && $entry['id'] === $studyId) {
                $studyEntry = $entry;
            }
        }

        $this->assertNotNull($studyEntry);
        $this->assertSame('keep', $studyEntry['action']);
        $this->assertSame('has comments', $studyEntry['reason']);
    }

    public function testPlanIncludesManifestFilesAsDeletable(): void
    {
        $teacherId = $this->seedTeacher();
        $seriesId  = $this->seedSeries($teacherId);
        $studyId   = $this->seedStudy($seriesId);
        $tag       = $this->seedCleanSet($teacherId, $seriesId, $studyId);

        Cwmimportmanifest::recordFile($tag, 'images/biblestudy/teachers/cwm2174-test.png');

        $plan = (new Cwmimportremover())->plan($tag);

        $fileEntry = null;

        foreach ($plan as $entry) {
            if ($entry['file'] === 'images/biblestudy/teachers/cwm2174-test.png') {
                $fileEntry = $entry;
            }
        }

        $this->assertNotNull($fileEntry);
        $this->assertSame('delete', $fileEntry['action']);
        $this->assertNull($fileEntry['table']);
    }
}
