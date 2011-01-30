<?php
/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class biblestudyViewcommentsedit extends JView {

    protected $form;
    protected $item;
    protected $state;

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
	JToolBarHelper::title(   JText::_( 'JBS_CMT_EDIT_COMMENT' ).': <small><small>[ ' . $text.' ]</small></small>', 'comments.png' );
        JToolBarHelper::save('commentsedit.save');
        if ($isNew)
            JToolBarHelper::cancel('commentsedit.cancel', 'JTOOLBAR_CLOSE');
        else {
            JToolBarHelper::apply('commentsedit.apply');
            JToolBarHelper::cancel('commentsedit.cancel', 'JTOOLBAR_CLOSE');
        }
    }

}
?>