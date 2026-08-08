<?php

/**
 * Integration tests for YouTube video-to-message matching.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmyoutubeHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1591.
 *
 * matchVideoToMessage()'s strategies 1 and 2 each look a title up against a
 * *particular* teacher. Neither checked that the name half actually resolved,
 * so on a miss the search ran with a null teacher -- which is the same call
 * strategy 3 makes, findMessageByTitle()'s second argument defaulting to null.
 * That moved strategy 3 ahead of the reversed reading meant to be tried first.
 *
 * The issue filed this as dormant. It is not: mod_proclaim_youtube's dispatcher
 * calls it through YoutubeHelper::findMatchingMessage() whenever the module's
 * auto_link_message param is on, and that defaults to 1.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmyoutubeHelper::class)]
class CwmyoutubeMatchTest extends IntegrationTestCase
{
    /**
     * @var  DatabaseDriver|null
     */
    private ?DatabaseDriver $db = null;

    /**
     * @var  array<string, int[]>
     */
    private array $created = [];

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

            try {
                $this->purge();
            } catch (\Throwable) {
                // Best effort; the assertions have already run.
            }
        }

        parent::tearDown();
    }

    #[TestDox('A title whose leading half names a teacher is matched by teacher, not by a same-named message')]
    public function testReversedOrderIsNotPreemptedByAnUnfilteredSearch(): void
    {
        // The fixture has to make part1 ambiguous: both a teacher's name and
        // some other message's title. Without that collision the two readings
        // return the same row and the test proves nothing.
        $teacherId = $this->insertTeacher('Faith');
        $wanted    = $this->insertStudy('The Cost of Discipleship');
        $decoy     = $this->insertStudy('Faith');

        $this->linkTeacher($wanted, $teacherId);

        // "Faith - The Cost of Discipleship" reads as teacher - title, so
        // strategy 2 should win. Before the fix, strategy 1 looked up a teacher
        // named "The Cost of Discipleship", got nothing, then searched titles
        // with no teacher filter and returned the decoy.
        $match = CwmyoutubeHelper::matchVideoToMessage('Faith - The Cost of Discipleship');

        $this->assertNotNull($match, 'The video should match a message');
        $this->assertSame(
            $wanted,
            (int) $match->id,
            'Matched the message merely titled like the teacher instead of the teacher\'s own message'
        );
        $this->assertNotSame($decoy, (int) $match->id);
    }

    #[TestDox('A title in message - teacher order still matches by teacher')]
    public function testForwardOrderStillMatches(): void
    {
        $teacherId = $this->insertTeacher('Hope Bearer');
        $studyId   = $this->insertStudy('Walking In Light');
        $this->linkTeacher($studyId, $teacherId);

        $match = CwmyoutubeHelper::matchVideoToMessage('Walking In Light - Hope Bearer');

        $this->assertNotNull($match);
        $this->assertSame($studyId, (int) $match->id);
    }

    #[TestDox('A title naming no known teacher still falls through to an unfiltered title search')]
    public function testUnknownTeacherStillFallsThroughToTitleSearch(): void
    {
        $studyId = $this->insertStudy('Streams In The Desert');

        // Neither half names a teacher, so strategies 1 and 2 are both skipped
        // and strategy 3's unfiltered search is what has to find it.
        $match = CwmyoutubeHelper::matchVideoToMessage('Streams In The Desert - Nobody At All');

        $this->assertNotNull($match, 'Skipping the teacher strategies must not lose the plain title match');
        $this->assertSame($studyId, (int) $match->id);
    }

    #[TestDox('A title matching nothing returns null')]
    public function testNoMatchReturnsNull(): void
    {
        $this->assertNull(
            CwmyoutubeHelper::matchVideoToMessage('Nothing Here Matches ' . uniqid('', true))
        );
    }

    /**
     * @return  void
     */
    private function purge(): void
    {
        foreach ($this->created['#__bsms_studies'] ?? [] as $id) {
            $this->db->setQuery(
                'DELETE FROM ' . $this->db->quoteName('#__bsms_study_teachers')
                . ' WHERE ' . $this->db->quoteName('study_id') . ' = ' . $id
            )->execute();
            $this->db->setQuery(
                'DELETE FROM ' . $this->db->quoteName('#__assets')
                . ' WHERE ' . $this->db->quoteName('name') . ' = '
                . $this->db->quote('com_proclaim.message.' . $id)
            )->execute();
        }

        foreach ($this->created['#__bsms_teachers'] ?? [] as $id) {
            $this->db->setQuery(
                'DELETE FROM ' . $this->db->quoteName('#__assets')
                . ' WHERE ' . $this->db->quoteName('name') . ' = '
                . $this->db->quote('com_proclaim.teacher.' . $id)
            )->execute();
        }

        foreach ($this->created as $table => $ids) {
            if ($ids === []) {
                continue;
            }

            $this->db->setQuery(
                'DELETE FROM ' . $this->db->quoteName($table)
                . ' WHERE ' . $this->db->quoteName('id')
                . ' IN (' . implode(',', array_map('intval', $ids)) . ')'
            )->execute();
        }

        $this->created = [];
    }

    /**
     * @param   string  $name  Teacher name.
     *
     * @return  int  Teacher ID.
     */
    private function insertTeacher(string $name): int
    {
        $row = (object) [
            'teachername' => $name,
            'alias'       => 'yt-' . uniqid('', true),
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'address'     => '',
        ];

        $this->db->insertObject('#__bsms_teachers', $row);

        $id                                  = (int) $this->db->insertid();
        $this->created['#__bsms_teachers'][] = $id;

        return $id;
    }

    /**
     * @param   string  $title  Study title.
     *
     * @return  int  Study ID.
     */
    private function insertStudy(string $title): int
    {
        $row = (object) [
            'studytitle'  => $title,
            'alias'       => 'yt-study-' . uniqid('', true),
            'studydate'   => '2026-01-15 10:00:00',
            'messagetype' => '1',
            'booknumber'  => 101,
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 0,
            'params'      => '{}',
        ];

        $this->db->insertObject('#__bsms_studies', $row);

        $id                                 = (int) $this->db->insertid();
        $this->created['#__bsms_studies'][] = $id;

        return $id;
    }

    /**
     * @param   int  $studyId    Study ID.
     * @param   int  $teacherId  Teacher ID.
     *
     * @return  void
     */
    private function linkTeacher(int $studyId, int $teacherId): void
    {
        $row = (object) [
            'study_id'   => $studyId,
            'teacher_id' => $teacherId,
            'ordering'   => 0,
        ];

        $this->db->insertObject('#__bsms_study_teachers', $row);
    }
}
