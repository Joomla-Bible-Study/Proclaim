<?php

/**
 * View for Style edit
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

/**
 * View class for Style
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class biblestudyViewStyle extends JViewLegacy {

    /**
     * Form
     * @var array
     */
    protected $form;

    /**
     * Item
     * @vararray
     */
    protected $item;

    /**
     * State
     * @var array
     */
    protected $state;

    /**
     * Defaults
     * @var array
     */
    protected $defaults;

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
        $item = $this->get("Item");
        if ($item->id == 0) {
            jimport('joomla.client.helper');
            jimport('joomla.filesystem.file');
            JClientHelper::setCredentialsFromRequest('ftp');
            //$ftp = JClientHelper::getCredentials('ftp');
            $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'biblestudy.css';
            $this->defaultstyle = JFile::read($file);
        }
        $this->item = $item;
        $this->state = $this->get("State");
        $this->canDo = JBSMHelper::getActions($this->item->id, 'style');
         $this->setLayout("edit");
        // Check for errors.
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
     * Add Toolbar
     * @since 7.0.0
     */
    protected function addToolbar() {
        JFactory::getApplication()->input->set('hidemainmenu', true);
        $isNew = ($this->item->id == 0);
        JToolBarHelper::title(JText::_('JBS_CMN_STYLES') . ': <small><small>[' . ($isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT')) . ']</small></small>', 'css.png');

        if ($isNew && $this->canDo->get('core.create')) {
            JToolBarHelper::apply('style.apply');
            JToolBarHelper::save('style.save');
            JToolbarHelper::save2new('style.save2new');
            JToolBarHelper::cancel('style.cancel');
        } else {
            if ($this->canDo->get('core.edit', 'com_biblestudy')) {
                JToolBarHelper::apply('style.apply');
                JToolBarHelper::save('style.save');
                JToolBarHelper::save2copy('style.save2copy');
            }
            JToolBarHelper::cancel('style.cancel', 'JTOOLBAR_CLOSE');
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
        $document->setTitle($isNew ? JText::_('JBS_TITLE_STYLES_CREATING') : JText::sprintf('JBS_TITLE_STYLES_EDITING', $this->item->filename));
    }

}