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

        $newFileName  = $img_base['dirname'] . '/' . $img_base['filename'] . '-200x112.' . $img_base['extension'];
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
            } catch (\Exception $e) {
                // Fall back to original if resize fails
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
        $info = getimagesize($originalFile);
        $mime = $info['mime'];

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

        $img              = $image_create_func($originalFile);
        [$width, $height] = getimagesize($originalFile);

        $newHeight = ($height / $width) * $newWidth;
        $tmp       = imagecreatetruecolor($canv_width, $canv_height);
        imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        if (file_exists($targetFile)) {
            unlink($targetFile);
        }

        $image_save_func($tmp, $targetFile);
    }
}
