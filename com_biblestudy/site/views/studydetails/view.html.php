<?php

//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.admin.class.php');

$uri = JFactory::getURI();

class biblestudyViewstudydetails extends JView {

    function display($tpl = null) {

        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $document = JFactory::getDocument();
        $document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');
        $document->addScript('http://www.google.com/recaptcha/api/js/recaptcha_ajax.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/player/jwplayer.js');
        $pathway = $mainframe->getPathWay();
        $contentConfig = JComponentHelper::getParams('com_biblestudy');
        $dispatcher = JDispatcher::getInstance();
        // Get the menu item object

        $studydetails = $this->get('Data');
        //Adjust the slug if there is no alias in the row
        //Set the slug
        $studydetails->slug = $studydetails->alias ? ($studydetails->id . ':' . $studydetails->alias) : str_replace(' ', '-', htmlspecialchars_decode($studydetails->studytitle, ENT_QUOTES)) . ':' . $studydetails->id;


        //Load the Admin settings and params from the template
        $this->addHelperPath(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers');
        $this->loadHelper('params');

        $t = JRequest::getInt('t', 'get', 1);
        if (!$t) {
            $t = 1;
        }
        $template = $this->get('template');

        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadJSON($template[0]->params);
        $params = $registry;
        $a_params = $this->get('Admin');

        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadJSON($a_params[0]->params);
        $this->admin_params = $registry;

        // Convert item paremeters into objects
        $registry = new JRegistry;
        $registry->loadJSON($studydetails->params);
        $itemparams = $registry;
        $adminrows = new JBSAdmin();

        //check permissions for this view by running through the records and removing those the user doesn't have permission to see
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();

        //   $count = count($items);
        if ($studydetails->access > 1) {
            if (!in_array($studydetails->access, $groups)) {
                return JError::raiseError('403', JText::_('JBS_CMN_ACCESS_FORBIDDEN'));
            }
        }

        //Prepare meta information (under development)
        if ($itemparams->get('metakey')) {
            $document->setMetadata('keywords', $itemparams->get('metakey'));
        } elseif (!$itemparams->get('metakey')) {
            $document->setMetadata('keywords', $studydetails->topic_text . ',' . $studydetails->studytitle);
        }

        if ($itemparams->get('metadesc')) {
            $document->setDescription($itemparams->get('metadesc'));
        } elseif (!$itemparams->get('metadesc')) {
            $document->setDescription($studydetails->studyintro);
        }
        //Passage link to BibleGateway
        $plugin = JPluginHelper::getPlugin('content', 'scripturelinks');
        if ($plugin) {
            $plugin = JPluginHelper::getPlugin('content', 'scripturelinks');
            // Convert parameter fields to objects.
            $registry = new JRegistry;
            $registry->loadJSON($plugin->params);
            $st_params = $registry;
            $version = $st_params->get('bible_version');
            $windowopen = "window.open(this.href,this.target,'width=800,height=500,scrollbars=1');return false;";
        }

        //Added database queries from the default template - moved here instead
        $database = JFactory::getDBO();
        $query = "SELECT id"
                . "\nFROM #__menu"
                . "\nWHERE link ='index.php?option=com_biblestudy&view=studieslist' and published = 1";
        $database->setQuery($query);
        $menuid = $database->loadResult();
        $this->assignRef('menuid', $menuid);


        if ($this->getLayout() == 'pagebreak') {
            $this->_displayPagebreak($tpl);
            return;
        }
        $print = JRequest::getBool('print');
        // build the html select list for ordering

        /*
         * Process the prepare content plugins
         */
        $article->text = $studydetails->studytext;
        $linkit = $params->get('show_scripture_link');
        if ($linkit) {
            switch ($linkit) {
                case 0:
                    break;
                case 1:
                    JPluginHelper::importPlugin('content');
                    break;
                case 2:
                    JPluginHelper::importPlugin('content', 'scripturelinks');
                    break;
            }
            $limitstart = JRequest::getVar('limitstart', 'int');
            $results = $dispatcher->trigger('onPrepareContent', array(& $article, & $params, $limitstart));
            $article->studytext = $article->text;
        } //end if $linkit
        //Prepares a link string for use in social networking
        $u = JURI::getInstance();
        $detailslink = htmlspecialchars($u->toString());
        $detailslink = JRoute::_($detailslink);
        $this->assignRef('detailslink', $detailslink);
        //End social networking
        // End process prepare content plugins
        $this->assignRef('template', $template);
        $this->assignRef('print', $print);
        $this->assignRef('params', $params);
        $this->assignRef('studydetails', $studydetails);
        $this->assignRef('article', $article);
        $this->assignRef('passage_link', $passage_link);

        parent::display($tpl);
    }

    function _displayPagebreak($tpl) {
        $document = JFactory::getDocument();
        $document->setTitle(JText::_('JBS_CMN_READ_MORE'));
        parent::display($tpl);
    }



}