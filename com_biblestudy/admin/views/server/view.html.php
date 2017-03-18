<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2017 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * View class for Server
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyViewServer extends JViewLegacy
{
	/**
	 * Form
	 *
	 * @var object
	 * @since    7.0.0
	 */
	protected $form;

	/**
	 * Server form
	 *
	 * @var string
	 * @since    7.0.0
	 */
	protected $server_form;

	/**
	 * Item
	 *
	 * @var object
	 * @since    7.0.0
	 */
	protected $item;

	/**
	 * State
	 *
	 * @var object
	 * @since    7.0.0
	 */
	protected $state;

	/**
	 * Admin
	 *
	 * @var object
	 * @since    7.0.0
	 */
	protected $admin;

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
		$this->form        = $this->get("form");
		$this->server_form = $this->get('AddonServerForm');

		$this->item  = $this->get("Item");
		$this->state = $this->get("State");
		$this->canDo = JBSMBibleStudyHelper::getActions($this->item->id, 'server');

		$this->setLayout("edit");

		// Set the toolbar
		$this->addToolbar();

		// Set the document
		$this->setDocument();

		// Display the template
		return parent::display($tpl);
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
		$isNew = ($this->item->id < 1);
		$title = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
		JToolbarHelper::title(JText::_('JBS_CMN_SERVERS') . ': <small><small>[' . $title . ']</small></small>', 'database database');

		if ($isNew && $this->canDo->get('core.create', 'com_biblestudy'))
		{
			JToolbarHelper::apply('server.apply');
			JToolbarHelper::save('server.save');
			JToolbarHelper::save2new('server.save2new');
			JToolbarHelper::cancel('server.cancel');
		}
		else
		{
			if ($this->canDo->get('core.edit', 'com_biblestudy'))
			{
				JToolbarHelper::apply('server.apply');
				JToolbarHelper::save('server.save');

				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($this->canDo->get('core.create', 'com_biblestudy'))
				{
					JToolbarHelper::save2new('server.save2new');
				}
			}
			// If checked out, we can still save
			if ($this->canDo->get('core.create', 'com_biblestudy'))
			{
				JToolbarHelper::save2copy('server.save2copy');
			}

			JToolbarHelper::cancel('server.cancel', 'JTOOLBAR_CLOSE');
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
		$document->setTitle($isNew ? JText::_('JBS_TITLE_SERVERS_CREATING') : JText::sprintf('JBS_TITLE_SERVERS_EDITING', $this->item->server_name));
	}
}
