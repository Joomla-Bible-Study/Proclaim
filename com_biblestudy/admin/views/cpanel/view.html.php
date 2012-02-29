<?php

/**
 * @version $Id: view.html.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.defines.php');
jimport('joomla.application.component.view');

class biblestudyViewcpanel extends JView {

    function display($tpl = null) {

        JHTML::stylesheet('cpanel.css', JURI::base() . '../media/com_biblestudy/css/');

        $this->addToolbar();


        parent::display($tpl);
    }

    protected function addToolbar() {
        JToolBarHelper::title(JText::_('JBS_CMN_CONTROL_PANEL'), 'administration');
    }

}