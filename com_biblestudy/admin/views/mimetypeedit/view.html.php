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

class biblestudyViewMimetypeedit extends JView {

    protected $form;
    protected $item;
    protected $state;
    protected $defaults;

    function display($tpl = null) {
        $this->form = $this->get("Form");
        $this->item = $this->get("Item");
        $this->state = $this->get("State");

        $this->setLayout("form");
        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar() {
        $isNew = ($this->item->id < 1);
        $title = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
        JToolBarHelper::title(JText::_('JBS_MMT_MIME_TYPE_EDIT') . ': <small><small>[' . $title . ']</small></small>', 'mimetype.png');
        JToolBarHelper::save('mimetypeedit.save');
        if ($isNew)
            JToolBarHelper::cancel();
        else {
            JToolBarHelper::apply('mimetypeedit.apply');
            JToolBarHelper::cancel('mimetypeedit.cancel', 'JTOOLBAR_CLOSE');
        }
    }

}
?>