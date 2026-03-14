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
use Joomla\CMS\Image\Image;
use Joomla\CMS\Log\Log;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;

/**
 * Thumbnail helper class
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class Cwmthumbnail
{
    public const int SCALE_INSIDE = 2;

    /**
     * Allowed base paths for image operations (safety whitelist)
     */
    private const array ALLOWED_PATHS = [
        'images/biblestudy/studies/',
        'images/biblestudy/teachers/',
        'images/biblestudy/series/',
    ];

    /**
     * Validate an image file before processing
     *
     * @param   string  $filePath       Absolute path to the file
     * @param   array   $allowedTypes   Allowed MIME types
     * @param   int     $maxSizeBytes   Maximum file size in bytes (default: 10MB)
     * @param   int     $maxDimension   Maximum width/height in pixels (default: 5000)
     *
     * @return  array{valid: bool, error: ?string}  Validation result
     *
     * @since 10.1.0
     */
    public static function validate(
        string $filePath,
        array $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        int $maxSizeBytes = 10485760,
        int $maxDimension = 5000
    ): array {
        // Check file exists and is readable
        if (!is_file($filePath) || !is_readable($filePath)) {
            return ['valid' => false, 'error' => 'File not found or not readable'];
        }

        // Check file size
        $fileSize = filesize($filePath);
        if ($fileSize > $maxSizeBytes) {
            $maxMB = round($maxSizeBytes / 1048576, 1);

            return ['valid' => false, 'error' => "File size exceeds maximum allowed ({$maxMB}MB)"];
        }

        // Check MIME type using finfo
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($filePath);

        if (!\in_array($mimeType, $allowedTypes, true)) {
            return ['valid' => false, 'error' => 'Invalid file type. Allowed: JPG, PNG, GIF, WEBP'];
        }

        // Verify extension matches MIME type (security check)
        $extension     = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $expectedExts  = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png'  => ['png'],
            'image/gif'  => ['gif'],
            'image/webp' => ['webp'],
        ];

        if (isset($expectedExts[$mimeType]) && !\in_array($extension, $expectedExts[$mimeType], true)) {
            return ['valid' => false, 'error' => 'File extension does not match content type'];
        }

        // Check image dimensions
        $imageInfo = @getimagesize($filePath);
        if ($imageInfo === false) {
            return ['valid' => false, 'error' => 'Could not read image dimensions'];
        }

        if ($imageInfo[0] > $maxDimension || $imageInfo[1] > $maxDimension) {
            return ['valid' => false, 'error' => "Image dimensions exceed maximum ({$maxDimension}px)"];
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Delete an image folder safely (only within allowed paths)
     *
     * @param   string  $folderPath  Relative path to folder (e.g., 'images/biblestudy/studies/alias-123')
     *
     * @return  bool  True on success, false if path not allowed or deletion failed
     *
     * @since 10.1.0
     */
    public static function deleteFolder(string $folderPath): bool
    {
        // Normalize path
        $folderPath = trim($folderPath, '/');

        // CRITICAL: Validate path is within allowed scope
        $isAllowed = false;
        foreach (self::ALLOWED_PATHS as $prefix) {
            if (str_starts_with($folderPath, $prefix)) {
                $isAllowed = true;
                break;
            }
        }

        if (!$isAllowed) {
            Log::add(
                'Attempted to delete folder outside allowed scope: ' . $folderPath,
                Log::WARNING,
                'com_proclaim'
            );

            return false;
        }

        $absolutePath = Path::clean(JPATH_ROOT . '/' . $folderPath);

        if (is_dir($absolutePath)) {
            $result = Folder::delete($absolutePath);
            if ($result) {
                Log::add('Deleted image folder: ' . $folderPath, Log::INFO, 'com_proclaim');
            }

            return $result;
        }

        return true; // Folder doesn't exist, nothing to delete
    }

    /**
     * Creates a thumbnail for an uploaded image and moves original to destination
     *
     * @param   string       $file        Source file path (relative to JPATH_ROOT)
     * @param   string       $path        Destination folder path (relative to JPATH_ROOT)
     * @param   int          $size        Thumbnail size (default 300)
     * @param   string|null  $title       Optional title to use in filename (will be converted to URL-safe alias)
     * @param   bool         $preserveOld If true, keeps existing files; if false, archives old folder
     *
     * @return array{image: string, thumbnail: string, image_webp: ?string, thumbnail_webp: ?string}|false
     *
     * @since 9.0.0
     */
    public static function create(
        string $file,
        string $path,
        int $size = 300,
        ?string $title = null,
        bool $preserveOld = true
    ): array|false {
        $originalPath = Path::clean(JPATH_ROOT . '/' . $file);
        $destFolder   = Path::clean(JPATH_ROOT . '/' . $path);

        // Verify source image exists
        if (!is_file($originalPath)) {
            return false;
        }

        // Get file extension
        $extension = strtolower(pathinfo($originalPath, PATHINFO_EXTENSION));

        // Generate filename from title or use original basename
        if ($title !== null && trim($title) !== '') {
            $baseFilename = ApplicationHelper::stringURLSafe($title);
        } else {
            $baseFilename = pathinfo($originalPath, PATHINFO_FILENAME);
        }

        // Add a short version hash based on file content to bust browser cache
        // when the image is replaced with a new file at the same logical path.
        $versionHash  = substr(md5_file($originalPath), 0, 8);
        $newFilename  = $baseFilename . '-' . $versionHash . '.' . $extension;
        $thumbName    = 'thumb_' . $baseFilename . '-' . $versionHash . '.jpg';
        $newImagePath = $destFolder . '/' . $newFilename;
        $thumbPath    = $destFolder . '/' . $thumbName;

        // Handle existing destination folder
        if (is_dir($destFolder)) {
            if ($preserveOld) {
                // Clean up old files so we don't accumulate stale images across re-uploads.
                // Matches both versioned (name-hash.ext) and pre-versioning (name.ext) files.
                $newFiles = [$newFilename, $thumbName];
                $patterns = [
                    $destFolder . '/' . $baseFilename . '-*',
                    $destFolder . '/' . $baseFilename . '.*',
                    $destFolder . '/thumb_' . $baseFilename . '-*',
                    $destFolder . '/thumb_' . $baseFilename . '.*',
                ];

                foreach ($patterns as $pattern) {
                    $oldFiles = glob($pattern);

                    if ($oldFiles) {
                        foreach ($oldFiles as $oldFile) {
                            if (!\in_array(basename($oldFile), $newFiles, true)) {
                                File::delete($oldFile);
                            }
                        }
                    }
                }
            } else {
                // Archive old folder with timestamp instead of deleting
                $archivePath = $destFolder . '_archive_' . date('YmdHis');
                Folder::move($destFolder, $archivePath);
                Log::add('Archived old image folder to: ' . $archivePath, Log::INFO, 'com_proclaim');
            }
        }

        // Create destination folder if it doesn't exist
        if (!is_dir($destFolder)) {
            Folder::create($destFolder);
        }

        // Move original image to destination folder (if not already there)
        $normalizedOriginal = Path::clean($originalPath);
        $normalizedNew      = Path::clean($newImagePath);

        if ($normalizedOriginal !== $normalizedNew) {
            // Copy instead of move if source is in a different location
            if (!File::copy($originalPath, $newImagePath)) {
                return false;
            }

            // Delete the original only if copy succeeded and it's outside the dest folder
            if (!str_starts_with($normalizedOriginal, $destFolder)) {
                File::delete($originalPath);
            }
        }

        // Create JPEG thumbnail for universal compatibility
        $image     = new Image($newImagePath);
        $thumbnail = $image->resize($size, (int) round($size * 0.5625), true, self::SCALE_INSIDE);
        $thumbnail->toFile($thumbPath, IMAGETYPE_JPEG);

        $result = [
            'image'          => $path . '/' . $newFilename,
            'thumbnail'      => $path . '/' . $thumbName,
            'image_webp'     => null,
            'thumbnail_webp' => null,
        ];

        // Generate WebP variants if GD supports it
        if (\function_exists('imagewebp')) {
            $webpThumbName = 'thumb_' . $baseFilename . '-' . $versionHash . '.webp';
            $webpThumbPath = $destFolder . '/' . $webpThumbName;
            $thumbnail->toFile($webpThumbPath, IMAGETYPE_WEBP);
            $result['thumbnail_webp'] = $path . '/' . $webpThumbName;

            // Generate WebP of the full-size image (skip if source is already WebP)
            if ($extension !== 'webp') {
                $webpImageName = $baseFilename . '-' . $versionHash . '.webp';
                $webpImagePath = $destFolder . '/' . $webpImageName;
                $fullImage     = new Image($newImagePath);
                $fullImage->toFile($webpImagePath, IMAGETYPE_WEBP);
                $result['image_webp'] = $path . '/' . $webpImageName;
            } else {
                $result['image_webp'] = $result['image'];
            }
        }

        return $result;
    }

    /**
     * Resize image thumbnail
     *
     * @param   string    $path        Absolute path to source image file
     * @param   int       $newSize     New thumbnail size (width)
     * @param   int|null  $outputType  Output image type constant (default: IMAGETYPE_JPEG)
     *
     * @return bool True on success, false if source image doesn't exist
     *
     * @since 9.0
     */
    public static function resize(string $path, int $newSize, ?int $outputType = null): bool
    {
        // Verify source image exists
        if (!is_file($path)) {
            return false;
        }

        // Determine output type - use JPEG for consistency with create() or preserve original
        if ($outputType === null) {
            $imageInfo  = @getimagesize($path);
            $outputType = ($imageInfo !== false && isset($imageInfo[2])) ? $imageInfo[2] : IMAGETYPE_JPEG;
        }

        // Get filename without any prefix patterns
        $filename = basename($path);

        // Remove common prefixes if present
        $prefixesToRemove = ['original_', 'thumb_'];
        foreach ($prefixesToRemove as $prefix) {
            if (str_starts_with($filename, $prefix)) {
                $filename = substr($filename, \strlen($prefix));
                break;
            }
        }

        // Get extension for output type
        $extension = image_type_to_extension($outputType, false);

        // Remove old extension and add new one
        $filenameBase  = pathinfo($filename, PATHINFO_FILENAME);
        $thumbFilename = 'thumb_' . $filenameBase . '.' . $extension;

        // Delete existing thumbnails in this directory
        $directory = \dirname($path);
        $oldThumbs = Folder::files($directory, '^thumb_' . preg_quote($filenameBase, '/'), false, true);

        foreach ($oldThumbs as $thumb) {
            File::delete($thumb);
        }

        // Create new thumbnail with 16:9 aspect ratio (consistent with create())
        $image     = new Image($path);
        $height    = (int) round($newSize * 0.5625);
        $thumbnail = $image->resize($newSize, $height, true, self::SCALE_INSIDE);
        $thumbnail->toFile($directory . '/' . $thumbFilename, $outputType);

        // Generate WebP variant alongside
        if (\function_exists('imagewebp')) {
            $webpThumbFilename = 'thumb_' . $filenameBase . '.webp';
            $thumbnail->toFile($directory . '/' . $webpThumbFilename, IMAGETYPE_WEBP);
        }

        return true;
    }

    /**
     * Check an image path
     *
     * @param string  $path  Path to file
     * @param ?string $file  file to check
     *
     * @return bool
     *
     * @since 9.0
     */
    public static function check(string $path, ?string $file = null): bool
    {
        if (!is_dir($path)) {
            return false;
        }

        if ($file) {
            return file_exists(JPATH_ROOT . $path . $file);
        }

        return true;
    }
}
