<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * View class for Style
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyViewStyle extends JViewLegacy
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
	 * Defaults
	 *
	 * @var string
	 */
	protected $defaults;

	/**
	 * Default Style
	 *
	 * @var string
	 */
	protected $defaultstyle;

	/**
	 * Can Do
	 *
	 * @var object
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
		$this->form = $this->get("Form");
		$item       = $this->get("Item");

		if ($item->id == 0)
		{
			jimport('joomla.client.helper');
			jimport('joomla.filesystem.file');
			JClientHelper::setCredentialsFromRequest('ftp');
			$file               = JPATH_ROOT . '/media/com_biblestudy/css/biblestudy.css';
			$this->defaultstyle = file_get_contents($file);
		}
		$this->item  = $item;
		$this->state = $this->get("State");
		$this->canDo = JBSMBibleStudyHelper::getActions($this->item->id, 'style');
		$this->setLayout("edit");

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JFactory::getApplication()->enqueueMessage(implode("\n", $errors), 'error');

			return false;
		}

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
		JFactory::getApplication()->input->set('hidemainmenu', true);
		$isNew = ($this->item->id == 0);
		JToolBarHelper::title(
			JText::_('JBS_CMN_STYLES') . ': <small><small>[' . ($isNew ? JText::_('JBS_CMN_NEW')
				: JText::_('JBS_CMN_EDIT')) . ']</small></small>', 'contract contract'
		);

		if ($isNew && $this->canDo->get('core.create'))
		{
			JToolBarHelper::apply('style.apply');
			JToolBarHelper::save('style.save');
			JToolbarHelper::save2new('style.save2new');
			JToolBarHelper::cancel('style.cancel');
		}
		else
		{
			if ($this->canDo->get('core.edit', 'com_biblestudy'))
			{
				JToolBarHelper::apply('style.apply');
				JToolBarHelper::save('style.save');
				JToolBarHelper::save2copy('style.save2copy');
			}
			JToolBarHelper::cancel('style.cancel', 'JTOOLBAR_CLOSE');
		}
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
		$document->setTitle($isNew ? JText::_('JBS_TITLE_STYLES_CREATING') : JText::sprintf('JBS_TITLE_STYLES_EDITING', $this->item->filename));
	}

}
