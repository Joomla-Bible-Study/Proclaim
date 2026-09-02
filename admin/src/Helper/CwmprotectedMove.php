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

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Filesystem\Folder;
use Joomla\Registry\Registry;

/**
 * Moves a media file into protected storage and back, rewriting the record
 * with the file so neither is ever pointing at the other's past.
 *
 * The record's `filename` is a site-relative path and the server row supplies
 * only the host, so a move touches one record and never the server other
 * media shares. Delivery needs no flag: it is keyed on where the file
 * actually is (CwmprotectedStorage::holds()), so the rewrite of `filename`
 * IS the switch to serving through Proclaim.
 *
 * ⚠️ Eligibility is refusal-first and the reasons are the API. The rules that
 * matter most are the ones that say no:
 *
 *   - Podcast-referenced media is not eligible, full stop. A feed's
 *     <enclosure> is a direct URL, so a protected file cannot reach a
 *     subscriber at all. Refused, not warned — a warning can be clicked past.
 *   - Only a local server whose "offer protected storage" switch is on. The
 *     switch governs this action and nothing else; delivery cannot be gated
 *     on a setting, because a file the web server refuses is not made to
 *     work by one.
 *
 * @since  __DEPLOY_VERSION__
 */
final class CwmprotectedMove
{
    /**
     * Param key holding the pre-move path, so moving out restores rather
     * than guesses. Lives on the media record because the move does.
     *
     * @since  __DEPLOY_VERSION__
     */
    public const PREVIOUS_PATH_PARAM = 'preprotect_filename';

    /**
     * Where a file with no recorded previous home is moved out to.
     *
     * @since  __DEPLOY_VERSION__
     */
    public const FALLBACK_DIR = 'images/biblestudy/media';

