<?php

/**
 * Image Libs Helper
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

/**
 * Creates an instance of the necessary library class, as specified in the admin
 * params.
 *
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class ImageLib {
    //public abstract static function resize($img);
}

/**
 * Abstraction layer for the ImageMagick PHP library
 *
 * @package BibleStudy.Admin
 * @since   7.0.0
 */
class ImageMagickLib extends ImageLib {

    /**
     * Resize Image
     *
     * @param string $image
     */
    public static function resize($image) {
        try {
            /*             * * a file that does not exist ** */
            $image = '$image';

            /*             * * a new imagick object ** */
            $im = new Imagick($image);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

}

/**
 * Abstraction layer for the GD PHP library
 *
 * @package BibleStudy.Admin
 * @since   7.0.0
 */
class GDLib extends ImageLib {

    /**
     * Construct System.
     */
    public function __construct() {
        //Check that the library exists
        if (!function_exists("gd_info"))
            die("GD is not found");
    }

}