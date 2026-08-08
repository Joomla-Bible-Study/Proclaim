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

/**
 * Creates an instance of the necessary library class, as specified in the administrator
 * params.
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class Cwmimagelib
{
    /**
     * Largest source edge, in pixels, that will be decoded.
     *
     * Output is a 300x169 thumbnail, so anything approaching this is already
     * far larger than needed.
     *
     * @since  10.5.6
     */
    public const int MAX_SOURCE_DIMENSION = 8000;

    /**
     * Extension Name
     *
     * @var string
     *
     * @since 1.5
     */
    public static $extension = 'com_proclaim';

    /**
     * Get Series Podcast File
     *
     * @param   string  $img  Org Image File
     *
     * @return string Empty string if no valid image, otherwise path to resized image
     *
     * @throws \Exception
     * @since 9.0.18
     */
    public static function getSeriesPodcast(string $img): string
    {
        // Return empty if no image path provided
        if (empty($img)) {
            return '';
        }

        // Prep files
        $img_base = pathinfo($img);

        // Ensure we have a valid filename with extension
        if (empty($img_base['filename']) || empty($img_base['extension'])) {
            return '';
        }

        $newFileName  = $img_base['dirname'] . '/' . $img_base['filename'] . '-fit.' . $img_base['extension'];
        $newFilePath  = JPATH_ROOT . '/' . $newFileName;
        $origFilePath = JPATH_ROOT . '/' . $img;

        // Check if original file exists
        if (!file_exists($origFilePath)) {
            return '';
        }

        // If resized version already exists, return it
        if (file_exists($newFilePath)) {
            return $newFileName;
        }

        // Try to create resized version
        if (\function_exists('gd_info') && \extension_loaded('gd')) {
            try {
                self::resizeImage($newFilePath, $origFilePath);

                return $newFileName;
            } catch (\Throwable $e) {
                // Fall back to the original image. Throwable rather than
                // Exception because GD raises TypeError -- an Error -- when
                // handed a failed decode, and that would otherwise escape and
                // take down every page rendering this list.
                return $img;
            }
        }

        // Return original if GD not available
        return $img;
    }

    /**
     * Resize Image
     *
     * @param   string  $targetFile    Target File Path
     * @param   string  $originalFile  File
     * @param   int     $newWidth      Image New Width
     * @param   float   $canv_width    Image Canvas Width
     * @param   float   $canv_height   Image Canvas Height
     *
     * @return void
     *
     * @throws \Exception
     * @since 9.0.18
     */
    public static function resizeImage(
        string $targetFile,
        string $originalFile,
        int $newWidth = 300,
        float $canv_width = 300,
        float $canv_height = 169
    ): void {
        $info = @getimagesize($originalFile);

        if (!\is_array($info) || (int) ($info[0] ?? 0) < 1 || (int) ($info[1] ?? 0) < 1) {
            throw new \RuntimeException('Could not read image dimensions from ' . $originalFile);
        }

        [$width, $height] = [(int) $info[0], (int) $info[1]];
        $mime             = (string) ($info['mime'] ?? '');

        switch ($mime) {
            case 'image/jpeg':
                $image_create_func = 'imagecreatefromjpeg';
                $image_save_func   = 'imagejpeg';
                $new_image_ext     = 'jpg';
                break;

            case 'image/png':
                $image_create_func = 'imagecreatefrompng';
                $image_save_func   = 'imagepng';
                $new_image_ext     = 'png';
                break;

            case 'image/gif':
                $image_create_func = 'imagecreatefromgif';
                $image_save_func   = 'imagegif';
                $new_image_ext     = 'gif';
                break;

            default:
                throw new \RuntimeException('Unknown image type.');
        }

        // PNG and GIF compress well enough that a small file can declare huge
        // dimensions, and decoding is what allocates the memory -- 20000x20000
        // is roughly 1.6GB as RGBA. Exceeding memory_limit is a raw fatal, not
        // a Throwable, so nothing downstream could catch it. The only place to
        // stop it is before the decode.
        if ($width > self::MAX_SOURCE_DIMENSION || $height > self::MAX_SOURCE_DIMENSION) {
            throw new \RuntimeException(
                \sprintf(
                    'Refusing to decode %dx%d image %s: over the %dpx limit',
                    $width,
                    $height,
                    $originalFile,
                    self::MAX_SOURCE_DIMENSION
                )
            );
        }

        $img = @$image_create_func($originalFile);

        // A file can satisfy getimagesize() and still fail to decode -- a
        // truncated PNG reports its header dimensions correctly and then
        // returns false here. Passing that false on to imagecopyresampled()
        // raises a TypeError, which is an Error rather than an Exception and so
        // escapes the caller's catch, taking the whole page down.
        if (!$img instanceof \GdImage) {
            throw new \RuntimeException('Could not decode image ' . $originalFile);
        }

        // Scale to fit within canvas while preserving aspect ratio (letterbox)
        $scale     = min($canv_width / $width, $canv_height / $height);
        $newWidth  = (int) round($width * $scale);
        $newHeight = (int) round($height * $scale);
        $dst_x     = (int) round(($canv_width - $newWidth) / 2);
        $dst_y     = (int) round(($canv_height - $newHeight) / 2);

        $tmp = imagecreatetruecolor((int) $canv_width, (int) $canv_height);

        if (!$tmp instanceof \GdImage) {
            throw new \RuntimeException('Could not allocate the target canvas');
        }

        // Fill background with neutral grey matching the CSS placeholder colour
        $bg = imagecolorallocate($tmp, 233, 236, 239);
        imagefill($tmp, 0, 0, $bg);

        imagecopyresampled($tmp, $img, $dst_x, $dst_y, 0, 0, $newWidth, $newHeight, $width, $height);

        if (file_exists($targetFile)) {
            unlink($targetFile);
        }

        $image_save_func($tmp, $targetFile);
    }
}
