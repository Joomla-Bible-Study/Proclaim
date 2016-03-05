<?php
/**
 * DataBase html
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * View class for Admin
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyViewDataBase extends JViewLegacy
{

	/**
	 * Version
	 *
	 * @var string
	 */
	public $version;

	/**
	 * Can Do
	 *
	 * @var string
	 */
	public $canDo;

	/**
	 * Change Set
	 *
	 * @var string
	 */
	public $changeSet;

	/**
	 * Errors
	 *
	 * @var string
	 */
	public $errors;

	/**
	 * Results
	 *
	 * @var string
	 */
	public $results;

	/**
	 * Schema Version
	 *
	 * @var string
	 */
	public $schemaVersion;

	/**
	 * Update Version
	 *
	 * @var string
	 */
	public $updateVersion;

	/**
	 * Filter Params
	 *
	 * @var Registry
	 */
	public $filterParams;

	/**
	 * Pagination
	 *
	 * @var string
	 */
	public $pagination;

	/**
	 * Error Count
	 *
	 * @var string
	 */
	public $errorCount;

	/**
	 * Joomla BibleStudy Version
	 *
	 * @var string
	 */
	public $jversion;

	/**
	 * Temp Destination
	 *
	 * @var string
	 */
	public $tmp_dest;

	/**
	 * Player Stats
	 *
	 * @var string
	 */
	public $playerstats;

	/**
	 * Assets
	 *
	 * @var string
	 */
	public $assets;

	/**
	 * Popups
	 *
	 * @var string
	 */
	public $popups;

	/**
	 * SS
	 *
	 * @var string
	 */
	public $ss;

	/**
	 * Lists
	 *
	 * @var string
	 */
	public $lists;

	/**
	 * PI
	 *
	 * @var string
	 */
	public $pi;

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
		$this->form  = $this->get("Form");
		$this->item  = $this->get("Item");
		$this->state = $this->get("State");
		$this->canDo = JBSMBibleStudyHelper::getActions($this->item->id);

		// Get data from the model for database
		$this->changeSet     = $this->get('Items');
		$this->errors        = $this->changeSet->check();
		$this->results       = $this->changeSet->getStatus();
		$this->schemaVersion = $this->get('SchemaVersion');
		$this->updateVersion = $this->get('UpdateVersion');
		$this->filterParams  = $this->get('DefaultTextFilters');
		$this->schemaVersion = ($this->schemaVersion) ? $this->schemaVersion : JText::_('JNONE');
		$this->updateVersion = ($this->updateVersion) ? $this->updateVersion : JText::_('JNONE');
		$this->pagination    = $this->get('Pagination');
		$this->errorCount    = count($this->errors);
		$this->jversion      = $this->get('CompVersion');

		$jbsversion    = JInstaller::parseXMLInstallFile(JPATH_ADMINISTRATOR . '/components/com_biblestudy/biblestudy.xml');
		$this->version = $jbsversion['version'];

		if (!(strncmp($this->schemaVersion, $this->version, 5) === 0))
		{
			$this->errorCount++;
		}
		if (!$this->filterParams)
		{
			$this->errorCount++;
		}
		if (($this->updateVersion != $this->version))
		{
			$this->errorCount++;
		}

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
		JToolBarHelper::custom('admin.fix', 'refresh', 'refresh', 'JBS_ADM_DB_FIX', false, false);
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
