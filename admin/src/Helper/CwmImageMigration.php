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

        // Find the original image (not the thumbnail)
        $oldAbsPath = Path::clean(JPATH_ROOT . '/' . $oldPath);

        // If this is a thumbnail path, try to find the original
        $sourceImage = $oldPath;
        if (str_contains(basename($oldPath), 'thumb_')) {
            $originalName     = str_replace('thumb_', '', basename($oldPath));
            $possibleOriginal = \dirname($oldPath) . '/' . $originalName;
            $possibleAbsPath  = Path::clean(JPATH_ROOT . '/' . $possibleOriginal);

            if (is_file($possibleAbsPath)) {
                $sourceImage = $possibleOriginal;
            } elseif (!is_file($oldAbsPath)) {
                return ['success' => false, 'newPath' => null, 'error' => 'Source file not found'];
            }
        } elseif (!is_file($oldAbsPath)) {
            return ['success' => false, 'newPath' => null, 'error' => 'Source file not found'];
        }

        // Create new thumbnail using Cwmthumbnail
        $result = Cwmthumbnail::create($sourceImage, $newFolder, $thumbSize, $title);

        if ($result === false) {
            return ['success' => false, 'newPath' => null, 'error' => 'Failed to create thumbnail'];
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

        return ['success' => true, 'newPath' => $result['thumbnail'], 'error' => null];
    }

    /**
     * Get a batch of records to migrate
     *
     * @param   string  $type   Record type
     * @param   int     $limit  Maximum number of records to return
     *
     * @return  array{records: array, remaining: int}
     *
     * @since 10.2.0
     */
    public static function getBatch(string $type, int $limit = 10): array
    {
        $records   = self::getRecordsNeedingMigration($type);
        $batch     = \array_slice($records, 0, $limit);
        $remaining = \count($records) - \count($batch);

        return [
            'records'   => $batch,
            'remaining' => $remaining,
        ];
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
        $result  = ['converted' => 0, 'errors' => 0, 'remaining' => 0];

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
                    $result['converted']++;
                } catch (\Exception $e) {
                    $result['errors']++;
                }

                $processed++;
            }
        }

        // Count remaining across all not-yet-scanned dirs
        $result['remaining'] = $total - $processed;

        return $result;
    }
}
