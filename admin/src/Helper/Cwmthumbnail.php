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
     * Maximum length of the generated base filename (before the version hash and
     * extension are appended). Keeps the final name — including the "thumb_"
     * prefix and ".webp" variants — comfortably within the 255-byte filesystem
     * limit and avoids unwieldy names built from long sermon titles.
     *
     * @since 10.3.3
     */
    private const int MAX_BASENAME_LENGTH = 80;

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
            return ['valid' => false, 'error' => "Image dimensions exceed maximum ({$maxDimension}px)", 'warning' => null];
        }

        // Warn about low-resolution images (below 400px on longest side)
        $minRecommended = 400;
        $warning        = null;

        if ($imageInfo[0] < $minRecommended && $imageInfo[1] < $minRecommended) {
            $warning = "Image is only {$imageInfo[0]}×{$imageInfo[1]}px. "
                . "For best quality, use images at least {$minRecommended}px on the longest side.";
        }

        return ['valid' => true, 'error' => null, 'warning' => $warning];
    }

    /**
     * Resolve a path and confirm it really lands inside ALLOWED_PATHS.
     *
     * The previous check was `str_starts_with($relative, $prefix)` followed by
     * Path::clean(). Path::clean() normalises separators but does NOT collapse
     * '..', so 'images/biblestudy/studies/../../../administrator' satisfied the
     * prefix test and still resolved outside the allowed tree once the
     * filesystem walked it -- the same defect fixed in CwmImageMigration
     * (#1537) and CwmImageCleanup (#1563).
     *
     * Resolution is lexical rather than realpath()-based: callers legitimately
     * pass paths that do not exist yet (deleteFolder() reports success for an
     * already-absent folder, create() writes new files), and realpath() returns
     * false for those. Both the candidate and each allowed base go through the
     * same normaliser so the comparison is like-for-like.
     *
     * @param   string  $path  Absolute path, or one relative to JPATH_ROOT
     *
     * @return  string|false  Normalised path, or false if outside the allow-list
     *
     * @since 10.5.6
     */
    private static function resolveWithinAllowedPaths(string $path): string|false
    {
        $candidate = Path::clean(
            str_starts_with($path, JPATH_ROOT) ? $path : JPATH_ROOT . '/' . ltrim($path, '/')
        );

        $resolved = self::normaliseLexically($candidate);

        if ($resolved === false) {
            return false;
        }

        foreach (self::ALLOWED_PATHS as $prefix) {
            // The base goes through the same normaliser as the candidate --
            // comparing a normalised path against a raw one silently never
            // matches (JPATH_ROOT is './' under phpunit.xml, so the base keeps
            // a './' the candidate has already shed).
            $base = self::normaliseLexically(Path::clean(JPATH_ROOT . '/' . $prefix));

            if ($base === false) {
                continue;
            }

            $base = rtrim($base, \DIRECTORY_SEPARATOR);

            if ($resolved === $base || str_starts_with($resolved, $base . \DIRECTORY_SEPARATOR)) {
                return $resolved;
            }
        }

        return false;
    }

    /**
     * Collapse '.' and '..' segments without requiring the path to exist.
     *
     * Path::clean() normalises separators but leaves '..' in place, which is
     * what allowed the old prefix checks to be walked out of. realpath() would
     * collapse them but returns false for paths that do not exist yet, and
     * callers legitimately pass those.
     *
     * Relative-vs-absolute is preserved rather than assumed: JPATH_ROOT is not
     * always absolute (phpunit.xml defines it as '.'), so forcing a leading
     * separator would turn a repo-relative path into a filesystem-root one.
     *
     * @param   string  $path  Path to normalise
     *
     * @return  string|false  Normalised path, or false if it escapes its start
     *
     * @since 10.5.6
     */
    private static function normaliseLexically(string $path): string|false
    {
        $isAbsolute = str_starts_with($path, \DIRECTORY_SEPARATOR);
        $normalised = [];

        foreach (explode(\DIRECTORY_SEPARATOR, $path) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }

            if ($part === '..') {
                if ($normalised === []) {
                    // Walked out past the starting point — reject outright.
                    return false;
                }

                array_pop($normalised);

                continue;
            }

            $normalised[] = $part;
        }

        return ($isAbsolute ? \DIRECTORY_SEPARATOR : '') . implode(\DIRECTORY_SEPARATOR, $normalised);
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

        $absolutePath = self::resolveWithinAllowedPaths($folderPath);

        if ($absolutePath === false) {
            Log::add(
                'Attempted to delete folder outside allowed scope: ' . $folderPath,
                Log::WARNING,
                'com_proclaim'
            );

            return false;
        }

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
    /**
     * Build a filesystem- and URL-safe base filename from a title (or fall back
     * to the original filename), slugified and length-capped.
     *
     * Both inputs are run through ApplicationHelper::stringURLSafe so that spaces,
     * smart quotes, commas and other characters that break filesystem paths and
     * URLs are stripped — the previous behaviour passed the raw original filename
     * through unchanged, producing names such as
     * "Anticipating the Return of Christ … 1105 am by.jpg" (#1272). The result is
     * truncated to MAX_BASENAME_LENGTH characters and falls back to "image" when
     * slugification yields an empty string.
     *
     * @param   string|null  $title         Preferred source for the name (e.g. sermon title)
     * @param   string       $originalPath  Path used for the fallback basename
     *
     * @return  string  A safe, non-empty base filename (no extension, no version hash)
     *
     * @since 10.3.3
     */
    public static function buildSafeBaseFilename(?string $title, string $originalPath): string
    {
        $source = ($title !== null && trim($title) !== '')
            ? $title
            : pathinfo($originalPath, PATHINFO_FILENAME);

        $base = ApplicationHelper::stringURLSafe((string) $source);

        if (mb_strlen($base) > self::MAX_BASENAME_LENGTH) {
            // Trim to the limit, then drop any dash left dangling by the cut.
            $base = rtrim(mb_substr($base, 0, self::MAX_BASENAME_LENGTH), '-');
        }

        return $base !== '' ? $base : 'image';
    }

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

        // Generate a filesystem- and URL-safe filename from the title (falling
        // back to the original basename), slugified and length-capped. See #1272.
        $baseFilename = self::buildSafeBaseFilename($title, $originalPath);

        // Add a short version hash based on file content to bust browser cache
        // when the image is replaced with a new file at the same logical path.
        $versionHash  = substr(md5_file($originalPath), 0, 8);
        $newFilename  = $baseFilename . '-' . $versionHash . '.' . $extension;
        $thumbName    = 'thumb_' . $baseFilename . '-' . $versionHash . '.jpg';
        $newImagePath = $destFolder . '/' . $newFilename;
        $thumbPath    = $destFolder . '/' . $thumbName;

        // Superseded files are identified now but deleted only after the
        // replacements are confirmed on disk (see the end of this method), so a
        // failed copy or encode leaves the record's existing image intact.
        // $versionHash is a content hash, so a different photo for the same
        // record yields a different filename and the live file is not among
        // those kept.
        $supersededFiles = [];

        // Handle existing destination folder
        if (is_dir($destFolder)) {
            if ($preserveOld) {
                // Matches both versioned (name-hash.ext) and pre-versioning (name.ext) files.
                // Protect both the new target files and the source file from deletion
                $keepFiles = [$newFilename, $thumbName, basename($originalPath)];
                $patterns  = [
                    $destFolder . '/' . $baseFilename . '-*',
                    $destFolder . '/' . $baseFilename . '.*',
                    $destFolder . '/thumb_' . $baseFilename . '-*',
                    $destFolder . '/thumb_' . $baseFilename . '.*',
                ];

                foreach ($patterns as $pattern) {
                    $oldFiles = glob($pattern);

                    if ($oldFiles) {
                        foreach ($oldFiles as $oldFile) {
                            if (!\in_array(basename($oldFile), $keepFiles, true)) {
                                $supersededFiles[] = $oldFile;
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
            // Cleanup is deferred until after the copy, so reaching here with
            // no source file means it genuinely vanished.
            if (!is_file($originalPath)) {
                return false;
            }

            // Copy instead of move if source is in a different location
            if (!File::copy($originalPath, $newImagePath)) {
                return false;
            }

            // Delete the original only if copy succeeded and it's outside the dest folder
            if (!str_starts_with($normalizedOriginal, $destFolder)) {
                File::delete($originalPath);
            }
        }

        // Create JPEG thumbnail preserving original aspect ratio.
        // $size is the max dimension — the other scales proportionally.
        $image        = new Image($newImagePath);
        $sourceWidth  = $image->getWidth();
        $sourceHeight = $image->getHeight();

        if ($sourceWidth >= $sourceHeight) {
            // Landscape or square: width is the max
            $thumbWidth  = $size;
            $thumbHeight = (int) round($size * ($sourceHeight / $sourceWidth));
        } else {
            // Portrait: height is the max
            $thumbHeight = $size;
            $thumbWidth  = (int) round($size * ($sourceWidth / $sourceHeight));
        }

        $thumbnail = $image->resize($thumbWidth, $thumbHeight, true, self::SCALE_INSIDE);

        // toFile() returns imagejpeg()'s bool rather than throwing, so the
        // result has to be checked or a failed encode looks like success and
        // the caller stores a path to a file that does not exist. Returning
        // false is safe for the record: the superseded files are still on disk.
        if (!$thumbnail->toFile($thumbPath, IMAGETYPE_JPEG) || !is_file($thumbPath)) {
            Log::add('Thumbnail encode failed, keeping previous image: ' . $newImagePath, Log::WARNING, 'com_proclaim');

            return false;
        }

        $result = [
            'image'          => $path . '/' . $newFilename,
            'thumbnail'      => $path . '/' . $thumbName,
            'image_webp'     => null,
            'thumbnail_webp' => null,
        ];

        // Generate WebP variants if GD supports it. These are optional
        // enhancements, so a failed encode is logged and the variant left null
        // rather than failing the whole operation. The path is reported only
        // when the file exists, so no reference to an unwritten file is stored.
        if (\function_exists('imagewebp')) {
            $webpThumbName = 'thumb_' . $baseFilename . '-' . $versionHash . '.webp';
            $webpThumbPath = $destFolder . '/' . $webpThumbName;

            if ($thumbnail->toFile($webpThumbPath, IMAGETYPE_WEBP) && is_file($webpThumbPath)) {
                $result['thumbnail_webp'] = $path . '/' . $webpThumbName;
            } else {
                Log::add('WebP thumbnail encode failed for: ' . $newImagePath, Log::WARNING, 'com_proclaim');
            }

            // Generate WebP of the full-size image (skip if source is already WebP)
            if ($extension !== 'webp') {
                $webpImageName = $baseFilename . '-' . $versionHash . '.webp';
                $webpImagePath = $destFolder . '/' . $webpImageName;
                $fullImage     = new Image($newImagePath);

                if ($fullImage->toFile($webpImagePath, IMAGETYPE_WEBP) && is_file($webpImagePath)) {
                    $result['image_webp'] = $path . '/' . $webpImageName;
                } else {
                    Log::add('WebP full-image encode failed for: ' . $newImagePath, Log::WARNING, 'com_proclaim');
                }
            } else {
                $result['image_webp'] = $result['image'];
            }
        }

        // Every replacement is now confirmed on disk — retire the superseded
        // files. Anything we just wrote is excluded defensively, in case a
        // glob pattern above matched a name we then reused.
        $justWritten = array_filter([
            $newFilename,
            $thumbName,
            basename((string) ($result['thumbnail_webp'] ?? '')),
            basename((string) ($result['image_webp'] ?? '')),
        ]);

        foreach ($supersededFiles as $oldFile) {
            if (!\in_array(basename($oldFile), $justWritten, true)) {
                File::delete($oldFile);
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
        // CRITICAL: this method deletes every thumb_* sibling in the target
        // directory and writes new files there, and its only caller
        // (CwmadminController::createThumbnailXHR) takes `image_path` straight
        // from request input. Without this check a crafted path reaches any
        // directory on disk.
        $resolvedPath = self::resolveWithinAllowedPaths($path);

        if ($resolvedPath === false) {
            Log::add(
                'Attempted to resize an image outside allowed scope: ' . $path,
                Log::WARNING,
                'com_proclaim'
            );

            return false;
        }

        $path = $resolvedPath;

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

        // Old thumbnails are collected now but deleted only after the
        // replacement is confirmed on disk (below), so a failed GD encode
        // leaves the existing thumbnail in place.
        $directory = \dirname($path);
        $oldThumbs = Folder::files($directory, '^thumb_' . preg_quote($filenameBase, '/'), false, true);

        // Create new thumbnail preserving original aspect ratio
        $image        = new Image($path);
        $sourceWidth  = $image->getWidth();
        $sourceHeight = $image->getHeight();

        if ($sourceWidth >= $sourceHeight) {
            $thumbWidth  = $newSize;
            $thumbHeight = (int) round($newSize * ($sourceHeight / $sourceWidth));
        } else {
            $thumbHeight = $newSize;
            $thumbWidth  = (int) round($newSize * ($sourceWidth / $sourceHeight));
        }

        $thumbnail    = $image->resize($thumbWidth, $thumbHeight, true, self::SCALE_INSIDE);
        $newThumbPath = $directory . '/' . $thumbFilename;

        // Image::toFile() calls imagejpeg()/imagewebp() and returns their bool
        // rather than throwing, so the result has to be checked: a failed
        // encode (disk full, GD hitting memory_limit) is otherwise
        // indistinguishable from success.
        if (!$thumbnail->toFile($newThumbPath, $outputType) || !is_file($newThumbPath)) {
            Log::add('Thumbnail encode failed for: ' . $path, Log::WARNING, 'com_proclaim');

            return false;
        }

        // Generate WebP variant alongside. Optional: a failure here is logged
        // and tolerated, since the primary thumbnail above already succeeded.
        if (\function_exists('imagewebp')) {
            $webpThumbFilename = 'thumb_' . $filenameBase . '.webp';

            if (!$thumbnail->toFile($directory . '/' . $webpThumbFilename, IMAGETYPE_WEBP)) {
                Log::add('WebP thumbnail encode failed for: ' . $path, Log::WARNING, 'com_proclaim');
            }
        }

        // Replacement confirmed — now retire the superseded thumbnails, skipping
        // anything we just wrote.
        $justWritten = [$thumbFilename, 'thumb_' . $filenameBase . '.webp'];

        foreach ($oldThumbs as $thumb) {
            if (!\in_array(basename($thumb), $justWritten, true)) {
                File::delete($thumb);
            }
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
