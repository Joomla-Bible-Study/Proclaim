<?php

/**
 * @version     $Id
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

/**
 * Creates an instance of the necessary library class, as specified in the admin
 * params
 */
class ImageLib {
    //public abstract static function resize($img);
}

/**
 * Abstraction layer for the ImageMagick PHP library
 *
 * @since   7.0
 */
class ImageMagickLib extends ImageLib {

    public static function resize($image) {
        try {
            /*             * * a file that does not exist ** */
            $image = '$image';

            /*             * * a new imagick object ** */
            $im = new Imagick($image);

            var_dump( $im->getImageGeometry());
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

}

/**
 * Abstraction layer for the GD PHP library
 *
 * @since   7.0
 */
class GDLib extends ImageLib {

    public function __construct() {
        //Check that the library exists
        if (!function_exists("gd_info"))
            die("GD is not found");
    }

}