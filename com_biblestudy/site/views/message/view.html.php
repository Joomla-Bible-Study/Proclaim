<?php

/**
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.admin.class.php');
require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'biblestudy.php');
jimport('joomla.application.component.view');

/**
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class biblestudyViewmessage extends JView {

    protected $form;
    protected $item;
    protected $state;
    protected $admin;

    function display($tpl = null) {

        $this->form = $this->get("Form");
        $this->item = $this->get("Item");
        $option = JRequest::getCmd('option');
        @JApplication::setUserState($option . 'sid', $this->item->id);
        @JApplication::setUserState($option . 'sdate', $this->item->studydate);
        $this->mediafiles = $this->get('MediaFiles');
        $this->setLayout('form');
        $this->canDo = BibleStudyHelper::getActions($this->item->id, 'message');
        $this->loadHelper('params');
        $this->admin = @BsmHelper::getAdmin($isSite = true);

        $user = JFactory::getUser();


        $canDo = BibleStudyHelper::getActions($this->item->id, 'message');

        if (!$canDo->get('core.edit')) {
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }

        $document = JFactory::getDocument();
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/jquery.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/noconflict.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/ui/jquery-ui.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/plugins/jquery.tokeninput.js');
        $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/token-input-jbs.css');
        $document->addStyleSheet(JURI::base() . 'administrator/templates/system/css/system.css');
        $document->addStyleSheet(JURI::base() . 'administrator/templates/bluestork/css/template.css');
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
        $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/jquery.tagit.css');

        $document->addScript(JURI::base() . 'media/com_biblestudy/js/biblestudy.js');

        parent::display($tpl);
    }

}