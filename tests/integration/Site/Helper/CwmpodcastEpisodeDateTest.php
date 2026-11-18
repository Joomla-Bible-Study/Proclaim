<?php

/**
 * Integration tests for the date each podcast episode is published under.
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
 * A media file's createdate may be NULL: the column allows it, and nothing
 * fills it in when a media file is created without one, through the REST API
 * for example. Such an episode is dated by its message, and by the time the
 * feed is written when the message has no date either.
 *
 * Each test builds a real feed with makePodcasts(). writeFile() is replaced so
 * the feed is captured rather than written under the site root.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(Cwmpodcast::class)]
class CwmpodcastEpisodeDateTest extends IntegrationTestCase
{
    /**
     * @var    DatabaseDriver|null
     * @since  __DEPLOY_VERSION__
     */
    private ?DatabaseDriver $db = null;

    /**
     * @var    mixed
     * @since  __DEPLOY_VERSION__
     */
    private mixed $previousFactoryLanguage = null;

    /**
     * @return  void
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->previousFactoryLanguage = $this->silenceDateLanguageWarnings();

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart(true);
    }

    /**
     * @return  void
     * @since   __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback(true);
            } catch (\Throwable) {
                // Connection may have been lost — nothing to roll back.
            }
        }

        Factory::$language = $this->previousFactoryLanguage;

        parent::tearDown();
    }

    /**
     * @return  void
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('An undated media file is published under its message\'s date')]
    public function testUndatedMediaFileTakesItsMessagesDate(): void
    {
        $feed = $this->buildFeed(null, '2026-03-01 15:30:00');

        $this->assertSame([$this->feedDate('2026-03-01 15:30:00')], $this->pubDates($feed));
    }

    /**
     * @return  void
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A dated media file is published under its own date, not its message\'s')]
    public function testDatedMediaFileKeepsItsOwnDate(): void
    {
        $feed = $this->buildFeed('2026-04-02 09:00:00', '2026-03-01 15:30:00');

        $this->assertSame([$this->feedDate('2026-04-02 09:00:00')], $this->pubDates($feed));
    }

    /**
     * @return  void
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('With no date on the media file or its message, the episode is dated when the feed is written')]
    public function testUndatedMessageIsDatedWhenTheFeedIsWritten(): void
    {
        $feed  = $this->buildFeed(null, null);
        $dates = $this->pubDates($feed);

        $this->assertCount(1, $dates);
        $this->assertSame(1, preg_match('#<lastBuildDate>([^<]+)</lastBuildDate>#', $feed, $built));

        // Against the feed's own build time, not the clock: Factory::getDate()
        // caches 'now' per process, so in a long run it predates the test.
        $episode = \DateTimeImmutable::createFromFormat(\DateTimeInterface::RFC2822, $dates[0]);
        $written = \DateTimeImmutable::createFromFormat(\DateTimeInterface::RFC2822, $built[1]);

        $this->assertNotFalse($episode, 'The pubDate should be an RFC 2822 date');
        $this->assertNotFalse($written, 'The lastBuildDate should be an RFC 2822 date');
        $this->assertLessThanOrEqual(5, abs($episode->getTimestamp() - $written->getTimestamp()));
    }

    /**
     * Seed one podcast with one episode, build every feed, and return this one.
     *
     * @param   string|null  $createdate  The media file's createdate
     * @param   string|null  $studydate   The message's studydate
     *
     * @return  string  The feed's XML
     *
     * @since   __DEPLOY_VERSION__
     */
    private function buildFeed(?string $createdate, ?string $studydate): string
    {
        $filename  = $this->uniqueAlias('episode-date') . '.xml';
        $podcastId = $this->insertPodcast($filename);

        $this->insertMediaFile($this->insertMessage($studydate), $podcastId, $createdate);

        $helper = new class () extends Cwmpodcast {
            /** @var array<string, string> Feed XML by file name */
            public array $written = [];

            public function writeFile(string $file, string $filecontent): bool
            {
                $this->written[basename($file)] = $filecontent;

                return true;
            }
        };

        $helper->makePodcasts();

        $this->assertArrayHasKey($filename, $helper->written, 'The podcast\'s feed should have been built');

        return $helper->written[$filename];
    }

    /**
     * @param   string  $feed  Feed XML
     *
     * @return  string[]  Every item's pubDate, in feed order
     *
     * @since   __DEPLOY_VERSION__
     */
    private function pubDates(string $feed): array
    {
        preg_match_all('#<pubDate>([^<]+)</pubDate>#', $feed, $matches);

        return $matches[1];
    }

    /**
     * @param   string  $stored  A UTC date as the database stores it
     *
     * @return  string  The date as the feed writes it
     *
     * @since   __DEPLOY_VERSION__
     */
    private function feedDate(string $stored): string
    {
        $siteTz = new \DateTimeZone(Factory::getApplication()->get('offset', 'UTC'));

        return (new \ReflectionMethod(Cwmpodcast::class, 'formatFeedDate'))->invoke(new Cwmpodcast(), $stored, $siteTz);
    }

    /**
     * @param   string  $filename  The feed's file name
     *
     * @return  int  The new podcast id
     *
     * @since   __DEPLOY_VERSION__
     */
    private function insertPodcast(string $filename): int
    {
        // The feed passes website and podcastimage to string parameters.
        // podcastlink is set because older dev databases hold it NOT NULL with no default.
        $row = (object) [
            'title'        => 'Episode date podcast',
            'website'      => 'example.org',
            'filename'     => $filename,
            'podcastimage' => '',
            'podcastlink'  => '',
            'published'    => 1,
            'access'       => 1,
            'language'     => '*',
        ];

        $this->db->insertObject('#__bsms_podcast', $row);

        return (int) $this->db->insertid();
    }

    /**
     * @param   string|null  $studydate  The message's date
     *
     * @return  int  The new message id
     *
     * @since   __DEPLOY_VERSION__
     */
    private function insertMessage(?string $studydate): int
    {
        $row = (object) [
            'studytitle'  => 'Episode date message',
            'alias'       => $this->uniqueAlias('episode-date-study'),
            'studydate'   => $studydate,
            'teacher_id'  => 0,
            'series_id'   => 0,
            'messagetype' => 1,
            'booknumber'  => 101,
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 0,
            'hits'        => 0,
            'checked_out' => 0,
            'asset_id'    => 0,
            'created_by'  => 0,
            'modified_by' => 0,
        ];

        // insertObject() skips NULL properties, so the column keeps its NULL default
        $this->db->insertObject('#__bsms_studies', $row);

        return (int) $this->db->insertid();
    }

    /**
     * @param   int          $studyId     The owning message
     * @param   int          $podcastId   The podcast, in the CSV podcast_id column
     * @param   string|null  $createdate  The media file's createdate
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function insertMediaFile(int $studyId, int $podcastId, ?string $createdate): void
    {
        $row = (object) [
            'study_id'   => $studyId,
            'podcast_id' => (string) $podcastId,
            'metadata'   => '',
            'params'     => '{"filename":"https://example.org/media/episode.mp3","size":"1000","mime_type":"audio/mpeg"}',
            'createdate' => $createdate,
            'published'  => 1,
            'access'     => 1,
            'language'   => '*',
        ];

        $this->db->insertObject('#__bsms_mediafiles', $row);
    }
}
