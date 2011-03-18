<?php
/**
 * @version     $Id: view.html.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die();
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'helpers' .DS. 'biblestudy.php');
jimport( 'joomla.application.component.view' );


class biblestudyViewmediaedit extends JView
{

    protected $form;
    protected $item;
    protected $state;
    protected $admin;
    protected $defaults;
	
	function display($tpl = null)
	{
        $this->form = $this->get("Form");
        $this->item = $this->get("Item");
        $this->state = $this->get("State");
        $this->setLayout('form');
		
		$admin = $this->get('Admin');
		$admin_params = new JParameter($admin[0]->params);
		$directory = ($admin_params->get('media_imagefolder') != '' ? '/images/'.$admin_params->get('media_imagefolder') : '/components/com_biblestudy/images');
        $this->assignRef('directory', $directory);
       
        $this->canDo	= BibleStudyHelper::getActions($this->item->id, 'mediaedit' );
        $this->addToolbar();
		parent::display($tpl);
	}
    
     protected function addToolbar() {
        $canDo = BibleStudyHelper::getActions($this->item->id, 'mediaedit');
        $isNew = $this->item->id == 0;
        if($isNew)
            $text = JText::_('JBS_CMN_NEW');
        else
            $text = JText::_('JBS_CMN_EDIT');

        JToolBarHelper::title(JText::_('JBS_MED_EDIT_MEDIA') . ': <small><small>[ ' . $text . ' ]</small></small>', 'mediaimages.png');
        JToolBarHelper::apply('mediaedit.apply');
        JToolBarHelper::save('mediaedit.save');
        JToolBarHelper::divider();
        
        JToolBarHelper::cancel('mediaedit.cancel');
    }
}
?>