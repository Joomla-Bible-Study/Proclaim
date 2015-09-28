<?php
/**
 * Assets html
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

/**
 * View class for Admin
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyViewAssets extends JViewLegacy
{
	/**
	 * Can Do
	 *
	 * @var string
	 */
	public $canDo;

	/**
	 * Assets
	 *
	 * @var string
	 */
	public $assets;

	/**
	 * Form
	 *
	 * @var array
	 */
	protected $form;

	/**
	 * Item
	 *
	 * @var array
	 */
	protected $item;

	/**
	 * State
	 *
	 * @var array
	 */
	protected $state;

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
		$model = JModelLegacy::getInstance('Admin', 'BiblestudyModel');
		$this->setModel($model, true);

		$language = JFactory::getLanguage();
		$language->load('com_installer');

		// Get data from the model
		$this->form   = $this->get("Form");
		$this->item   = $this->get("Item");
		$this->state  = $this->get("State");
		$this->canDo  = JBSMBibleStudyHelper::getActions($this->item->id);
		$this->assets = JFactory::getApplication()->input->get('checkassets', null, 'get');

		$this->setLayout('edit');

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
	 * @return null
	 *
	 * @since 7.0.0
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		JToolBarHelper::title(JText::_('JBS_CMN_ADMINISTRATION'), 'administration');
		JToolBarHelper::preferences('com_biblestudy', '600', '800', 'JBS_ADM_PERMISSIONS');
		JToolBarHelper::divider();
		JToolBarHelper::custom('admin.back', 'back', 'back', 'JTOOLBAR_BACK', false);
		JToolBarHelper::divider();
		JToolBarHelper::help('biblestudy', true);
	}

	/**
	 * Add the page title to browser.
	 *
	 * @return null
	 *
	 * @since    7.1.0
	 */
	protected function setDocument()
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JBS_TITLE_ADMINISTRATION'));
	}

}
