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
require_once (JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'upload.php');
jimport('joomla.application.component.view');

class BiblestudyViewMessage extends JView {

    protected $form;
    protected $item;
    protected $state;
    protected $admin;

    function display($tpl = null) {
        $this->form = $this->get("Form");
        $this->item = $this->get("Item");

        $this->mediafiles = $this->get('MediaFiles');
        $this->setLayout('form');

        $this->loadHelper('params');
        $this->admin = BsmHelper::getAdmin();
        $this->canDo = BibleStudyHelper::getActions($type = 'message', $Itemid = $this->item->id);
        $this->addToolbar();
        $host = JURI::base();
        $document = JFactory::getDocument();
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/plugins/jquery.tokeninput.js');
        $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/token-input-jbs.css');
        $document->addScript($host.'media/com_biblestudy/js/swfupload/swfupload.js');
        $document->addScript($host.'media/com_biblestudy/js/swfupload/swfupload.queue.js');
        $document->addScript($host.'media/com_biblestudy/js/swfupload/fileprogress.js');
        $document->addScript($host.'media/com_biblestudy/js/swfupload/handlers.js');
        $document->addScript(JURI::root() . 'administrator/components/com_biblestudy/views/message/tmpl/submitbutton.js');
        $document->addStyleSheet($host.'media/com_biblestudy/js/swfupload/default.css');
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

        $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/js/ui/theme/ui.all.css');

        $document->addScript(JURI::base() . 'media/com_biblestudy/js/biblestudy.js');
        $swfUploadHeadJs = JBSUpload::uploadjs($host);
        //add the javascript to the head of the html document
        $document->addScriptDeclaration($swfUploadHeadJs);
        parent::display($tpl);
    }

    protected function addToolbar() {
        JRequest::setVar('hidemainmenu', true);
        $isNew = ($this->item->id < 1);
        $title = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
        JToolBarHelper::title(JText::_('JBS_CMN_STUDIES') . ': <small><small>[ ' . $title . ' ]</small></small>', 'studies.png');

        $canDo = BibleStudyHelper::getActions($this->item->id, 'message');
        if ($this->canDo->get('core.edit', 'com_biblestudy')) {
            JToolBarHelper::save('message.save');
            JToolBarHelper::apply('message.apply');
        }
        JToolBarHelper::cancel('message.cancel', 'JTOOLBAR_CANCEL');
        if ($this->canDo->get('core.edit', 'com_biblestudy') && !$isNew) {
            JToolBarHelper::divider();
            JToolBarHelper::custom('resetHits', 'reset.png', 'Reset Hits', 'JBS_STY_RESET_HITS', false, false);
        }

        JToolBarHelper::divider();
        JToolBarHelper::help('biblestudy', true);
    }

}
