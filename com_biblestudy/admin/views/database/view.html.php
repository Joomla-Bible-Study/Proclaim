<?php
/**
 * DataBase html
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2017 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * View class for Admin
 *
 * @package  BibleStudy.Admin
 * @since    9.0.14
 */
class BiblestudyViewDataBase extends JViewLegacy
{
	/**
	 * Version
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public $version;

	/**
	 * Can Do
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public $canDo;

	/**
	 * Change Set
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public $changeSet;

	/**
	 * Errors
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public $errors;

	/**
	 * Results
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public $results;

	/**
	 * Schema Version
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public $schemaVersion;

	/**
	 * Update Version
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public $updateVersion;

	/**
	 * Filter Params
	 *
	 * @var Registry
	 * @since    9.0.14
	 */
	public $filterParams;

	/**
	 * Pagination
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public $pagination;

	/**
	 * Error Count
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public $errorCount;

	/**
	 * Joomla BibleStudy Version
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public $jversion;

	/**
	 * Temp Destination
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public $tmp_dest;

	/**
	 * Player Stats
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public $playerstats;

	/**
	 * Assets
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public $assets;

	/**
	 * Popups
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public $popups;

	/**
	 * SS
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public $ss;

	/**
	 * Lists
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public $lists;

	/**
	 * PI
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public $pi;

	/**
	 * Form
	 *
	 * @var array
	 * @since    9.0.14
	 */
	protected $form;

	/**
	 * Item
	 *
	 * @var array
	 * @since    9.0.14
	 */
	protected $item;

	/**
	 * State
	 *
	 * @var array
	 * @since    9.0.14
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
	 * @since   9.0.14
	 */
	public function display($tpl = null)
	{
		// Set variables
		$app = JFactory::getApplication();

		$model = JModelLegacy::getInstance('Admin', 'BiblestudyModel');
		$this->setModel($model, true);

		$language = JFactory::getLanguage();
		$language->load('com_installer');

		// Get data from the model
		$this->form  = $this->get("Form");
		$this->state = $this->get("State");

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

		if ($this->errorCount === 0)
		{
			$app->enqueueMessage(JText::_('COM_INSTALLER_MSG_DATABASE_OK'), 'notice');
		}
		else
		{
			$app->enqueueMessage(JText::_('COM_INSTALLER_MSG_DATABASE_ERRORS'), 'warning');
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
	 * @return void
	 *
	 * @since 9.0.14
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		JToolbarHelper::title(JText::_('JBS_CMN_ADMINISTRATION'), 'administration');
		JToolbarHelper::preferences('com_biblestudy', '600', '800', 'JBS_ADM_PERMISSIONS');
		JToolbarHelper::divider();
		JToolbarHelper::custom('admin.back', 'back', 'back', 'JTOOLBAR_BACK', false);
		JToolbarHelper::divider();
		JToolbarHelper::custom('admin.fix', 'refresh', 'refresh', 'JBS_ADM_DB_FIX', false);
		JToolbarHelper::divider();
		JToolbarHelper::help('biblestudy', true);
	}

	/**
	 * Add the page title to browser.
	 *
	 * @return void
	 *
	 * @since    9.0.14
	 */
	protected function setDocument()
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JBS_TITLE_ADMINISTRATION'));
	}
}
