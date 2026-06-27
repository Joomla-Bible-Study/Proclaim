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
use CWM\Component\Proclaim\Administrator\Addons\Servers\Youtube\CWMAddonYoutube;
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
     * This is the single entry point shared by the toolbar action and the
     * scheduled task. Passing 0 sweeps every published YouTube server.
     *
     * @param   integer  $serverId  Server ID to import, or 0 for all YouTube servers.
     *
     * @return  array{servers:int, playlistsCreated:int, playlistsUpdated:int, itemsMatched:int, itemsUnmatched:int, errors:string[]}
     *
     * @throws  \Exception
     * @since   __DEPLOY_VERSION__
     */
    public static function import(int $serverId = 0): array
    {
        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $servers = $serverId > 0 ? [$serverId] : self::getYoutubeServerIds($db);

        $stats = [
            'servers'          => 0,
            'playlistsCreated' => 0,
            'playlistsUpdated' => 0,
            'itemsMatched'     => 0,
            'itemsUnmatched'   => 0,
            'errors'           => [],
        ];

        if ($servers === []) {
            $stats['errors'][] = 'No published YouTube server configured.';

            return $stats;
        }

        // Build the local video-id -> mediafile-id map once for the whole run.
        $videoMap = self::buildLocalVideoMap($db);

        /** @var CWMAddonYoutube $addon */
        $addon = CWMAddon::getInstance('youtube');

        foreach ($servers as $sid) {
            $stats['servers']++;

            $imported = self::importChannelPlaylists($db, $addon, $sid);

            if ($imported['error'] !== null) {
                $stats['errors'][] = \sprintf('Server %d: %s', $sid, $imported['error']);

                continue;
            }

            $stats['playlistsCreated'] += $imported['created'];
            $stats['playlistsUpdated'] += $imported['updated'];

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
     * Existing playlists are matched on (youtube_playlist_id, server_id). The
     * YouTube-authoritative fields (title, description) are refreshed; user-owned
     * fields (series_id, default_settings, access, published) are preserved.
     *
     * @param   DatabaseInterface  $db        Database driver.
     * @param   CWMAddonYoutube    $addon     YouTube addon instance.
     * @param   integer            $serverId  Server ID to import from.
     *
     * @return  array{created:int, updated:int, playlistIds:int[], error:?string}
     *
     * @throws  \Exception
     * @since   __DEPLOY_VERSION__
     */
    public static function importChannelPlaylists(DatabaseInterface $db, CWMAddonYoutube $addon, int $serverId): array
    {
        $out = ['created' => 0, 'updated' => 0, 'playlistIds' => [], 'error' => null];

        $response = $addon->fetchChannelPlaylists(new Input(['server_id' => $serverId]));

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

            $table = new CwmplaylistTable($db);
            $isNew = $existingId === 0;

            if (!$isNew) {
                $table->load($existingId);
            }

            $data = [
                'title'               => (string) ($pl['title'] ?? $remoteId),
                'youtube_playlist_id' => $remoteId,
                'server_id'           => $serverId,
                'last_sync'           => $now,
            ];

            if ($isNew) {
                // Sensible defaults for a freshly imported playlist; everything
                // here is user-editable afterwards.
                $data['published']    = 1;
                $data['access']       = 1;
                $data['language']     = '*';
                $data['sync_enabled'] = 1;
                $data['params']       = '{}';
                $data['description']  = (string) ($pl['description'] ?? '');
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
     * Reconcile a single playlist's videos against the local media library.
     *
     * Fetches every video in the playlist (paginated), matches each YouTube
     * video ID against the supplied local map, and upserts a junction row per
     * video. Junction rows for videos no longer in the playlist are removed.
     *
     * @param   DatabaseInterface  $db          Database driver.
     * @param   CWMAddonYoutube    $addon       YouTube addon instance.
     * @param   integer            $playlistId  Local playlist row ID.
     * @param   array<string,int>  $videoMap    videoId => mediafileId map (built once per run).
     *
     * @return  array{items:int, matched:int, unmatched:int, error:?string}
     *
     * @throws  \Exception
     * @since   __DEPLOY_VERSION__
     */
    public static function reconcilePlaylist(DatabaseInterface $db, CWMAddonYoutube $addon, int $playlistId, array $videoMap): array
    {
        $out = ['items' => 0, 'matched' => 0, 'unmatched' => 0, 'error' => null];

        $playlist = $db->setQuery(
            $db->getQuery(true)
                ->select($db->quoteName(['youtube_playlist_id', 'server_id']))
                ->from($db->quoteName('#__bsms_playlists'))
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':id', $playlistId, \Joomla\Database\ParameterType::INTEGER)
        )->loadObject();

        if (!$playlist || $playlist->youtube_playlist_id === '') {
            $out['error'] = 'Playlist has no remote YouTube playlist ID.';

            return $out;
        }

        $remoteId = (string) $playlist->youtube_playlist_id;
        $serverId = (int) $playlist->server_id;
        $now      = Factory::getDate()->toSql();

        $position   = 0;
        $seenVideos = [];
        $pageToken  = '';

        do {
            $response = $addon->fetchPlaylistVideos(new Input([
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

                $mediafileId = $videoMap[$videoId] ?? null;
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
     * Build a YouTube-video-ID => mediafile-ID map from the existing media library.
     *
     * Reads every media file's stored URL (params.filename) and extracts the
     * YouTube video ID from it. This is the reconciliation index that lets bulk
     * import link to media we already have rather than duplicate it.
     *
     * @param   DatabaseInterface  $db  Database driver.
     *
     * @return  array<string,int>  videoId => mediafileId (first match wins).
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function buildLocalVideoMap(DatabaseInterface $db): array
    {
        $rows = $db->setQuery(
            $db->getQuery(true)
                ->select($db->quoteName(['id', 'params']))
                ->from($db->quoteName('#__bsms_mediafiles'))
                ->where($db->quoteName('params') . ' LIKE ' . $db->quote('%youtu%'))
        )->loadAssocList();

        return self::extractVideoMapFromRows($rows ?: []);
    }

    /**
     * Pure mapper: turn media-file rows into a videoId => mediafileId map.
     *
     * Extracted as a static, dependency-free method so the matching core can be
     * unit-tested without a database. Each row must have 'id' and 'params'
     * (a JSON string whose 'filename' holds the media URL).
     *
     * @param   array<int,array{id:mixed,params:mixed}>  $rows  Media-file rows.
     *
     * @return  array<string,int>  videoId => mediafileId (first match wins).
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function extractVideoMapFromRows(array $rows): array
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

            $videoId = CWMAddonYoutube::extractMediaId((string) $decoded['filename']);

            if ($videoId === null || isset($map[$videoId])) {
                continue;
            }

            $map[$videoId] = (int) $row['id'];
        }

        return $map;
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
                ->where($db->quoteName('youtube_video_id') . ' = :vid')
                ->bind(':pid', $playlistId, \Joomla\Database\ParameterType::INTEGER)
                ->bind(':vid', $videoId, \Joomla\Database\ParameterType::STRING)
        )->loadResult() ?? 0);

        $item = (object) [
            'playlist_id'      => $playlistId,
            'mediafile_id'     => $mediafileId,
            'youtube_video_id' => $videoId,
            'title'            => $title,
            'position'         => $position,
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
            $query->where($db->quoteName('youtube_video_id') . ' NOT IN (' . implode(',', $quoted) . ')');
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
                ->where($db->quoteName('youtube_playlist_id') . ' = :rid')
                ->where($db->quoteName('server_id') . ' = :sid')
                ->bind(':rid', $remoteId, \Joomla\Database\ParameterType::STRING)
                ->bind(':sid', $serverId, \Joomla\Database\ParameterType::INTEGER)
        )->loadResult() ?? 0);
    }

    /**
     * Get the IDs of all published YouTube-type servers.
     *
     * @param   DatabaseInterface  $db  Database driver.
     *
     * @return  int[]
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function getYoutubeServerIds(DatabaseInterface $db): array
    {
        $ids = $db->setQuery(
            $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__bsms_servers'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('youtube'))
                ->where($db->quoteName('published') . ' = 1')
        )->loadColumn();

        return array_map('intval', $ids ?: []);
    }
}
