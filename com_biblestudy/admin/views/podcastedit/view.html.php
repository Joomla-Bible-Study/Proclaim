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

class biblestudyViewpodcastedit extends JView {
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
        JToolBarHelper::title(JText::_('JBS_PDC_PODCAST_EDIT') . ': <small><small>[' . $title . ']</small></small>', 'podcast.png');
        JToolBarHelper::save('podcastedit.save');
        if($isNew)
            JToolBarHelper::cancel('podcastedit.cancel', 'JTOOLBAR_CANCEL');
        else {
            JToolBarHelper::apply('podcastedit.apply');
            JToolBarHelper::cancel('podcastedit.cancel', 'JTOOLBAR_CLOSE');
        }
		JToolBarHelper::divider();
        JToolBarHelper::help('biblestudy', true);

    }
}
?>