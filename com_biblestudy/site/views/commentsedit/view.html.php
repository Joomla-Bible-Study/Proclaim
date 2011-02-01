<?php
/**
 * @version     $Id: view.html.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.admin.class.php');

jimport('joomla.application.component.view');

class biblestudyViewcommentsedit extends JView {

    protected $form;
    protected $item;
    protected $state;

    function display($tpl = null) {
        $this->form = $this->get("Form");
        $this->item = $this->get("Item");
        $this->state = $this->get("State");

 //Load the Admin settings
        $this->loadHelper('params');
        $this->admin = BsmHelper::getAdmin($issite = true);
//check permissions to enter studies
      $admin_settings = new JBSAdmin();
      $permission = $admin_settings->getPermission();
       if ($permission !== true) {
    			JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
    			return false;
    		}      
        $this->setLayout('form');
        
        
        parent::display($tpl);
    }

   
  
}
?>