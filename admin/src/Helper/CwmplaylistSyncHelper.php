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
use Joomla\Database\ParameterType;
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
 * @since    10.3.3
 */
final class CwmplaylistSyncHelper
{
    /**
     * Page size for the YouTube playlistItems pagination (API max is 50).
     *
     * @var integer
     * @since 10.3.3
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
     * When $pushChanges is true, write-back runs for playlists opted in
     * (writeback_enabled = 1): a locally-authoritative title or description (edited
     * and diverged) is pushed back to the remote — completing the conflict gate
     * instead of only logging it — and videos Proclaim considers members (via the
     * playlist's linked series) are added to the remote playlist if missing.
     * $dryRun reports what would be pushed without calling the remote write API.
     *
     * @param   integer  $serverId     Server ID to import, or 0 for all YouTube servers.
     * @param   boolean  $discoverNew  Whether to create playlists not seen locally yet.
     * @param   boolean  $pushChanges  Whether to push locally-authoritative titles, descriptions + memberships to the remote.
     * @param   boolean  $dryRun       Report would-push changes without writing to the remote.
     *
     * @return  array{servers:int, playlistsCreated:int, playlistsUpdated:int, playlistsSkipped:int, itemsMatched:int, itemsUnmatched:int, titlesPushed:int, titlesWouldPush:string[], descriptionsPushed:int, descriptionsWouldPush:string[], membershipsPushed:int, membershipsWouldPush:string[], membershipsRemoved:int, membershipsWouldRemove:string[], pushErrors:string[], conflicts:string[], errors:string[]}
     *
     * @throws  \Exception
     * @since   10.3.3
     */
    public static function import(int $serverId = 0, bool $discoverNew = true, bool $pushChanges = false, bool $dryRun = false): array
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
            'servers'                => 0,
            'playlistsCreated'       => 0,
            'playlistsUpdated'       => 0,
            'playlistsSkipped'       => 0,
            'itemsMatched'           => 0,
            'itemsUnmatched'         => 0,
            'titlesPushed'           => 0,
            'titlesWouldPush'        => [],
            'descriptionsPushed'     => 0,
            'descriptionsWouldPush'  => [],
            'membershipsPushed'      => 0,
            'membershipsWouldPush'   => [],
            'membershipsRemoved'     => 0,
            'membershipsWouldRemove' => [],
            'pushErrors'             => [],
            'conflicts'              => [],
            'errors'                 => [],
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
            $imported = self::importChannelPlaylists($db, $addon, $sid, $discoverNew, $pushChanges, $dryRun);

            if ($imported['error'] !== null) {
                $stats['errors'][] = \sprintf('Server %d: %s', $sid, $imported['error']);

                continue;
            }

            $stats['playlistsCreated'] += $imported['created'];
            $stats['playlistsUpdated'] += $imported['updated'];
            $stats['playlistsSkipped'] += $imported['skipped'];
            $stats['titlesPushed'] += $imported['titlesPushed'];
            $stats['titlesWouldPush']   = array_merge($stats['titlesWouldPush'], $imported['titlesWouldPush']);
            $stats['descriptionsPushed'] += $imported['descriptionsPushed'];
            $stats['descriptionsWouldPush'] = array_merge($stats['descriptionsWouldPush'], $imported['descriptionsWouldPush']);
            $stats['pushErrors']            = array_merge($stats['pushErrors'], $imported['pushErrors']);
            $stats['conflicts']             = array_merge($stats['conflicts'], $imported['conflicts']);

