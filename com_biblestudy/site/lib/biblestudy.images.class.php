<?php

/**
 * @version $Id: biblestudy.images.class.php 1 $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
jimport('joomla.html.parameter');

class jbsImages {

    function adminSettings() {
        $database = & JFactory::getDBO();
        $database->setQuery("SELECT params FROM #__bsms_admin WHERE id = 1");
        $database->query();
        $compat = $database->loadObject();

        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadJSON($compat->params);
        $admin_params = $registry;

        return $admin_params;
    }

    function getImagePath($path) {
        error_reporting(0);
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        $tmp = new JObject();
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
                $info = @getimagesize($tmp->path);
                $tmp->width = @$info[0];
                $tmp->height = @$info[1];
                $tmp->type = @$info[2];
                $tmp->mime = @$info['mime'];
                if (!$tmp->width) {
                    $tmp->width = 0;
                }
                if (!$tmp->height) {
                    $tmp->height = 0;
                }
        }
        return $tmp;
    }

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

    function getMediaImageFolder() {

        $mediaimagefolder = 'media/com_biblestudy/images';


        return $mediaimagefolder;
    }

    function getSeriesImageFolder() {

        $seriesimagefolder = 'images';

        return $seriesimagefolder;
    }

    function getStudiesImageFolder() {

        $studiesimagefolder = 'images';

        return $studiesimagefolder;
    }

    function getTeacherImageFolder() {

        $teacherimagefolder = 'images';

        return $teacherimagefolder;
    }

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
            if (substr_count($media1, '/') > 0) {
                $path = $image1;
            }
        }
        $imagepath = $this->getImagePath($path);
        return $imagepath;
    }

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

    function getShowHide() {
        $database = & JFactory::getDBO();
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