    /**
     * Whether any positive podcast id references this media.
     *
     * The column holds '' / '0' / '-1' for "no podcast" on real sites, and a
     * comma list of ids otherwise — FIND_IN_SET is how the feed reads it.
     *
     * @param   ?string  $podcastId  The raw column value.
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function podcastReferenced(?string $podcastId): bool
    {
        foreach (explode(',', (string) $podcastId) as $id) {
            if ((int) trim($id) > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Why this record may not be moved, or null when it may.
     *
     * Pure over the row so the rules can be tested without a filesystem: the
     * file's own existence is checked by the move, which is the only moment
     * the answer cannot go stale.
     *
     * @param   object  $media  Row carrying server_type, server_params,
     *                          podcast_id and params.
     *
     * @return  ?string  A language key naming the refusal, or null.
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function refusal(object $media): ?string
    {
        // A legacy server's files often ARE on this filesystem — the type
        // just predates the move. Telling its owner "not on this site's
        // filesystem" points them away from the actual next step, which the
        // legacy-server health check is already asking for: migrate to Local.
        if (($media->server_type ?? '') === 'legacy') {
            return 'JBS_MED_PROTECT_REFUSED_SERVER_LEGACY';
        }

        if (($media->server_type ?? '') !== 'local') {
            // A server describes where media lives; only media on this
            // machine's filesystem can be moved into a folder on it.
            return 'JBS_MED_PROTECT_REFUSED_SERVER_TYPE';
        }

        $serverParams = new Registry($media->server_params ?? '{}');

        if ((int) $serverParams->get('protected_storage', 0) !== 1) {
            return 'JBS_MED_PROTECT_REFUSED_SERVER_OFF';
        }

        if (self::podcastReferenced($media->podcast_id ?? null)) {
            return 'JBS_MED_PROTECT_REFUSED_PODCAST';
        }

        $filename = (string) (new Registry($media->params ?? '{}'))->get('filename', '');

        if ($filename === '') {
            return 'JBS_MED_PROTECT_REFUSED_NO_FILE';
        }

        // A filename carrying a scheme or host is served by someone else,
        // whatever the server row says.
        if (str_contains($filename, '://') || str_starts_with($filename, '//')) {
            return 'JBS_MED_PROTECT_REFUSED_REMOTE';
        }

        return null;
    }

    /**
     * Whether the media list should offer the move at all.
     *
     * @param   DatabaseInterface  $db  Database driver.
     *
     * @return  bool  True when at least one published local server opts in.
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function anyServerOptedIn(DatabaseInterface $db): bool
    {
        $query = $db->createQuery()
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__bsms_servers'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('local'))
            ->where($db->quoteName('published') . ' = 1');

        foreach ($db->setQuery($query)->loadColumn() ?: [] as $json) {
            if ((int) (new Registry($json))->get('protected_storage', 0) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Load the row a move decision needs.
     *
     * @param   DatabaseInterface  $db  Database driver.
     * @param   int                $id  Media file id.
     *
     * @return  ?object
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function load(DatabaseInterface $db, int $id): ?object
    {
        $query = $db->createQuery()
            ->select($db->quoteName(['mf.id', 'mf.params', 'mf.podcast_id']))
            ->select($db->quoteName('s.type', 'server_type'))
            ->select($db->quoteName('s.params', 'server_params'))
            ->from($db->quoteName('#__bsms_mediafiles', 'mf'))
            ->leftJoin(
                $db->quoteName('#__bsms_servers', 's')
                . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('mf.server_id')
            )
            ->where($db->quoteName('mf.id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);

        return $db->setQuery($query)->loadObject();
    }

    /**
     * Persist a rewritten params blob, or say why not.
     *
     * @param   DatabaseInterface  $db      Database driver.
     * @param   int                $id      Media file id.
     * @param   Registry           $params  The new params.
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function store(DatabaseInterface $db, int $id, Registry $params): bool
    {
        try {
            $json  = $params->toString();
            $query = $db->createQuery()
                ->update($db->quoteName('#__bsms_mediafiles'))
                ->set($db->quoteName('params') . ' = :params')
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':params', $json, ParameterType::STRING)
                ->bind(':id', $id, ParameterType::INTEGER);
            $db->setQuery($query)->execute();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Move one media file into protected storage.
     *
     * @param   DatabaseInterface  $db  Database driver.
     * @param   int                $id  Media file id.
     *
     * @return  array{ok: bool, reason: ?string}
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function moveIn(DatabaseInterface $db, int $id): array
    {
        $media = self::load($db, $id);

        if (!$media) {
            return ['ok' => false, 'reason' => 'JBS_MED_PROTECT_REFUSED_NO_FILE'];
        }

        $reason = self::refusal($media);

        if ($reason !== null) {
            return ['ok' => false, 'reason' => $reason];
        }

        $params   = new Registry($media->params);
        $filename = ltrim((string) $params->get('filename'), '/');
        $absolute = realpath(rtrim(JPATH_ROOT, '/\\') . '/' . $filename);

        // Idempotent: already protected is the asked-for state, not an error.
        if ($absolute !== false && CwmprotectedStorage::holds($absolute)) {
            return ['ok' => true, 'reason' => null];
        }

        if ($absolute === false || !is_file($absolute)) {
            return ['ok' => false, 'reason' => 'JBS_MED_PROTECT_REFUSED_MISSING'];
        }

        $moved = CwmprotectedStorage::moveInto($filename);

        if ($moved === null) {
            return ['ok' => false, 'reason' => 'JBS_MED_PROTECT_REFUSED_MOVE_FAILED'];
        }

        $params->set(self::PREVIOUS_PATH_PARAM, '/' . $filename);
        $params->set('filename', '/' . $moved);

        if (!self::store($db, $id, $params)) {
            // ⚠️ The file has moved and the record has not — exactly the split
            // this action exists to prevent. Put the file back; a failed move
            // must leave the world as it found it.
            @rename(
                rtrim(JPATH_ROOT, '/\\') . '/' . $moved,
                rtrim(JPATH_ROOT, '/\\') . '/' . $filename
            );

            return ['ok' => false, 'reason' => 'JBS_MED_PROTECT_REFUSED_DB'];
        }

        return ['ok' => true, 'reason' => null];
    }

    /**
     * Move one media file back out of protected storage.
     *
     * Restores the recorded pre-move path when there is one; a file placed by
     * hand, with no recorded home, goes to FALLBACK_DIR.
     *
     * @param   DatabaseInterface  $db  Database driver.
     * @param   int                $id  Media file id.
     *
     * @return  array{ok: bool, reason: ?string}
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function moveOut(DatabaseInterface $db, int $id): array
    {
        $media = self::load($db, $id);

        if (!$media) {
            return ['ok' => false, 'reason' => 'JBS_MED_PROTECT_REFUSED_NO_FILE'];
        }

        $params   = new Registry($media->params);
        $filename = ltrim((string) $params->get('filename'), '/');
        $root     = rtrim(JPATH_ROOT, '/\\');
        $absolute = realpath($root . '/' . $filename);

        if ($absolute === false || !is_file($absolute) || !CwmprotectedStorage::holds($absolute)) {
            return ['ok' => false, 'reason' => 'JBS_MED_PROTECT_REFUSED_NOT_PROTECTED'];
        }

        $previous = ltrim((string) $params->get(self::PREVIOUS_PATH_PARAM, ''), '/');
        $destDir  = $previous !== '' ? \dirname($previous) : self::FALLBACK_DIR;

        // The previous home may have been deleted since; recreate it rather
        // than fail a restore over a missing folder.
        if (!is_dir($root . '/' . $destDir) && !Folder::create($root . '/' . $destDir)) {
            return ['ok' => false, 'reason' => 'JBS_MED_PROTECT_REFUSED_MOVE_FAILED'];
        }

        // Never overwrite whatever now lives at the old address.
        $name        = CwmprotectedStorage::uniqueName($root . '/' . $destDir, basename($absolute));
        $destination = $destDir . '/' . $name;

        if (!@rename($absolute, $root . '/' . $destination)) {
            return ['ok' => false, 'reason' => 'JBS_MED_PROTECT_REFUSED_MOVE_FAILED'];
        }

        $params->set('filename', '/' . $destination);
        $params->remove(self::PREVIOUS_PATH_PARAM);

        if (!self::store($db, $id, $params)) {
            @rename($root . '/' . $destination, $absolute);

            return ['ok' => false, 'reason' => 'JBS_MED_PROTECT_REFUSED_DB'];
        }

        return ['ok' => true, 'reason' => null];
    }
}
