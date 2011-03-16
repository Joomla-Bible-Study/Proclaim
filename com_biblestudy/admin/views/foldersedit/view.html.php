<?php

/**
 * @version     $Id
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
jimport('joomla.application.component.view');

class BibleStudyViewFoldersedit extends JView {

    protected $form;
    protected $item;
    protected $state;
    protected $defaults;

    function display($tpl = null) {
        $this->form = $this->get("Form");
        $this->item = $this->get("Item");
        $this->state = $this->get("State");
        $this->canDo	= BibleStudyHelper::getActions($type = 'foldersedit', $Itemid = $this->item->id);
        $this->setLayout("form");
        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar() {
        $canDo = BibleStudyHelper::getActions($type = 'foldersedit', $Itemid = $this->item->id);
        $isNew = ($this->item->id < 1);
        $title = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
        JToolBarHelper::title(JText::_('JBS_FLD_FOLDERS_EDIT') . ': <small><small>[' . $title . ']</small></small>', 'folder.png');
        JToolBarHelper::save('foldersedit.save');
        if ($isNew)
            JToolBarHelper::cancel('foldersedit.cancel', 'JTOOLBAR_CLOSE');
        else {
            JToolBarHelper::apply('foldersedit.apply');
            JToolBarHelper::cancel('foldersedit.cancel', 'JTOOLBAR_CLOSE');
        }
    }

}
?>