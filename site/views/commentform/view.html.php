<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * View class for Comment
 *
 * @property mixed canDo
 * @property mixed pageclass_sfx
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class BiblestudyViewCommentform extends JViewLegacy
{
	/**
	 * Form
	 *
	 * @var array
	 *
	 * @since 7.0
	 */
	protected $form;

	/**
	 * Item
	 *
	 * @var Object
	 *
	 * @since 7.0
	 */
	protected $item;

	/**
	 * Return Page
	 *
	 * @var string
	 *
	 * @since 7.0
	 */
	protected $return_page;

	/**
	 * State
	 *
	 * @var array
	 *
	 * @since 7.0
	 */
	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @throws Exception
	 *
	 * @return  void
	 *
	 * @since 7.0
	 */
	public function display($tpl = null)
	{
		// Get model data.
		$this->state       = $this->get('State');
		$this->item        = $this->get('Item');
		$this->form        = $this->get('Form');
		$this->return_page = $this->get('ReturnPage');

		$this->canDo = JBSMBibleStudyHelper::getActions($this->item->id, 'comment');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		$language = Factory::getLanguage();
		$language->load('', JPATH_ADMINISTRATOR, null, true);

		// Check permissions to enter comments
		if (!$this->canDo->get('core.edit'))
		{
			Factory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

			return;
		}

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->state->params->get('pageclass_sfx'));

		// Display the template
		parent::display($tpl);

		// Set the document
		$this->setDocument();
	}

	/**
	 * Add the page title to browser.
	 *
	 * @return void
	 *
	 * @since    7.1.0
	 */
	protected function setDocument()
	{
		$isNew    = ($this->item->id < 1);
		$document = Factory::getDocument();
		$document->setTitle($isNew ? JText::_('JBS_TITLE_COMMENT_CREATING') : JText::sprintf('JBS_TITLE_COMMENT_EDITING', $this->item->id));
	}
}
