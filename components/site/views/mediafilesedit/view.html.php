<?php

/**
 * @version     $Id: viewj16.html.php 1394 2011-01-17 21:39:05Z genu $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

jimport('joomla.application.component.view');
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.admin.class.php');

class biblestudyViewmediafilesedit extends JView {

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
        $this->admin = BsmHelper::getAdmin($issite = true);

        //Needed to load the article field type for the article selector
        jimport('joomla.form.helper');
        JFormHelper::addFieldPath(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'models'.DS.'fields'.DS.'modal');
    //check permissions to enter studies
      $admin_settings = new JBSAdmin();
      $permission = $admin_settings->getPermission();
       if ($permission !== true) {
    			JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
    			return false;
    		}      
        $this->setLayout('form');
        
      //  $this->addToolbar();
        parent::display($tpl);
    }

/*    protected function addToolbar() {
        $isNew = ($this->item->id < 1);
        $title = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
        JToolBarHelper::title(JText::_('JBS_MED_EDIT_MEDIA') . ': <small><small>[' . $title . ']</small></small>', 'mp3.png');
        JToolBarHelper::apply('mediafilesedit.apply');
        JToolBarHelper::save('mediafilesedit.save');
        JToolBarHelper::divider();
        if (!$isNew) {
            JToolBarHelper::custom('resetDownloads', 'download.png', 'Reset Download Hits', 'JBS_MED_RESET_DOWNLOAD_HITS', false, false);
            JToolBarHelper::custom('resetPlays', 'play.png', 'Reset Plays', 'JBS_MED_RESET_PLAYS', false, false);
            JToolBarHelper::divider();
        }
        JToolBarHelper::cancel('mediafilesedit.cancel');

        // Add an upload button and view a popup screen width 550 and height 400
        JToolBarHelper::media_manager();
        JToolBarHelper::divider();
        JToolBarHelper::help('biblestudy', true);
    }
*/
}

?>