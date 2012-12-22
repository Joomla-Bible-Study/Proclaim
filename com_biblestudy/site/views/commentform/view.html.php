<?php

/**
 * View Html
 *
 * @package BibleStudy.Admin
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link    http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;
//require_once(JPATH_ADMINISTRATOR.'/components/com_biblestudy/helpers/biblestudy.php');
JLoader::register('JBSMHelper', JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/biblstudy.php');
/**
 * View class for Comment
 *
 * @package BibleStudy.Admin
 * @since   7.0.0
 */
class BiblestudyViewCommentform extends JViewLegacy
{

	/**
	 * Form
	 * @var array
	 */
	protected $form;

	/**
	 * Item
	 * @var array
	 */
	protected $item;


	/**
	 * Return Page
	 * @var string
	 */
	protected $return_page;

	/**
	 * State
	 * @var array
	 */
	protected $state;

	/**
	 * Admin
	 * @var array
	 */
	protected $admin;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{

		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		// Get model data.
		$this->state = $this->get('State');
		$this->item = $this->get('Item');
		$this->form = $this->get('Form');
		$this->return_page = $this->get('ReturnPage');

		$this->canDo = JBSMHelper::getActions($this->item->id, 'comment');
        $document = JFactory::getDocument();

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors), 500);
			return false;
		}
        //check permissions to enter comments
        if (!$this->canDo->get('core.edit')) {
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }

		//Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->state->params->get('pageclass_sfx'));

		// Display the template
		parent::display($tpl);

		// Set the document
		$this->setDocument();
	}

	/**
	 * Add the page title to browser.
	 *
	 * @since    7.1.0
	 */
	protected function setDocument()
	{
		$isNew = ($this->item->id < 1);
		$document = JFactory::getDocument();
		$document->setTitle($isNew ? JText::_('JBS_TITLE_COMMENT_CREATING') : JText::sprintf('JBS_TITLE_COMMENT_EDITING', $this->item->id));
	}

}