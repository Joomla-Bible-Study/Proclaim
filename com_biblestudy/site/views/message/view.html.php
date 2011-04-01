<?php

/**
 * @version     $Id: view.html.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();
require_once (JPATH_SITE  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.admin.class.php');
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'helpers' .DS. 'biblestudy.php');
jimport('joomla.application.component.view');

class biblestudyViewmessage extends JView {

    protected $form;
    protected $item;
    protected $state;
    protected $admin;

    function display($tpl = null) {
        
        $this->form = $this->get("Form");
        $this->item = $this->get("Item");
        $this->mediafiles = $this->get('MediaFiles');
        $this->setLayout('form');
        $this->canDo	= BibleStudyHelper::getActions($this->item->id, 'studiesedit');
        $this->loadHelper('params');
        $this->admin = BsmHelper::getAdmin($isSite = true);
        
                
        $user = JFactory::getUser();
      
      
      $canDo = BibleStudyHelper::getActions($this->item->id, 'studiesedit');
    
      if (!$canDo->get('core.edit'))
      {
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return false; 
      }
      
        parent::display($tpl);
    }

}
?>