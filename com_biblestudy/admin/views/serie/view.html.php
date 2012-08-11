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

jimport('joomla.application.component.view');

/**
 * View class for Serie
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyViewSerie extends JView {

    /**
     * Form
     * @var array
     */
    protected $form;

    /**
     * Item
     * @var array
     */
    protected $item;

    /**
     * State
     * @var array
     */
    protected $state;

    /**
     * Admin
     * @var array
     */
    protected $admin;

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
        $this->form = $this->get("Form");
        $this->item = $this->get("Item");
        $this->state = $this->get("State");
        $this->canDo = BibleStudyHelper::getActions($this->item->id, 'serie');
        //Load the Admin settings
        $this->loadHelper('params');
        $this->admin = @BsmHelper::getAdmin();

        if (!JFactory::getUser()->authorize('core.manage', 'com_biblestudy')) {
            JError::raiseError(404, JText::_('JBS_CMN_NOT_AUTHORIZED'));
            return false;
        }

        $this->setLayout("form");
        // Set the toolbar
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
        JRequest::setVar('hidemainmenu', true);
        $isNew = ($this->item->id == 0);
        $title = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
        JToolBarHelper::title(JText::_('JBS_CMN_SERIES') . ': <small><small>[' . $title . ']</small></small>', 'series.png');

        if ($isNew && $this->canDo->get('core.create', 'com_biblestudy')) {
            JToolBarHelper::apply('serie.apply');
            JToolBarHelper::save('serie.save');
            JToolBarHelper::cancel('serie.cancel');
        } else {
            if ($this->canDo->get('core.edit', 'com_biblestudy')) {
                JToolBarHelper::apply('serie.apply');
                JToolBarHelper::save('serie.save');
            }
            JToolBarHelper::cancel('serie.cancel', 'JTOOLBAR_CLOSE');
        }
        JToolBarHelper::divider();
        JToolBarHelper::help('biblestudy', true);
    }

    /**
     * Add the page title to browser.
     *
     * @since	7.1.0
     */
    protected function setDocument() {
        $isNew = ($this->item->id < 1);
        $document = JFactory::getDocument();
        $document->setTitle($isNew ? JText::_('JBS_TITLE_SERIES_CREATING') : JText::sprintf('JBS_TITLE_SERIES_EDITING', $this->item->series_text));
    }

}