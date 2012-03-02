<?php
/**
 * @version $Id: view.html.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ADMINISTRATOR  .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_biblestudy' .DIRECTORY_SEPARATOR. 'lib' .DIRECTORY_SEPARATOR. 'biblestudy.defines.php');
require_once (JPATH_ADMINISTRATOR  .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_biblestudy' .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'biblestudy.php');
jimport('joomla.application.component.view');

class BiblestudyViewShares extends JView {

	protected $items;
	protected $pagination;
	protected $state;

	public function display($tpl = null) {
		$this->items        = $this->get('Items');
		$this->pagination   = $this->get('Pagination');
		$this->state        = $this->get('State');
		$this->canDo        = BibleStudyHelper::getActions('', 'share');
		//Check for errors
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

                // Preprocess the list of items to find ordering divisions.
		// TODO: Complete the ordering stuff with nested sets
		foreach ($this->items as &$item) {
			$item->order_up = true;
			$item->order_dn = true;
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar
	 *
	 * @since 7.0
	 */
	protected function addToolbar() {
                $user	= JFactory::getUser();
		JToolBarHelper::title(JText::_('JBS_CMN_SOCIAL_NETWORKING_LINKS'), 'social.png');
		if ($this->canDo->get('core.create'))
		{
			JToolBarHelper::addNew('share.add');
		}
		if ($this->canDo->get('core.edit'))
		{
			JToolBarHelper::editList('share.edit');
		}
		if ($this->canDo->get('core.edit.state')) {
			JToolBarHelper::divider();
			JToolBarHelper::publishList('shares.publish');
			JToolBarHelper::unpublishList('shares.unpublish');
			JToolBarHelper::archiveList('shares.archive','JTOOLBAR_ARCHIVE');
		}
		if ($this->canDo->get('core.delete'))
		{
			JToolBarHelper::trash('shares.trash');
		}
		if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'shares.delete','JTOOLBAR_EMPTY_TRASH');
		}
	}

}