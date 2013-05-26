<?php

/**
 * JView html
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;


/**
 * View class for Mediaimage
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyViewMediaimage extends JViewLegacy
{

	/**
	 * Form
	 *
	 * @var object
	 */
	protected $form;

	/**
	 * Item
	 *
	 * @var object
	 */
	protected $item;

	/**
	 * State
	 *
	 * @var object
	 */
	protected $state;

	/**
	 * Admin
	 *
	 * @var object
	 */
	protected $admin;

	/**
	 * Defaults
	 *
	 * @var object
	 */
	protected $defaults;

	/**
	 * Directory
	 *
	 * @var string
	 */
	protected $directory;

	/**
	 * Can Do
	 *
	 * @var object
	 */
	protected $canDo;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @see     fetch()
	 * @since   11.1
	 */
	public function display($tpl = null)
	{
		$this->form  = $this->get("Form");
		$this->item  = $this->get("Item");
		$this->state = $this->get("State");
		$this->setLayout('edit');
		$directory       = '/media/com_biblestudy/images';
		$this->directory = $directory;

		$this->canDo = JBSMBibleStudyHelper::getActions($this->item->id, 'mediaimage');

		// Set the toolbar
		$this->addToolbar();

		// Display the template
		parent::display($tpl);

		// Set the document
		$this->setDocument();
	}

	/**
	 * Add Toolbar
	 *
	 * @since 7.0.0
	 *
	 * @return void
	 */
	protected function addToolbar()
	{
		$input = new JInput;
		$input->set('hidemainmenu', true);
		$isNew = ($this->item->id < 1);
		$title = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
		JToolBarHelper::title(JText::_('JBS_CMN_MEDIAIMAGES') . ': <small><small>[' . $title . ']</small></small>', 'mediaimages.png');

		if ($this->canDo->get('core.edit', 'com_biblestudy'))
		{
			JToolBarHelper::save('mediaimage.save');
			JToolBarHelper::apply('mediaimage.apply');
		}
		JToolBarHelper::cancel('mediaimage.cancel', 'JTOOLBAR_CANCEL');

		JToolBarHelper::divider();
		JToolBarHelper::help('biblestudy', true);
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
		$document->setTitle(
			$isNew ? JText::_('JBS_TITLE_MEDIAIMAGES_CREATING')
				: JText::sprintf('JBS_TITLE_MEDIAIMAGES_EDITING', $this->item->media_image_name)
		);
	}

}
