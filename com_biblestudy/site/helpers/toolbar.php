<?php

/**
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
jimport('joomla.html.toolbar');

/**
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class biblestudyHelperToolbar extends JObject {

    /**
     *
     * @return type
     */
    function getToolbar() {

        $directory = 'images';
        $bar = new JToolBar('Toolbar');
        $toolview = JRequest::getVar('view');
        if ($toolview == 'mediafile') {
            $bar->appendButton('Popup', 'upload', 'JBS_MED_UPLOAD', "index.php?option=com_media&tmpl=component&task=popupUpload&folder=", 600, 400);
        }

        return $bar->render();
    }

}
