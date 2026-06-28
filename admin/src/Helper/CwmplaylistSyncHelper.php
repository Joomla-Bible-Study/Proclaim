<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Administrator\Table\CwmplaylistTable;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Input\Input;

/**
 * Playlist sync engine (#1273).
 *
 * Bulk-imports a YouTube channel's playlists as first-class Proclaim Playlist
 * entities, then reconciles each playlist's videos against existing
 * #__bsms_mediafiles so we link to media we already have rather than creating
 * duplicates. The reconciliation key is the YouTube video ID, extracted from
 * each media file's stored URL (params.filename).
 *
 * This engine is deliberately UI-agnostic: the Playlists toolbar action and the
 * scheduled task (#1273 phase 3) both call the same code path.
 *
 * @package  Proclaim.Admin
 * @since    __DEPLOY_VERSION__
 */
final class CwmplaylistSyncHelper
{
    /**
     * Page size for the YouTube playlistItems pagination (API max is 50).
     *
     * @var integer
     * @since __DEPLOY_VERSION__
     */
    private const PAGE_SIZE = 50;

    /**
     * Import every playlist from one (or all) YouTube server(s) and reconcile
     * their videos against the local media library.
     *
     * This is the single entry point shared by the "Import from YouTube" toolbar
     * action and the scheduled task. Passing 0 sweeps every published YouTube
     * server.
     *
     * The scheduled task passes $discoverNew = false so it only refreshes
     * playlists that were already imported by a human — it never silently
     * creates a channel's playlists for the first time. The interactive toolbar
     * action passes true (the deliberate initial import).
     *
     * @param   integer  $serverId     Server ID to import, or 0 for all YouTube servers.
     * @param   boolean  $discoverNew  Whether to create playlists not seen locally yet.
     *
     * @return  array{servers:int, playlistsCreated:int, playlistsUpdated:int, playlistsSkipped:int, itemsMatched:int, itemsUnmatched:int, conflicts:string[], errors:string[]}
     *
     * @throws  \Exception
     * @since   __DEPLOY_VERSION__
     */
    public static function import(int $serverId = 0, bool $discoverNew = true): array
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // The capability registry (CWMAddon::supportsPlaylists) is the single
        // source of truth for which servers can host playlists — so the engine
        // never hardcodes "youtube" and a new platform addon opts in by simply
        // returning true from supportsPlaylists().
        $typeById = [];

        foreach (CWMAddon::getPlaylistCapableServers() as $srv) {
            $typeById[(int) $srv['id']] = (string) $srv['type'];
        }

        $serverIds = $serverId > 0 ? [$serverId] : array_keys($typeById);

        $stats = [
            'servers'          => 0,
            'playlistsCreated' => 0,
            'playlistsUpdated' => 0,
            'playlistsSkipped' => 0,
            'itemsMatched'     => 0,
            'itemsUnmatched'   => 0,
            'conflicts'        => [],
            'errors'           => [],
        ];

        if ($serverIds === []) {
            $stats['errors'][] = 'No published playlist-capable server configured.';

            return $stats;
        }

        // Build the local video-id -> mediafile-id map once for the whole run.
        $videoMap = self::buildLocalVideoMap($db);

        foreach ($serverIds as $sid) {
            $sid  = (int) $sid;
            $type = $typeById[$sid] ?? null;

            if ($type === null) {
                $stats['errors'][] = \sprintf('Server %d does not support playlists.', $sid);

                continue;
            }

            $stats['servers']++;

            $addon    = CWMAddon::getInstance($type);
            $imported = self::importChannelPlaylists($db, $addon, $sid, $discoverNew);

            if ($imported['error'] !== null) {
                $stats['errors'][] = \sprintf('Server %d: %s', $sid, $imported['error']);

                continue;
            }

            $stats['playlistsCreated'] += $imported['created'];
            $stats['playlistsUpdated'] += $imported['updated'];
            $stats['playlistsSkipped'] += $imported['skipped'];
            $stats['conflicts']         = array_merge($stats['conflicts'], $imported['conflicts']);

            foreach ($imported['playlistIds'] as $pid) {
                $reconciled = self::reconcilePlaylist($db, $addon, $pid, $videoMap);

                if ($reconciled['error'] !== null) {
                    $stats['errors'][] = \sprintf('Playlist %d: %s', $pid, $reconciled['error']);

                    continue;
                }

                $stats['itemsMatched'] += $reconciled['matched'];
                $stats['itemsUnmatched'] += $reconciled['unmatched'];
            }
        }

