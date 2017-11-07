<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2017 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * View class for Template
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class BiblestudyViewTemplate extends JViewLegacy
{
	/**
	 * Pagination
	 *
	 * @var array
	 * @since    7.0.0
	 */
	protected $pagination;

	/**
	 * State
	 *
	 * @var array
	 * @since    7.0.0
	 */
	protected $state;

	/**
	 * Item
	 *
	 * @var object
	 * @since    7.0.0
	 */
	protected $item;

	/**
	 * Types
	 *
	 * @var object
	 * @since    7.0.0
	 */
	protected $types;

	/**
	 * Form
	 *
	 * @var object
	 * @since    7.0.0
	 */
	protected $form;

	/**
	 * Can Do
	 *
	 * @var object
	 * @since    7.0.0
	 */
	protected $canDo;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @see     fetch()
	 * @since   11.1
	 */
	public function display($tpl = null)
	{
		$this->item       = $this->get('Item');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');
		$this->types      = $this->get('Types');
		$this->form       = $this->get("Form");
		$this->canDo      = JBSMBibleStudyHelper::getActions($this->item->id, 'template');

		$this->setLayout("edit");

		// Set the toolbar
		$this->addToolbar();

		// Set the document
		$this->setDocument();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Add Toolbar
	 *
	 * @return void
	 *
	 * @since 7.0.0
	 */
	protected function addToolbar()
	{
		$input = new JInput;
		$input->set('hidemainmenu', true);
		$isNew = ($this->item->id == 0);
		$title = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
		JToolbarHelper::title(JText::_('JBS_CMN_TEMPLATES') . ': <small><small>[' . $title . ']</small></small>', 'square square');

		if ($isNew && $this->canDo->get('core.create', 'com_biblestudy'))
		{
			JToolbarHelper::apply('template.apply');
			JToolbarHelper::save('template.save');
			JToolbarHelper::save2new('template.save2new');
			JToolbarHelper::cancel('template.cancel');
		}
		else
		{
			if ($this->canDo->get('core.edit', 'com_biblestudy'))
			{
				JToolbarHelper::apply('template.apply');
				JToolbarHelper::save('template.save');

				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($this->canDo->get('core.create', 'com_biblestudy'))
				{
					JToolbarHelper::save2new('template.save2new');
				}
			}
			// If checked out, we can still save
			if ($this->canDo->get('core.create', 'com_biblestudy'))
			{
				JToolbarHelper::save2copy('template.save2copy');
			}

			JToolbarHelper::cancel('template.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('biblestudy', true);
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
		$document = JFactory::getDocument();
		$document->setTitle($isNew ? JText::_('JBS_TITLE_TEMPLATES_CREATING') : JText::sprintf('JBS_TITLE_TEMPLATES_EDITING', $this->item->title));
	}
}
