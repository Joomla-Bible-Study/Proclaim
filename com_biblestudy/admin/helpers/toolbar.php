<?php

/**
 * Toolbar Helper
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
jimport('joomla.html.toolbar');

/**
 * Toolbar Helper
 *
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class JBSMToolbar extends JObject {

    public static $extension = 'com_biblestudy';

    /**
     * Get Toolbar
     *
     * @return object
     * @deprecated 7.1.3
     */
    function getToolbar() {

        $directory = 'images';
        $bar = new JToolBar('Toolbar');
        $bar->appendButton('Standard', 'new', 'New Record', 'new', false);
        $bar->appendButton('Standard', 'delete', 'Delete Record', 'delete', false);
        $bar->appendButton('Standard', 'publish', 'Publish Record', 'delete', false);
        $bar->appendButton('Standard', 'unpublish', 'Unpublish Record', 'delete', false);
        $toolview = JFactory::getApplication()->input->get('view');
        if ($toolview == 'mediafile') {
            $bar->appendButton('Popup', 'upload', 'Upload', "index.php?option=com_media&tmpl=component&task=popupUpload&directory=$directory", 600, 400);
        }

        return $bar->render();
    }

}
