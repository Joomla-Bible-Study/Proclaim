<?php

/**
 * Integration tests for CwmplaylistSyncHelper's junction-row upserts
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
use Joomla\Input\Input;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Pins what an upsert onto an existing #__bsms_playlist_items row must and must
 * not overwrite (#1658).
 *
 * Both writers became single INSERT … ON DUPLICATE KEY UPDATE statements, which
 * makes the update list explicit where it used to be implied — updateObject()
 * skipped null properties by default, and only the columns each helper named were
 * ever touched. A statement that simply assigns every inserted value would still
 * pass a row-count assertion while clearing a media link, resetting a created
 * date, or blanking a title the platform supplied. These tests assert the columns
 * that must survive, not just that one row exists.
 *
 * Each test runs inside a transaction that is rolled back, so nothing persists.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmplaylistSyncHelper::class)]
class CwmplaylistUpsertTest extends IntegrationTestCase
{
    /**
     * A stable remote video ID used as the junction key throughout.
     */
    private const VIDEO_ID = 'dQw4w9WgXcQ';

    /**
     * A created timestamp old enough that an accidental overwrite is unmistakable.
     */
    private const SEEDED_CREATED = '2020-01-01 00:00:00';

    /**
     * @var  DatabaseDriver|null
     */
    private ?DatabaseDriver $db = null;

    /**
     * Factory::$language as it was before silenceDateLanguageWarnings()
     * replaced it, so tearDown() can restore process-wide state.
     *
     * @var mixed
     */
    private mixed $previousFactoryLanguage = null;

    /**
     * @var  int
     */
    private int $serverId = 0;

    /**
     * @var  int
     */
    private int $seriesId = 0;

    /**
     * Begin a transaction and seed a YouTube server + a series.
     *
     * @return  void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->previousFactoryLanguage = $this->silenceDateLanguageWarnings();

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

        Factory::$language = $this->previousFactoryLanguage;

        parent::tearDown();
    }

    /**
     * The load-bearing case. A sync run that cannot match a video to local media
     * passes a null media file ID; the row it lands on may already be linked, and
     * that link must survive. updateObject() skipped nulls, so this was previously
     * true by accident — expressing it as COALESCE is what keeps it true.
     *
     * @return  void
     */
    public function testReconcileKeepsMediafileLinkWhenRemoteHasNoLocalMatch(): void
    {
        $mediafileId = $this->insertSeriesMedia('https://youtu.be/' . self::VIDEO_ID, 'Sermon A');
        $playlistId  = $this->insertPlaylist();

        $this->insertJunction($playlistId, self::VIDEO_ID, $mediafileId, 7, 'Stale title');

        // An empty video map is the unmatched case: reconcile passes null through.
        $result = CwmplaylistSyncHelper::reconcilePlaylist(
            $this->db,
            $this->fakeAddon([['videoId' => self::VIDEO_ID, 'title' => 'Fresh title']]),
            $playlistId,
            []
        );

        $this->assertNull($result['error']);
        $this->assertSame(1, $result['unmatched']);

        $rows = $this->junctionRows($playlistId);
        $this->assertCount(1, $rows, 'The unique key must have made this an update, not a second row.');

        $row = $rows[0];
        $this->assertSame($mediafileId, (int) $row->mediafile_id, 'An unmatched sync must not clear an existing media link.');
        $this->assertSame('Fresh title', $row->title, 'The platform title is authoritative and must be applied.');
        $this->assertSame(0, (int) $row->position, 'Position follows the platform ordering.');
        $this->assertSame(self::SEEDED_CREATED, $row->created, 'created records when the row first appeared and must not be reset.');
    }

    /**
     * A matched video links the row it lands on, and repeating the whole run
     * changes nothing — which is what the duplicate-key path is for.
     *
     * @return  void
     */
    public function testReconcileLinksMatchedVideoAndIsRepeatable(): void
    {
        $mediafileId = $this->insertSeriesMedia('https://youtu.be/' . self::VIDEO_ID, 'Sermon A');
        $playlistId  = $this->insertPlaylist();

        $this->insertJunction($playlistId, self::VIDEO_ID, null, 0, '');

        $videoMap = ['youtube' => [self::VIDEO_ID => $mediafileId]];
        $addon    = $this->fakeAddon([['videoId' => self::VIDEO_ID, 'title' => 'Sermon A']]);

        $first = CwmplaylistSyncHelper::reconcilePlaylist($this->db, $addon, $playlistId, $videoMap);
        $this->assertSame(1, $first['matched']);

        $second = CwmplaylistSyncHelper::reconcilePlaylist($this->db, $addon, $playlistId, $videoMap);
        $this->assertNull($second['error']);

        $rows = $this->junctionRows($playlistId);
        $this->assertCount(1, $rows);
        $this->assertSame($mediafileId, (int) $rows[0]->mediafile_id);
    }

    /**
     * Selecting a playlist the video is already in — imported from the platform,
     * not yet linked to any media — links it without disturbing what the platform
     * put there. A row that says 'remote' is on the platform; calling it 'manual'
     * would make the write-back push try to add it again.
     *
     * @return  void
     */
    public function testManualAssignmentPreservesImportedSourceAndTitle(): void
    {
        $mediafileId = $this->insertSeriesMedia('https://youtu.be/' . self::VIDEO_ID, 'Sermon A');
        $playlistId  = $this->insertPlaylist();

        $this->insertJunction($playlistId, self::VIDEO_ID, null, 4, 'From platform', 'remote');

        CwmplaylistSyncHelper::setManualPlaylistAssignments(
            $mediafileId,
            [$playlistId],
            json_encode(['filename' => 'https://youtu.be/' . self::VIDEO_ID])
        );

        $rows = $this->junctionRows($playlistId);
        $this->assertCount(1, $rows);

        $row = $rows[0];
        $this->assertSame($mediafileId, (int) $row->mediafile_id, 'The assignment must link the media file.');
        $this->assertSame('remote', $row->source, 'A row confirmed on the platform must not be downgraded to manual.');
        $this->assertSame('From platform', $row->title, 'A title the platform supplied must not be blanked.');
        $this->assertSame(4, (int) $row->position, 'Position belongs to the platform ordering.');
        $this->assertSame(self::SEEDED_CREATED, $row->created);
    }

    /**
     * Re-selecting a playlist that was queued for removal cancels the removal: the
     * video is still on the platform, so the row goes back to 'remote'.
     *
     * @return  void
     */
    public function testManualAssignmentCancelsAQueuedRemoval(): void
    {
        $mediafileId = $this->insertSeriesMedia('https://youtu.be/' . self::VIDEO_ID, 'Sermon A');
        $playlistId  = $this->insertPlaylist();

        $this->insertJunction($playlistId, self::VIDEO_ID, $mediafileId, 0, 'Queued', 'remove_pending');

        CwmplaylistSyncHelper::setManualPlaylistAssignments(
            $mediafileId,
            [$playlistId],
            json_encode(['filename' => 'https://youtu.be/' . self::VIDEO_ID])
        );

        $rows = $this->junctionRows($playlistId);
        $this->assertCount(1, $rows);
        $this->assertSame('remote', $rows[0]->source, 'Re-selecting a queued removal restores it to remote.');
    }

    /**
     * With no row to collide with, the insert branch runs and the row is marked as
     * the user's own assignment, awaiting a push.
     *
     * @return  void
     */
    public function testManualAssignmentInsertsANewRowAsManual(): void
    {
        $mediafileId = $this->insertSeriesMedia('https://youtu.be/' . self::VIDEO_ID, 'Sermon A');
        $playlistId  = $this->insertPlaylist();

        CwmplaylistSyncHelper::setManualPlaylistAssignments(
            $mediafileId,
            [$playlistId],
            json_encode(['filename' => 'https://youtu.be/' . self::VIDEO_ID])
        );

        $rows = $this->junctionRows($playlistId);
        $this->assertCount(1, $rows);

        $row = $rows[0];
        $this->assertSame('manual', $row->source);
        $this->assertSame($mediafileId, (int) $row->mediafile_id);
        $this->assertSame(self::VIDEO_ID, $row->remote_video_id);
        $this->assertSame('', $row->title);
    }

    /**
     * Build a fake playlist-capable addon that serves a fixed page of videos.
     *
     * The CWMAddon constructor (which loads an addon XML file) is deliberately
     * overridden to a no-op so the double needs no config on disk, and ID
     * extraction delegates to the real YouTube extractor.
     *
     * @param   array<int,array{videoId:string,title:string}>  $videos  Page to serve.
     *
     * @return  CWMAddon
     */
    private function fakeAddon(array $videos): CWMAddon
    {
        return new class ($videos) extends CWMAddon {
            /** @var array<int,array{videoId:string,title:string}> */
            private array $videos;

            public function __construct(array $videos)
            {
                $this->videos = $videos;
            }

            public function supportsPlaylists(): bool
            {
                return true;
            }

            public function extractRemoteMediaId(string $text): ?string
            {
                return CWMAddonYoutube::extractMediaId($text);
            }

            public function fetchRemotePlaylistItems(Input $input): array
            {
                return ['success' => true, 'videos' => $this->videos, 'nextPageToken' => ''];
            }

            public function renderGeneral(object $media_form, bool $new): string
            {
                return '';
            }

            public function render(object $media_form, bool $new): string
            {
                return '';
            }

            protected function upload(Input $data): mixed
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
     * Insert a study in the seeded series plus a media file pointing at a URL.
     *
     * @param   string  $url    Media URL stored in params.filename.
     * @param   string  $title  Study title.
     *
     * @return  int  The media file ID.
     */
    private function insertSeriesMedia(string $url, string $title): int
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
            'server_id' => $this->serverId,
            'metadata'  => '',
            'params'    => json_encode(['filename' => $url]),
            'published' => 1,
            'access'    => 1,
            'language'  => '*',
        ];

        $this->db->insertObject('#__bsms_mediafiles', $media);

        return (int) $this->db->insertid();
    }

    /**
     * Insert a playlist on the seeded server.
     *
     * @return  int  Playlist ID.
     */
    private function insertPlaylist(): int
    {
        $row = (object) [
            'title'              => 'Test Playlist',
            'alias'              => 'test-playlist',
            'remote_playlist_id' => 'PL_TEST_123',
            'server_id'          => $this->serverId,
            'series_id'          => $this->seriesId,
            'sync_enabled'       => 1,
            'writeback_enabled'  => 0,
            'params'             => '{}',
            'language'           => '*',
            'published'          => 1,
            'access'             => 1,
        ];

        $this->db->insertObject('#__bsms_playlists', $row);

        return (int) $this->db->insertid();
    }

    /**
     * Seed a junction row. Written with a raw statement rather than insertObject()
     * because a null mediafile_id — the unmatched-import case these tests turn on —
     * is exactly what insertObject() drops.
     *
     * @param   int       $playlistId   Playlist ID.
     * @param   string    $videoId      Remote video ID.
     * @param   int|null  $mediafileId  Linked media file ID, or null when unmatched.
     * @param   int       $position     Position in the playlist.
     * @param   string    $title        Stored title.
     * @param   string    $source       Row provenance.
     *
     * @return  void
     */
    private function insertJunction(
        int $playlistId,
        string $videoId,
        ?int $mediafileId,
        int $position,
        string $title,
        string $source = 'remote'
    ): void {
        $this->db->setQuery(
            'INSERT INTO ' . $this->db->quoteName('#__bsms_playlist_items')
            . ' (' . implode(', ', $this->db->quoteName(
                ['playlist_id', 'mediafile_id', 'remote_video_id', 'title', 'position', 'source', 'created']
            )) . ')'
            . ' VALUES (' . (int) $playlistId . ', ' . ($mediafileId === null ? 'NULL' : (int) $mediafileId) . ', '
            . $this->db->quote($videoId) . ', ' . $this->db->quote($title) . ', ' . (int) $position . ', '
            . $this->db->quote($source) . ', ' . $this->db->quote(self::SEEDED_CREATED) . ')'
        )->execute();
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
            $this->db->createQuery()
                ->select('*')
                ->from($this->db->quoteName('#__bsms_playlist_items'))
                ->where($this->db->quoteName('playlist_id') . ' = ' . (int) $playlistId)
                ->order($this->db->quoteName('position') . ' ASC')
        )->loadObjectList() ?: [];
    }
}
