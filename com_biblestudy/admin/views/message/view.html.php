<?php

/**
 * @version     $Id: view.html.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.defines.php');
require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'biblestudy.php');

jimport('joomla.application.component.view');

class BiblestudyViewMessage extends JView {

    protected $form;
    protected $item;
    protected $state;
    protected $admin;

    function display($tpl = null) {
        $this->form = $this->get("Form");
        $this->item = $this->get("Item");
JApplication::setUserState('sid',$this->item->id);

        $this->mediafiles = $this->get('MediaFiles');

        $this->loadHelper('params');
        $this->admin = @BsmHelper::getAdmin();
        $this->canDo = BibleStudyHelper::getActions($type = 'message', $Itemid = $this->item->id);
        $host = JURI::base();
        $document = JFactory::getDocument();
        $document->addScript(JURI::root() . 'media/com_biblestudy/js/plugins/jquery.tokeninput.js');
        $document->addStyleSheet(JURI::root() . 'media/com_biblestudy/css/token-input-jbs.css');
        $script = "
            \$j(document).ready(function() {
                \$j('#topics').tokenInput(" . $this->get('alltopics') . ",
                {
                    theme: 'jbs',
                    hintText: '" . JText::_('JBS_CMN_TOPIC_TAG') . "',
                    noResultsText: '" . JText::_('JBS_CMN_NOT_FOUND') . "',
                    searchingText: '" . JText::_('JBS_CMN_SEARCHING') . "',
                    animateDropdown: false,
                    preventDuplicates: true,
                    prePopulate: " . $this->get('topics') . "
                });
            });
             ";

        $document->addScriptDeclaration($script);

        $document->addStyleSheet(JURI::root() . 'media/com_biblestudy/js/ui/theme/ui.all.css');

        $document->addScript(JURI::root() . 'media/com_biblestudy/js/biblestudy.js');

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
        $title = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
        JToolBarHelper::title(JText::_('JBS_CMN_STUDIES') . ': <small><small>[ ' . $title . ' ]</small></small>', 'studies.png');

        if ($isNew && $this->canDo->get('core.create', 'com_biblestudy')) {
            JToolBarHelper::apply('message.apply');
            JToolBarHelper::save('message.save');
            JToolBarHelper::save2new('message.save2new');
            JToolBarHelper::cancel('message.cancel');
        } else {
            if ($this->canDo->get('core.edit', 'com_biblestudy')) {
                JToolBarHelper::apply('message.apply');
                JToolBarHelper::save('message.save');

                // We can save this record, but check the create permission to see if we can return to make a new one.
                if ($this->canDo->get('core.create', 'com_biblestudy')) {
                    JToolBarHelper::save2new('message.save2new');
                }
            }
            // If checked out, we can still save
            if ($this->canDo->get('core.create', 'com_biblestudy')) {
                JToolBarHelper::save2copy('message.save2copy');
            }
            JToolBarHelper::cancel('message.cancel', 'JTOOLBAR_CLOSE');
            if ($this->canDo->get('core.edit', 'com_biblestudy')) {
                JToolBarHelper::divider();
                JToolBarHelper::custom('resetHits', 'reset.png', 'Reset Hits', 'JBS_STY_RESET_HITS', false, false);
            }
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
        $document->setTitle($isNew ? JText::_('JBS_TITLE_STUDIES_CREATING') : JText::sprintf('JBS_TITLE_STUDIES_EDITING', $this->item->studytitle));
    }

}
