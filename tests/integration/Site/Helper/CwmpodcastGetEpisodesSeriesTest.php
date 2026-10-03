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
    private function insertSermon(string $title, int $published = 1, int $seriesId = 0, int $access = 1): int
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
            'access'      => $access,
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
    private function insertMediaFile(int $studyId, int $podcastId, int $access = 1): int
    {
        $row = (object) [
            'study_id'   => $studyId,
            'podcast_id' => (string) $podcastId,
            'metadata'   => '',
            'createdate' => '2026-07-25 11:05:00',
            'published'  => 1,
            'access'     => $access,
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

    #[TestDox('The feed lists what a guest may see, and not a Registered-only message or media file')]
    public function testRestrictedItemsAreNotListed(): void
    {
        $podcastId  = $this->insertPodcast();
        $publicId   = $this->insertSermon('Public message');
        $messageId  = $this->insertSermon('Registered message', 1, 0, 2);
        $mediaId    = $this->insertSermon('Registered media');

        $this->insertMediaFile($publicId, $podcastId);
        $this->insertMediaFile($messageId, $podcastId);
        $this->insertMediaFile($mediaId, $podcastId, 2);

        $sids = array_map(static fn ($e) => (int) $e->sid, (new Cwmpodcast())->getEpisodes($podcastId, ''));

        $this->assertContains($publicId, $sids);
        $this->assertNotContains($messageId, $sids, 'A Registered-only message must not be listed in a public feed.');
        $this->assertNotContains($mediaId, $sids, 'A Registered-only media file must not be listed in a public feed.');
    }

    #[TestDox('A Super User building the feed does not widen it')]
    public function testASuperUserBuildingTheFeedDoesNotWidenIt(): void
    {
        $superUser = (int) $this->db->setQuery(
            'SELECT ' . $this->db->quoteName('user_id') . ' FROM ' . $this->db->quoteName('#__user_usergroup_map')
            . ' WHERE ' . $this->db->quoteName('group_id') . ' = 8',
            0,
            1
        )->loadResult();

        if ($superUser === 0) {
            $this->markTestSkipped('No Super User on this database.');
        }

        $app      = Factory::getApplication();
        $previous = $app->getIdentity();
        $app->loadIdentity(\Joomla\CMS\User\User::getInstance($superUser));

        try {
            $podcastId = $this->insertPodcast();
            $publicId  = $this->insertSermon('Public message');
            $hiddenId  = $this->insertSermon('Registered message', 1, 0, 2);

            $this->insertMediaFile($publicId, $podcastId);
            $this->insertMediaFile($hiddenId, $podcastId);

            $sids = array_map(static fn ($e) => (int) $e->sid, (new Cwmpodcast())->getEpisodes($podcastId, ''));
        } finally {
            $app->loadIdentity($previous);
        }

        $this->assertContains($publicId, $sids);
        $this->assertNotContains($hiddenId, $sids, 'The feed is read by an anonymous app, whoever builds it.');
    }
}
