<?php

/**
 * Integration tests for Cwmpodcast::getEpisodes() series-id regression.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * A message with no series stores series_id = 0 (CwmmessageModel casts the
 * empty selection to (int)). getEpisodes() previously only treated
 * series_id = -1 as "no series", so series_id = 0 fell through the
 * `(se.published = 1 OR series_id = -1)` filter and every seriesless episode
 * was silently dropped from the podcast feed. Regression coverage for that fix.
 *
 * Self-contained fixtures: this file previously extended the Api suite's
 * ApiDataTestCase, which was removed in the 2026-08 test-correctness pass
 * (its own tests only verified the test helper against itself); the insert
 * helpers this genuine regression test needs were inlined here instead.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(Cwmpodcast::class)]
class CwmpodcastGetEpisodesSeriesTest extends IntegrationTestCase
{
    /**
     * @var  DatabaseDriver|null
     */
    private ?DatabaseDriver $db = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart();
    }

    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback();
            } catch (\Throwable) {
                // Connection may have been lost — nothing to roll back.
            }
        }

        parent::tearDown();
    }

    /**
     * Insert a test series and return the ID.
     *
     * @param   string  $title      Series title
     * @param   int     $published  Published state
     *
     * @return  int
     */
    private function insertSeries(string $title, int $published = 1): int
    {
        $row = (object) [
            'series_text' => $title,
            'alias'       => strtolower(str_replace(' ', '-', $title)),
            'published'   => $published,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 0,
        ];

        $this->db->insertObject('#__bsms_series', $row);

        return (int) $this->db->insertid();
    }

    /**
     * Insert a test sermon (study) and return the ID.
     *
     * @param   string  $title      Sermon title
     * @param   int     $published  Published state
     * @param   int     $seriesId   Series ID (0 = seriesless)
     *
     * @return  int
     */
    private function insertSermon(string $title, int $published = 1, int $seriesId = 0): int
    {
        $row = (object) [
            'studytitle'  => $title,
            'alias'       => strtolower(str_replace(' ', '-', $title)),
            'studydate'   => '2026-01-15 10:00:00',
            'teacher_id'  => 0,
            'series_id'   => $seriesId,
            'messagetype' => 1,
            'booknumber'  => 101,
            'published'   => $published,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 0,
            'hits'        => 0,
            'checked_out' => 0,
            'asset_id'    => 0,
            'created_by'  => 0,
            'modified_by' => 0,
        ];

        $this->db->insertObject('#__bsms_studies', $row);

        return (int) $this->db->insertid();
    }

    /**
     * Insert a podcast channel and return its ID.
     *
     * @param   string  $title  Podcast title
     *
     * @return  int
     */
    private function insertPodcast(string $title = 'Test Podcast'): int
    {
        $row = (object) [
            'title'    => $title,
            'filename' => 'test-podcast.xml',
            // Explicit empty string: current install SQL allows NULL, but
            // older dev DBs carry podcastlink as NOT NULL with no default
            // (schema drift), where omitting it makes the INSERT fail.
            'podcastlink' => '',
            'published'   => 1,
            'access'      => 1,
        ];

        $this->db->insertObject('#__bsms_podcast', $row);

        return (int) $this->db->insertid();
    }

    /**
     * Insert a media file linked to a study and podcast, return its ID.
     *
     * @param   int  $studyId    Owning study ID
     * @param   int  $podcastId  Podcast ID to tag via the CSV podcast_id column
     *
     * @return  int
     */
    private function insertMediaFile(int $studyId, int $podcastId): int
    {
        $row = (object) [
            'study_id'   => $studyId,
            'podcast_id' => (string) $podcastId,
            'metadata'   => '',
            'createdate' => '2026-07-25 11:05:00',
            'published'  => 1,
            'access'     => 1,
            'language'   => '*',
        ];

        $this->db->insertObject('#__bsms_mediafiles', $row);

        return (int) $this->db->insertid();
    }

    #[TestDox('A published message with no series (series_id = 0) appears in the podcast feed')]
    public function testSeriesLessMessageAppearsInEpisodes(): void
    {
        $podcastId = $this->insertPodcast();
        $studyId   = $this->insertSermon('What about . . . God?', 1, 0);
        $this->insertMediaFile($studyId, $podcastId);

        $helper   = new Cwmpodcast();
        $episodes = $helper->getEpisodes($podcastId, '');

        $sids = array_map(static fn ($e) => (int) $e->sid, $episodes);

        $this->assertContains(
            $studyId,
            $sids,
            'A published, seriesless message with a tagged media file must appear in getEpisodes().'
        );
    }

    #[TestDox('A message in an unpublished series is still excluded from the podcast feed')]
    public function testUnpublishedSeriesMessageStillExcluded(): void
    {
        $podcastId = $this->insertPodcast();
        $seriesId  = $this->insertSeries('Unpublished Series', 0);
        $studyId   = $this->insertSermon('Hidden Message', 1, $seriesId);
        $this->insertMediaFile($studyId, $podcastId);

        $helper   = new Cwmpodcast();
        $episodes = $helper->getEpisodes($podcastId, '');

        $sids = array_map(static fn ($e) => (int) $e->sid, $episodes);

        $this->assertNotContains(
            $studyId,
            $sids,
            'A message whose series is unpublished must still be excluded from getEpisodes().'
        );
    }
}
