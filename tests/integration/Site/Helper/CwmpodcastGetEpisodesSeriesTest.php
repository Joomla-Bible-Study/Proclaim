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
use CWM\Component\Proclaim\Tests\Integration\Admin\Api\ApiDataTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * A message with no series stores series_id = 0 (CwmmessageModel casts the
 * empty selection to (int)). getEpisodes() previously only treated
 * series_id = -1 as "no series", so series_id = 0 fell through the
 * `(se.published = 1 OR series_id = -1)` filter and every seriesless episode
 * was silently dropped from the podcast feed. Regression coverage for that fix.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(Cwmpodcast::class)]
class CwmpodcastGetEpisodesSeriesTest extends ApiDataTestCase
{
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
            'title'     => $title,
            'filename'  => 'test-podcast.xml',
            'published' => 1,
            'access'    => 1,
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
