<?php

/**
 * @version     $Id: view.html.php
 * @package BibleStudy
 * @Copyright (C) 2007 - 2012 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * @since 7.1.0
 * */
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'biblestudy.php');

// import Joomla view library
jimport('joomla.application.component.view');

class BiblestudyViewStyles extends JView {

    function display($tpl = null) {
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