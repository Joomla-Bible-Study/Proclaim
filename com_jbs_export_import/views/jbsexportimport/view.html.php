<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class jbsexportimportViewjbsexportimport extends JView {

    function display($tpl = null) {

        $config = & JFactory::getConfig();
        $tmp_dest = $config->getValue('config.tmp_path');
        $this->assignRef('tmp_dest', $tmp_dest);
        $doc = JFactory::getDocument();
        $doc->addStyleDeclaration('.icon-48-sync { background-image: url(./components/com_jbsexportimport/images/sync.png);');
        
        $this->addToolbar();
        parent::display($tpl);
    }

    function addToolbar() {
        JToolBarHelper::title(JText::_('JBS_EI_TITLE'), 'sync.png');
        jimport('joomla.i18n.help');
        JToolBarHelper::help('jbsexportimport', true);
    }

}

?>