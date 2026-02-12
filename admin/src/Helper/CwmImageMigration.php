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

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Image\Image;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;

/**
 * Image Migration Helper - Migrate existing images to new folder structure
 *
 * @package  Proclaim.Admin
 * @since    10.2.0
 */
class CwmImageMigration
{
    /**
     * Get records needing migration for a specific type
     *
     * @param   string  $type  Record type: 'studies', 'teachers', or 'series'
     *
     * @return  array  Records with images not in the new folder structure
     *
     * @since 10.2.0
     */
    public static function getRecordsNeedingMigration(string $type): array
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);

        switch ($type) {
            case 'studies':
                $query->select(
                    $db->qn('id') . ', ' . $db->qn('studytitle') . ', ' . $db->qn('alias') . ', '
                    . $db->qn('thumbnailm', 'image_path')
                )
                    ->from($db->qn('#__bsms_studies'))
                    ->where($db->qn('thumbnailm') . ' IS NOT NULL')
                    ->where($db->qn('thumbnailm') . ' != ' . $db->q(''))
                    ->where('LENGTH(' . $db->qn('thumbnailm') . ') > 1')
                    ->where($db->qn('thumbnailm') . ' NOT LIKE ' . $db->q('images/biblestudy/studies/%-%/%'));
                break;

            case 'teachers':
                $query->select(
                    $db->qn('id') . ', ' . $db->qn('teachername') . ', ' . $db->qn('alias') . ', '
                    . $db->qn('teacher_thumbnail', 'image_path')
                )
                    ->from($db->qn('#__bsms_teachers'))
                    ->where($db->qn('teacher_thumbnail') . ' IS NOT NULL')
                    ->where($db->qn('teacher_thumbnail') . ' != ' . $db->q(''))
                    ->where('LENGTH(' . $db->qn('teacher_thumbnail') . ') > 1')
                    ->where($db->qn('teacher_thumbnail') . ' NOT LIKE ' . $db->q('images/biblestudy/teachers/%-%/%'));
                break;

            case 'series':
                $query->select(
                    $db->qn('id') . ', ' . $db->qn('series_text', 'title') . ', ' . $db->qn('alias') . ', '
                    . $db->qn('series_thumbnail', 'image_path')
                )
                    ->from($db->qn('#__bsms_series'))
                    ->where($db->qn('series_thumbnail') . ' IS NOT NULL')
                    ->where($db->qn('series_thumbnail') . ' != ' . $db->q(''))
                    ->where('LENGTH(' . $db->qn('series_thumbnail') . ') > 1')
                    ->where($db->qn('series_thumbnail') . ' NOT LIKE ' . $db->q('images/biblestudy/series/%-%/%'));
                break;

            default:
                return [];
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Get migration counts for all types
     *
     * @return  array{studies: int, teachers: int, series: int, total: int}
     *
     * @since 10.2.0
     */
    public static function getMigrationCounts(): array
    {
        $counts = [
            'studies'  => \count(self::getRecordsNeedingMigration('studies')),
            'teachers' => \count(self::getRecordsNeedingMigration('teachers')),
            'series'   => \count(self::getRecordsNeedingMigration('series')),
        ];

        $counts['total'] = $counts['studies'] + $counts['teachers'] + $counts['series'];

        return $counts;
    }

    /**
     * Look up a record from the DB and migrate its images
     *
     * @param   string  $type  Record type: 'studies', 'teachers', or 'series'
     * @param   int     $id    Record ID
     *
     * @return  array{success: bool, newPath: ?string, error: ?string}
     *
     * @since 10.2.0
     */
    public static function migrateRecordById(string $type, int $id): array
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);

        switch ($type) {
            case 'studies':
                $query->select($db->qn(['studytitle', 'thumbnailm']))
                    ->from($db->qn('#__bsms_studies'))
                    ->where($db->qn('id') . ' = ' . (int) $id);
                break;

            case 'teachers':
                $query->select($db->qn('teachername') . ', ' . $db->qn('teacher_thumbnail'))
                    ->from($db->qn('#__bsms_teachers'))
                    ->where($db->qn('id') . ' = ' . (int) $id);
                break;

            case 'series':
                $query->select($db->qn('series_text') . ', ' . $db->qn('series_thumbnail'))
                    ->from($db->qn('#__bsms_series'))
                    ->where($db->qn('id') . ' = ' . (int) $id);
                break;

            default:
                return ['success' => false, 'newPath' => null, 'error' => 'Invalid type: ' . $type];
        }

        $db->setQuery($query);
        $row = $db->loadObject();

        if (!$row) {
            return ['success' => false, 'newPath' => null, 'error' => 'Record not found: ' . $type . ' #' . $id];
        }

        // Extract title and image path from the row
        $title   = $row->studytitle ?? $row->teachername ?? $row->series_text ?? '';
        $oldPath = trim($row->thumbnailm ?? $row->teacher_thumbnail ?? $row->series_thumbnail ?? '');

        // Treat '0', single-char junk, and empty as "no image" — skip (not migratable)
        if ($oldPath === '' || \strlen($oldPath) <= 1) {
            return ['success' => false, 'newPath' => null, 'error' => 'No valid image path for ' . $type . ' #' . $id, 'junk' => true];
        }

        return self::migrateRecord($type, $id, $title, $oldPath);
    }

    /**
     * Migrate a single record's images to the new folder structure
     *
     * @param   string  $type       Record type: 'studies', 'teachers', or 'series'
     * @param   int     $id         Record ID
     * @param   string  $title      Record title for folder naming
     * @param   string  $oldPath    Current image path
     * @param   int     $thumbSize  Thumbnail size (default: 300)
     *
     * @return  array{success: bool, newPath: ?string, error: ?string}
     *
     * @since 10.2.0
     */
    public static function migrateRecord(
        string $type,
        int $id,
        string $title,
        string $oldPath,
        int $thumbSize = 300
    ): array {
        // Build the new folder path with title-ID format
        $alias     = ApplicationHelper::stringURLSafe($title ?: $type);
        $newFolder = 'images/biblestudy/' . $type . '/' . $alias . '-' . $id;

        // Clean Joomla media field metadata (#joomlaImage://...)
        $cleanImage = HTMLHelper::_('cleanImageURL', $oldPath);
        $cleanPath  = $cleanImage->url;

        // Handle bare filenames (no directory separator) by prepending configured image folder
        if (!str_contains($cleanPath, '/')) {
            $folderKeys = [
                'studies'  => 'image_folder',
                'teachers' => 'teacher_image_folder',
                'series'   => 'series_image_folder',
            ];
            $folderKey   = $folderKeys[$type] ?? 'image_folder';
            $imageFolder = Cwmparams::getAdmin()->params->get($folderKey, 'images');
            $cleanPath   = $imageFolder . '/' . $cleanPath;
        }

        // Find the original image (not the thumbnail)
        $oldAbsPath = Path::clean(JPATH_ROOT . '/' . $cleanPath);

        // If this is a thumbnail path, try to find the original full-size image first
        $sourceImage = $cleanPath;
        $sourceFound = true;
        $relocated   = false;
        $foundAt     = null;
        $basename    = basename($cleanPath);

        if (str_contains($basename, 'thumb_')) {
            $originalName     = str_replace('thumb_', '', $basename);
            $possibleOriginal = \dirname($cleanPath) . '/' . $originalName;
            $possibleAbsPath  = Path::clean(JPATH_ROOT . '/' . $possibleOriginal);

            if (is_file($possibleAbsPath)) {
                $sourceImage = $possibleOriginal;
            } elseif (is_file($oldAbsPath)) {
                // Thumbnail exists, use it as source
                $sourceImage = $cleanPath;
            } else {
                $sourceFound = false;
            }
        } elseif (!is_file($oldAbsPath)) {
            $sourceFound = false;
        }

        // Fallback: search the images directory tree for a matching filename
        if (!$sourceFound) {
            $searchName = str_contains($basename, 'thumb_')
                ? str_replace('thumb_', '', $basename)
                : $basename;

            $found = self::findImageFile($searchName, $type);

            if ($found !== null) {
                $sourceImage = $found;
                $sourceFound = true;
                $relocated   = true;
                $foundAt     = $found;
            }
        }

        // If source file is still missing, report it but do NOT clear the DB field.
        // The admin can clear unresolvable records manually via the dedicated button.
        if (!$sourceFound) {
            return [
                'success'      => false,
                'newPath'      => null,
                'error'        => 'Source file not found',
                'missingPath'  => $cleanPath,
                'absolutePath' => $oldAbsPath,
            ];
        }

        // Create new thumbnail using Cwmthumbnail
        try {
            $result = Cwmthumbnail::create($sourceImage, $newFolder, $thumbSize, $title);
        } catch (\Throwable $e) {
            return [
                'success'     => false,
                'newPath'     => null,
                'error'       => 'Thumbnail error: ' . $e->getMessage(),
                'missingPath' => $cleanPath,
            ];
        }

        if ($result === false) {
            return [
                'success'     => false,
                'newPath'     => null,
                'error'       => 'Failed to create thumbnail (file copy or GD init failed)',
                'missingPath' => $cleanPath,
            ];
        }

        // Update database
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);

        switch ($type) {
            case 'studies':
                $query->update($db->qn('#__bsms_studies'))
                    ->set($db->qn('thumbnailm') . ' = ' . $db->q($result['thumbnail']))
                    ->set($db->qn('image') . ' = ' . $db->q($result['image']))
                    ->where($db->qn('id') . ' = ' . (int) $id);
                break;

            case 'teachers':
                $query->update($db->qn('#__bsms_teachers'))
                    ->set($db->qn('teacher_thumbnail') . ' = ' . $db->q($result['thumbnail']))
                    ->set($db->qn('teacher_image') . ' = ' . $db->q($result['image']))
                    ->set($db->qn('image') . ' = ' . $db->q($result['image']))
                    ->where($db->qn('id') . ' = ' . (int) $id);
                break;

            case 'series':
                $query->update($db->qn('#__bsms_series'))
                    ->set($db->qn('series_thumbnail') . ' = ' . $db->q($result['thumbnail']))
                    ->set($db->qn('image') . ' = ' . $db->q($result['image']))
                    ->where($db->qn('id') . ' = ' . (int) $id);
                break;
        }

        $db->setQuery($query);
        $db->execute();

        $response = ['success' => true, 'newPath' => $result['thumbnail'], 'error' => null];

        if ($relocated) {
            $response['relocated']    = true;
            $response['originalPath'] = $cleanPath;
            $response['foundAt']      = $foundAt;
        }

        return $response;
    }

    /**
     * Get a batch of records to migrate
     *
     * @param   string  $type        Record type
     * @param   int     $limit       Maximum number of records to return
     * @param   int[]   $excludeIds  Record IDs to skip (already failed)
     *
     * @return  array{records: array, remaining: int}
     *
     * @since 10.2.0
     */
    public static function getBatch(string $type, int $limit = 10, array $excludeIds = []): array
    {
        $records = self::getRecordsNeedingMigration($type);

        // Filter out records that already failed — they stay in the DB
        // but the JS tracks them and sends their IDs to skip
        if (!empty($excludeIds)) {
            $records = array_values(array_filter(
                $records,
                fn ($r) => !\in_array((int) $r->id, $excludeIds, true)
            ));
        }

        $batch     = \array_slice($records, 0, $limit);
        $remaining = \count($records) - \count($batch);

        return [
            'records'   => $batch,
            'remaining' => $remaining,
        ];
    }

    /**
     * Clear the image field for a record so it won't be returned by getRecordsNeedingMigration()
     *
     * Called when the source file is missing — the image is gone, nothing to migrate.
     * Logs the cleared value to a CSV file so admins can search for the file manually.
     *
     * @param   string  $type     Record type: 'studies', 'teachers', or 'series'
     * @param   int     $id       Record ID
     * @param   string  $oldPath  The image path being cleared (for the log)
     * @param   string  $title    Record title (for the log)
     *
     * @return  void
     *
     * @since 10.2.0
     */
    private static function clearImageField(string $type, int $id, string $oldPath = '', string $title = ''): void
    {
        // Log the cleared value before wiping it
        self::logClearedImage($type, $id, $oldPath, $title);

        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);

        switch ($type) {
            case 'studies':
                $query->update($db->qn('#__bsms_studies'))
                    ->set($db->qn('thumbnailm') . ' = ' . $db->q(''))
                    ->where($db->qn('id') . ' = ' . (int) $id);
                break;

            case 'teachers':
                $query->update($db->qn('#__bsms_teachers'))
                    ->set($db->qn('teacher_thumbnail') . ' = ' . $db->q(''))
                    ->where($db->qn('id') . ' = ' . (int) $id);
                break;

            case 'series':
                $query->update($db->qn('#__bsms_series'))
                    ->set($db->qn('series_thumbnail') . ' = ' . $db->q(''))
                    ->where($db->qn('id') . ' = ' . (int) $id);
                break;
        }

        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Log a cleared image value to CSV for administrator review
     *
     * Writes to administrator/logs/proclaim_cleared_images.csv so admins
     * can search other locations for the missing files.
     *
     * @param   string  $type     Record type
     * @param   int     $id       Record ID
     * @param   string  $oldPath  The image path that was cleared
     * @param   string  $title    Record title for identification
     *
     * @return  void
     *
     * @since 10.2.0
     */
    private static function logClearedImage(string $type, int $id, string $oldPath, string $title): void
    {
        $logFile = JPATH_ADMINISTRATOR . '/logs/proclaim_cleared_images.csv';

        // Write header row if this is a new file
        if (!is_file($logFile)) {
            file_put_contents($logFile, "Date,Type,ID,Title,Cleared Path\n");
        }

        $row = [
            date('Y-m-d H:i:s'),
            $type,
            (string) $id,
            str_replace(['"', "\n", "\r"], ['""', ' ', ' '], $title),
            str_replace('"', '""', $oldPath),
        ];

        file_put_contents($logFile, '"' . implode('","', $row) . "\"\n", FILE_APPEND);
    }

    /**
     * Get the path to the cleared images log file
     *
     * @return  string  Absolute path to the CSV log
     *
     * @since 10.2.0
     */
    public static function getClearedLogPath(): string
    {
        return JPATH_ADMINISTRATOR . '/logs/proclaim_cleared_images.csv';
    }

    /**
     * Find all records with image paths that cannot be resolved to real files
     *
     * Checks each record returned by getRecordsNeedingMigration() to see if
     * the source file actually exists on disk. Returns those that don't.
     *
     * @return  array{records: list<array{type: string, id: int, title: string, path: string}>, count: int}
     *
     * @since 10.2.0
     */
    public static function getUnresolvableRecords(): array
    {
        $unresolvable = [];

        foreach (['studies', 'teachers', 'series'] as $type) {
            $records = self::getRecordsNeedingMigration($type);

            foreach ($records as $row) {
                $imagePath = trim($row->image_path ?? '');

                // Junk values are always unresolvable
                if ($imagePath === '' || \strlen($imagePath) <= 1) {
                    $title = $row->studytitle ?? $row->teachername ?? $row->title ?? '';
                    $unresolvable[] = ['type' => $type, 'id' => (int) $row->id, 'title' => $title, 'path' => $imagePath];

                    continue;
                }

                // Clean Joomla media field metadata
                $cleanImage = HTMLHelper::_('cleanImageURL', $imagePath);
                $cleanPath  = $cleanImage->url;

                // Handle bare filenames
                if (!str_contains($cleanPath, '/')) {
                    $folderKeys = [
                        'studies'  => 'image_folder',
                        'teachers' => 'teacher_image_folder',
                        'series'   => 'series_image_folder',
                    ];
                    $folderKey   = $folderKeys[$type] ?? 'image_folder';
                    $imageFolder = Cwmparams::getAdmin()->params->get($folderKey, 'images');
                    $cleanPath   = $imageFolder . '/' . $cleanPath;
                }

                $absPath  = Path::clean(JPATH_ROOT . '/' . $cleanPath);
                $basename = basename($cleanPath);

                // Check if original or thumbnail exists
                $found = false;

                if (str_contains($basename, 'thumb_')) {
                    $originalName    = str_replace('thumb_', '', $basename);
                    $possibleOriginal = \dirname($cleanPath) . '/' . $originalName;

                    if (is_file(Path::clean(JPATH_ROOT . '/' . $possibleOriginal)) || is_file($absPath)) {
                        $found = true;
                    }
                } elseif (is_file($absPath)) {
                    $found = true;
                }

                // Fallback: search image directories
                if (!$found) {
                    $searchName = str_contains($basename, 'thumb_')
                        ? str_replace('thumb_', '', $basename)
                        : $basename;
                    $found = self::findImageFile($searchName, $type) !== null;
                }

                if (!$found) {
                    $title = $row->studytitle ?? $row->teachername ?? $row->title ?? '';
                    $unresolvable[] = ['type' => $type, 'id' => (int) $row->id, 'title' => $title, 'path' => $cleanPath];
                }
            }
        }

        return ['records' => $unresolvable, 'count' => \count($unresolvable)];
    }

    /**
     * Clear all unresolvable image fields from the database
     *
     * For each record whose image file cannot be found, clears the DB field
     * and logs the old value to CSV. This is a manual opt-in operation — the
     * admin must explicitly trigger it after reviewing the missing files.
     *
     * @return  array{cleared: int, logFile: string}
     *
     * @since 10.2.0
     */
    public static function clearUnresolvableImages(): array
    {
        $result = self::getUnresolvableRecords();
        $cleared = 0;

        foreach ($result['records'] as $record) {
            self::clearImageField($record['type'], $record['id'], $record['path'], $record['title']);
            $cleared++;
        }

        return ['cleared' => $cleared, 'logFile' => self::getClearedLogPath()];
    }

    /**
     * Search the images directory tree for a file by name
     *
     * Looks in the configured image folder and common locations to find a file
     * that matches the given filename. This handles the old naming convention
     * where images were stored as bare filenames without record IDs.
     *
     * @param   string  $filename  The filename to search for (e.g., 'sermon-title.jpg')
     * @param   string  $type      Record type for folder hints: 'studies', 'teachers', 'series'
     *
     * @return  ?string  Relative path from JPATH_ROOT if found, null otherwise
     *
     * @since 10.2.0
     */
    private static function findImageFile(string $filename, string $type): ?string
    {
        if (empty($filename)) {
            return null;
        }

        // Skip generic filenames that could match unrelated files
        $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
        $genericNames   = [
            'image', 'images', 'photo', 'picture', 'pic', 'img',
            'thumbnail', 'thumb', 'default', 'placeholder', 'no-image',
            'noimage', 'blank', 'sample', 'test', 'temp', 'untitled',
            'new', 'file', 'upload', 'download', 'icon', 'logo', 'banner',
        ];

        if (\in_array(strtolower($nameWithoutExt), $genericNames, true)) {
            return null;
        }

        // Configured image folders per type
        $folderKeys = [
            'studies'  => 'image_folder',
            'teachers' => 'teacher_image_folder',
            'series'   => 'series_image_folder',
        ];
        $folderKey     = $folderKeys[$type] ?? 'image_folder';
        $configuredDir = Cwmparams::getAdmin()->params->get($folderKey, 'images');

        // Directories to search, in priority order
        $searchDirs = [
            JPATH_ROOT . '/' . $configuredDir,
            JPATH_ROOT . '/images',
            JPATH_ROOT . '/images/biblestudy',
            JPATH_ROOT . '/images/biblestudy/' . $type,
            JPATH_ROOT . '/media/com_proclaim/images',
            JPATH_ROOT . '/media/com_proclaim/images/stockimages',
        ];

        // De-duplicate
        $searchDirs = array_unique(array_map(fn ($d) => rtrim($d, '/'), $searchDirs));

        foreach ($searchDirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            // Direct match in this directory
            $candidate = $dir . '/' . $filename;
            if (is_file($candidate)) {
                return self::makeRelative($candidate);
            }

            // Recursive search one level deep (subdirectories)
            $subDirs = Folder::folders($dir, '.', false, true);
            foreach ($subDirs as $subDir) {
                $candidate = $subDir . '/' . $filename;
                if (is_file($candidate)) {
                    return self::makeRelative($candidate);
                }
            }
        }

        return null;
    }

    /**
     * Convert an absolute path to a JPATH_ROOT-relative path
     *
     * @param   string  $absPath  Absolute filesystem path
     *
     * @return  string  Path relative to JPATH_ROOT
     *
     * @since 10.2.0
     */
    private static function makeRelative(string $absPath): string
    {
        $root = rtrim(JPATH_ROOT, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (str_starts_with($absPath, $root)) {
            return substr($absPath, \strlen($root));
        }

        return $absPath;
    }

    /**
     * Scan legacy image folders for leftover files after migration
     *
     * Checks the old folder locations from com_biblestudy/com_proclaim history
     * and reports any image files still present that are NOT inside the new
     * structured folders (images/biblestudy/{type}/{alias}-{id}/).
     *
     * @return array{folders: array<string, array{path: string, files: int, size: int, filenames: list<string>}>, total_files: int, total_size: int}
     *
     * @since 10.2.0
     */
    public static function getLegacyFolderReport(): array
    {
        $report = ['folders' => [], 'total_files' => 0, 'total_size' => 0];

        // Legacy locations specific to BibleStudy/Proclaim — never scan the
        // root images/ folder since that belongs to the entire Joomla site
        $admin         = Cwmparams::getAdmin();
        $imageFolder   = $admin->params->get('image_folder', 'images');
        $teacherFolder = $admin->params->get('teacher_image_folder', 'images');
        $seriesFolder  = $admin->params->get('series_image_folder', 'images');

        // Only include configured folders if they're Proclaim-specific (not the root images/)
        $genericRoots = ['images', 'media'];
        $candidates   = [$imageFolder, $teacherFolder, $seriesFolder];
        $configuredDirs = array_filter(
            $candidates,
            fn ($d) => !\in_array(rtrim($d, '/'), $genericRoots, true)
        );

        $legacyDirs = array_unique(array_filter(array_merge(
            $configuredDirs,
            [
                'images/biblestudy',
                'media/com_proclaim/images',
                'media/com_proclaim/images/stockimages',
            ]
        )));

        // New structured folder pattern — skip these
        $newPattern = '#^images/biblestudy/(studies|teachers|series)/[^/]+-\d+/#';

        $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];

        foreach ($legacyDirs as $relDir) {
            $absDir = JPATH_ROOT . '/' . $relDir;

            if (!is_dir($absDir)) {
                continue;
            }

            // Collect image files directly in this folder (not in subdirs)
            $directFiles = self::getImageFilesInDir($absDir, $imageExts);
            if (!empty($directFiles)) {
                $size = array_sum(array_map(fn ($f) => filesize($f), $directFiles));
                $report['folders'][] = [
                    'path'      => $relDir,
                    'files'     => \count($directFiles),
                    'size'      => $size,
                    'filenames' => array_map('basename', $directFiles),
                ];
                $report['total_files'] += \count($directFiles);
                $report['total_size']  += $size;
            }

            // Check subdirectories — but skip new structured folders
            $subDirs = Folder::folders($absDir, '.', false, true);
            foreach ($subDirs as $subDir) {
                $relSub = self::makeRelative($subDir);

                // Skip if it matches the new folder pattern (alias-ID)
                if (preg_match($newPattern, $relSub . '/')) {
                    continue;
                }

                // Skip non-image directories (media, etc.)
                $subName = basename($subDir);
                if (\in_array($subName, ['media', 'studies', 'teachers', 'series'], true)) {
                    // Check inside these for loose files (not in alias-ID subfolders)
                    $looseFiles = self::getImageFilesInDir($subDir, $imageExts);
                    if (!empty($looseFiles)) {
                        $size = array_sum(array_map(fn ($f) => filesize($f), $looseFiles));
                        $report['folders'][] = [
                            'path'      => $relSub,
                            'files'     => \count($looseFiles),
                            'size'      => $size,
                            'filenames' => array_map('basename', $looseFiles),
                        ];
                        $report['total_files'] += \count($looseFiles);
                        $report['total_size']  += $size;
                    }

                    continue;
                }

                $files = self::getImageFilesInDir($subDir, $imageExts);
                if (!empty($files)) {
                    $size = array_sum(array_map(fn ($f) => filesize($f), $files));
                    $report['folders'][] = [
                        'path'      => $relSub,
                        'files'     => \count($files),
                        'size'      => $size,
                        'filenames' => array_map('basename', $files),
                    ];
                    $report['total_files'] += \count($files);
                    $report['total_size']  += $size;
                }
            }
        }

        return $report;
    }

    /**
     * Get image files directly in a directory (non-recursive)
     *
     * @param   string  $dir   Absolute directory path
     * @param   array   $exts  Allowed extensions
     *
     * @return  list<string>  Absolute paths of image files found
     *
     * @since 10.2.0
     */
    private static function getImageFilesInDir(string $dir, array $exts): array
    {
        $pattern = '\\.(' . implode('|', $exts) . ')$';

        try {
            return Folder::files($dir, $pattern, false, true) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Delete legacy image files from selected folders
     *
     * Only deletes image files directly in the folder (not recursive).
     * Removes the folder afterwards if it becomes empty.
     * Validates paths are within known Proclaim image directories.
     *
     * @param   array  $folderPaths  Relative paths (e.g. 'images/biblestudy/123')
     *
     * @return  array{deleted: int, errors: list<string>}
     *
     * @since 10.1.0
     */
    public static function deleteLegacyFiles(array $folderPaths): array
    {
        $deleted = 0;
        $errors  = [];

        // Allowed base paths for safety — never delete outside Proclaim dirs
        $allowedBases = [
            'images/biblestudy',
            'media/com_proclaim/images',
        ];

        $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];

        foreach ($folderPaths as $relPath) {
            $relPath = trim($relPath, '/');

            // SAFETY: Validate path is within allowed scope
            $isAllowed = false;

            foreach ($allowedBases as $base) {
                if ($relPath === $base || str_starts_with($relPath, $base . '/')) {
                    $isAllowed = true;
                    break;
                }
            }

            if (!$isAllowed) {
                $errors[] = 'Path not allowed: ' . $relPath;
                continue;
            }

            $absDir = Path::clean(JPATH_ROOT . '/' . $relPath);

            if (!is_dir($absDir)) {
                $errors[] = 'Folder not found: ' . $relPath;
                continue;
            }

            // Delete only image files directly in this folder
            $files = self::getImageFilesInDir($absDir, $imageExts);
            $folderDeleted = 0;

            foreach ($files as $file) {
                if (@unlink($file)) {
                    $folderDeleted++;
                } else {
                    $errors[] = 'Failed to delete: ' . self::makeRelative($file);
                }
            }

            $deleted += $folderDeleted;

            // If the folder is now empty (no files, no subdirs), remove it
            $remaining = @scandir($absDir);

            if ($remaining !== false && \count($remaining) <= 2) {
                // Only . and .. remain — safe to remove
                Folder::delete($absDir);
            }
        }

        return ['deleted' => $deleted, 'errors' => $errors];
    }

    /**
     * Get count of images needing WebP conversion
     *
     * Scans image directories for JPEG/PNG files that lack a WebP sibling.
     *
     * @return array{studies: int, teachers: int, series: int, total: int}
     *
     * @since 10.1.0
     */
    public static function getWebPMigrationCounts(): array
    {
        $baseDirs = [
            'studies'  => JPATH_ROOT . '/images/biblestudy/studies',
            'teachers' => JPATH_ROOT . '/images/biblestudy/teachers',
            'series'   => JPATH_ROOT . '/images/biblestudy/series',
        ];

        $counts = ['studies' => 0, 'teachers' => 0, 'series' => 0];

        foreach ($baseDirs as $type => $baseDir) {
            if (!is_dir($baseDir)) {
                continue;
            }

            $subDirs = Folder::folders($baseDir, '.', false, true);

            foreach ($subDirs as $dir) {
                $files = Folder::files($dir, '\.(jpe?g|png)$', false, true);

                foreach ($files as $file) {
                    $webpSibling = preg_replace('/\.(jpe?g|png)$/i', '.webp', $file);

                    if (!is_file($webpSibling)) {
                        $counts[$type]++;
                    }
                }
            }
        }

        $counts['total'] = $counts['studies'] + $counts['teachers'] + $counts['series'];

        return $counts;
    }

    /**
     * Generate WebP variants for a batch of images in a given type directory
     *
     * @param   string  $type   Directory type: 'studies', 'teachers', or 'series'
     * @param   int     $limit  Maximum images to process per batch
     *
     * @return array{converted: int, errors: int, remaining: int}
     *
     * @since 10.1.0
     */
    public static function migrateToWebP(string $type, int $limit = 10): array
    {
        $baseDir = JPATH_ROOT . '/images/biblestudy/' . $type;
        $result  = ['converted' => 0, 'errors' => 0, 'remaining' => 0, 'errorFiles' => []];

        if (!is_dir($baseDir) || !\function_exists('imagewebp')) {
            return $result;
        }

        $subDirs   = Folder::folders($baseDir, '.', false, true);
        $processed = 0;
        $total     = 0;

        foreach ($subDirs as $dir) {
            $files = Folder::files($dir, '\.(jpe?g|png)$', false, true);

            foreach ($files as $file) {
                $webpPath = preg_replace('/\.(jpe?g|png)$/i', '.webp', $file);

                if (is_file($webpPath)) {
                    continue;
                }

                $total++;

                if ($processed >= $limit) {
                    $result['remaining']++;
                    continue;
                }

                try {
                    $image = new Image($file);
                    $image->toFile($webpPath, IMAGETYPE_WEBP);

                    // Verify the WebP file is a valid image
                    if (!is_file($webpPath) || filesize($webpPath) === 0) {
                        @unlink($webpPath);
                        throw new \RuntimeException('Output file is empty or missing');
                    }

                    $info = @getimagesize($webpPath);

                    if ($info === false || ($info[0] ?? 0) === 0) {
                        @unlink($webpPath);
                        throw new \RuntimeException('Output file is not a valid image');
                    }

                    $result['converted']++;
                } catch (\Exception $e) {
                    $result['errors']++;
                    $result['errorFiles'][] = self::makeRelative($file) . ': ' . $e->getMessage();
                }

                $processed++;
            }
        }

        // Count remaining across all not-yet-scanned dirs
        $result['remaining'] = $total - $processed;

        return $result;
    }
}
