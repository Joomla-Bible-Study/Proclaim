<?php

/**
 * Integration tests for access filtering on the podcast media endpoints.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmpodcastTrackHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * CwmpodcastTrackHelper::findPublishedMedia() is the only gate in front of
 * CwmmediaStreamer::serve() on the podcast endpoints, and the streamer
 * authorises nothing by design — the caller decides who may have the bytes,
 * before any are written. Whatever this method returns is streamed, so every
 * condition it applies is a boundary rather than a filter.
 *
 * Reachability has to mean the same thing here as it does in
 * Cwmpodcast::getEpisodes(), which decides what a feed may list, and
 * Cwmdownload::isAccessible(), which guards the download route: the media
 * file, the message it belongs to, and that message's series, each published
 * and each within the levels held by whoever is asking.
 *
 * Exercised against the real database rather than by asserting on the SQL. The
 * question is which rows come back, and a query that reads correctly can still
 * be wrong about that — an unbracketed OR satisfies any source-level check
 * while quietly discarding every condition before it. Each test runs inside a
 * transaction that is rolled back.
 *
 * @since  10.7.1
 */
#[CoversClass(CwmpodcastTrackHelper::class)]
class CwmpodcastTrackAccessTest extends IntegrationTestCase
{
    /**
     * View levels a logged-out visitor holds on a default Joomla install.
     *
     * @var    int[]
     * @since  10.7.0
     */
    private const GUEST = [1];

    /**
     * Public plus Registered.
     *
     * @var    int[]
     * @since  10.7.0
     */
    private const MEMBER = [1, 2];

    /**
     * The database, inside a transaction each test rolls back. tearDown() checks that the
     * rollback restored the media files.
     *
     * @var    DatabaseDriver|null
     * @since  10.7.0
     */
    private ?DatabaseDriver $db = null;

    /**
     * How many media files there were when setUp() ran.
     *
     * @var    int
     * @since  10.7.0
     */
    private int $mediaAtStart = 0;

    /**
     * Skip without a test database. Otherwise open a transaction and count the media files.
     *
     * @return  void
     *
     * @since   10.7.0
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);

        // ⚠️ true, not false. Table::store() issues an implicit COMMIT, and a
        // transaction started without the "as savepoint" flag is gone by the
        // time tearDown tries to roll it back.
        $this->db->transactionStart(true);

        $this->mediaAtStart = $this->countMedia();
    }

    /**
     * Roll back the transaction setUp() opened, then fail the test if the media files don't
     * number what they did at setUp().
     *
     * @return  void
     *
     * @since   10.7.0
     */
    protected function tearDown(): void
    {
        $leaked = null;

        if ($this->db !== null) {
            try {
                $this->db->transactionRollback(true);

                $after = $this->countMedia();

                if ($after !== $this->mediaAtStart) {
                    $leaked = \sprintf(
                        'Test isolation broke: #__bsms_mediafiles went from %d to %d rows.',
                        $this->mediaAtStart,
                        $after
                    );
                }
            } catch (\Throwable) {
                // Connection lost; nothing to roll back.
            }
        }

        parent::tearDown();

        if ($leaked !== null) {
            $this->fail($leaked);
        }
    }

    /**
     * The number of media files.
     *
     * @return  int  Total rows in the media table.
     *
     * @since   10.7.0
     */
    private function countMedia(): int
    {
        return (int) $this->db->setQuery(
            $this->db->createQuery()->select('COUNT(*)')->from($this->db->quoteName('#__bsms_mediafiles'))
        )->loadResult();
    }

    /**
     * Build a series / message / media file chain and return the media id.
     *
     * @param   array<string, int>  $state  Overrides for any of the seven flags.
     *
     * @return  int  The new media file's id.
     *
     * @since   10.7.0
     */
    private function makeChain(array $state = []): int
    {
        $state += [
            'series_published' => 1,
            'series_access'    => 1,
            'study_published'  => 1,
            'study_access'     => 1,
            'media_published'  => 1,
            'media_access'     => 1,
            'in_series'        => 1,
        ];

        $seriesId = 0;

        if ($state['in_series']) {
            $series = (object) [
                'series_text' => 'ghsa fixture series',
                'alias'       => $this->uniqueAlias('cwmpodcasttrackaccess-series'),
                'published'   => $state['series_published'],
                'access'      => $state['series_access'],
                'language'    => '*',
            ];
            $this->db->insertObject('#__bsms_series', $series, 'id');
            $seriesId = (int) $this->db->insertid();
        }

        $study = (object) [
            'studytitle' => 'ghsa fixture message',
            'alias'      => $this->uniqueAlias('cwmpodcasttrackaccess-study'),
            'series_id'  => $seriesId,
            'published'  => $state['study_published'],
            'access'     => $state['study_access'],
            'language'   => '*',
        ];
        $this->db->insertObject('#__bsms_studies', $study, 'id');
        $studyId = (int) $this->db->insertid();

        $media = (object) [
            'study_id'  => $studyId,
            'server_id' => 0,
            'published' => $state['media_published'],
            'access'    => $state['media_access'],
            'params'    => '{"filename":"ghsa-fixture.mp3"}',
            'metadata'  => '',
            'language'  => '*',
        ];
        $this->db->insertObject('#__bsms_mediafiles', $media, 'id');

        return (int) $this->db->insertid();
    }

