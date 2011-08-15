<?php

/**
 * @version     $Id: view.html.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();
require_once (JPATH_SITE  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.admin.class.php');
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'helpers' .DS. 'biblestudy.php');
jimport('joomla.application.component.view');

class biblestudyViewmessage extends JView {

    protected $form;
    protected $item;
    protected $state;
    protected $admin;

    function display($tpl = null) {
        
        $this->form = $this->get("Form");
        $this->item = $this->get("Item");
        $this->mediafiles = $this->get('MediaFiles');
        $this->setLayout('form');
        $this->canDo = BibleStudyHelper::getActions($this->item->id, 'studiesedit');
        $this->loadHelper('params');
        $this->admin = BsmHelper::getAdmin($isSite = true);

        $user = JFactory::getUser();


        $canDo = BibleStudyHelper::getActions($this->item->id, 'studiesedit');

        if (!$canDo->get('core.edit'))
        {
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return false; 
        }

        $document = JFactory::getDocument();
        $document->addScript(JURI::base() . 'administrator/components/com_biblestudy/js/jquery.js');
        $document->addScript(JURI::base() . 'administrator/components/com_biblestudy/js/noconflict.js');
        $document->addScript(JURI::base() . 'administrator/components/com_biblestudy/js/ui/jquery-ui.js');
        $document->addScript(JURI::base() . 'administrator/components/com_biblestudy/js/plugins/jquery.tokeninput.js');
        $document->addStyleSheet(JURI::base() . 'administrator/components/com_biblestudy/css/token-input-jbs.css');

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

        //$document->addScript(JURI::base() . 'components/com_biblestudy/js/plugins/jquery.tokeninput.js');
        $document->addStyleSheet(JURI::base() . 'administrator/components/com_biblestudy/js/ui/theme/ui.all.css');
        $document->addStyleSheet(JURI::base() . 'administrator/components/com_biblestudy/css/jquery.tagit.css');

        //$document->addStyleSheet(JURI::base() . 'components/com_biblestudy/css/token-input-facebook.css');

        $document->addScript(JURI::base() . 'administrator/components/com_biblestudy/js/biblestudy.js');
        //$document->addScript(JURI::base() . 'components/com_biblestudy/js/plugins/jquery.selectboxes.js');
    
        parent::display($tpl);
    }
}