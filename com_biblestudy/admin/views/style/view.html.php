<?php

/**
 * @version $Id: view.html.php 2025 2011-08-28 04:08:06Z genu $
 * @since 7.1.0
 * @desc View for Style edit
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'biblestudy.php');
jimport('joomla.application.component.view');

class biblestudyViewStyle extends JView {

    protected $form;
    protected $item;
    protected $state;
    protected $defaults;

    function display($tpl = null) {
        $this->form = $this->get("Form");
        $item = $this->get("Item");
        if ($item->id == 0) {
            jimport('joomla.client.helper');
            jimport('joomla.filesystem.file');
            JClientHelper::setCredentialsFromRequest('ftp');
            //$ftp = JClientHelper::getCredentials('ftp');
            $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'biblestudy.css.dist';
            $this->defaultstyle = JFile::read($file);
        }
        $this->item = $item;
        $this->state = $this->get("State");
        $this->canDo = BibleStudyHelper::getActions($this->item->id, 'style');
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

    protected function addToolbar() {
        JRequest::setVar('hidemainmenu', true);
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