            foreach ($imported['playlistIds'] as $pid) {
                $reconciled = self::reconcilePlaylist($db, $addon, $pid, $videoMap);

                if ($reconciled['error'] !== null) {
                    $stats['errors'][] = \sprintf('Playlist %d: %s', $pid, $reconciled['error']);

                    continue;
                }

                $stats['itemsMatched'] += $reconciled['matched'];
                $stats['itemsUnmatched'] += $reconciled['unmatched'];

                // Membership write-back (phase 6.2): after the junction reflects
                // YouTube, ensure videos Proclaim considers members (via the
                // playlist's linked series) are actually in the playlist.
                if ($pushChanges) {
                    $membership = self::pushMemberships($db, $addon, $pid, $dryRun);

                    $stats['membershipsPushed'] += $membership['pushed'];
                    $stats['membershipsWouldPush']  = array_merge($stats['membershipsWouldPush'], $membership['wouldPush']);
                    $stats['membershipsRemoved'] += $membership['removed'];
                    $stats['membershipsWouldRemove'] = array_merge($stats['membershipsWouldRemove'], $membership['wouldRemove']);
                    $stats['pushErrors']             = array_merge($stats['pushErrors'], $membership['errors']);
                }
            }
        }

        return $stats;
    }

    /**
     * Fetch a channel's playlists and upsert each as a Proclaim Playlist.
     *
     * Existing playlists are matched on (remote_playlist_id, server_id).
     *
     * Conflict gate (see fieldPushDecision): a remote title or description is pulled
     * into an existing playlist only when the local row was NOT edited by a human
     * since the last sync. When the local row diverged AND was locally edited, the
     * local value is authoritative — it is either pushed back to the remote (when
     * $pushChanges and the playlist's writeback_enabled are both on, phase 6) or,
     * absent write-back, kept and reported as a conflict. The description gate runs
     * only when the platform reports a description. Playlists opted out of sync
     * (sync_enabled = 0) are skipped entirely. last_sync is bumped only when the
     * row ends in sync with the remote on both fields, so an unresolved local edit
     * stays protected across runs.
     *
     * @param   DatabaseInterface  $db           Database driver.
     * @param   CWMAddon           $addon        Playlist-capable addon instance.
     * @param   integer            $serverId     Server ID to import from.
     * @param   boolean            $discoverNew  Whether to create not-yet-local playlists.
     * @param   boolean            $pushChanges   Whether to push locally-authoritative titles + descriptions to the remote.
     * @param   boolean            $dryRun       Report would-push changes without writing to the remote.
     *
     * @return  array{created:int, updated:int, skipped:int, titlesPushed:int, titlesWouldPush:string[], descriptionsPushed:int, descriptionsWouldPush:string[], pushErrors:string[], playlistIds:int[], conflicts:string[], error:?string}
     *
     * @throws  \Exception
     * @since   10.3.3
     */
    public static function importChannelPlaylists(DatabaseInterface $db, CWMAddon $addon, int $serverId, bool $discoverNew = true, bool $pushChanges = false, bool $dryRun = false): array
    {
        $out = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'titlesPushed' => 0, 'titlesWouldPush' => [], 'descriptionsPushed' => 0, 'descriptionsWouldPush' => [], 'pushErrors' => [], 'playlistIds' => [], 'conflicts' => [], 'error' => null];

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
                ];

                // Bump last_sync only when the row ends in sync with the remote;
                // keep it untouched while a local edit remains unresolved so the
                // edit stays protected across runs. Title and description share the
                // same edit signal and write-back gate; either left diverged keeps
                // the row unsynced.
                $markSynced    = true;
                $writebackOn   = $pushChanges
                    && (int) $table->writeback_enabled === 1
                    && $addon->supportsPlaylistWriteback();
                $locallyEdited = self::isLocallyEdited($table);

                // ── Title ──────────────────────────────────────────────────
                if ($newTitle !== (string) $table->title) {
                    $decision = self::fieldPushDecision(
                        (string) $table->title,
                        $newTitle,
                        $locallyEdited,
                        $writebackOn
                    );

                    switch ($decision) {
                        case 'pull':
                            // Remote changed, no local edit — safe to apply.
                            $data['title'] = $newTitle;
                            break;

                        case 'push':
                            if ($dryRun) {
                                $out['titlesWouldPush'][] = \sprintf(
                                    'Playlist "%s" (#%d): would push local title to remote (remote currently "%s").',
                                    (string) $table->title,
                                    $existingId,
                                    $newTitle
                                );
                                $markSynced = false;
                            } else {
                                $push = $addon->pushPlaylistTitle($serverId, $remoteId, (string) $table->title);

                                if (!empty($push['success'])) {
                                    // Remote now matches local — converged.
                                    $out['titlesPushed']++;
                                } else {
                                    $out['pushErrors'][] = \sprintf(
                                        'Playlist "%s" (#%d): title write-back failed: %s',
                                        (string) $table->title,
                                        $existingId,
                                        $push['error'] ?? 'unknown error'
                                    );
                                    $markSynced = false;
                                }
                            }
                            break;

                        case 'conflict':
                            $out['conflicts'][] = \sprintf(
                                'Playlist "%s" (#%d): kept local title; remote title "%s" not applied.',
                                (string) $table->title,
                                $existingId,
                                $newTitle
                            );
                            $markSynced = false;
                            break;
                    }
                }

                // ── Description ────────────────────────────────────────────
                // Only when the platform actually reports a description, so a
                // platform that omits it never blanks the local copy.
                if (\array_key_exists('description', $pl)) {
                    $remoteDescription = (string) $pl['description'];

                    if ($remoteDescription !== (string) $table->description) {
                        $decision = self::fieldPushDecision(
                            (string) $table->description,
                            $remoteDescription,
                            $locallyEdited,
                            $writebackOn
                        );

                        switch ($decision) {
                            case 'pull':
                                // Remote changed, no local edit — safe to apply.
                                $data['description'] = $remoteDescription;
                                break;

                            case 'push':
                                if ($dryRun) {
                                    $out['descriptionsWouldPush'][] = \sprintf(
                                        'Playlist "%s" (#%d): would push local description to remote.',
                                        (string) $table->title,
                                        $existingId
                                    );
                                    $markSynced = false;
                                } else {
                                    $push = $addon->pushPlaylistDescription($serverId, $remoteId, (string) $table->description);

                                    if (!empty($push['success'])) {
                                        // Remote now matches local — converged.
                                        $out['descriptionsPushed']++;
                                    } else {
                                        $out['pushErrors'][] = \sprintf(
                                            'Playlist "%s" (#%d): description write-back failed: %s',
                                            (string) $table->title,
                                            $existingId,
                                            $push['error'] ?? 'unknown error'
                                        );
                                        $markSynced = false;
                                    }
                                }
                                break;

                            case 'conflict':
                                $out['conflicts'][] = \sprintf(
                                    'Playlist "%s" (#%d): kept local description; remote description not applied.',
                                    (string) $table->title,
                                    $existingId
                                );
                                $markSynced = false;
                                break;
                        }
                    }
                }

                if ($markSynced) {
                    $data['last_sync'] = $now;
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
     * Decide how to resolve a divergence between a local playlist field and the
     * same field on the remote platform. Field-agnostic (title, description, …)
     * and pure (no I/O) so the gate is unit-testable.
     *
     * Returns one of:
     *   'none'     — values already match; nothing to do.
     *   'pull'     — remote changed and the local row was not edited; apply remote.
     *   'push'     — local row is authoritative (edited + diverged) and opted in to
     *                write-back; push the local value to the remote.
     *   'conflict' — local row is authoritative but write-back is off; keep local,
     *                report the divergence.
     *
     * @param   string   $localValue        Current local value.
     * @param   string   $remoteValue       Current remote value.
     * @param   boolean  $locallyEdited     Whether the local row was edited since last sync.
     * @param   boolean  $writebackEnabled  Whether write-back is on for this run + playlist.
     *
     * @return  string  One of 'none', 'pull', 'push', 'conflict'.
     *
     * @since   10.3.3
     */
    public static function fieldPushDecision(string $localValue, string $remoteValue, bool $locallyEdited, bool $writebackEnabled): string
    {
        if ($localValue === $remoteValue) {
            return 'none';
        }

        if (!$locallyEdited) {
            return 'pull';
        }

        return $writebackEnabled ? 'push' : 'conflict';
    }

    /**
     * Backwards-compatible alias for {@see self::fieldPushDecision()}; the gate is
     * identical for any playlist field, and title was the first field wired up.
     *
     * @param   string   $localTitle        Current local title.
     * @param   string   $remoteTitle       Current remote title.
     * @param   boolean  $locallyEdited     Whether the local row was edited since last sync.
     * @param   boolean  $writebackEnabled  Whether write-back is on for this run + playlist.
     *
     * @return  string  One of 'none', 'pull', 'push', 'conflict'.
     *
     * @since   10.3.3
     */
    public static function titlePushDecision(string $localTitle, string $remoteTitle, bool $locallyEdited, bool $writebackEnabled): string
    {
        return self::fieldPushDecision($localTitle, $remoteTitle, $locallyEdited, $writebackEnabled);
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
     * @since   10.3.3
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
     * @since   10.3.3
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
     * Membership write-back (phase 6.2): ensure videos Proclaim considers members
     * of a playlist are actually in the playlist on the remote platform.
     *
     * Two membership sources, both requiring write-back opt-in (writeback_enabled
     * = 1):
     *   (A) Series-derived — every published media file on the playlist's server
     *       whose study belongs to the playlist's linked series (skipped when no
     *       series is linked). Videos already recorded as members are skipped.
     *   (B) Manual (phase 6.2b) — junction rows flagged source='manual' by a user's
     *       explicit media-file playlist assignment; pushed then flipped to 'remote'.
     *   (C) Removals (phase 6.2b, deleteSync) — junction rows flagged
     *       source='remove_pending' by a de-selection; removed from the platform then
     *       dropped from the junction.
     * Each call is quota-checked by the addon; on a quota/auth failure the run stops
     * early and reports it. Call reconcile first so the junction is fresh.
     *
     * @param   DatabaseInterface  $db          Database driver.
     * @param   CWMAddon           $addon       Playlist-capable addon instance.
     * @param   integer            $playlistId  Local playlist row ID.
     * @param   boolean            $dryRun      Report would-push without writing to the remote.
     *
     * @return  array{pushed:int, wouldPush:string[], removed:int, wouldRemove:string[], errors:string[]}
     *
     * @throws  \Exception
     * @since   10.3.3
     */
    public static function pushMemberships(DatabaseInterface $db, CWMAddon $addon, int $playlistId, bool $dryRun = false): array
    {
        $out = ['pushed' => 0, 'wouldPush' => [], 'removed' => 0, 'wouldRemove' => [], 'errors' => []];

        $pl = $db->setQuery(
            $db->getQuery(true)
                ->select($db->quoteName(['remote_playlist_id', 'server_id', 'series_id', 'writeback_enabled', 'title']))
                ->from($db->quoteName('#__bsms_playlists'))
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':id', $playlistId, ParameterType::INTEGER)
        )->loadObject();

        // Only opted-in playlists drive write-back.
        if (!$pl || (int) $pl->writeback_enabled !== 1) {
            return $out;
        }

        $serverId         = (int) $pl->server_id;
        $remotePlaylistId = (string) $pl->remote_playlist_id;
        $now              = Factory::getDate()->toSql();
        $stopped          = false;

        // Push one video to the remote playlist. Records failures and flips the
        // shared early-stop flag on quota/auth errors (further inserts would only
        // repeat the same failure). Returns true only on a confirmed add.
        $doPush = function (string $videoId) use ($db, $addon, $serverId, $remotePlaylistId, $playlistId, $pl, &$out, &$stopped): bool {
            $res = $addon->addPlaylistMembership($serverId, $remotePlaylistId, $videoId);

            if (!empty($res['success'])) {
                return true;
            }

            $error           = (string) ($res['error'] ?? 'unknown error');
            $out['errors'][] = \sprintf('Playlist "%s" (#%d): add video %s failed: %s', (string) $pl->title, $playlistId, $videoId, $error);

            if (stripos($error, 'quota') !== false || stripos($error, 'oauth') !== false) {
                $stopped = true;
            }

            return false;
        };

        // ── (A) Series-derived memberships ─────────────────────────────────
        // Every published media on this server whose study is in the linked
        // series should be a member. Only runs when a series is linked.
        if (!empty($pl->series_id)) {
            $seriesId = (int) $pl->series_id;

            $rows = $db->setQuery(
                $db->getQuery(true)
                    ->select([
                        $db->quoteName('mf.id', 'id'),
                        $db->quoteName('mf.params', 'params'),
                        $db->quoteName('st.studytitle', 'title'),
                    ])
                    ->from($db->quoteName('#__bsms_mediafiles', 'mf'))
                    ->innerJoin(
                        $db->quoteName('#__bsms_studies', 'st')
                        . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('mf.study_id')
                    )
                    ->where($db->quoteName('st.series_id') . ' = :sid')
                    ->where($db->quoteName('mf.server_id') . ' = :srv')
                    ->where($db->quoteName('mf.published') . ' = 1')
                    ->bind(':sid', $seriesId, ParameterType::INTEGER)
                    ->bind(':srv', $serverId, ParameterType::INTEGER)
            )->loadAssocList();

            if ($rows !== []) {
                // Video IDs already recorded as members of this playlist.
                $existing = $db->setQuery(
                    $db->getQuery(true)
                        ->select($db->quoteName('remote_video_id'))
                        ->from($db->quoteName('#__bsms_playlist_items'))
                        ->where($db->quoteName('playlist_id') . ' = :id')
                        ->bind(':id', $playlistId, ParameterType::INTEGER)
                )->loadColumn() ?: [];

                $toPush   = self::membershipsToPush($rows, [$addon, 'extractRemoteMediaId'], $existing);
                $position = \count($existing);

                foreach ($toPush as $item) {
                    if ($stopped) {
                        break;
                    }

                    if ($dryRun) {
                        $out['wouldPush'][] = \sprintf(
                            'Playlist "%s" (#%d): would add video %s%s.',
                            (string) $pl->title,
                            $playlistId,
                            $item['videoId'],
                            $item['title'] !== '' ? \sprintf(' ("%s")', $item['title']) : ''
                        );

                        continue;
                    }

                    if ($doPush($item['videoId'])) {
                        self::upsertItem($db, $playlistId, $item['videoId'], $item['title'], $item['mediafileId'], $position++, $now);
                        $out['pushed']++;
                    }
                }
            }
        }

        // ── (B) Manual assignments (phase 6.2b) ────────────────────────────
        // Junction rows flagged source='manual' are user-assigned memberships not
        // yet confirmed on the platform. Push each, then flip to 'remote' so a
        // repeat run does not re-push it.
        $manual = $db->setQuery(
            $db->getQuery(true)
                ->select($db->quoteName(['remote_video_id', 'title']))
                ->from($db->quoteName('#__bsms_playlist_items'))
                ->where($db->quoteName('playlist_id') . ' = :id')
                ->where($db->quoteName('source') . ' = ' . $db->quote('manual'))
                ->bind(':id', $playlistId, ParameterType::INTEGER)
        )->loadAssocList();

        foreach ($manual as $row) {
            $videoId = (string) ($row['remote_video_id'] ?? '');

            if ($videoId === '' || $stopped) {
                if ($stopped) {
                    break;
                }

                continue;
            }

            if ($dryRun) {
                $out['wouldPush'][] = \sprintf(
                    'Playlist "%s" (#%d): would add manually-assigned video %s%s.',
                    (string) $pl->title,
                    $playlistId,
                    $videoId,
                    ($row['title'] ?? '') !== '' ? \sprintf(' ("%s")', $row['title']) : ''
                );

                continue;
            }

            if ($doPush($videoId)) {
                $db->setQuery(
                    $db->getQuery(true)
                        ->update($db->quoteName('#__bsms_playlist_items'))
                        ->set($db->quoteName('source') . ' = ' . $db->quote('remote'))
                        ->where($db->quoteName('playlist_id') . ' = :pid')
                        ->where($db->quoteName('remote_video_id') . ' = :vid')
                        ->where($db->quoteName('source') . ' = ' . $db->quote('manual'))
                        ->bind(':pid', $playlistId, ParameterType::INTEGER)
                        ->bind(':vid', $videoId, ParameterType::STRING)
                )->execute();
                $out['pushed']++;
            }
        }

        // ── (C) Queued removals (phase 6.2b, deleteSync) ───────────────────
        // Rows flagged source='remove_pending' are memberships de-selected on a
        // media file when platform-side removal is enabled. Remove each from the
        // platform then drop the junction row.
        $removePending = $db->setQuery(
            $db->getQuery(true)
                ->select($db->quoteName(['id', 'remote_video_id', 'title']))
                ->from($db->quoteName('#__bsms_playlist_items'))
                ->where($db->quoteName('playlist_id') . ' = :id')
                ->where($db->quoteName('source') . ' = ' . $db->quote('remove_pending'))
                ->bind(':id', $playlistId, ParameterType::INTEGER)
        )->loadAssocList();

        foreach ($removePending as $row) {
            if ($stopped) {
                break;
            }

            $videoId = (string) ($row['remote_video_id'] ?? '');
            $itemId  = (int) ($row['id'] ?? 0);

            // No resolvable video — just drop the stale row locally.
            if ($videoId === '') {
                self::deleteItemById($db, $itemId);

                continue;
            }

            if ($dryRun) {
                $out['wouldRemove'][] = \sprintf(
                    'Playlist "%s" (#%d): would remove video %s%s from the platform.',
                    (string) $pl->title,
                    $playlistId,
                    $videoId,
                    ($row['title'] ?? '') !== '' ? \sprintf(' ("%s")', $row['title']) : ''
                );

                continue;
            }

            $res = $addon->removePlaylistMembership($serverId, $remotePlaylistId, $videoId);

            if (!empty($res['success'])) {
                self::deleteItemById($db, $itemId);
                $out['removed']++;

                continue;
            }

            $error           = (string) ($res['error'] ?? 'unknown error');
            $out['errors'][] = \sprintf('Playlist "%s" (#%d): remove video %s failed: %s', (string) $pl->title, $playlistId, $videoId, $error);

            if (stripos($error, 'quota') !== false || stripos($error, 'oauth') !== false) {
                $stopped = true;
            }
        }

        return $out;
    }

    /**
     * Delete a single junction row by ID. Used when a queued removal has been
     * applied on the platform (or is unresolvable) and the local row should go.
     *
     * @param   DatabaseInterface  $db      Database driver.
     * @param   integer            $itemId  Junction row ID.
     *
     * @return  void
     *
     * @since   10.3.3
     */
    private static function deleteItemById(DatabaseInterface $db, int $itemId): void
    {
        if ($itemId <= 0) {
            return;
        }

        $db->setQuery(
            $db->getQuery(true)
                ->delete($db->quoteName('#__bsms_playlist_items'))
                ->where($db->quoteName('id') . ' = :rid')
                ->bind(':rid', $itemId, ParameterType::INTEGER)
        )->execute();
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
     * @since   10.3.3
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
     * @since   10.3.3
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
     * The playlist IDs a media file is currently a member of (any source), used
     * as the value of the media-file playlist field. Never throws.
     *
     * @param   integer  $mediafileId  The media file's ID.
     *
     * @return  int[]  Playlist row IDs.
     *
     * @since   10.3.3
     */
    public static function getMediafilePlaylistIds(int $mediafileId): array
    {
        if ($mediafileId <= 0) {
            return [];
        }

        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            $ids = $db->setQuery(
                $db->getQuery(true)
                    ->select($db->quoteName('playlist_id'))
                    ->from($db->quoteName('#__bsms_playlist_items'))
                    ->where($db->quoteName('mediafile_id') . ' = :mid')
                    // A row queued for removal (remove_pending) is no longer a
                    // membership the user has — hide it from the field value.
                    ->where($db->quoteName('source') . ' <> ' . $db->quote('remove_pending'))
                    ->bind(':mid', $mediafileId, \Joomla\Database\ParameterType::INTEGER)
            )->loadColumn() ?: [];

            return array_values(array_unique(array_map('intval', $ids)));
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Playlists linked to the series of a given study — the auto-fill source for
     * a new media file's playlist assignment (Message → Series → Playlist). Never
     * throws.
     *
     * @param   integer  $studyId  The study (Message) ID.
     *
     * @return  int[]  Published playlist row IDs linked to the study's series.
     *
     * @since   10.3.3
     */
    public static function getSeriesPlaylistIdsForStudy(int $studyId): array
    {
        if ($studyId <= 0) {
            return [];
        }

        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            $seriesId = (int) ($db->setQuery(
                $db->getQuery(true)
                    ->select($db->quoteName('series_id'))
                    ->from($db->quoteName('#__bsms_studies'))
                    ->where($db->quoteName('id') . ' = :id')
                    ->bind(':id', $studyId, \Joomla\Database\ParameterType::INTEGER)
            )->loadResult() ?? 0);

            if ($seriesId <= 0) {
                return [];
            }

            $ids = $db->setQuery(
                $db->getQuery(true)
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__bsms_playlists'))
                    ->where($db->quoteName('series_id') . ' = :sid')
                    ->where($db->quoteName('published') . ' = 1')
                    ->bind(':sid', $seriesId, \Joomla\Database\ParameterType::INTEGER)
            )->loadColumn() ?: [];

            return array_values(array_unique(array_map('intval', $ids)));
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Pure planner: diff the playlists a media file is currently in against the
     * desired set, returning the additions and removals. Kept side-effect-free so
     * the reconcile logic is unit-testable.
     *
     * @param   int[]  $currentIds  Playlist IDs the media is currently in.
     * @param   int[]  $desiredIds  Playlist IDs the user selected.
     *
     * @return  array{add:int[], remove:int[]}
     *
     * @since   10.3.3
     */
    public static function planPlaylistAssignments(array $currentIds, array $desiredIds): array
    {
        $current = array_values(array_unique(array_map('intval', $currentIds)));
        $desired = array_values(array_unique(array_filter(
            array_map('intval', $desiredIds),
            static fn ($id) => $id > 0
        )));

        return [
            'add'    => array_values(array_diff($desired, $current)),
            'remove' => array_values(array_diff($current, $desired)),
        ];
    }

    /**
     * Reconcile a media file's manual playlist assignments to the user's selection.
     *
     * Called on media-file save. LOCAL-ONLY (no platform API): it upserts a
     * junction row (source='manual') for every newly-selected playlist and reconciles
     * de-selected playlists. A never-pushed manual row is simply deleted. A row that
     * IS on the platform (source='remote') is deleted locally when $deleteSync is off
     * (the platform stays authoritative), or queued as 'remove_pending' when
     * $deleteSync is on so the write-back push removes it from the platform too.
     * Additions need a resolvable remote video ID (the junction key); when the media
     * has none, only removals apply. Never throws — a media save must not fail on
     * playlist bookkeeping.
     *
     * @param   integer  $mediafileId         The saved media file's ID.
     * @param   int[]    $desiredPlaylistIds  Playlist IDs the user selected.
     * @param   string   $params              The media file's params JSON (holds filename).
     * @param   boolean  $deleteSync          Queue de-selected remote memberships for platform removal.
     *
     * @return  void
     *
     * @since   10.3.3
     */
    public static function setManualPlaylistAssignments(int $mediafileId, array $desiredPlaylistIds, string $params, bool $deleteSync = false): void
    {
        if ($mediafileId <= 0) {
            return;
        }

        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            $plan = self::planPlaylistAssignments(
                self::getMediafilePlaylistIds($mediafileId),
                $desiredPlaylistIds
            );

            if ($plan['remove'] !== []) {
                // A pending manual row (never pushed) is just dropped — nothing on
                // the platform to remove.
                $db->setQuery(
                    $db->getQuery(true)
                        ->delete($db->quoteName('#__bsms_playlist_items'))
                        ->where($db->quoteName('mediafile_id') . ' = :mid')
                        ->where($db->quoteName('source') . ' = ' . $db->quote('manual'))
                        ->whereIn($db->quoteName('playlist_id'), $plan['remove'], \Joomla\Database\ParameterType::INTEGER)
                        ->bind(':mid', $mediafileId, \Joomla\Database\ParameterType::INTEGER)
                )->execute();

                if ($deleteSync) {
                    // Queue de-selected on-platform memberships for removal; the
                    // write-back push calls the platform then drops the row.
                    $db->setQuery(
                        $db->getQuery(true)
                            ->update($db->quoteName('#__bsms_playlist_items'))
                            ->set($db->quoteName('source') . ' = ' . $db->quote('remove_pending'))
                            ->where($db->quoteName('mediafile_id') . ' = :mid')
                            ->where($db->quoteName('source') . ' = ' . $db->quote('remote'))
                            ->whereIn($db->quoteName('playlist_id'), $plan['remove'], \Joomla\Database\ParameterType::INTEGER)
                            ->bind(':mid', $mediafileId, \Joomla\Database\ParameterType::INTEGER)
                    )->execute();
                }
            }

            if ($plan['add'] === []) {
                return;
            }

            // Additions need the media's remote video ID as the junction key.
            $decoded = json_decode($params, true);
            $videoId = \is_array($decoded) && !empty($decoded['filename'])
                ? self::extractAnyRemoteId((string) $decoded['filename'])
                : null;

            if ($videoId === null || $videoId === '') {
                return;
            }

            $now = Factory::getDate()->toSql();

            foreach ($plan['add'] as $playlistId) {
                self::upsertManualItem($db, (int) $playlistId, $videoId, $mediafileId, $now);
            }
        } catch (\Exception $e) {
            // Playlist bookkeeping must never fail a media save.
        }
    }

    /**
     * Insert (or link) a manual junction row for a media file's playlist
     * assignment. If the (playlist, video) row already exists — e.g. imported from
     * the platform — it is left in place with its source preserved and only its
     * mediafile_id ensured; otherwise a new source='manual' row is inserted.
     *
     * @param   DatabaseInterface  $db           Database driver.
     * @param   integer            $playlistId   Playlist row ID.
     * @param   string             $videoId      Remote video ID (junction key).
     * @param   integer            $mediafileId  Media file ID to link.
     * @param   string             $now          SQL timestamp.
     *
     * @return  void
     *
     * @since   10.3.3
     */
    private static function upsertManualItem(DatabaseInterface $db, int $playlistId, string $videoId, int $mediafileId, string $now): void
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

        if ($existingId > 0) {
            // Already a member (imported or manual): ensure it links here. If it was
            // queued for removal, re-selecting it cancels that — it is confirmed on
            // the platform, so restore it to 'remote'.
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__bsms_playlist_items'))
                ->set($db->quoteName('mediafile_id') . ' = :mid')
                ->set(
                    $db->quoteName('source') . ' = CASE WHEN ' . $db->quoteName('source')
                    . ' = ' . $db->quote('remove_pending') . ' THEN ' . $db->quote('remote')
                    . ' ELSE ' . $db->quoteName('source') . ' END'
                )
                ->where($db->quoteName('id') . ' = :rid')
                ->bind(':mid', $mediafileId, \Joomla\Database\ParameterType::INTEGER)
                ->bind(':rid', $existingId, \Joomla\Database\ParameterType::INTEGER);

            $db->setQuery($query)->execute();

            return;
        }

        $db->insertObject('#__bsms_playlist_items', (object) [
            'playlist_id'     => $playlistId,
            'mediafile_id'    => $mediafileId,
            'remote_video_id' => $videoId,
            'title'           => '',
            'position'        => 0,
            'source'          => 'manual',
            'created'         => $now,
        ]);
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
     * @since   10.3.3
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
     * @since   10.3.3
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
     * Pure planner: from candidate media-file rows, decide which videos need to
     * be pushed into a playlist (i.e. resolve to a platform video ID and are not
     * already members). Dependency-free so the membership planner is unit-testable.
     *
     * @param   array<int,array{id:mixed,params:mixed,title?:mixed}>  $rows             Candidate media rows.
     * @param   callable                                             $extractor         fn(string $url): ?string.
     * @param   string[]                                             $existingVideoIds  Video IDs already in the playlist.
     *
     * @return  array<int,array{mediafileId:int,videoId:string,title:string}>  Videos to push (deduped).
     *
     * @since   10.3.3
     */
    public static function membershipsToPush(array $rows, callable $extractor, array $existingVideoIds): array
    {
        $existing = array_flip($existingVideoIds);
        $seen     = [];
        $out      = [];

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

            if ($videoId === null || isset($existing[$videoId]) || isset($seen[$videoId])) {
                continue;
            }

            $seen[$videoId] = true;
            $out[]          = [
                'mediafileId' => (int) $row['id'],
                'videoId'     => $videoId,
                'title'       => (string) ($row['title'] ?? ''),
            ];
        }

        return $out;
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
     * @since   10.3.3
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
     * @since   10.3.3
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
     * @since   10.3.3
     */
    private static function pruneItems(DatabaseInterface $db, int $playlistId, array $keepVideos): void
    {
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__bsms_playlist_items'))
            ->where($db->quoteName('playlist_id') . ' = :pid')
            // Never prune a local-only pending row: a manual assignment not yet
            // pushed, or a row queued for removal from the remote playlist.
            ->where($db->quoteName('source') . ' NOT IN (' . $db->quote('manual') . ', ' . $db->quote('remove_pending') . ')')
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
     * @since   10.3.3
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
