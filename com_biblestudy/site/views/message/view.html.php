<?php

/**
 * Message JView
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.admin.class.php');
require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'biblestudy.php');
require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'params.php');

/**
 * View class for Message
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class biblestudyViewmessage extends JViewLegacy {

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
        $option = JRequest::getCmd('option');
        $JApplication = new JApplication();
        $JApplication->setUserState($option . 'sid', $this->item->id);
        $JApplication->setUserState($option . 'sdate', $this->item->studydate);
        $this->mediafiles = $this->get('MediaFiles');
        $this->setLayout('form');
        $this->canDo = JBSMHelper::getActions($this->item->id, 'message');
        $this->loadHelper('params');
        $this->admin = BsmHelper::getAdmin($isSite = true);

        $user = JFactory::getUser();


        $canDo = JBSMHelper::getActions($this->item->id, 'message');

        if (!$canDo->get('core.edit')) {
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }

        $document = JFactory::getDocument();
        $document->addScript(JURI::base() . 'media/com_biblestudy/jui/js/jquery.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/jui/js/jquery-noconflict.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/jui/js/jquery-ui.core.min.js');
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