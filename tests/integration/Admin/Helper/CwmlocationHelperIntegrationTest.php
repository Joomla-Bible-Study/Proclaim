<?php

/**
 * Integration tests for CwmlocationHelper's DB-touching methods.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmlocationHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1561: getTeacherLocations() built its UNION query by
 * string-concatenating two bound Query objects, which silently discards both
 * bound parameters -- fatal on real MySQL ("No data supplied for parameters").
 * None of the prior unit tests touched a real database, so this bug shipped
 * undetected. Each test runs inside a transaction that is rolled back.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmlocationHelper::class)]
class CwmlocationHelperIntegrationTest extends IntegrationTestCase
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

        CwmlocationHelper::resetCache();
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

        CwmlocationHelper::resetCache();

        parent::tearDown();
    }

    #[TestDox('getTeacherLocations() does not throw and finds a location via the many-to-many study_teachers path')]
    public function testGetTeacherLocationsManyToManyPath(): void
    {
        $userId     = 90001;
        $locationId = $this->insertLocation('Campus A');
        $teacherId  = $this->insertTeacher($userId);
        $studyId    = $this->insertStudy($locationId);

        $this->insertStudyTeacher($studyId, $teacherId);

        $result = CwmlocationHelper::getTeacherLocations($userId);

        $this->assertSame([$locationId], $result);
    }

    #[TestDox('a study linked only by the retired column is not found')]
    public function testAStudyWithNoJunctionRowIsNotFound(): void
    {
        // ⚠️ This used to pass through a second UNION branch reading
        // studies.teacher_id. #1731 retired that column, so a study with no
        // junction row is simply not this teacher's -- there is nothing left to
        // read it from.
        $userId     = 90002;
        $locationId = $this->insertLocation('Campus B');
        $teacherId  = $this->insertTeacher($userId);
        $this->insertStudy($locationId, $teacherId);

        $this->assertSame([], CwmlocationHelper::getTeacherLocations($userId));
    }

    #[TestDox('two studies at the same location yield that location once')]
    public function testDuplicateLocationsAreCollapsed(): void
    {
        $userId    = 90003;
        $sharedLoc = $this->insertLocation('Campus C');
        $otherLoc  = $this->insertLocation('Campus D');
        $teacherId = $this->insertTeacher($userId);

        foreach ([$sharedLoc, $sharedLoc, $otherLoc] as $location) {
            $this->insertStudyTeacher($this->insertStudy($location), $teacherId);
        }

        $result = CwmlocationHelper::getTeacherLocations($userId);
        sort($result);

        $this->assertCount(2, $result, 'The repeated location must be collapsed by DISTINCT.');
        $this->assertContains($sharedLoc, $result);
        $this->assertContains($otherLoc, $result);
    }

    #[TestDox('getTeacherLocations() returns an empty array for a user with no teacher record')]
    public function testGetTeacherLocationsReturnsEmptyForUnknownUser(): void
    {
        $result = CwmlocationHelper::getTeacherLocations(90099);

        $this->assertSame([], $result);
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
     * @param   int  $userId  Joomla user ID to link.
     *
     * @return  int  Teacher ID.
     */
    private function insertTeacher(int $userId): int
    {
        $row = (object) [
            'teachername' => 'Test Teacher ' . $userId,
            'alias'       => 'test-teacher-' . $userId,
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'user_id'     => $userId,
            'address'     => '',
        ];

        $this->db->insertObject('#__bsms_teachers', $row);

        return (int) $this->db->insertid();
    }

    /**
     * @param   int       $locationId  Location ID to assign.
     * @param   int|null  $teacherId   Legacy teacher_id column (null = unset).
     *
     * @return  int  Study ID.
     */
    private function insertStudy(int $locationId, ?int $teacherId = null): int
    {
        $row = (object) [
            'studytitle'  => 'Test Study ' . uniqid('', true),
            'alias'       => 'test-study-' . uniqid('', true),
            'studydate'   => '2026-01-15 10:00:00',
            'messagetype' => 1,
            'booknumber'  => 101,
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 0,
            'location_id' => $locationId,
            'teacher_id'  => $teacherId,
            'params'      => '{}',
        ];

        $this->db->insertObject('#__bsms_studies', $row);

        return (int) $this->db->insertid();
    }

    /**
     * @param   int  $studyId    Study ID.
     * @param   int  $teacherId  Teacher ID.
     *
     * @return  void
     */
    private function insertStudyTeacher(int $studyId, int $teacherId): void
    {
        $row = (object) [
            'study_id'   => $studyId,
            'teacher_id' => $teacherId,
            'ordering'   => 0,
        ];

        $this->db->insertObject('#__bsms_study_teachers', $row);
    }
}
