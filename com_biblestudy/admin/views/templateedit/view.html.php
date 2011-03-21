<?php

/**
 * @version     $Id
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'helpers' .DS. 'biblestudy.php');
jimport('joomla.application.component.view');

class biblestudyViewTemplateedit extends JView {

    protected $items;
    protected $pagination;
    protected $state;

    function display($tpl = null) {
        $this->item = $this->get('Item');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->types = $this->get('Types');
        $this->form = $this->get("Form");
        $this->canDo	= BibleStudyHelper::getActions($this->item->id, 'templateedit');
        $this->addToolbar();
        
        $this->setLayout("form");
        parent::display($tpl);
    }

    protected function addToolbar() {
        $isNew = $this->item->id == 0;
        if ($isNew) {
            $text = JText::_('JBS_CMN_NEW');
        } else {
            $text = JText::_('JBS_CMN_EDIT');
        }
        JToolBarHelper::title(JText::_('JBS_TPL_CREATE_TEMPLATE'), 'templates.png');
        JToolbarHelper::save('templateedit.save');
		if ($isNew)
			JToolbarHelper::cancel('templateedit.cancel', 'JTOOLBAR_CANCEL');
		else {
			JToolbarHelper::apply('templateedit.apply');
			JToolbarHelper::cancel('templateedit.cancel', 'JTOOLBAR_CLOSE');
		}
        JToolBarHelper::divider();
        JToolBarHelper::help('biblestudy', true);
    }

}
?>