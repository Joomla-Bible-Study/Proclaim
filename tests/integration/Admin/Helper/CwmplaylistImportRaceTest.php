<?php

/**
 * Integration tests for CwmplaylistSyncHelper::importChannelPlaylists()'s
 * duplicate-playlist handling.
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
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1560.
 *
 * importChannelPlaylists() looks a playlist up and inserts when absent, with
 * nothing between the two steps -- so a scheduled sync overlapping a manual
 * "Import from YouTube" could have both runs pass the lookup and both insert a
 * row for the same remote playlist. uq_server_remote_playlist now makes the
 * second insert fail; these tests pin the behaviour that failure produces, and
 * the constraint's own boundaries.
 *
 * Each test runs inside a transaction that is rolled back, so nothing persists.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmplaylistSyncHelper::class)]
class CwmplaylistImportRaceTest extends IntegrationTestCase
{
    /**
     * @var  DatabaseDriver|null
     */
    private ?DatabaseDriver $db = null;

    /**
     * @var  int
     */
    private int $serverId = 0;

    /**
     * Factory::$language as it was before silenceDateLanguageWarnings().
     *
     * @var  mixed
     */
    private mixed $previousFactoryLanguage = null;

    /**
     * Server rows this test created, for the explicit cleanup in tearDown().
     *
     * @var  int[]
     */
    private array $serverIds = [];

    /**
     * @return  void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        // importChannelPlaylists() stamps last_sync via Factory::getDate().
        $this->previousFactoryLanguage = $this->silenceDateLanguageWarnings();

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart();

        $this->serverId = $this->insertServer();
    }

    /**
     * Remove everything this test created.
     *
     * The surrounding transaction is not enough on its own: Table::store()
     * creates the row's #__assets entry, and the nested-set bookkeeping there
     * runs LOCK TABLES, which MySQL treats as an implicit COMMIT. Anything
     * written before that point is already durable when the rollback runs, so
     * the rows have to be deleted by hand.
     *
     * @return  void
     */
    private function purgeCreatedRows(): void
    {
        if ($this->serverIds === []) {
            return;
        }

        $servers = implode(',', array_map('intval', $this->serverIds));

        $ids = $this->db->setQuery(
            'SELECT ' . $this->db->quoteName('id') . ' FROM ' . $this->db->quoteName('#__bsms_playlists')
            . ' WHERE ' . $this->db->quoteName('server_id') . ' IN (' . $servers . ')'
        )->loadColumn() ?: [];

        foreach ($ids as $id) {
            $this->db->setQuery(
                'DELETE FROM ' . $this->db->quoteName('#__assets')
                . ' WHERE ' . $this->db->quoteName('name') . ' = '
                . $this->db->quote('com_proclaim.playlist.' . (int) $id)
            )->execute();
        }

        $this->db->setQuery(
            'DELETE FROM ' . $this->db->quoteName('#__bsms_playlists')
            . ' WHERE ' . $this->db->quoteName('server_id') . ' IN (' . $servers . ')'
        )->execute();

        $this->db->setQuery(
            'DELETE FROM ' . $this->db->quoteName('#__bsms_servers')
            . ' WHERE ' . $this->db->quoteName('id') . ' IN (' . $servers . ')'
        )->execute();

        $this->serverIds = [];
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
                $this->purgeCreatedRows();
            } catch (\Throwable) {
                // Best effort; the assertions below already ran.
            }
        }

        Factory::$language = $this->previousFactoryLanguage;

        parent::tearDown();
    }

    #[TestDox('A remote playlist already present is adopted, not inserted a second time')]
    public function testExistingRemotePlaylistIsNotDuplicated(): void
    {
        $existingId = $this->insertPlaylist('PLraced', $this->serverId, 'Created by the other run');

        $addon  = $this->fakeAddon([['playlistId' => 'PLraced', 'title' => 'Remote title']]);
        $result = CwmplaylistSyncHelper::importChannelPlaylists($this->db, $addon, $this->serverId);

        $this->assertNull($result['error'], 'Import must not fail over an already-present playlist');
        $this->assertSame(0, $result['created'], 'Nothing new should be created');
        $this->assertContains($existingId, $result['playlistIds'], 'The import must resolve to the existing row');
        $this->assertSame(
            1,
            $this->countPlaylists('PLraced'),
            'Exactly one row may exist for a (remote playlist, server) pair'
        );
    }

    #[TestDox('Importing the same channel twice creates the playlist once')]
    public function testRepeatedImportCreatesOnlyOneRow(): void
    {
        $addon = $this->fakeAddon([['playlistId' => 'PLfresh', 'title' => 'Fresh playlist']]);

        $first  = CwmplaylistSyncHelper::importChannelPlaylists($this->db, $addon, $this->serverId);
        $second = CwmplaylistSyncHelper::importChannelPlaylists($this->db, $addon, $this->serverId);

        $this->assertNull($first['error']);
        $this->assertNull($second['error']);
        $this->assertSame(1, $first['created'], 'First import creates the row');
        $this->assertSame(0, $second['created'], 'Second import must find it rather than insert again');
        $this->assertSame(1, $this->countPlaylists('PLfresh'));
        $this->assertSame(
            $first['playlistIds'],
            $second['playlistIds'],
            'Both runs must resolve to the same playlist row'
        );
    }

    #[TestDox('uq_server_remote_playlist rejects a duplicate (remote playlist, server) pair')]
    public function testConstraintRejectsDuplicatePair(): void
    {
        $this->insertPlaylist('PLunique', $this->serverId, 'First');

        $this->expectException(\RuntimeException::class);
        $this->insertPlaylist('PLunique', $this->serverId, 'Second');
    }

    #[TestDox('The constraint still allows several hand-made playlists, which carry no remote ID')]
    public function testConstraintAllowsManuallyCreatedPlaylists(): void
    {
        // remote_playlist_id is NOT NULL DEFAULT '' and the edit form does not
        // require it, so every hand-made playlist shares the same value. A plain
        // UNIQUE (remote_playlist_id, server_id) would permit only one per server;
        // the generated column maps '' to NULL to keep them unconstrained.
        $first  = $this->insertPlaylist('', $this->serverId, 'Hand-made one');
        $second = $this->insertPlaylist('', $this->serverId, 'Hand-made two');

        $this->assertNotSame($first, $second);
        $this->assertSame(2, $this->countPlaylists(''));
    }

    #[TestDox('The constraint still allows the same remote playlist under a different server')]
    public function testConstraintIsScopedPerServer(): void
    {
        $otherServerId = $this->insertServer();

        $this->insertPlaylist('PLshared', $this->serverId, 'On server A');
        $this->insertPlaylist('PLshared', $otherServerId, 'On server B');

        $this->assertSame(1, $this->countPlaylists('PLshared'));
        $this->assertSame(1, $this->countPlaylists('PLshared', $otherServerId));
    }

    /**
     * @param   string    $remoteId  Remote playlist ID ('' for a hand-made playlist).
     * @param   int|null  $serverId  Server to scope to; defaults to this test's server.
     *
     * @return  int  Number of playlist rows carrying that remote ID on that server.
     */
    private function countPlaylists(string $remoteId, ?int $serverId = null): int
    {
        // Scoped to one server on purpose: the constraint is per-server, so a
        // count across servers would not answer the question being asked and
        // would drift with whatever else the database happens to hold.
        $serverId ??= $this->serverId;

        return (int) $this->db->setQuery(
            $this->db->createQuery()
                ->select('COUNT(*)')
                ->from($this->db->quoteName('#__bsms_playlists'))
                ->where($this->db->quoteName('remote_playlist_id') . ' = :rid')
                ->where($this->db->quoteName('server_id') . ' = :sid')
                ->bind(':rid', $remoteId, \Joomla\Database\ParameterType::STRING)
                ->bind(':sid', $serverId, \Joomla\Database\ParameterType::INTEGER)
        )->loadResult();
    }

    /**
     * @param   string  $remoteId  Remote playlist ID.
     * @param   int     $serverId  Server ID.
     * @param   string  $title     Playlist title.
     *
     * @return  int  The new playlist row ID.
     */
    private function insertPlaylist(string $remoteId, int $serverId, string $title): int
    {
        $row = (object) [
            'title'              => $title,
            'alias'              => 'pl-' . uniqid('', true),
            'remote_playlist_id' => $remoteId,
            'server_id'          => $serverId,
            'published'          => 1,
            'access'             => 1,
            'language'           => '*',
            'sync_enabled'       => 1,
            'params'             => '{}',
        ];

        $this->db->insertObject('#__bsms_playlists', $row);

        return (int) $this->db->insertid();
    }

    /**
     * @return  int  Server ID.
     */
    private function insertServer(): int
    {
        $row = (object) [
            'server_name' => 'Test YouTube ' . uniqid('', true),
            'published'   => 1,
            'access'      => 1,
            'type'        => 'youtube',
            'params'      => '{}',
            'media'       => '{}',
        ];

        $this->db->insertObject('#__bsms_servers', $row);

        $id                = (int) $this->db->insertid();
        $this->serverIds[] = $id;

        return $id;
    }

    /**
     * Build a playlist-capable addon double that reports a fixed remote playlist
     * list, so no test ever reaches a real YouTube account.
     *
     * @param   array<int,array{playlistId:string,title:string}>  $playlists  Remote playlists to report.
     *
     * @return  CWMAddon
     */
    private function fakeAddon(array $playlists): CWMAddon
    {
        return new class ($playlists) extends CWMAddon {
            /** @var array<int,array{playlistId:string,title:string}> */
            private array $playlists;

            public function __construct(array $playlists = [])
            {
                $this->playlists = $playlists;
            }

            public function supportsPlaylists(): bool
            {
                return true;
            }

            public function supportsPlaylistWriteback(): bool
            {
                return false;
            }

            public function fetchRemotePlaylists(\Joomla\Input\Input $data): array
            {
                return ['success' => true, 'playlists' => $this->playlists];
            }

            public function extractRemoteMediaId(string $text): ?string
            {
                return CWMAddonYoutube::extractMediaId($text);
            }

            public function renderGeneral(object $media_form, bool $new): string
            {
                return '';
            }

            public function render(object $media_form, bool $new): string
            {
                return '';
            }

            protected function upload(\Joomla\Input\Input $data): mixed
            {
                return null;
            }
        };
    }
}
