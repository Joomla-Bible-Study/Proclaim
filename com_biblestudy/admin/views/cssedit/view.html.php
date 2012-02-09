<?php

/**
 * @version     $Id
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'biblestudy.php');
jimport('joomla.application.component.view');

class biblestudyViewcssedit extends JView {
    var $lists;

    function display($tpl = null) {
        if (!JFactory::getUser()->authorize('core.manage', 'com_biblestudy')) {
            JError::raiseError(404, JText::_('JBS_CMN_NOT_AUTHORIZED'));
            return false;
        }

        JHTML::_('stylesheet', 'icons.css', JURI::base() . 'components/com_biblestudy/css/');
        $lists = $this->get('Data');
        $text = JText::_('JBS_CSS_CSS_EDIT');


        $this->assignRef('lists', $lists);
        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar() {
        JRequest::setVar('hidemainmenu', true);
        JToolBarHelper::title(JText::_('JBS_CSS_CSS_EDIT'), 'css.png');
        JToolBarHelper::save('cssedit.save', 'JTOOLBAR_SAVE');
        JToolBarHelper::apply('cssedit.apply', 'JTOOLBAR_APPLY');
        JToolBarHelper::cancel('cssedit.cancel', 'JTOOLBAR_CANCEL');
        JToolBarHelper::divider();
        JToolBarHelper::custom('cssedit.backup', 'archive', 'Backup CSS', 'JBS_CSS_BACKUP_CSS', false, false);
        JToolBarHelper::custom('cssedit.restorecss', 'save', 'Restore Backup', 'JBS_CSS_RESTORE_CSS', false, false);
        JToolBarHelper::custom('cssedit.resetcss', 'save', 'Reset CSS', 'JBS_CSS_RESET_CSS', false, false);
        JToolBarHelper::divider();
        JToolBarHelper::help('biblestudy', true);
    }

}