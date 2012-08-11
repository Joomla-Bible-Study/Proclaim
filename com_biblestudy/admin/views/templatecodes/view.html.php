<?php

/**
 * JView html
 * @package BibleStudy
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
 * View class for TemplateCodes
 * @package BibleStudy.Admin
 * @since 7.1.0
 */
class BiblestudyViewTemplatecodes extends JView {

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
        $this->canDo = BibleStudyHelper::getActions('', 'templatecode');
        $this->state = $this->get('State');
        $this->items = $this->get('Items');
        foreach ($this->items as $item) {
            switch ($item->type) {
                case 1:
                    $item->typetext = JText::_('JBS_TPLCODE_SERMONLIST');
                    break;
                case 2:
                    $item->typetext = JText::_('JBS_TPLCODE_SERMON');
                    break;
                case 3:
                    $item->typetext = JText::_('JBS_TPLCODE_TEACHERS');
                    break;
                case 4:
                    $item->typetext = JText::_('JBS_TPLCODE_TEACHER');
                    break;
                case 5:
                    $item->typetext = JText::_('JBS_TPLCODE_SERIESDISPLAYS');
                    break;
                case 6:
                    $item->typetext = JText::_('JBS_TPLCODE_SERIESDISPLAY');
                    break;
                case 7:
                    $item->typetext = JText::_('JBS_TPLCODE_MODULE');
                    break;
            }
        }
        $modelView = $this->getModel();
        $this->pagination = $this->get('Pagination');
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
     * @since 7.1.0
     */
    protected function addToolbar() {

        JToolBarHelper::title(JText::_('JBS_TPLCODE_TPLCODES'), 'templates.png');

        if ($this->canDo->get('core.create')) {
            JToolBarHelper::addNew('templatecode.add');
        }

        if ($this->canDo->get('core.edit')) {
            JToolBarHelper::editList('templatecode.edit');
        }

        if ($this->canDo->get('core.edit.state')) {
            JToolBarHelper::divider();
            JToolBarHelper::publishList('templatecodes.publish', 'JTOOLBAR_PUBLISH', true);
            JToolBarHelper::unpublishList('templatecodes.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolBarHelper::divider();
            JToolBarHelper::archiveList('templatecodes.archive', 'JTOOLBAR_ARCHIVE');
        }

        if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete')) {
            JToolBarHelper::deleteList('', 'templatecodes.delete', 'JTOOLBAR_EMPTY_TRASH');
        } elseif ($this->canDo->get('core.edit.state')) {
            JToolBarHelper::trash('templatecodes.trash');
            JToolBarHelper::divider();
        }
    }

    /**
     * Add the page title to browser.
     *
     * @since	7.1.0
     */
    protected function setDocument() {
        $document = JFactory::getDocument();
        $document->setTitle(JText::_('JBS_TITLE_TEMPLATECODES'));
    }

}
