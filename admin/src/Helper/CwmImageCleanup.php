<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;
use Joomla\Registry\Registry;

/**
 * Image Cleanup Helper - Find and remove orphaned image folders
 *
 * @package  Proclaim.Admin
 * @since 10.1.0
 */
class CwmImageCleanup
{
    /**
     * Allowed base paths (SAFETY: only scan within these)
     */
    private const array ALLOWED_PATHS = [
        'images/biblestudy/studies',
        'images/biblestudy/teachers',
        'images/biblestudy/series',
    ];

    /**
     * Scan for orphaned folders across all types
     *
     * @return  array  Orphaned folders grouped by type
     *
     * @since 10.1.0
     */
    public static function findOrphanedFolders(): array
    {
        $orphans = [];

        foreach (self::ALLOWED_PATHS as $basePath) {
            $type           = basename($basePath);
            $orphans[$type] = self::scanTypeFolder($basePath, $type);
        }

        return $orphans;
    }

    /**
     * Scan a specific type folder for orphaned directories
     *
     * @param   string  $basePath  Base path to scan (relative to JPATH_ROOT)
     * @param   string  $type      Type identifier (studies, teachers, series)
     *
     * @return  array  Array of orphan info objects
     *
     * @since 10.1.0
     */
    private static function scanTypeFolder(string $basePath, string $type): array
    {
        $absolutePath = Path::clean(JPATH_ROOT . '/' . $basePath);
        $orphans      = [];

        if (!is_dir($absolutePath)) {
            return $orphans;
        }

        // Get all subdirectories in this folder
        $folders = Folder::folders($absolutePath, '.', false, false);

        if (empty($folders)) {
            return $orphans;
        }

        // Get all valid record IDs from database
        $validIds = self::getValidIds($type);

        foreach ($folders as $folder) {
            // Extract ID from folder name (format: alias-ID or just ID)
            $folderId = self::extractIdFromFolderName($folder);

            if ($folderId !== null && !\in_array($folderId, $validIds, true)) {
                $folderPath         = $basePath . '/' . $folder;
                $absoluteFolderPath = $absolutePath . '/' . $folder;

                if (!self::looksLikeAppManagedFolder($absoluteFolderPath)) {
                    continue;
                }

                $orphans[] = [
                    'path'         => $folderPath,
                    'name'         => $folder,
                    'size'         => self::getFolderSize($absoluteFolderPath),
                    'files'        => self::getFileCount($absoluteFolderPath),
                    'extracted_id' => $folderId,
                ];
            }
        }

        return $orphans;
    }

