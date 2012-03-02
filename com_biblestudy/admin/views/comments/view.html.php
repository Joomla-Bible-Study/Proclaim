<?php
/**
 * @version     $Id: view.html.php 2025 2011-08-28 04:08:06Z genu $
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

class BiblestudyViewComments extends JView
{
	protected $items;
	protected $pagination;
	protected $state;

	function display($tpl = null) {



		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');

		//Check for errors
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
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
		$canDo = BibleStudyHelper::getActions('', 'comment');
		JToolBarHelper::title(JText::_('JBS_CMN_COMMENTS'), 'comments.png');
		if ($canDo->get('core.create'))
		{
			JToolBarHelper::addNew('comment.add');
		}
		if ($canDo->get('core.edit'))
		{
			JToolBarHelper::editList('comment.edit');
		}
		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::divider();
			JToolBarHelper::publishList('comments.publish');
			JToolBarHelper::unpublishList('comments.unpublish');
		}
		if ($canDo->get('core.delete'))
		{
			JToolBarHelper::trash('comments.trash');
		}
		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'comments.delete','JTOOLBAR_EMPTY_TRASH');
		}

	}

}