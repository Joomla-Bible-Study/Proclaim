<?php

/**
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Controler for MP3
 * @package BibleStudy.Admin
 */
class biblestudyImportControllermp3 extends JController {

    function __construct() {
        parent::__construct();
    }

    /**
     * Main View
     */
    function main() {
        $model = $this->getModel('biblestudy');
        $test = $model;
        JRequest::setVar('view', 'mp3');
        parent::display();
    }

    /**
     * Edit the topic
     */
    function edit() {
        JRequest::setVar('view', 'topicsedit');
        JRequest::setVar('layout', 'form');
        JRequest::setVar('hidemainmenu', 1);

        parent::display();
    }

    /**
     * save a record (and redirect to main page)
     * @return void
     */
    function save() {
        $model = $this->getModel('topicsedit');

        if ($model->store($post)) {
            $msg = JText::_('Topic Saved!');
        } else {
            $msg = JText::_('Error Saving Topic');
        }

        // Check the table in so it can be edited.... we are done with it anyway
        $link = 'index.php?option=com_biblestudy&view=topicslist';
        $this->setRedirect($link, $msg);
    }

    /**
     * remove record(s)
     * @return void
     */
    function remove() {
        $model = $this->getModel('topicsedit');
        if (!$model->delete()) {
            $msg = JText::_('Error: One or More Topics Items Could not be Deleted');
        } else {
            $msg = JText::_('Topics Item(s) Deleted');
        }

        $this->setRedirect('index.php?option=com_biblestudy&view=topicslist', $msg);
    }

    /**
     * Publish Topics
     *
     * @global type $mainframe
     */
    function publish() {
        global $mainframe;

        $cid = JRequest::getVar('cid', array(0), 'post', 'array');

        if (!is_array($cid) || count($cid) < 1) {
            JError::raiseError(500, JText::_('Select an item to publish'));
        }

        $model = $this->getModel('topicsedit');
        if (!$model->publish($cid, 1)) {
            echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
        }

        $this->setRedirect('index.php?option=com_biblestudy&view=topicslist');
    }

    /**
     * Unpublish topic
     *
     * @global type $mainframe
     */
    function unpublish() {
        global $mainframe;

        $cid = JRequest::getVar('cid', array(0), 'post', 'array');

        if (!is_array($cid) || count($cid) < 1) {
            JError::raiseError(500, JText::_('Select an item to unpublish'));
        }

        $model = $this->getModel('topicsedit');
        if (!$model->publish($cid, 0)) {
            echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
        }

        $this->setRedirect('index.php?option=com_biblestudy&view=topicslist');
    }

    /**
     * cancel editing a record
     * @return void
     */
    function cancel() {
        $msg = JText::_('Operation Cancelled');
        $this->setRedirect('index.php?option=com_biblestudy&view=topicslist', $msg);
    }

}