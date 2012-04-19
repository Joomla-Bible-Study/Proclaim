<?php

/**
 * @version     $Id: view.html.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'biblestudy.php');
jimport('joomla.application.component.view');

class BiblestudyViewPodcasts extends JView {

    protected $items;
    protected $pagination;
    protected $state;

    function display($tpl = null) {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->canDo = BibleStudyHelper::getActions('', 'podcast');
        $this->addToolbar();

        // Display the template
        parent::display($tpl);

        // Set the document
        $this->setDocument();
    }

    protected function addToolbar() {
        JToolBarHelper::title(JText::_('JBS_CMN_PODCASTS'), 'podcast.png');
        if ($this->canDo->get('core.create')) {
            JToolBarHelper::addNew('podcast.add');
        }
        if ($this->canDo->get('core.edit')) {
            JToolBarHelper::editList('podcast.edit');
        }
        if ($this->canDo->get('core.edit.state')) {
            JToolBarHelper::divider();
            JToolBarHelper::publishList('podcasts.publish');
            JToolBarHelper::unpublishList('podcasts.unpublish');
            JToolBarHelper::archiveList('podcasts.archive', 'JTOOLBAR_ARCHIVE');
        }
        if ($this->canDo->get('core.delete')) {
            JToolBarHelper::trash('podcasts.trash');
            if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete')) {
                JToolBarHelper::deleteList('', 'podcasts.delete', 'JTOOLBAR_EMPTY_TRASH');
            }
        }
        if ($this->canDo->get('core.create')) {
            JToolBarHelper::divider();
            JToolBarHelper::custom('writeXMLFile', 'xml.png', 'JBS_PDC_WRITE_XML_FILES', 'JBS_PDC_WRITE_XML_FILES', false, false);
        }
    }

    /**
     * Add the page title to browser.
     *
     * @since	7.1.0
     */
    protected function setDocument() {
        $document = JFactory::getDocument();
        $document->setTitle(JText::_('JBS_TITLE_PODCASTS'));
    }

}