<?php

/**
 * JView html
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for Podcasts
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyViewPodcasts extends JViewLegacy {

    /**
     * Items
     * @var array
     */
    protected $items;

    /**
     * Pagination
     * @var array
     */
    protected $pagination;

    /**
     * State
     * @var array
     */
    protected $state;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise a JError object.
     *
     * @see     fetch()
     * @since   11.1
     */
    public function display($tpl = null) {
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

    /**
     * Add Toolbar
     * @since 7.0.0
     */
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