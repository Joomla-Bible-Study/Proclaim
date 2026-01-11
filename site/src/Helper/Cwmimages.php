<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Image\Image;
use Joomla\Registry\Registry;

/**
 * Proclaim images class
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class Cwmimages
{
    /**
     * Main Study Image
     *
     * @param   ?Registry  $params  Sermon Params
     *
     * @return object
     * @example  {
     *             path: 'string',
     *             width: integer,
     *             height: integer,
     *             type: 'string',
     *             mime: 'string',
     *           }
     *
     * @since    7.0
     */
    public static function mainStudyImage(?Registry $params = null): object
    {
        if ($params === null) {
            $params = Cwmparams::getAdmin()->params;
        }

        $path = $params->get('default_main_image') ?: 'media/com_proclaim/images/openbible.png';

        return self::getImagePath($path);
    }

    /**
     * Get Image Path
     *
     * @param   string  $path  File path to image
     *
     * @return object
     * @example  {
     *             path: 'string',
     *             width: integer,
     *             height: integer,
     *             type: 'string',
     *             mime: 'string',
     *           }
     *
     * @since    7.0
     */
    public static function getImagePath(string $path): object
    {
        $tmp         = new \stdClass();
        $tmp->path   = null;
        $tmp->size   = 0;
        $tmp->width  = 0;
        $tmp->height = 0;

        if (empty($path)) {
            return $tmp;
        }

        $imagePath  = HTMLHelper::_('cleanImageURL', $path);
        $fullPath   = JPATH_ROOT . DIRECTORY_SEPARATOR . $imagePath->url;
        $fileExists = file_exists($fullPath) && is_file($fullPath);

        if ($imagePath->attributes['width'] === 0 && $fileExists) {
            $tmp       = Image::getImageFileProperties($fullPath);
            $tmp->path = $imagePath->url;
        } elseif ($fileExists) {
            $tmp->path   = $imagePath->url;
            $tmp->size   = filesize($fullPath);
            $tmp->width  = (int) $imagePath->attributes['width'];
            $tmp->height = (int) $imagePath->attributes['height'];
        }

        return $tmp;
    }

    /**
     * Get Study Thumbnail
     *
     * @param   string  $image  file path to image
     *
     * @return object
     * @example  {
     *             path: 'string',
     *             width: integer,
     *             height: integer,
     *             type: 'string',
     *             mime: 'string',
     *           }
     *
     * @since    7.0
     */
    public static function getStudyThumbnail(string $image = 'openbible.png'): object
    {
        $path = $image;

        if (!str_contains($image, '/')) {
            $path = self::getStudiesImageFolder() . '/' . $image;
        }

        return self::getImagePath($path);
    }

    /**
     * Get StudiesImage Folder
     *
     * @return string
     *
     * @since 7.0
     */
    private static function getStudiesImageFolder(): string
    {
        return Cwmparams::getAdmin()->params->get('image_folder', 'images');
    }

    /**
     * Get Series Thumbnail
     *
     * @param   ?string  $image  Image file
     *
     * @return object
     * @example  {
     *             path: 'string',
     *             width: integer,
     *             height: integer,
     *             type: 'string',
     *             mime: 'string',
     *           }
     *
     * @since    7.0
     */
    public static function getSeriesThumbnail(?string $image): object
    {
        if ($image === null) {
            return self::getImagePath('');
        }

        $path = $image;

        if (!str_contains($image, '/')) {
            $path = self::getSeriesImageFolder() . '/' . $image;
        }

        return self::getImagePath($path);
    }

    /**
     * Get SeriesImage Folder
     *
     * @return string
     *
     * @since 7.0
     */
    private static function getSeriesImageFolder(): string
    {
        return Cwmparams::getAdmin()->params->get('series_image_folder', 'images');
    }

    /**
     * Get Teacher Thumbnail
     *
     * @param   ?string  $image1  ?
     * @param   ?string  $image2  ?
     *
     * @return object
     * @example  {
     *             path: 'string',
     *             width: integer,
     *             height: integer,
     *             type: 'string',
     *             mime: 'string',
     *           }
     *
     * @since    7.0
     */
    public static function getTeacherThumbnail(?string $image1 = '', ?string $image2 = ''): object
    {
        $folder = self::getTeacherImageFolder();

        return self::extracted($image1, $image2, $folder);
    }

    /**
     * Get TeacherImage Folder
     *
     * @return string
     *
     * @since 7.0
     */
    private static function getTeacherImageFolder(): string
    {
        return Cwmparams::getAdmin()->params->get('teacher_image_folder', 'images');
    }

    /**
     * Get Teacher Image
     *
     * @param   ?string  $image1  ?
     * @param   ?string  $image2  ?
     *
     * @return object
     * @example  {
     *             path: 'string',
     *             width: integer,
     *             height: integer,
     *             type: 'string',
     *             mime: 'string',
     *           }
     *
     * @since    7.0
     *
     * @todo This looks like it needs a rework.
     */
    public static function getTeacherImage(?string $image1 = null, ?string $image2 = null): object
    {
        $path = '';

        if ($image1 === null && $image2 === null) {
            return self::getImagePath($path);
        }

        if ($image2 && (!$image1 || str_starts_with($image1, '- '))) {
            $path = $image2;

            if (!str_contains((string) $path, '/')) {
                $path = self::getTeacherImageFolder() . '/' . $image2;
            }
        } else {
            $path = (string) $image1;

            if (!str_contains($path, '/')) {
                $path = self::getTeacherImageFolder() . '/' . $image1;
            }
        }

        return self::getImagePath($path);
    }

    /**
     * Get Media Image
     *
     * @param   string  $media1  ?
     * @param   string  $media2  ?
     *
     * @return object
     * @example  {
     *             path: 'string',
     *             width: integer,
     *             height: integer,
     *             type: 'string',
     *             mime: 'string',
     *           }
     *
     * @since    7.0
     */
    public static function getMediaImage(string $media1 = '', string $media2 = ''): object
    {
        $folder = self::getMediaImageFolder();

        return self::extracted($media1, $media2, $folder);
    }

    /**
     * Get MediaImage Folder
     *
     * @return string
     *
     * @since 7.0
     */
    private static function getMediaImageFolder(): string
    {
        return 'media/com_proclaim/images';
    }

    /**
     * Get Show Hide
     *
     * @return object
     * @throws \Exception
     * @example  {
     *             path: 'string',
     *             width: integer,
     *             height: integer,
     *             type: 'string',
     *             mime: 'string',
     *           }
     *
     * @since    7.0
     */
    public static function getShowHide(): object
    {
        $admin = Cwmparams::getAdmin();

        if (!$admin->params->get('default_showHide_image')) {
            $path = 'media/com_proclaim/images/showhide.gif';
        } else {
            $path = $admin->params->get('default_showHide_image');
        }

        return self::getImagePath($path);
    }

    /**
     * @param   ?string  $image1
     * @param   ?string  $image2
     * @param   string   $folder
     *
     * @return object
     *
     * @since version
     */
    public static function extracted(?string $image1, ?string $image2, string $folder): object
    {
        if ($image1 === null && $image2 === null) {
            return self::getImagePath('');
        }

        if (!$image1 || str_starts_with($image1, '- ')) {
            $path = (string) $image2;

            if (!str_contains($path, '/')) {
                $path = $folder . '/' . $image2;
            }
        } else {
            $path = $image1;

            if (!str_contains($image1, '/')) {
                $path = $folder . '/' . $image1;
            }
        }

        return self::getImagePath($path);
    }
}
