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
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'helpers' .DS. 'biblestudy.php');

class biblestudyViewmediafile extends JView {

    protected $form;
    protected $item;
    protected $state;
    protected $admin;


    function display($tpl = null) {
        $this->form = $this->get("Form");
        $this->item = $this->get("Item");
        $this->state = $this->get("State");
        $this->canDo	= BibleStudyHelper::getActions($this->item->id, 'mediafilesedit');
        //Load the Admin settings
        $this->loadHelper('params');
        $this->admin = BsmHelper::getAdmin($issite = true);

        //Needed to load the article field type for the article selector
        jimport('joomla.form.helper');
        JFormHelper::addFieldPath(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'models'.DS.'fields'.DS.'modal');
   
        $user = JFactory::getUser();
      
     
        if (!$this->canDo->get('core.edit')) 
        {
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }   
        $this->setLayout('form');
        
        	require_once( JPATH_COMPONENT_SITE.DS.'helpers'.DS.'toolbar.php' );
		$toolbar = biblestudyHelperToolbar::getToolbar();
		$this->assignRef('toolbar', $toolbar);
		$isNew		= ($mediafilesedit->id < 1);
	
        parent::display($tpl);
    }


}