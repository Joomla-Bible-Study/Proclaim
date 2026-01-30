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

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;

/**
 * Image Cleanup Helper - Find and remove orphaned image folders
 *
 * @package  Proclaim.Admin
 * @since    10.2.0
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
     * @since 10.2.0
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
     * @since 10.2.0
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
     * @since 10.2.0
     */
    private static function extractIdFromFolderName(string $folderName): ?int
    {
        // Try format: alias-ID (ends with -number)
        if (preg_match('/-(\d+)$/', $folderName, $matches)) {
            return (int) $matches[1];
        }

        // Try format: just a number
        if (preg_match('/^(\d+)$/', $folderName, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Get valid record IDs for a specific type
     *
     * @param   string  $type  Type: studies, teachers, series
     *
     * @return  array  Array of valid integer IDs
     *
     * @since 10.2.0
     */
    private static function getValidIds(string $type): array
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);

        $table = match ($type) {
            'studies'  => '#__bsms_studies',
            'teachers' => '#__bsms_teachers',
            'series'   => '#__bsms_series',
            default    => null
        };

        if ($table === null) {
            return [];
        }

        $query->select('id')->from($table);
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
     * @since 10.2.0
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
     * @since 10.2.0
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
     * @since 10.2.0
     */
    public static function deleteOrphans(array $folderPaths): array
    {
        $deleted = 0;
        $errors  = [];

        foreach ($folderPaths as $path) {
            // SAFETY: Validate path is within allowed scope
            $isAllowed = false;
            foreach (self::ALLOWED_PATHS as $allowedBase) {
                if (str_starts_with($path, $allowedBase . '/')) {
                    $isAllowed = true;
                    break;
                }
            }

            if (!$isAllowed) {
                $errors[] = 'Path not allowed: ' . $path;
                Log::add('Cleanup rejected path outside scope: ' . $path, Log::WARNING, 'com_proclaim');
                continue;
            }

            $absolutePath = Path::clean(JPATH_ROOT . '/' . $path);

            if (is_dir($absolutePath)) {
                if (Folder::delete($absolutePath)) {
                    $deleted++;
                    Log::add('Cleanup deleted orphan folder: ' . $path, Log::INFO, 'com_proclaim');
                } else {
                    $errors[] = 'Failed to delete: ' . $path;
                }
            } else {
                $errors[] = 'Folder not found: ' . $path;
            }
        }

        return ['deleted' => $deleted, 'errors' => $errors];
    }

    /**
     * Get summary totals for orphaned folders
     *
     * @param   array  $orphans  Orphans array from findOrphanedFolders()
     *
     * @return  array{folders: int, size: int, size_formatted: string}
     *
     * @since 10.2.0
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
     * @since 10.2.0
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
}
