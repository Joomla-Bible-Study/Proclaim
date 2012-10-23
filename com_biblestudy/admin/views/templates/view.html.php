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
 * View class for Templates
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyViewTemplates extends JViewLegacy {

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
        $this->canDo = BibleStudyHelper::getActions('', 'template');
        $templates = $this->get('templates');
        $types[] = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_TEMPLATE'));
        $types = array_merge($types, $templates);
        $this->templates = JHTML::_('select.genericlist', $types, 'template_export', 'class="inputbox" size="1" ' , 'value', 'text', "$");

        // Set the toolbar
        $this->addToolbar();
        $bar = JToolBar::getInstance('toolbar');
        $url = JRoute::_('index.php?option=com_biblestudy&view=templates&layout=default_export');
        $bar->appendButton('Link','export', 'JBS_TPL_IMPORT_EXPORT_TEMPLATE', $url);

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
        JToolBarHelper::title(JText::_('JBS_CMN_TEMPLATES'), 'templates.png');
        if ($this->canDo->get('core.create')) {
            JToolBarHelper::addNew('template.add');
        }
        if ($this->canDo->get('core.edit')) {
            JToolBarHelper::editList('template.edit');
          //  JToolBarHelper::custom( $task = 'template.template_export ', $icon = 'download.png', $iconOver = 'JBS_TPL_EXPORT_TEMPLATE', $alt = 'JBS_TPL_EXPORT_TEMPLATE', $listSelect = true, $x = false );
           // JToolBarHelper::custom( $task = 'template.template_import ', $icon = 'upload.png', $iconOver = 'JBS_TPL_IMPORT_TEMPLATE', $alt = 'JBS_TPL_IMPORT_TEMPLATE', $listSelect = false, $x = false );
        }
        if ($this->canDo->get('core.edit.state')) {
            JToolBarHelper::divider();
            JToolBarHelper::publishList('templates.publish');
            JToolBarHelper::unpublishList('templates.unpublish');
        }
        if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete')) {
            JToolBarHelper::deleteList('', 'templates.delete', 'JTOOLBAR_EMPTY_TRASH');
        } elseif ($this->canDo->get('core.delete')) {
            JToolBarHelper::trash('templates.trash');
        }
        JToolBarHelper::divider();
    }

    /**
     * Add the page title to browser.
     *
     * @since	7.1.0
     */
    protected function setDocument() {
        $document = JFactory::getDocument();
        $document->setTitle(JText::_('JBS_TITLE_TEMPLATES'));
    }

}