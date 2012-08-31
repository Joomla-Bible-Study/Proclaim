<?php

/**
 * BibleStudy images class
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
jimport('joomla.html.parameter');

/**
 * BibleStudy images class
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class jbsImages {

    /**
     * Admin Settings
     * @todo move to the helper
     * @return \JRegistry
     */
    function adminSettings() {
        $database = JFactory::getDBO();
        $database->setQuery("SELECT params FROM #__bsms_admin WHERE id = 1");
        $database->query();
        $compat = $database->loadObject();

        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadJSON($compat->params);
        $admin_params = $registry;

        return $admin_params;
    }

    /**
     * Get Image Path
     * @param string $path
     * @return \JObject
     */
    function getImagePath($path) {
        $tmp = new JObject();
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        if (JFile::exists(JPATH_ROOT . DIRECTORY_SEPARATOR . $path)):
            $tmp->path = $path;
            $tmp->size = filesize($tmp->path);
            $ext = strtolower(JFile::getExt($path));
            switch ($ext) {
                // Image
                case 'jpg':
                case 'png':
                case 'gif':
                case 'xcf':
                case 'odg':
                case 'bmp':
                case 'jpeg':
                    $info = getimagesize($tmp->path);
                    $tmp->width = $info[0];
                    $tmp->height = $info[1];
                    $tmp->type = $info[2];
                    $tmp->mime = $info['mime'];
                    if (!$tmp->width) {
                        $tmp->width = 0;
                    }
                    if (!$tmp->height) {
                        $tmp->height = 0;
                    }
            }
        else:
            $tmp->path = NULL;
            $tmp->size = NULL;
            $tmp->width = 0;
            $tmp->height = 0;
            $tmp->type = '';
            $tmp->mime = '';
        endif;
        return $tmp;
    }

    /**
     * Main Study Image
     * @return string
     */
    function mainStudyImage() {
        $mainimage = array();
        $path = null;
        $image = null;
        $database = JFactory::getDBO();
        $database->setQuery("SELECT * FROM #__bsms_admin WHERE id = 1");
        $admin = $database->loadObject();

        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadJSON($admin->params);
        $admin_params = $registry;

        if (!$admin_params->get('default_main_image')) {
            $path = 'media/com_biblestudy/images/openbible.png';
        } else {
            $path = $admin_params->get('default_main_image');
        }

        $mainimage = $this->getImagePath($path);
        return $mainimage;
    }

    /**
     * Get MediaImage Folder
     * @return string
     */
    function getMediaImageFolder() {

        $mediaimagefolder = 'media/com_biblestudy/images';


        return $mediaimagefolder;
    }

    /**
     * Get SeriesImage Folder
     * @todo Tom need to look at this and verify this is right
     * @return string
     */
    function getSeriesImageFolder() {

        $seriesimagefolder = 'images';

        return $seriesimagefolder;
    }

    /**
     * Get StudiesImage Folder
     * @todo Tom need to look at this and verify this is right
     * @return string
     */
    function getStudiesImageFolder() {

        $studiesimagefolder = 'images';

        return $studiesimagefolder;
    }

    /**
     * Get TeacherImage Folder
     * @todo Tom need to look at this and verify this is right
     * @return string
     */
    function getTeacherImageFolder() {

        $teacherimagefolder = 'images';

        return $teacherimagefolder;
    }

    /**
     * Get Study Thumbnail
     * @param string $image
     * @return string
     */
    function getStudyThumbnail($image = 'openbible.png') {
        $imagepath = array();
        $folder = $this->getStudiesImageFolder();
        $path = $folder . '/' . $image;
        if (substr_count($image, '/')) {
            $path = $image;
        }
        $imagepath = $this->getImagePath($path);
        return $imagepath;
    }

    /**
     * Get Series Thumbnail
     * @param string $image
     * @return string
     */
    function getSeriesThumbnail($image = 'openbible.png') {
        $imagepath = array();
        $folder = $this->getSeriesImageFolder();
        $path = $folder . '/' . $image;
        if (substr_count($image, '/')) {
            $path = $image;
        }
        $imagepath = $this->getImagePath($path);
        return $imagepath;
    }

    /**
     * Get Teacher Thumbnail
     * @param string $image1
     * @param string $image2
     * @return string
     */
    function getTeacherThumbnail($image1 = NULL, $image2 = NULL) {
        $imagepath = array();
        $folder = $this->getTeacherImageFolder();

        if (!$image1 || $image1 == '0' || strncmp($image1, '- ', 2) == 0) {
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

        $imagepath = $this->getImagePath($path);
        return $imagepath;
    }

    /**
     * Get Teacher Image
     * @param string $image1
     * @param string $image2
     * @return string
     */
    function getTeacherImage($image1 = null, $image2 = null) {
        $imagepath = array();
        $folder = $this->getTeacherImageFolder();
        if (!$image1 || $image1 == '0' || strncmp($image1, '- ', 2) == 0) {
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
        $imagepath = $this->getImagePath($path);
        return $imagepath;
    }

    /**
     * Get Media Image
     * @param string $media1
     * @param string $media2
     * @return string
     */
    function getMediaImage($media1 = NULL, $media2 = NULL) {
        $imagepath = array();
        $folder = $this->getMediaImageFolder();
        if (!$media1 || $media1 == '0' || strncmp($media1, '- ', 2) == 0) {
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
        $imagepath = $this->getImagePath($path);
        return $imagepath;
    }

    /**
     * Get Show Hide
     * @todo Brent Cordis need to refacter this to pull admin params from the helper.php
     * @return string
     */
    function getShowHide() {
        $database = JFactory::getDBO();
        $database->setQuery("SELECT * FROM #__bsms_admin WHERE id = 1");
        $admin = $database->loadObject();

        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadJSON($admin->params);
        $admin_params = $registry;

        if (!$admin_params->get('default_showHide_image')) {
            $path = 'media/com_biblestudy/images/showhide.gif';
        } else {
            $path = $admin_params->get('default_showHide_image');
        }

        $imagepath = $this->getImagePath($path);
        return $imagepath;
    }

}

// End of class