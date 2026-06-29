<?php

/**
 * Integration tests for CwmplaylistSyncHelper::pushMemberships()
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Administrator\Addons\Servers\Youtube\CWMAddonYoutube;
use CWM\Component\Proclaim\Administrator\Helper\CwmplaylistSyncHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Exercises the membership write-back planner against a real database.
 *
 * The pure mapper (membershipsToPush) is covered by the unit suite; this suite
 * pins the DB-driven branches it can't reach: candidate selection by linked
 * series + server + published state, the writeback_enabled / series_id opt-in
 * gates, dry-run reporting, junction upsert after a successful push, and the
 * quota/auth early-stop. Each test runs inside a transaction that is rolled
 * back, so nothing persists.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmplaylistSyncHelper::class)]
class CwmplaylistMembershipPushTest extends IntegrationTestCase
{
    /**
     * @var  DatabaseDriver|null
     */
    private ?DatabaseDriver $db = null;

    /**
     * @var  integer
     */
    private int $serverId = 0;

    /**
     * @var  integer
     */
    private int $seriesId = 0;

    /**
     * Begin a transaction and seed a YouTube server + a linked series.
     *
     * @return  void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        // pushMemberships() stamps junction rows via Factory::getDate(), which
        // reads Factory::$language->getTag(). In the console-app test harness
        // that static language is an untagged instance, so getTag() is null and
        // the date path emits PHP warnings that trip beStrictAboutOutputDuringTests.
        // Prime the static with the application's configured (tagged) language.
        Factory::$language = Factory::getApplication()->getLanguage();

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart();

        $this->serverId = $this->insertServer();
        $this->seriesId = $this->insertSeries();
    }

    /**
     * Roll back everything this test inserted.
     *
     * @return  void
     */
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
     * The happy path: every published video in the linked series that is not yet
     * a member is pushed once, the junction is backfilled, and an already-present
     * video is left alone.
     *
     * @return  void
     */
    public function testPushAddsMissingSeriesVideosAndBackfillsJunction(): void
    {
        $mfA = $this->insertSeriesMedia('https://youtu.be/cXhKlo2nxPs', 'Sermon A');
        $mfB = $this->insertSeriesMedia('https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'Sermon B');
        $mfC = $this->insertSeriesMedia('https://www.youtube.com/embed/AbCdEfGhIjK', 'Sermon C');

        $playlistId = $this->insertPlaylist($this->seriesId, 1);

        // dQw4w9WgXcQ (Sermon B) is already a member — it must be skipped.
        $this->insertJunction($playlistId, 'dQw4w9WgXcQ', $mfB, 0);

        $addon  = $this->fakeAddon();
        $result = CwmplaylistSyncHelper::pushMemberships($this->db, $addon, $playlistId, false);

        $this->assertSame(2, $result['pushed']);
        $this->assertSame([], $result['wouldPush']);
        $this->assertSame([], $result['errors']);

        // Only the two missing videos were sent to the platform.
        $this->assertEqualsCanonicalizing(
            ['cXhKlo2nxPs', 'AbCdEfGhIjK'],
            array_column($addon->calls, 'videoId')
        );

        // Junction now holds all three, with media files linked.
        $items = $this->junctionRows($playlistId);
        $this->assertCount(3, $items);

        $byVideo = [];

        foreach ($items as $row) {
            $byVideo[$row->remote_video_id] = $row;
        }

        $this->assertSame($mfA, (int) $byVideo['cXhKlo2nxPs']->mediafile_id);
        $this->assertSame($mfC, (int) $byVideo['AbCdEfGhIjK']->mediafile_id);
        $this->assertSame('Sermon A', $byVideo['cXhKlo2nxPs']->title);
    }

    /**
     * Dry-run reports what would be pushed without calling the platform or
     * touching the junction table.
     *
     * @return  void
     */
    public function testDryRunReportsWithoutWriting(): void
    {
        $this->insertSeriesMedia('https://youtu.be/cXhKlo2nxPs', 'Sermon A');
        $this->insertSeriesMedia('https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'Sermon B');

        $playlistId = $this->insertPlaylist($this->seriesId, 1);

        $addon  = $this->fakeAddon();
        $result = CwmplaylistSyncHelper::pushMemberships($this->db, $addon, $playlistId, true);

        $this->assertSame(0, $result['pushed']);
        $this->assertCount(2, $result['wouldPush']);
        $this->assertSame([], $addon->calls);
        $this->assertCount(0, $this->junctionRows($playlistId));
    }

    /**
     * A playlist not opted in to write-back is a no-op.
     *
     * @return  void
     */
    public function testWritebackDisabledIsNoOp(): void
    {
        $this->insertSeriesMedia('https://youtu.be/cXhKlo2nxPs', 'Sermon A');

        $playlistId = $this->insertPlaylist($this->seriesId, 0);

        $addon  = $this->fakeAddon();
        $result = CwmplaylistSyncHelper::pushMemberships($this->db, $addon, $playlistId, false);

        $this->assertSame(0, $result['pushed']);
        $this->assertSame([], $addon->calls);
    }

    /**
     * A playlist with no linked series has nothing to drive membership from.
     *
     * @return  void
     */
    public function testNoLinkedSeriesIsNoOp(): void
    {
        $this->insertSeriesMedia('https://youtu.be/cXhKlo2nxPs', 'Sermon A');

        $playlistId = $this->insertPlaylist(null, 1);

        $addon  = $this->fakeAddon();
        $result = CwmplaylistSyncHelper::pushMemberships($this->db, $addon, $playlistId, false);

        $this->assertSame(0, $result['pushed']);
        $this->assertSame([], $addon->calls);
    }

    /**
     * Unpublished media and media on a different server are not candidates.
     *
     * @return  void
     */
    public function testOnlyPublishedSameServerMediaAreCandidates(): void
    {
        // Published, in series, on this server — the only valid candidate.
        $mf = $this->insertSeriesMedia('https://youtu.be/cXhKlo2nxPs', 'Valid');

        // Unpublished — excluded.
        $this->insertSeriesMedia('https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'Unpublished', 0);

        // Published but on a different server — excluded.
        $otherServer = $this->insertServer();
        $this->insertSeriesMedia('https://www.youtube.com/embed/AbCdEfGhIjK', 'Other server', 1, $otherServer);

        $playlistId = $this->insertPlaylist($this->seriesId, 1);

        $addon  = $this->fakeAddon();
        $result = CwmplaylistSyncHelper::pushMemberships($this->db, $addon, $playlistId, false);

        $this->assertSame(1, $result['pushed']);
        $this->assertSame(['cXhKlo2nxPs'], array_column($addon->calls, 'videoId'));
        $this->assertSame($mf, (int) $this->junctionRows($playlistId)[0]->mediafile_id);
    }

    /**
     * A quota error stops the run early instead of hammering the failing API.
     *
     * @return  void
     */
    public function testQuotaErrorStopsRunEarly(): void
    {
        $this->insertSeriesMedia('https://youtu.be/cXhKlo2nxPs', 'Sermon A');
        $this->insertSeriesMedia('https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'Sermon B');

        $playlistId = $this->insertPlaylist($this->seriesId, 1);

        // First insert fails with a quota error → the loop breaks before the second.
        $addon = $this->fakeAddon(['__default__' => ['success' => false, 'error' => 'quotaExceeded']]);

        $result = CwmplaylistSyncHelper::pushMemberships($this->db, $addon, $playlistId, false);

        $this->assertSame(0, $result['pushed']);
        $this->assertCount(1, $result['errors']);
        $this->assertCount(1, $addon->calls);
        $this->assertCount(0, $this->junctionRows($playlistId));
    }

    /**
     * Build a fake playlist-capable addon that records membership pushes.
     *
     * Per-video results may be supplied keyed by video ID; '__default__' sets the
     * fallback. The CWMAddon constructor (which loads an addon XML file) is
     * deliberately overridden to a no-op so the double needs no config on disk,
     * and ID extraction delegates to the real YouTube extractor.
     *
     * @param   array<string,array{success:bool,error?:string}>  $results  Result map.
     *
     * @return  CWMAddon
     */
    private function fakeAddon(array $results = []): CWMAddon
    {
        return new class ($results) extends CWMAddon {
            /** @var array<int,array{videoId:string,playlistId:string}> */
            public array $calls = [];

            /** @var array<string,array{success:bool,error?:string}> */
            private array $results;

            public function __construct(array $results = [])
            {
                $this->results = $results;
            }

            public function supportsPlaylists(): bool
            {
                return true;
            }

            public function supportsPlaylistWriteback(): bool
            {
                return true;
            }

            public function extractRemoteMediaId(string $text): ?string
            {
                return CWMAddonYoutube::extractMediaId($text);
            }

            public function addPlaylistMembership(int $serverId, string $remotePlaylistId, string $remoteVideoId): array
            {
                $this->calls[] = ['videoId' => $remoteVideoId, 'playlistId' => $remotePlaylistId];

                return $this->results[$remoteVideoId]
                    ?? $this->results['__default__']
                    ?? ['success' => true];
            }

            public function renderGeneral(object $media_form, bool $new): string
            {
                return '';
            }

            public function render(object $media_form, bool $new): string
            {
                return '';
            }

            protected function upload(?array $data): mixed
            {
                return null;
            }
        };
    }

    /**
     * Insert a published YouTube server.
     *
     * @return  int  Server ID.
     */
    private function insertServer(): int
    {
        $row = (object) [
            'server_name' => 'Test YouTube',
            'published'   => 1,
            'access'      => 1,
            'type'        => 'youtube',
            'params'      => '{}',
            'media'       => '{}',
        ];

        $this->db->insertObject('#__bsms_servers', $row);

        return (int) $this->db->insertid();
    }

    /**
     * Insert a published series.
     *
     * @return  int  Series ID.
     */
    private function insertSeries(): int
    {
        $row = (object) [
            'series_text' => 'Test Series',
            'alias'       => 'test-series',
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 0,
        ];

        $this->db->insertObject('#__bsms_series', $row);

        return (int) $this->db->insertid();
    }

    /**
     * Insert a study in a series plus a media file whose params point at a URL.
     *
     * @param   string   $url        Media URL stored in params.filename.
     * @param   string   $title      Study title (carried onto the pushed item).
     * @param   int      $published  Media published state.
     * @param   int|null $serverId   Override server (defaults to the seeded one).
     *
     * @return  int  The media file ID.
     */
    private function insertSeriesMedia(string $url, string $title, int $published = 1, ?int $serverId = null): int
    {
        $study = (object) [
            'studytitle'  => $title,
            'alias'       => strtolower(str_replace(' ', '-', $title)),
            'studydate'   => '2026-01-15 10:00:00',
            'series_id'   => $this->seriesId,
            'messagetype' => 1,
            'booknumber'  => 101,
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 0,
        ];

        $this->db->insertObject('#__bsms_studies', $study);
        $studyId = (int) $this->db->insertid();

        $media = (object) [
            'study_id'  => $studyId,
            'server_id' => $serverId ?? $this->serverId,
            'metadata'  => '',
            'params'    => json_encode(['filename' => $url]),
            'published' => $published,
            'access'    => 1,
            'language'  => '*',
        ];

        $this->db->insertObject('#__bsms_mediafiles', $media);

        return (int) $this->db->insertid();
    }

    /**
     * Insert a playlist linked to a series, with write-back on/off.
     *
     * @param   int|null  $seriesId           Linked series, or null for none.
     * @param   int       $writebackEnabled   Write-back opt-in flag.
     *
     * @return  int  Playlist ID.
     */
    private function insertPlaylist(?int $seriesId, int $writebackEnabled): int
    {
        $row = (object) [
            'title'              => 'Test Playlist',
            'alias'              => 'test-playlist',
            'remote_playlist_id' => 'PL_TEST_123',
            'server_id'          => $this->serverId,
            'series_id'          => $seriesId,
            'sync_enabled'       => 1,
            'writeback_enabled'  => $writebackEnabled,
            'params'             => '{}',
            'language'           => '*',
            'published'          => 1,
            'access'             => 1,
        ];

        $this->db->insertObject('#__bsms_playlists', $row);

        return (int) $this->db->insertid();
    }

    /**
     * Insert a junction row marking a video as an existing playlist member.
     *
     * @param   int     $playlistId   Playlist ID.
     * @param   string  $videoId      Remote video ID.
     * @param   int     $mediafileId  Linked media file ID.
     * @param   int     $position     Position in the playlist.
     *
     * @return  void
     */
    private function insertJunction(int $playlistId, string $videoId, int $mediafileId, int $position): void
    {
        $row = (object) [
            'playlist_id'     => $playlistId,
            'mediafile_id'    => $mediafileId,
            'remote_video_id' => $videoId,
            'title'           => '',
            'position'        => $position,
        ];

        $this->db->insertObject('#__bsms_playlist_items', $row);
    }

    /**
     * Fetch the junction rows for a playlist, ordered by position.
     *
     * @param   int  $playlistId  Playlist ID.
     *
     * @return  object[]
     */
    private function junctionRows(int $playlistId): array
    {
        return $this->db->setQuery(
            $this->db->getQuery(true)
                ->select('*')
                ->from($this->db->quoteName('#__bsms_playlist_items'))
                ->where($this->db->quoteName('playlist_id') . ' = ' . (int) $playlistId)
                ->order($this->db->quoteName('position') . ' ASC')
        )->loadObjectList() ?: [];
    }
}
