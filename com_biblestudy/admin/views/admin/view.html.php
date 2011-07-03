<?php
/**
 * @version     $Id
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
jimport('joomla.application.component.view');
jimport('joomla.application.component.helper');
jimport('joomla.i18n.help');

require_once (BIBLESTUDY_PATH_ADMIN_LIB . DS . 'biblestudy.stats.class.php');

class biblestudyViewadmin extends JView {

    protected $form;
    protected $item;
    protected $state;

    function display($tpl = null) {

        $this->form = $this->get("Form");
        $this->item = $this->get("Item");
        $this->state = $this->get("State");
        $this->setLayout('form');
        $this->addToolbar();


        $db = JFactory::getDBO();    

        $stats = new jbStats();
        $playerstats = $stats->players();
        $this->assignRef('playerstats', $playerstats);

        $popups = $stats->popups();
        $this->assignRef('popups', $popups);

        parent::display($tpl);
    }

    protected function addToolbar() {
        JToolBarHelper::title(JText::_('JBS_CMN_ADMINISTRATION'), 'administration');
        JToolBarHelper::preferences('com_biblestudy','600','800','JBS_ADM_PERMISSIONS');
        JToolBarHelper::divider();
        JToolBarHelper::save('admin.save');
        JToolBarHelper::apply('admin.apply');
        JToolBarHelper::cancel('admin.cancel', 'JTOOLBAR_CLOSE');
        JToolBarHelper::divider();
        JToolBarHelper::custom('admin.resetHits', 'reset.png', 'Reset All Hits', 'JBS_ADM_RESET_ALL_HITS', false, false);
        JToolBarHelper::custom('admin.resetDownloads', 'download.png', 'Reset All Download Hits', 'JBS_ADM_RESET_ALL_DOWNLOAD_HITS', false, false);
        JToolBarHelper::custom('admin.resetPlays', 'play.png', 'Reset All Plays', 'JBS_ADM_RESET_ALL_PLAYS', false, false);
        JToolBarHelper::divider();
        JToolBarHelper::help('biblestudy', true);
    }

}
