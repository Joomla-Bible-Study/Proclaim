<?php

/**
 * JView html
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;


/**
 * View class for Mimetypes
 * @package     BibleStudy.Admin
 * @since       7.0
 */
class BiblestudyViewMimetypes extends JViewLegacy {

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
        $this->canDo = JBSMHelper::getActions('', 'mimetype');
        //Check for errors
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }
        // Set the toolbar
        $this->addToolbar();

        // Display the template
        parent::display($tpl);

        // Set the document
        $this->setDocument();
    }

    /**
     * Add the page title and toolbar
     *
     * @since 7.0
     */
    protected function addToolbar() {
        JToolBarHelper::title(JText::_('JBS_CMN_MIME_TYPES'), 'mimetype.png');
        if ($this->canDo->get('core.create')) {
            JToolBarHelper::addNew('mimetype.add');
        }
        if ($this->canDo->get('core.edit')) {
            JToolBarHelper::editList('mimetype.edit');
        }
        if ($this->canDo->get('core.edit.state')) {
            JToolBarHelper::divider();
            JToolBarHelper::publishList('mimetypes.publish');
            JToolBarHelper::unpublishList('mimetypes.unpublish');
            JToolBarHelper::archiveList('mimetypes.archive', 'JTOOLBAR_ARCHIVE');
        }
        if ($this->canDo->get('core.delete')) {
            JToolBarHelper::trash('mimetypes.trash');
        }
        if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete')) {
            JToolBarHelper::deleteList('', 'mimetypes.delete', 'JTOOLBAR_EMPTY_TRASH');
        }
    }

    /**
     * Add the page title to browser.
     *
     * @since	7.1.0
     */
    protected function setDocument() {
        $document = JFactory::getDocument();
        $document->setTitle(JText::_('JBS_TITLE_MIME_TYPES'));
    }

}