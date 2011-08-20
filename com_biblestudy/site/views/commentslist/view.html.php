<?php
/**
 * @version     $Id: view.html.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();
//require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.admin.class.php');
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'helpers' .DS. 'biblestudy.php');

jimport('joomla.application.component.view');

class biblestudyViewcommentslist extends JView
{
	protected $items;
	protected $pagination;
	protected $state;

	function display($tpl = null) {
		$this->canDo	= BibleStudyHelper::getActions('', 'commentsedit');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');

		//Check for errors
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		//Load the Admin settings
		$this->loadHelper('params');
		$this->admin = BsmHelper::getAdmin($issite = true);

		//check permissions to enter studies
		if (!$this->canDo->get('core.edit'))
		{
			JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
			return false;
		}
		$this->setLayout('form');

		parent::display($tpl);
	}

	 
}