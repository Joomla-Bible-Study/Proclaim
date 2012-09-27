<?php

/**
 * JView html
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2012 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * @since 7.1.0
 * */
//No Direct Access
defined('_JEXEC') or die;

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * View class for Styles
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyViewStyles extends JView {

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
        $this->canDo = BibleStudyHelper::getActions('', 'style');
        $this->state = $this->get('State');
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        //Check for errors
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        // We don't need toolbar in the modal window.
        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();
        }

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

        JToolBarHelper::title(JText::_('JBS_CMN_STYLES'), 'css.png');

        if ($this->canDo->get('core.create')) {
            JToolBarHelper::addNew('style.add');
        }

        if ($this->canDo->get('core.edit')) {
            JToolBarHelper::editList('style.edit');
        }

        if ($this->canDo->get('core.edit.state')) {
            JToolBarHelper::divider();
            JToolBarHelper::publishList('styles.publish', 'JTOOLBAR_PUBLISH', true);
            JToolBarHelper::unpublishList('styles.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolBarHelper::archiveList('styles.archive', 'JTOOLBAR_ARCHIVE');
        }

        if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete')) {
            JToolBarHelper::deleteList('', 'styles.delete', 'JTOOLBAR_EMPTY_TRASH');
        } elseif ($this->canDo->get('core.edit.state')) {
            JToolBarHelper::trash('styles.trash');
        }


        if ($this->canDo->get('core.edit')) {
            JToolBarHelper::divider();
            JToolBarHelper::custom('style.fixcss', 'refresh', 'refresh', 'JBS_ADM_DB_FIX', false, false);
        }
    }

    /**
     * Add the page title to browser.
     *
     * @since	7.1.0
     */
    protected function setDocument() {
        $document = JFactory::getDocument();
        $document->setTitle(JText::_('JBS_TITLE_STYLES'));
    }

}