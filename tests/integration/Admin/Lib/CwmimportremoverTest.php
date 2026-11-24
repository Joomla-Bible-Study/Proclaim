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
    /**
     * The database, inside a transaction each test rolls back.
     *
     * @var    DatabaseDriver|null
     *
     * @since  __DEPLOY_VERSION__
     */
    private ?DatabaseDriver $db = null;

    /**
     * A running count that unique() appends to each suffix it makes.
     *
     * @var    int
     *
     * @since  __DEPLOY_VERSION__
     */
    private int $seq = 0;

    /**
     * Skip without a test database. Otherwise open a transaction for the test's rows.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart(true);
    }

    /**
     * Roll back the test's rows.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
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
     * @return  string  A fixed prefix, eight random hex digits and a running count.
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
    private function unique(): string
    {
        return 'cwm2174-' . bin2hex(random_bytes(4)) . '-' . $this->seq++;
    }

    /**
     * Insert a teacher with a unique name and alias.
     *
     * @param   bool  $modified  Whether to mark the row edited since import (a nonzero modified_by).
     *
     * @return  int  The new teacher's id.
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
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

    /**
     * Insert a series with a unique title and alias, whose teacher is the one given.
     *
     * @param   int   $teacherId  The series' teacher.
     * @param   bool  $modified   Whether to mark the row edited since import (a nonzero modified_by).
     *
     * @return  int  The new series' id.
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
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

    /**
     * Insert a study with a unique title and alias, in the given series.
     *
     * @param   int   $seriesId  The series the study belongs to.
     * @param   bool  $modified  Whether to mark the row edited since import (a nonzero modified_by).
     *
     * @return  int  The new study's id.
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
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

    /**
     * Credit a teacher on a study, through the study-teachers junction table.
     *
     * @param   int  $studyId    The study's id.
     * @param   int  $teacherId  The teacher's id.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function seedStudyTeacher(int $studyId, int $teacherId): void
    {
        $row = (object) ['study_id' => $studyId, 'teacher_id' => $teacherId, 'ordering' => 0];
        $this->db->insertObject('#__bsms_study_teachers', $row);
    }

    /**
     * Insert a media file on the given study.
     *
     * @param   int   $studyId   The study the media file belongs to.
     * @param   bool  $modified  Whether to mark the row edited since import (a nonzero modified_by).
     *
     * @return  int  The new media file's id.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function seedMediafile(int $studyId, bool $modified = false): int
    {
        $row = (object) [
            'study_id'    => $studyId,
            'metadata'    => '{}',
            'language'    => '*',
            'modified_by' => $modified ? 99 : 0,
        ];
        $this->db->insertObject('#__bsms_mediafiles', $row, 'id');

        return (int) $this->db->insertid();
    }

    /**
     * Insert a location with a unique name.
     *
     * @param   bool  $modified  Whether to mark the row edited since import (a nonzero modified_by).
     *
     * @return  int  The new location's id.
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
    private function seedLocation(bool $modified = false): int
    {
        $text = $this->unique();
        $row  = (object) [
            'location_text' => $text,
            'language'      => '*',
            'sortname1'     => '',
            'sortname2'     => '',
            'sortname3'     => '',
            'params'        => '',
            'metakey'       => '',
            'metadesc'      => '',
            'metadata'      => '',
            'xreference'    => '',
            'modified_by'   => $modified ? 99 : 0,
        ];
        $this->db->insertObject('#__bsms_locations', $row, 'id');

        return (int) $this->db->insertid();
    }

    /**
     * A minimal server row, only for exercising the "location used by a
     * server" external-reference check — not a real, playable server.
     *
     * @param   int  $locationId  The location the server row points at.
     *
     * @return  int  The new server's id.
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
    private function seedServerAtLocation(int $locationId): int
    {
        $name = $this->unique();
        $row  = (object) [
            'server_name' => $name,
            'type'        => 'legacy',
            'location_id' => $locationId,
            'params'      => '{}',
            'media'       => '{}',
        ];
        $this->db->insertObject('#__bsms_servers', $row, 'id');

        return (int) $this->db->insertid();
    }

    /**
     * Record a teacher, a series and a study under one fresh manifest tag, with
     * the study crediting the teacher.
     *
     * @param   int  $teacherId  The teacher's id.
     * @param   int  $seriesId   The series' id.
     * @param   int  $studyId    The study's id.
     *
     * @return  string  The manifest tag the three rows were recorded under.
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
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
     * The action a removal plan takes for one row.
     *
     * @param   array   $plan   A plan, as Cwmimportremover::plan() returns it.
     * @param   string  $table  The row's table.
     * @param   int     $id     The row's id.
     *
     * @return  ?string  The planned action, or null when the plan doesn't include the row.
     *
     * @since   __DEPLOY_VERSION__
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

    /**
     * A removal plan's entry for one row.
     *
     * @param   array   $plan   A plan, as Cwmimportremover::plan() returns it.
     * @param   string  $table  The row's table.
     * @param   int     $id     The row's id.
     *
     * @return  ?array  The entry, or null when the plan doesn't include the row.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function entryFor(array $plan, string $table, int $id): ?array
    {
        foreach ($plan as $entry) {
            if ($entry['table'] === $table && $entry['id'] === $id) {
                return $entry;
            }
        }

        return null;
    }

    /**
     * Planning a tag that no import has used throws a RuntimeException saying
     * no import is tagged with it.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testPlanRejectsAnUnknownTag(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/No import tagged/');

        (new Cwmimportremover())->plan('cwm2174-does-not-exist');
    }

    /**
     * An untouched teacher, series and study, recorded under one tag with the
     * study crediting the teacher, are all planned for deletion.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
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

    /**
     * A series edited since import is kept, with the reason "edited since
     * import", while its untouched study is still planned for deletion.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
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

    /**
     * When an edited series is kept, its teacher is kept too, with the reason
     * "used by a series outside this import".
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
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

    /**
     * A study with a media file the import didn't record is kept, with the
     * reason "has media files", and so are its series and teacher.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
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

    /**
     * The regression case #2187 introduced: before Cwmimportremover
     * could tell its own imported media files apart from ones outside the
     * import, ANY media file on a study — including one this same import
     * created — permanently pinned that study. This is the "clean set"
     * case: the media file is manifest-recorded and untouched, so both it
     * and its study must be deletable.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
    public function testPlanDeletesAMediaFileAndItsStudyWhenBothWereImportedTogether(): void
    {
        $teacherId = $this->seedTeacher();
        $seriesId  = $this->seedSeries($teacherId);
        $studyId   = $this->seedStudy($seriesId);
        $tag       = $this->seedCleanSet($teacherId, $seriesId, $studyId);

        $mediaId = $this->seedMediafile($studyId);
        Cwmimportmanifest::recordRow($tag, '#__bsms_mediafiles', $mediaId);

        $plan = (new Cwmimportremover())->plan($tag);

        $this->assertSame('delete', $this->actionFor($plan, '#__bsms_mediafiles', $mediaId));
        $this->assertSame('delete', $this->actionFor($plan, '#__bsms_studies', $studyId));
    }

    /**
     * Mirrors testPlanKeepsATeacherPinnedByAKeptSeries() one level
     * down the hierarchy: a media file edited since import is real content
     * now, so the study it references must not be deleted out from under it
     * — even though both were created by the same import.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
    public function testPlanKeepsAStudyPinnedByAModifiedMediaFileOfItsOwn(): void
    {
        $teacherId = $this->seedTeacher();
        $seriesId  = $this->seedSeries($teacherId);
        $studyId   = $this->seedStudy($seriesId);
        $tag       = $this->seedCleanSet($teacherId, $seriesId, $studyId);

        $mediaId = $this->seedMediafile($studyId, modified: true);
        Cwmimportmanifest::recordRow($tag, '#__bsms_mediafiles', $mediaId);

        $plan = (new Cwmimportremover())->plan($tag);

        $mediaEntry = null;

        foreach ($plan as $entry) {
            if ($entry['table'] === '#__bsms_mediafiles' && $entry['id'] === $mediaId) {
                $mediaEntry = $entry;
            }
        }

        $this->assertNotNull($mediaEntry);
        $this->assertSame('keep', $mediaEntry['action']);
        $this->assertSame('edited since import', $mediaEntry['reason']);

        $this->assertSame('keep', $this->actionFor($plan, '#__bsms_studies', $studyId));
    }

    /**
     * A study with a comment is kept, with the reason "has comments".
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
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

    /**
     * A file recorded under the tag is in the plan as a delete, with no table.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
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

    /**
     * A location used only by a study recorded under the same tag is planned
     * for deletion, and so is that study.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
    public function testPlanDeletesALocationAndItsStudyWhenBothWereImportedTogether(): void
    {
        $teacherId  = $this->seedTeacher();
        $seriesId   = $this->seedSeries($teacherId);
        $locationId = $this->seedLocation();
        $studyId    = $this->seedStudy($seriesId);
        $this->db->setQuery(
            $this->db->createQuery()
                ->update($this->db->quoteName('#__bsms_studies'))
                ->set($this->db->quoteName('location_id') . ' = ' . $locationId)
                ->where($this->db->quoteName('id') . ' = ' . $studyId)
        )->execute();

        $tag = $this->seedCleanSet($teacherId, $seriesId, $studyId);
        Cwmimportmanifest::recordRow($tag, '#__bsms_locations', $locationId);

        $plan = (new Cwmimportremover())->plan($tag);

        $this->assertSame('delete', $this->actionFor($plan, '#__bsms_locations', $locationId));
        $this->assertSame('delete', $this->actionFor($plan, '#__bsms_studies', $studyId));
    }

    /**
     * A location the import recorded is kept, with the reason "used by a
     * server", when a server outside the import points at it. The import's
     * teacher, series and study still delete.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
    public function testPlanKeepsALocationUsedByAServerOutsideTheImport(): void
    {
        $teacherId  = $this->seedTeacher();
        $seriesId   = $this->seedSeries($teacherId);
        $locationId = $this->seedLocation();
        $studyId    = $this->seedStudy($seriesId);
        $tag        = $this->seedCleanSet($teacherId, $seriesId, $studyId);
        Cwmimportmanifest::recordRow($tag, '#__bsms_locations', $locationId);

        $this->seedServerAtLocation($locationId);

        $plan = (new Cwmimportremover())->plan($tag);

        $locationEntry = $this->entryFor($plan, '#__bsms_locations', $locationId);

        $this->assertNotNull($locationEntry);
        $this->assertSame('keep', $locationEntry['action']);
        $this->assertSame('used by a server', $locationEntry['reason']);

        // A location kept for its own reason must not stop the rest of the
        // clean set — teacher, series and study — from deleting normally.
        $this->assertSame('delete', $this->actionFor($plan, '#__bsms_studies', $studyId));
        $this->assertSame('delete', $this->actionFor($plan, '#__bsms_series', $seriesId));
        $this->assertSame('delete', $this->actionFor($plan, '#__bsms_teachers', $teacherId));
    }

    /**
     * A location whose id is a key in the component's location group mapping is
     * kept, with the reason "referenced in the location group mapping". The test
     * writes the mapping to com_proclaim's #__extensions row, and inserts that
     * row when the harness has none.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
    public function testPlanKeepsALocationInTheGroupMapping(): void
    {
        $locationId = $this->seedLocation();
        $tag        = 'cwm2196-tag-' . bin2hex(random_bytes(4));
        Cwmimportmanifest::recordRow($tag, '#__bsms_locations', $locationId);

        $exists = (int) $this->db->setQuery(
            $this->db->createQuery()
                ->select('COUNT(*)')
                ->from($this->db->quoteName('#__extensions'))
                ->where($this->db->quoteName('element') . ' = ' . $this->db->quote('com_proclaim'))
                ->where($this->db->quoteName('type') . ' = ' . $this->db->quote('component'))
        )->loadResult() > 0;

        $encodedParams = json_encode(['location_group_mapping' => json_encode([(string) $locationId => [2]])]);

        if ($exists) {
            $this->db->setQuery(
                $this->db->createQuery()
                    ->update($this->db->quoteName('#__extensions'))
                    ->set($this->db->quoteName('params') . ' = :params')
                    ->where($this->db->quoteName('element') . ' = ' . $this->db->quote('com_proclaim'))
                    ->where($this->db->quoteName('type') . ' = ' . $this->db->quote('component'))
                    ->bind(':params', $encodedParams, \Joomla\Database\ParameterType::STRING)
            )->execute();
        } else {
            // A bare-SQL harness (this project's own CI DB build among them)
            // loads Proclaim's install.mysql.utf8.sql directly, never through
            // Joomla's real installer — so no #__extensions row for the
            // component exists at all. isInGroupMapping() reads via this same
            // table, so the test needs one to update against either way.
            $row = (object) [
                'name'           => 'com_proclaim',
                'type'           => 'component',
                'element'        => 'com_proclaim',
                'folder'         => '',
                'client_id'      => 1,
                'enabled'        => 1,
                'manifest_cache' => '',
                'params'         => $encodedParams,
                'custom_data'    => '',
            ];
            $this->db->insertObject('#__extensions', $row, 'extension_id');
        }

        $plan = (new Cwmimportremover())->plan($tag);

        $locationEntry = $this->entryFor($plan, '#__bsms_locations', $locationId);

        $this->assertNotNull($locationEntry);
        $this->assertSame('keep', $locationEntry['action']);
        $this->assertSame('referenced in the location group mapping', $locationEntry['reason']);
    }
}
