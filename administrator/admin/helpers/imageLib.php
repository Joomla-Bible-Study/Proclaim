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
    protected static $lib = null;

    /**
     * Creates an instance of either ImageMagickLib, or GDLib
     * @return <JObject> An instance of the appropiate library class
     * @since   7.0
     */
    public function __construct() {
        //Get the selected library from the settings
        //..
        //..
        $libType = 'ImageMagickLib';
        if(!is_class($this->$lib))
            $this->lib = new ${$libType};

        //@todo This should be cast as JObject
        return (Object)$this->lib;
    }
}

/**
 * Abstraction layer for the ImageMagick PHP library
 *
 * @since   7.0
 */
class ImageMagickLib extends ImageLib {

    public function __construct() {
        //Check that the library exists
        if(!class_exists("Imagick"))
            die("ImageMagick is not found");
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
        if(!function_exists("gd_info"))
            die("GD is not found");
    }
}