<?php

/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

jimport('joomla.application.component.view');
require_once (JPATH_ROOT  .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_biblestudy' .DIRECTORY_SEPARATOR. 'lib' .DIRECTORY_SEPARATOR. 'biblestudy.admin.class.php');
require_once (JPATH_ADMINISTRATOR  .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_biblestudy' .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'biblestudy.php');

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
		JFormHelper::addFieldPath(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_content'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'fields'.DIRECTORY_SEPARATOR.'modal');
		 
		$user = JFactory::getUser();

		 
		if (!$this->canDo->get('core.edit'))
		{
			JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
			return false;
		}
		$this->setLayout('form');

		require_once( JPATH_COMPONENT_SITE.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'toolbar.php' );
		$toolbar = biblestudyHelperToolbar::getToolbar();
		$this->assignRef('toolbar', $toolbar);
		$isNew		= ($mediafilesedit->id < 1);

		parent::display($tpl);
	}


}