        return $stats;
    }

    /**
     * Fetch a channel's playlists and upsert each as a Proclaim Playlist.
     *
     * Existing playlists are matched on (remote_playlist_id, server_id).
     *
     * Conflict gate: the YouTube title is only re-applied to an existing playlist
     * when it actually changed AND the local row was NOT edited by a human since
     * the last sync (modified <= last_sync). When both sides diverged, the local
     * value is kept and the divergence is reported as a conflict rather than
     * silently overwritten. Playlists opted out of sync (sync_enabled = 0) are
     * skipped entirely. Truly bidirectional resolution arrives with the phase-5
     * OAuth write-back; until then read-sync always prefers the local edit.
     *
     * @param   DatabaseInterface  $db           Database driver.
     * @param   CWMAddon           $addon        YouTube addon instance.
     * @param   integer            $serverId     Server ID to import from.
     * @param   boolean            $discoverNew  Whether to create not-yet-local playlists.
     *
     * @return  array{created:int, updated:int, skipped:int, playlistIds:int[], conflicts:string[], error:?string}
     *
     * @throws  \Exception
     * @since   __DEPLOY_VERSION__
     */
    public static function importChannelPlaylists(DatabaseInterface $db, CWMAddon $addon, int $serverId, bool $discoverNew = true): array
    {
        $out = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'playlistIds' => [], 'conflicts' => [], 'error' => null];

        $response = $addon->fetchRemotePlaylists(new Input(['server_id' => $serverId]));

        if (empty($response['success'])) {
            $out['error'] = $response['error'] ?? 'Unknown error fetching playlists.';

            return $out;
        }

        $now = Factory::getDate()->toSql();

        foreach ($response['playlists'] as $pl) {
            $remoteId = (string) ($pl['playlistId'] ?? '');

            if ($remoteId === '') {
                continue;
            }

            $existingId = self::findPlaylistId($db, $remoteId, $serverId);
            $isNew      = $existingId === 0;

            // The scheduled task refreshes existing playlists only; a brand-new
            // channel playlist must be added by the deliberate "Import from
            // YouTube" action first.
            if ($isNew && !$discoverNew) {
                $out['skipped']++;

                continue;
            }

            $newTitle = (string) ($pl['title'] ?? $remoteId);
            $table    = new CwmplaylistTable($db);

            if ($isNew) {
                $data = [
                    'title'              => $newTitle,
                    'remote_playlist_id' => $remoteId,
                    'server_id'          => $serverId,
                    'last_sync'          => $now,
                    // Sensible defaults for a freshly imported playlist; all
                    // user-editable afterwards.
                    'published'    => 1,
                    'access'       => 1,
                    'language'     => '*',
                    'sync_enabled' => 1,
                    'params'       => '{}',
                    'description'  => (string) ($pl['description'] ?? ''),
                ];
            } else {
                $table->load($existingId);

                // Respect a per-playlist opt-out from syncing.
                if ((int) $table->sync_enabled !== 1) {
                    $out['skipped']++;

                    continue;
                }

                $data = [
                    'remote_playlist_id' => $remoteId,
                    'server_id'          => $serverId,
                    'last_sync'          => $now,
                ];

                // Conflict gate + "only write if changed".
                if ($newTitle !== (string) $table->title) {
                    if (self::isLocallyEdited($table)) {
                        $out['conflicts'][] = \sprintf(
                            'Playlist "%s" (#%d): kept local title; YouTube title "%s" not applied.',
                            (string) $table->title,
                            $existingId,
                            $newTitle
                        );
                    } else {
                        $data['title'] = $newTitle;
                    }
                }
            }

            if (!$table->bind($data) || !$table->check() || !$table->store()) {
                $out['error'] = $table->getError() ?: 'Failed to store playlist.';

                return $out;
            }

            $out['playlistIds'][] = (int) $table->id;
            $isNew ? $out['created']++ : $out['updated']++;
        }

        return $out;
    }

    /**
     * Decide whether a playlist row was edited by a human since its last sync.
     *
     * The engine never bumps `modified` when it stores (only the admin form's
     * model does), so `modified > last_sync` reliably means a person changed the
     * row after the last sync ran — the signal the conflict gate uses to avoid
     * clobbering local edits.
     *
     * @param   CwmplaylistTable  $table  A loaded playlist row.
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function isLocallyEdited(CwmplaylistTable $table): bool
    {
        if (empty($table->last_sync) || empty($table->modified)) {
            return false;
        }

        return strtotime((string) $table->modified) > strtotime((string) $table->last_sync);
    }

    /**
     * Reconcile a single playlist's videos against the local media library.
     *
     * Fetches every video in the playlist (paginated), matches each YouTube
     * video ID against the supplied local map, and upserts a junction row per
     * video. Junction rows for videos no longer in the playlist are removed.
     *
     * @param   DatabaseInterface              $db          Database driver.
     * @param   CWMAddon                       $addon       Playlist-capable addon instance.
     * @param   integer                        $playlistId  Local playlist row ID.
     * @param   array<string,array<string,int>>  $videoMap  type => (videoId => mediafileId), built once per run.
     *
     * @return  array{items:int, matched:int, unmatched:int, error:?string}
     *
     * @throws  \Exception
     * @since   __DEPLOY_VERSION__
     */
    public static function reconcilePlaylist(DatabaseInterface $db, CWMAddon $addon, int $playlistId, array $videoMap): array
    {
        $out = ['items' => 0, 'matched' => 0, 'unmatched' => 0, 'error' => null];

        $playlist = $db->setQuery(
            $db->getQuery(true)
                ->select($db->quoteName(['p.remote_playlist_id', 'p.server_id', 's.type']))
                ->from($db->quoteName('#__bsms_playlists', 'p'))
                ->join('INNER', $db->quoteName('#__bsms_servers', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('p.server_id'))
                ->where($db->quoteName('p.id') . ' = :id')
                ->bind(':id', $playlistId, \Joomla\Database\ParameterType::INTEGER)
        )->loadObject();

        if (!$playlist || $playlist->remote_playlist_id === '') {
            $out['error'] = 'Playlist has no remote playlist ID.';

            return $out;
        }

        $remoteId = (string) $playlist->remote_playlist_id;
        $serverId = (int) $playlist->server_id;
        $typeMap  = $videoMap[(string) $playlist->type] ?? [];
        $now      = Factory::getDate()->toSql();

        $position   = 0;
        $seenVideos = [];
        $pageToken  = '';

        do {
            $response = $addon->fetchRemotePlaylistItems(new Input([
                'server_id'   => $serverId,
                'playlist_id' => $remoteId,
                'page_token'  => $pageToken,
                'max_results' => self::PAGE_SIZE,
            ]));

            if (empty($response['success'])) {
                $out['error'] = $response['error'] ?? 'Unknown error fetching playlist videos.';

                return $out;
            }

            foreach ($response['videos'] as $video) {
                $videoId = (string) ($video['videoId'] ?? '');

                if ($videoId === '') {
                    continue;
                }

                $mediafileId = $typeMap[$videoId] ?? null;
                $mediafileId === null ? $out['unmatched']++ : $out['matched']++;

                self::upsertItem($db, $playlistId, $videoId, (string) ($video['title'] ?? ''), $mediafileId, $position, $now);

                $seenVideos[] = $videoId;
                $position++;
                $out['items']++;
            }

            $pageToken = (string) ($response['nextPageToken'] ?? '');
        } while ($pageToken !== '');

        // Drop junction rows for videos that are no longer in the playlist.
        self::pruneItems($db, $playlistId, $seenVideos);

        return $out;
    }

    /**
     * Link a just-saved media file to the playlists that already contain its video.
     *
     * Called on every media-file save so a YouTube video gets attached to its
     * playlist(s) immediately, without waiting for the scheduled task. This is a
     * LOCAL-ONLY operation — no YouTube API call: it simply backfills the junction
     * rows that a previous import left unmatched (mediafile_id NULL) because the
     * media did not exist yet. If the media file's URL changed, its now-stale
     * links are cleared first. Never throws — a media save must not fail because
     * of playlist bookkeeping.
     *
     * @param   integer  $mediafileId  The saved media file's ID.
     * @param   string   $params       The media file's params JSON (holds filename).
     *
     * @return  integer  Number of junction rows linked to this media file.
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function linkMediafileToPlaylists(int $mediafileId, string $params): int
    {
        if ($mediafileId <= 0 || $params === '') {
            return 0;
        }

        $decoded = json_decode($params, true);

        if (!\is_array($decoded) || empty($decoded['filename'])) {
            return 0;
        }

        // Identify the video using any playlist-capable platform's extractor — a
        // media file's URL is the source of truth, regardless of which server
        // type it is filed under.
        $videoId = self::extractAnyRemoteId((string) $decoded['filename']);

        if ($videoId === null) {
            return 0;
        }

        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            // Clear links that no longer match (the media file's URL changed).
            $db->setQuery(
                $db->getQuery(true)
                    ->update($db->quoteName('#__bsms_playlist_items'))
                    ->set($db->quoteName('mediafile_id') . ' = NULL')
                    ->where($db->quoteName('mediafile_id') . ' = :mid')
                    ->where($db->quoteName('remote_video_id') . ' != :vid')
                    ->bind(':mid', $mediafileId, \Joomla\Database\ParameterType::INTEGER)
                    ->bind(':vid', $videoId, \Joomla\Database\ParameterType::STRING)
            )->execute();

            // Backfill every still-unmatched junction row for this video.
            $db->setQuery(
                $db->getQuery(true)
                    ->update($db->quoteName('#__bsms_playlist_items'))
                    ->set($db->quoteName('mediafile_id') . ' = :mid')
                    ->where($db->quoteName('remote_video_id') . ' = :vid')
                    ->where($db->quoteName('mediafile_id') . ' IS NULL')
                    ->bind(':mid', $mediafileId, \Joomla\Database\ParameterType::INTEGER)
                    ->bind(':vid', $videoId, \Joomla\Database\ParameterType::STRING)
            )->execute();

            return $db->getAffectedRows();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Detach a deleted media file from any playlist junction rows.
     *
     * Leaves the rows in place (the video may still be in the playlist) but nulls
     * the mediafile_id so no row points at a deleted media file. Never throws.
     *
     * @param   integer  $mediafileId  The deleted media file's ID.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function unlinkMediafile(int $mediafileId): void
    {
        if ($mediafileId <= 0) {
            return;
        }

        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            $db->setQuery(
                $db->getQuery(true)
                    ->update($db->quoteName('#__bsms_playlist_items'))
                    ->set($db->quoteName('mediafile_id') . ' = NULL')
                    ->where($db->quoteName('mediafile_id') . ' = :mid')
                    ->bind(':mid', $mediafileId, \Joomla\Database\ParameterType::INTEGER)
            )->execute();
        } catch (\Exception $e) {
            // A delete must not fail because of playlist bookkeeping.
        }
    }

    /**
     * Build a per-platform video-ID => mediafile-ID map from the media library.
     *
     * One sub-map per playlist-capable server type, each built with that
     * platform's own ID extractor — so a YouTube ID and a Vimeo ID never
     * collide. This is the reconciliation index that lets import/sync link to
     * media we already have rather than duplicate it.
     *
     * @param   DatabaseInterface  $db  Database driver.
     *
     * @return  array<string,array<string,int>>  type => (videoId => mediafileId).
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function buildLocalVideoMap(DatabaseInterface $db): array
    {
        $types = array_values(array_unique(array_map(
            static fn ($srv) => (string) $srv['type'],
            CWMAddon::getPlaylistCapableServers()
        )));

        if ($types === []) {
            return [];
        }

        // Scan all media with a URL once, then build one sub-map per platform by
        // running that platform's own extractor. A YouTube URL filed under any
        // server type still matches the YouTube map (the extractor recognises the
        // URL, not the server record); a platform's extractor returns null for
        // other platforms' URLs, so the maps stay disjoint.
        $rows = $db->setQuery(
            $db->getQuery(true)
                ->select($db->quoteName(['id', 'params']))
                ->from($db->quoteName('#__bsms_mediafiles'))
                ->where($db->quoteName('params') . ' LIKE ' . $db->quote('%http%'))
        )->loadAssocList() ?: [];

        $map = [];

        foreach ($types as $type) {
            $addon       = CWMAddon::getInstance($type);
            $map[$type]  = self::extractVideoMapFromRows($rows, [$addon, 'extractRemoteMediaId']);
        }

        return $map;
    }

    /**
     * Pure mapper: turn media-file rows into a videoId => mediafileId map.
     *
     * Extracted as a static, dependency-free method so the matching core can be
     * unit-tested without a database. Each row must have 'id' and 'params'
     * (a JSON string whose 'filename' holds the media URL). The platform's ID
     * extractor is injected so this stays platform-neutral and testable.
     *
     * @param   array<int,array{id:mixed,params:mixed}>  $rows       Media-file rows.
     * @param   callable                                 $extractor  fn(string $url): ?string.
     *
     * @return  array<string,int>  videoId => mediafileId (first match wins).
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function extractVideoMapFromRows(array $rows, callable $extractor): array
    {
        $map = [];

        foreach ($rows as $row) {
            $params = $row['params'] ?? '';

            if (!\is_string($params) || $params === '') {
                continue;
            }

            $decoded = json_decode($params, true);

            if (!\is_array($decoded) || empty($decoded['filename'])) {
                continue;
            }

            $videoId = $extractor((string) $decoded['filename']);

            if ($videoId === null || isset($map[$videoId])) {
                continue;
            }

            $map[$videoId] = (int) $row['id'];
        }

        return $map;
    }

    /**
     * Extract a remote media ID from a URL using any playlist-capable platform.
     *
     * Tries each capable platform's extractor and returns the first match. Used
     * by on-save linking, where we only have the media URL — not which platform
     * it belongs to. Platform extractors recognise only their own URLs, so the
     * first non-null result is unambiguous.
     *
     * @param   string  $url  The media URL.
     *
     * @return  string|null  The remote media ID, or null if no platform matched.
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function extractAnyRemoteId(string $url): ?string
    {
        $types = array_unique(array_map(
            static fn ($srv) => (string) $srv['type'],
            CWMAddon::getPlaylistCapableServers()
        ));

        foreach ($types as $type) {
            try {
                $videoId = CWMAddon::getInstance($type)->extractRemoteMediaId($url);
            } catch (\RuntimeException) {
                continue;
            }

            if ($videoId !== null) {
                return $videoId;
            }
        }

        return null;
    }

    /**
     * Insert or update a junction row for a playlist/video pair.
     *
     * @param   DatabaseInterface  $db           Database driver.
     * @param   integer            $playlistId   Playlist row ID.
     * @param   string             $videoId      YouTube video ID.
     * @param   string             $title        Video title.
     * @param   integer|null       $mediafileId  Matched media file ID, or null.
     * @param   integer            $position     Zero-based position in the playlist.
     * @param   string             $now          SQL timestamp.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function upsertItem(DatabaseInterface $db, int $playlistId, string $videoId, string $title, ?int $mediafileId, int $position, string $now): void
    {
        $existingId = (int) ($db->setQuery(
            $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__bsms_playlist_items'))
                ->where($db->quoteName('playlist_id') . ' = :pid')
                ->where($db->quoteName('remote_video_id') . ' = :vid')
                ->bind(':pid', $playlistId, \Joomla\Database\ParameterType::INTEGER)
                ->bind(':vid', $videoId, \Joomla\Database\ParameterType::STRING)
        )->loadResult() ?? 0);

        $item = (object) [
            'playlist_id'     => $playlistId,
            'mediafile_id'    => $mediafileId,
            'remote_video_id' => $videoId,
            'title'           => $title,
            'position'        => $position,
        ];

        if ($existingId > 0) {
            $item->id = $existingId;
            $db->updateObject('#__bsms_playlist_items', $item, 'id');

            return;
        }

        $item->created = $now;
        $db->insertObject('#__bsms_playlist_items', $item);
    }

    /**
     * Remove junction rows for videos that are no longer in the playlist.
     *
     * @param   DatabaseInterface  $db          Database driver.
     * @param   integer            $playlistId  Playlist row ID.
     * @param   string[]           $keepVideos  Video IDs to retain.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function pruneItems(DatabaseInterface $db, int $playlistId, array $keepVideos): void
    {
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__bsms_playlist_items'))
            ->where($db->quoteName('playlist_id') . ' = :pid')
            ->bind(':pid', $playlistId, \Joomla\Database\ParameterType::INTEGER);

        if ($keepVideos !== []) {
            $quoted = array_map([$db, 'quote'], array_unique($keepVideos));
            $query->where($db->quoteName('remote_video_id') . ' NOT IN (' . implode(',', $quoted) . ')');
        }

        $db->setQuery($query)->execute();
    }

    /**
     * Find an existing playlist by remote ID and server.
     *
     * @param   DatabaseInterface  $db        Database driver.
     * @param   string             $remoteId  YouTube playlist ID.
     * @param   integer            $serverId  Server ID.
     *
     * @return  integer  The playlist row ID, or 0 if none.
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function findPlaylistId(DatabaseInterface $db, string $remoteId, int $serverId): int
    {
        return (int) ($db->setQuery(
            $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__bsms_playlists'))
                ->where($db->quoteName('remote_playlist_id') . ' = :rid')
                ->where($db->quoteName('server_id') . ' = :sid')
                ->bind(':rid', $remoteId, \Joomla\Database\ParameterType::STRING)
                ->bind(':sid', $serverId, \Joomla\Database\ParameterType::INTEGER)
        )->loadResult() ?? 0);
    }
}
