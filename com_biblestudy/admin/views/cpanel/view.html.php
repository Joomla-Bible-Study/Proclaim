<?php

/**
 * @version $Id: view.html.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
include_once (BIBLESTUDY_PATH_ADMIN_LIB . DIRECTORY_SEPARATOR . 'biblestudy.stats.class.php');

class biblestudyViewcpanel extends JView {

    function display($tpl = null) {

        JHTML::stylesheet('cpanel.css', JURI::base() . '../media/com_biblestudy/css/');
        //get version information
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__extensions');
        $query->where('element = "com_biblestudy"');
        $db->setQuery($query);
        $data = $db->loadObject();
        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadJSON($data->manifest_cache);
        if ($data) {
            $this->version = $registry->get('version');
            $this->versiondate = $registry->get('creationDate');
        }
        //$jbstats = new jbStats();
        $this->total_messages = @jbStats::get_total_messages();

        $this->addToolbar();

        // Display the template
        parent::display($tpl);

        // Set the document
        $this->setDocument();
    }

    protected function addToolbar() {
        JToolBarHelper::title(JText::_('JBS_CMN_CONTROL_PANEL'), 'administration');
    }

    /**
     * Add the page title to browser.
     *
     * @since	7.1.0
     */
    protected function setDocument() {
        $document = JFactory::getDocument();
        $document->setTitle(JText::_('JBS_TITLE_CONTROL_PANEL'));
    }

}