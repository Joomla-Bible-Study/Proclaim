<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
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
     * @param   Registry|null  $params  Sermon Params
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
    public static function mainStudyImage(Registry $params = null): object
    {
        if ($params === null) {
            $database = Factory::getContainer()->get('DatabaseDriver');
            $query    = $database->getQuery(true);
            $query->select('*')->from('#__bsms_admin')->where('id = ' . 1);
            $database->setQuery($query);
            $params = $database->loadObject();

            // Convert parameter fields to objects.
            $registry = new Registry();
            $registry->loadString($params->params);
            $params = $registry;
        }

        if (!$params->get('default_main_image')) {
            $path = 'media/com_proclaim/images/openbible.png';
        } else {
            $path = $params->get('default_main_image');
        }

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
        $tmp->size   = null;
        $tmp->width  = 0;
        $tmp->height = 0;

        $path       = HTMLHelper::_('cleanImageURL', $path);
        $FileExists = File::exists(JPATH_ROOT . DIRECTORY_SEPARATOR . $path->url);

        if ($path->attributes['width'] === 0 && $FileExists) {
            $tmp       = Image::getImageFileProperties(JPATH_ROOT . DIRECTORY_SEPARATOR . $path->url);
            $tmp->path = $path->url;
        } elseif ($FileExists) {
            $tmp->path   = $path->url;
            $tmp->size   = filesize(JPATH_ROOT . DIRECTORY_SEPARATOR . $tmp->path);
            $tmp->width  = $path->attributes['width'];
            $tmp->height = $path->attributes['height'];
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
        $folder = self::getStudiesImageFolder();
        $path   = $folder . '/' . $image;

        if (substr_count($image, '/')) {
            $path = $image;
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
        return 'images';
    }

    /**
     * Get Series Thumbnail
     *
     * @param   string|null  $image  Image file
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
    public static function getSeriesThumbnail(?string $image = 'openbible.png'): object
    {
        $folder = self::getSeriesImageFolder();
        $path   = $folder . '/' . $image;

        if (substr_count($image, '/')) {
            $path = $image;
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
        return 'images';
    }

    /**
     * Get Teacher Thumbnail
     *
     * @param   string|null  $image1  ?
     * @param   string|null  $image2  ?
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

        if ($image1 === null && $image2 === null) {
            return self::getImagePath('');
        }

        if (!$image1 || $image1 === '0' || strncmp($image1, '- ', 2) === 0) {
            $path = $image2;

            if (!substr_count($path, '/')) {
                $path = $folder . '/' . $image2;
            }
        } else {
            $path = $folder . '/' . $image1;

            if (substr_count($image1, '/') > 0) {
                $path = $image1;
            }
        }

        return self::getImagePath($path);
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
        return 'images';
    }

    /**
     * Get Teacher Image
     *
     * @param   string|null  $image1  ?
     * @param   string|null  $image2  ?
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
    public static function getTeacherImage(string $image1 = null, string $image2 = null): object
    {
        $folder = self::getTeacherImageFolder();
        $path   = '';

        if ($image1 === null && $image2 === null) {
            return self::getImagePath($path);
        }

        if ($image2 && (!$image1 || strncmp($image1, '- ', 2) === 0)) {
            $path = $image2;

            if (!substr_count($path, '/')) {
                $path = $folder . '/' . $image2;
            }
        } else {
            $path = $folder . '/' . $image1;

            if (substr_count($image1, '/') > 0) {
                $path = $image1;
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

        if ($media1 === null && $media2 === null) {
            return self::getImagePath('');
        }

        if (!$media1 || strncmp($media1, '- ', 2) === 0) {
            $path = $media2;

            if (!substr_count($path, '/')) {
                $path = $folder . '/' . $media2;
            }
        } else {
            $path = $folder . '/' . $media1;

            if (substr_count($media1, '/') > 0) {
                $path = $media1;
            }
        }

        return self::getImagePath($path);
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
}
