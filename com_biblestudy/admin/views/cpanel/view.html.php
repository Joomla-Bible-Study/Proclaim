<?php

/**
 * View html
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
require_once (BIBLESTUDY_PATH_ADMIN_LIB . DIRECTORY_SEPARATOR . 'biblestudy.stats.class.php');
require_once (BIBLESTUDY_PATH_ADMIN_LIB . DIRECTORY_SEPARATOR . 'biblestudy.debug.php');
require_once (JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'liveupdate' . DIRECTORY_SEPARATOR . 'liveupdate.php');

/**
 * JView class for Cpanel
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class biblestudyViewcpanel extends JViewLegacy {

    /**
     * Display
     * @param string $tpl
     */
    public function display($tpl = null) {


        JHTML::stylesheet('media/com_biblestudy/css/cpanel.css');
        //get version information
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__extensions');
        $query->where('element = "com_biblestudy" and type = "component"');
        $db->setQuery($query);
        $data = $db->loadObject();
        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadJSON($data->manifest_cache);
        if ($data) {
            $this->version = $registry->get('version');
            $this->versiondate = $registry->get('creationDate');
        }
        /* @todo need to convert to a statec call */
        $this->total_messages = jbStats::get_total_messages();

        $this->addToolbar();

        // Display the template
        parent::display($tpl);

        // Set the document
        $this->setDocument();
    }

    /**
     * Add Toolbar to page
     *
     * @since 7.0.0
     */
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