<?php

/**
 * @version $Id: restore.php 1 $
 * @package COM_JBSMIGRATION
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.view');
jimport('joomla.application.component.helper');
jimport('joomla.i18n.help');


class jbsmigrationViewjbsmigration extends JView {

    function display($tpl = null) {

        $config = & JFactory::getConfig();
        $tmp_dest = $config->getValue('config.tmp_path');
        $this->assignRef('tmp_dest', $tmp_dest);
        $this->addToolbar();
        parent::display($tpl);
    }

    function addToolbar() {
        JToolBarHelper::title(JText::_('JBS_EI_TITLE'), 'folder');
        JToolBarHelper::help('jbsexportimport', true);
    }

}