    /**
     * Extract ID from folder name
     *
     * Supports formats:
     * - "alias-123" -> 123
     * - "123" -> 123
     *
     * @param   string  $folderName  Folder name
     *
     * @return  int|null  Extracted ID or null if not found
     *
     * @since 10.1.0
     */
    private static function extractIdFromFolderName(string $folderName): ?int
    {
        // Try format: alias-ID. Anchored end-to-end so a name like
        // "export-2026-08-04" doesn't loosely match on its trailing "-04" --
        // aliases are OutputFilter::stringURLSafe() output (lowercase
        // alphanumeric + hyphens only), so anything outside that class can't
        // be a real alias-ID folder.
        if (preg_match('/^[a-z0-9-]+-(\d+)$/', $folderName, $matches)) {
            return (int) $matches[1];
        }

        // Try format: just a number
        if (preg_match('/^(\d+)$/', $folderName, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Check whether a folder's contents look like something the app actually
     * created, as a second signal alongside the ID-suffix match. An empty
     * folder is treated as safe (nothing to lose); a non-empty folder must
     * contain at least one recognized image file. This is what actually
     * excludes a stray directory like "export-2026-08-04" -- its trailing
     * "-04" satisfies the alias-ID pattern just as well as a real folder's
     * does, so the regex alone can't tell them apart.
     *
     * @param   string  $absoluteFolderPath  Absolute path to the folder
     *
     * @return  bool
     *
     * @since 10.5.6
     */
    private static function looksLikeAppManagedFolder(string $absoluteFolderPath): bool
    {
        $files = Folder::files($absoluteFolderPath, '.', true, false);

        if (empty($files)) {
            return true;
        }

        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            if (\in_array($ext, self::IMAGE_EXTENSIONS, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get valid record IDs for a specific type
     *
     * @param   string  $type  Type: studies, teachers, series
     *
     * @return  array  Array of valid integer IDs
     *
     * @since 10.1.0
     */
    private static function getValidIds(string $type): array
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery();

        $table = match ($type) {
            'studies'  => '#__bsms_studies',
            'teachers' => '#__bsms_teachers',
            'series'   => '#__bsms_series',
            default    => null
        };

        if ($table === null) {
            return [];
        }

        $query->select($db->quoteName('id'))->from($db->quoteName($table));
        $db->setQuery($query);

        $results = $db->loadColumn();

        return $results ? array_map('intval', $results) : [];
    }

    /**
     * Get folder size in bytes
     *
     * @param   string  $path  Absolute path to folder
     *
     * @return  int  Size in bytes
     *
     * @since 10.1.0
     */
    private static function getFolderSize(string $path): int
    {
        $size = 0;

        if (!is_dir($path)) {
            return $size;
        }

        $files = Folder::files($path, '.', true, true);

        foreach ($files as $file) {
            if (is_file($file)) {
                $size += filesize($file);
            }
        }

        return $size;
    }

    /**
     * Get file count in folder
     *
     * @param   string  $path  Absolute path to folder
     *
     * @return  int  Number of files
     *
     * @since 10.1.0
     */
    private static function getFileCount(string $path): int
    {
        if (!is_dir($path)) {
            return 0;
        }

        $files = Folder::files($path, '.', true, false);

        return \count($files);
    }

    /**
     * Delete selected orphan folders
     *
     * @param   array  $folderPaths  Array of relative folder paths to delete
     *
     * @return  array{deleted: int, errors: array}
     *
     * @since 10.1.0
     */
    public static function deleteOrphans(array $folderPaths): array
    {
        $deleted        = 0;
        $errors         = [];
        $validIdsByType = [];

        foreach ($folderPaths as $path) {
            // SAFETY: Validate path is within allowed scope. String-prefix
            // check only -- Path::clean() below doesn't resolve '../', so
            // this alone is not authoritative (see the realpath() check
            // further down, which is).
            $matchedBase = null;

            foreach (self::ALLOWED_PATHS as $allowedBase) {
                if ($path === $allowedBase || str_starts_with($path, $allowedBase . '/')) {
                    $matchedBase = $allowedBase;
                    break;
                }
            }

            if ($matchedBase === null) {
                $errors[] = 'Path not allowed: ' . $path;
                Log::add('Cleanup rejected path outside scope: ' . $path, Log::WARNING, 'com_proclaim');
                continue;
            }

            $absolutePath = Path::clean(JPATH_ROOT . '/' . $path);

            if (!is_dir($absolutePath)) {
                $errors[] = 'Folder not found: ' . $path;
                continue;
            }

            // SAFETY: authoritative scope check -- resolves '../' and
            // symlinks before anything is deleted, unlike the string-prefix
            // check above.
            $realPath = realpath($absolutePath);

            if ($realPath === false || !self::isWithinAllowedBases($realPath)) {
                $errors[] = 'Path not allowed: ' . $path;
                Log::add('Cleanup rejected path outside scope after resolution: ' . $path, Log::WARNING, 'com_proclaim');
                continue;
            }

            // TOCTOU guard: findOrphanedFolders() (scan) and deleteOrphans()
            // (delete) are separate round trips with no lock/version token
            // between them. Re-derive the folder's ID and re-check it
            // against current DB state immediately before deleting, in case
            // a restore/import re-created the record in between.
            $type     = basename($matchedBase);
            $folderId = self::extractIdFromFolderName(basename($path));

            if ($folderId !== null) {
                if (!\array_key_exists($type, $validIdsByType)) {
                    $validIdsByType[$type] = self::getValidIds($type);
                }

                if (\in_array($folderId, $validIdsByType[$type], true)) {
                    $errors[] = 'Skipped (no longer orphaned): ' . $path;
                    Log::add('Cleanup skipped folder that is no longer orphaned: ' . $path, Log::WARNING, 'com_proclaim');
                    continue;
                }
            }

            if (Folder::delete($absolutePath)) {
                $deleted++;
                Log::add('Cleanup deleted orphan folder: ' . $path, Log::INFO, 'com_proclaim');
            } else {
                $errors[] = 'Failed to delete: ' . $path;
            }
        }

        return ['deleted' => $deleted, 'errors' => $errors];
    }

    /**
     * Check whether a resolved real path is within (or equal to) one of the
     * allowed base directories, using realpath() so '../' traversal and
     * symlinks can't escape the intended scope. Mirrors
     * CwmImageMigration::isWithinAllowedBases().
     *
     * @param   string  $realPath  Resolved real path of the target directory
     *
     * @return  bool
     *
     * @since 10.5.6
     */
    private static function isWithinAllowedBases(string $realPath): bool
    {
        foreach (self::ALLOWED_PATHS as $base) {
            $realBase = realpath(JPATH_ROOT . '/' . $base);

            if ($realBase === false) {
                continue;
            }

            if ($realPath === $realBase || str_starts_with($realPath . \DIRECTORY_SEPARATOR, $realBase . \DIRECTORY_SEPARATOR)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get summary totals for orphaned folders
     *
     * @param   array  $orphans  Orphans array from findOrphanedFolders()
     *
     * @return  array{folders: int, size: int, size_formatted: string}
     *
     * @since 10.1.0
     */
    public static function getTotals(array $orphans): array
    {
        $totalFolders = 0;
        $totalSize    = 0;

        foreach ($orphans as $folders) {
            $totalFolders += \count($folders);
            foreach ($folders as $folder) {
                $totalSize += $folder['size'];
            }
        }

        return [
            'folders'        => $totalFolders,
            'size'           => $totalSize,
            'size_formatted' => self::formatBytes($totalSize),
        ];
    }

    /**
     * Format bytes to human readable string
     *
     * @param   int  $bytes  Size in bytes
     *
     * @return  string  Formatted string (e.g., "1.5 MB")
     *
     * @since 10.1.0
     */
    public static function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 Bytes';
        }

        $k     = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i     = (int) floor(log($bytes) / log($k));

        return round($bytes / ($k ** $i), 2) . ' ' . $sizes[$i];
    }

    /**
     * Image file extensions recognised for cleanup
     */
    private const array IMAGE_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico',
    ];

    /**
     * Clean up an old media-file image when the filename changes on save.
     *
     * Checks whether the old file is still referenced by any OTHER media record
     * on the same server. If no other record uses it the file is deleted via the
     * addon's deleteFile() (which respects the server's `delete_files` setting).
     *
     * @param   string  $oldFilename  Previous filename (from DB before save)
     * @param   string  $newFilename  New filename being saved
     * @param   int     $serverId     Server ID for both old and new file
     * @param   int     $recordId     The media-file record being saved (excluded from ref count)
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public static function cleanupOldMediaImage(
        string $oldFilename,
        string $newFilename,
        int $serverId,
        int $recordId
    ): void {
        // Nothing to clean up if the filename hasn't changed or was empty
        if (empty($oldFilename) || $oldFilename === $newFilename) {
            return;
        }

        // Only clean up image files
        $ext = strtolower(pathinfo($oldFilename, PATHINFO_EXTENSION));

        if (!\in_array($ext, self::IMAGE_EXTENSIONS, true)) {
            return;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Load server type so we can instantiate the correct addon
        $query = $db->createQuery()
            ->select([$db->quoteName('type'), $db->quoteName('params')])
            ->from($db->quoteName('#__bsms_servers'))
            ->where($db->quoteName('id') . ' = ' . $serverId);
        $db->setQuery($query);
        $server = $db->loadObject();

        if (!$server || empty($server->type)) {
            return;
        }

        // Re-check other-record references immediately before the delete
        // call below, narrowing (not closing) the TOCTOU window: a Batch
        // Copy that duplicates this row's filename between this check and
        // deleteFile() could still race past it. There's no unique
        // constraint or lock backing this check -- and the check itself is
        // already an approximate LIKE-scan of the params JSON blob (a
        // substring match, so it over-counts and fails safe rather than
        // under-counts) -- so a real fix would need a proper
        // reference-counted asset table. Accepted as a residual risk.
        if (self::countOtherReferences($db, $oldFilename, $serverId, $recordId) > 0) {
            Log::add(
                'Image cleanup: skipping ' . $oldFilename . ' — still referenced by another record',
                Log::INFO,
                'com_proclaim'
            );

            return;
        }

        try {
            $addon        = CWMAddon::getInstance($server->type);
            $serverParams = new Registry($server->params ?: '{}');
            $addon->deleteFile($oldFilename, $serverParams);
        } catch (\Exception $e) {
            Log::add(
                'Image cleanup: failed to delete old image ' . $oldFilename . ' — ' . $e->getMessage(),
                Log::WARNING,
                'com_proclaim'
            );
        }
    }

    /**
     * Count media-file records (other than $recordId) on $serverId whose
     * params JSON references $filename.
     *
     * @param   DatabaseInterface  $db          Database instance
     * @param   string             $filename    Filename to search for
     * @param   int                $serverId    Server ID
     * @param   int                $recordId    Record ID to exclude
     *
     * @return  int
     *
     * @since 10.5.6
     */
    private static function countOtherReferences(DatabaseInterface $db, string $filename, int $serverId, int $recordId): int
    {
        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__bsms_mediafiles'))
            ->where($db->quoteName('server_id') . ' = ' . $serverId)
            ->where($db->quoteName('id') . ' != ' . $recordId)
            // The filename is stored inside the JSON params column
            ->where($db->quoteName('params') . ' LIKE ' . $db->quote('%' . $db->escape($filename, true) . '%'));
        $db->setQuery($query);

        return (int) $db->loadResult();
    }
}
