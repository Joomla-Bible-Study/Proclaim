<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Image\Image;

/**
 * Thumbnail helper class
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class Cwmthumbnail
{
    public const SCALE_INSIDE = 2;

    /**
     * Creates a thumbnail for an uploaded image
     *
     * @param   string  $file  File name
     * @param   string  $path  Path to file
     * @param   int     $size  Size of image with default of 100
     *
     * @return void
     *
     * @since 9.0.0
     */
    public static function create($file, $path, int $size = 300): void
    {
        $name     = basename($file);
        $original = JPATH_ROOT . '/' . $file;
        $thumb    = JPATH_ROOT . '/' . $path . '/thumb_' . $name;
        $w        = 300;
        $h        = 169;
        // Delete destination folder if it exists
        if (is_dir(JPATH_ROOT . '/' . $path)) {
            Folder::delete(JPATH_ROOT . '/' . $path);
        }
        // Move uploaded image to destination
        Folder::create(JPATH_ROOT . '/' . $path);

        // Create thumbnail
        $image     = new Image($original);
        $thumbnail = $image->resize($w, $h, true, $scaleMethod = self::SCALE_INSIDE);
        $thumbnail->toFile($thumb, IMAGETYPE_JPEG);
    }

    /**
     * Resize image
     *
     * @param string $path      Path to file
     * @param int $new_size  New image size
     *
     * @return void
     *
     * @since 9.0
     */
    public static function resize(string $path, int $new_size): void
    {
        $filename = str_replace('original_', '', basename($path));

        // Delete existing thumbnail
        $old_thumbs = Folder::files(dirname($path), 'thumb_', true, true);

        foreach ($old_thumbs as $thumb) {
            File::delete($thumb);
        }

        // Create new thumbnail
        $image     = new Image($path);
        $thumbnail = $image->resize($new_size, $new_size);
        $thumbnail->toFile(dirname($path) . '/thumb_' . $filename, IMAGETYPE_PNG);
    }

    /**
     * Check an image path
     *
     * @param string $path  Path to file
     * @param string|null $file  file to check
     *
     * @return bool
     *
     * @since 9.0
     */
    public static function check(string $path, string $file = null): bool
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
