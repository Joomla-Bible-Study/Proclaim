<?php

/**
 * View html
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;


/**
 * View class for Comments
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyViewComments extends JViewLegacy {

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
     * Add the page title and toolbar
     *
     * @since 7.0
     */
    protected function addToolbar() {
        $canDo = BibleStudyHelper::getActions('', 'comment');
        JToolBarHelper::title(JText::_('JBS_CMN_COMMENTS'), 'comments.png');
        if ($canDo->get('core.create')) {
            JToolBarHelper::addNew('comment.add');
        }
        if ($canDo->get('core.edit')) {
            JToolBarHelper::editList('comment.edit');
        }
        if ($canDo->get('core.edit.state')) {
            JToolBarHelper::divider();
            JToolBarHelper::publishList('comments.publish');
            JToolBarHelper::unpublishList('comments.unpublish');
        }
        if ($canDo->get('core.delete')) {
            JToolBarHelper::trash('comments.trash');
        }
        if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
            JToolBarHelper::deleteList('', 'comments.delete', 'JTOOLBAR_EMPTY_TRASH');
        }
    }

    /**
     * Add the page title to browser.
     *
     * @since	7.1.0
     */
    protected function setDocument() {
        $document = JFactory::getDocument();
        $document->setTitle(JText::_('JBS_TITLE_COMMENTS'));
    }

}