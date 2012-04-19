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
require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'biblestudy.php');
jimport('joomla.application.component.view');

class BiblestudyViewTemplate extends JView {

    protected $items;
    protected $pagination;
    protected $state;

    function display($tpl = null) {
        $this->item = $this->get('Item');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->types = $this->get('Types');
        $this->form = $this->get("Form");
        $this->canDo = BibleStudyHelper::getActions($this->item->id, 'template');

        $this->setLayout("form");
        // Set the toolbar
        $this->addToolbar();

        // Display the template
        parent::display($tpl);

        // Set the document
        $this->setDocument();
    }

    protected function addToolbar() {
        JRequest::setVar('hidemainmenu', true);
        $isNew = ($this->item->id == 0);
        $title = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
        JToolBarHelper::title(JText::_('JBS_CMN_TEMPLATES') . ': <small><small>[' . $title . ']</small></small>', 'templates.png');

        if ($isNew && $this->canDo->get('core.create', 'com_biblestudy')) {
            JToolBarHelper::apply('template.apply');
            JToolBarHelper::save('template.save');
            JToolBarHelper::save2new('template.save2new');
            JToolBarHelper::cancel('template.cancel');
        } else {
            if ($this->canDo->get('core.edit', 'com_biblestudy')) {
                JToolBarHelper::apply('template.apply');
                JToolBarHelper::save('template.save');

                // We can save this record, but check the create permission to see if we can return to make a new one.
                if ($this->canDo->get('core.create', 'com_biblestudy')) {
                    JToolbarHelper::save2new('template.save2new');
                }
            }
            // If checked out, we can still save
            if ($this->canDo->get('core.create', 'com_biblestudy')) {
                JToolBarHelper::save2copy('template.save2copy');
            }
            JToolBarHelper::cancel('template.cancel', 'JTOOLBAR_CLOSE');
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
        $document->setTitle($isNew ? JText::_('JBS_TITLE_TEMPLATES_CREATING') : JText::sprintf('JBS_TITLE_TEMPLATES_EDITING', $this->item->title));
    }

}