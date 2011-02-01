<?php

/**
 * @version     $Id: view.html.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
jimport('joomla.application.component.view');

class biblestudyViewteacheredit extends JView {

    protected $form;
    protected $item;
    protected $state;
    protected $admin;

    function display($tpl = null) {
        $this->form = $this->get("Form");
        $this->item = $this->get("Item");
        $this->state = $this->get("State");

        //Load the Admin settings
        $this->loadHelper('params');
        $this->admin = BsmHelper::getAdmin();

        $this->setLayout("form");
        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar() {
        $isNew = ($this->item->id < 1);
        $title = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
        JToolBarHelper::title(JText::_('JBS_TCH_TEACHER_EDIT') . ': <small><small>[' . $title . ']</small></small>', 'teachers.png');
        JToolBarHelper::save('teacheredit.save');
        if ($isNew)
            JToolBarHelper::cancel();
        else {
            JToolBarHelper::apply('teacheredit.apply');
            JToolBarHelper::cancel('teacheredit.cancel', 'JTOOLBAR_CLOSE');
        }
    }

} 
?>