    /**
     * findPublishedMedia() gives a guest the media file when it, its message and its series
     * are all published and public.
     *
     * @return  void
     *
     * @since   10.7.0
     */
    #[TestDox('A fully public, fully published chain is reachable by a guest')]
    public function testPublicMediaIsServed(): void
    {
        $id = $this->makeChain();

        // ⚠️ Positive control for every negative assertion below. Without it a
        // lookup broken for all input would satisfy the whole rest of this
        // class, and closing the hole by refusing everyone would read as a
        // pass. It is also the regression that matters most in production:
        // public podcast episodes have to keep being delivered.
        $this->assertNotNull(
            CwmpodcastTrackHelper::findPublishedMedia($this->db, $id, self::GUEST),
            'A public episode was refused to a guest. Every legitimate feed depends on this.'
        );
    }

    /**
     * findPublishedMedia() gives a guest the media file of a message that is in no series.
     *
     * @return  void
     *
     * @since   10.7.0
     */
    #[TestDox('A message outside any series is reachable, not filtered out by the series clauses')]
    public function testMediaWithNoSeriesIsServed(): void
    {
        $id = $this->makeChain(['in_series' => 0]);

        // The series conditions LEFT JOIN a row that does not exist. Written
        // without the "no series" escape they compare against NULL, which is
        // never true, and every unseried message silently vanishes.
        $this->assertNotNull(
            CwmpodcastTrackHelper::findPublishedMedia($this->db, $id, self::GUEST),
            'A message in no series was refused; the series clauses are over-filtering.'
        );
    }

    /**
     * findPublishedMedia() refuses a guest the media file when one link alone stands in the
     * way: the media file, its message or its series restricted to Registered, or any of
     * the three unpublished.
     *
     * @return  void
     *
     * @since   10.7.0
     */
    #[TestDox('Each link in the chain refuses a guest on its own')]
    public function testEachLinkRefusesIndependently(): void
    {
        $cases = [
            'restricted media file' => ['media_access' => 2],
            'restricted message'    => ['study_access' => 2],
            'restricted series'     => ['series_access' => 2],
            'unpublished message'   => ['study_published' => 0],
            'unpublished series'    => ['series_published' => 0],
            'unpublished media'     => ['media_published' => 0],
        ];

        foreach ($cases as $label => $state) {
            $id = $this->makeChain($state);

            $this->assertNull(
                CwmpodcastTrackHelper::findPublishedMedia($this->db, $id, self::GUEST),
                "A guest was handed media behind a $label. Whatever this returns is streamed."
            );
        }
    }

    /**
     * For a media file, message or series restricted to Registered, findPublishedMedia()
     * refuses a guest and gives the media file to a Registered user.
     *
     * @return  void
     *
     * @since   10.7.0
     */
    #[TestDox('Holding the level opens each link that refused without it')]
    public function testHoldingTheLevelGrantsAccess(): void
    {
        // The mirror image of the previous test: the same three rows a guest is
        // refused must be served to someone holding Registered. Refusing
        // everyone would otherwise pass every negative assertion in this class.
        foreach (['media_access', 'study_access', 'series_access'] as $field) {
            $id = $this->makeChain([$field => 2]);

            $this->assertNull(
                CwmpodcastTrackHelper::findPublishedMedia($this->db, $id, self::GUEST),
                "Setup failed: $field = 2 should refuse a guest."
            );

            $this->assertNotNull(
                CwmpodcastTrackHelper::findPublishedMedia($this->db, $id, self::MEMBER),
                "A Registered user was refused media gated only by $field."
            );
        }
    }

    /**
     * findPublishedMedia() refuses a public media file when given no view levels at all.
     *
     * @return  void
     *
     * @since   10.7.0
     */
    #[TestDox('An empty level set is treated as holding nothing, not everything')]
    public function testEmptyLevelSetRefusesPublicMedia(): void
    {
        $id = $this->makeChain();

        // An identity with no levels is a broken identity. The dangerous
        // reading is "no levels, so do not filter" — which serves everything.
        $this->assertNull(
            CwmpodcastTrackHelper::findPublishedMedia($this->db, $id, []),
            'An empty level set behaved as an unfiltered query.'
        );
    }

    /**
     * findPublishedMedia() returns null for a media id of 0.
     *
     * @return  void
     *
     * @since   10.7.0
     */
    #[TestDox('A missing media id returns null rather than an arbitrary row')]
    public function testUnknownIdReturnsNull(): void
    {
        $this->assertNull(
            CwmpodcastTrackHelper::findPublishedMedia($this->db, 0, self::GUEST)
        );
    }
}
