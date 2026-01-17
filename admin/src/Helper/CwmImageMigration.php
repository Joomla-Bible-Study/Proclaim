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
                $query->select('id, studytitle, alias, thumbnailm as image_path')
                    ->from('#__bsms_studies')
                    ->where('thumbnailm IS NOT NULL')
                    ->where('thumbnailm != ' . $db->q(''))
                    ->where('thumbnailm NOT LIKE ' . $db->q('images/biblestudy/studies/%-%/%'));
                break;

            case 'teachers':
                $query->select('id, teachername, alias, teacher_thumbnail as image_path')
                    ->from('#__bsms_teachers')
                    ->where('teacher_thumbnail IS NOT NULL')
                    ->where('teacher_thumbnail != ' . $db->q(''))
                    ->where('teacher_thumbnail NOT LIKE ' . $db->q('images/biblestudy/teachers/%-%/%'));
                break;

            case 'series':
                $query->select('id, series_text as title, alias, series_thumbnail as image_path')
                    ->from('#__bsms_series')
                    ->where('series_thumbnail IS NOT NULL')
                    ->where('series_thumbnail != ' . $db->q(''))
                    ->where('series_thumbnail NOT LIKE ' . $db->q('images/biblestudy/series/%-%/%'));
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
                $query->update('#__bsms_studies')
                    ->set('thumbnailm = ' . $db->q($result['thumbnail']))
                    ->set('image = ' . $db->q($result['image']))
                    ->where('id = ' . $id);
                break;

            case 'teachers':
                $query->update('#__bsms_teachers')
                    ->set('teacher_thumbnail = ' . $db->q($result['thumbnail']))
                    ->set('teacher_image = ' . $db->q($result['image']))
                    ->set('image = ' . $db->q($result['image']))
                    ->where('id = ' . $id);
                break;

            case 'series':
                $query->update('#__bsms_series')
                    ->set('series_thumbnail = ' . $db->q($result['thumbnail']))
                    ->set('image = ' . $db->q($result['image']))
                    ->where('id = ' . $id);
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
}
