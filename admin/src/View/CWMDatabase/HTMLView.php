<?php
/**
 * DataBase html
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMDatabase;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

use CWM\Component\Proclaim\Administrator\Model\CWMAdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

/**
 * View class for Admin
 *
 * @package  Proclaim.Admin
 * @since    9.0.14
 */
class HTMLView extends BaseHtmlView
{
	/**
	 * Version
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public string $version;

	/**
	 * Can Do
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public string $canDo;

	/**
	 * Change Set
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public string $changeSet;

	/**
	 * Errors
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public string $errors;

	/**
	 * Results
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public string $results;

	/**
	 * Schema Version
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public string $schemaVersion;

	/**
	 * Update Version
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public string $updateVersion;

	/**
	 * Filter Params
	 *
	 * @var Registry
	 * @since    9.0.14
	 */
	public Registry $filterParams;

	/**
	 * Pagination
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public string $pagination;

	/**
	 * Error Count
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public string $errorCount;

	/**
	 * Joomla BibleStudy Version
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public string $jversion;

	/**
	 * Temp Destination
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public string $tmp_dest;

	/**
	 * Player Stats
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public string $playerstats;

	/**
	 * Assets
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public string $assets;

	/**
	 * Popups
	 *
	 * @var string
	 * @since    9.0.14
	 */
	public string $popups;

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
	protected mixed $form;

	/**
	 * Item
	 *
	 * @var array
	 * @since    9.0.14
	 */
	protected array $item;

	/**
	 * State
	 *
	 * @var array
	 * @since    9.0.14
	 */
	protected array $state;

	protected string $updateJBSMVersion;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void  A string if successful, otherwise a JError object.
	 *
	 * @throws  \Exception
	 * @since   9.0.14
	 * @see     fetch()
	 */
	public function display($tpl = null)
	{
		// Set variables
		$app = Factory::getApplication();

		$model = new CWMAdminModel;
		$this->setModel($model, true);

		$language = $app->getLanguage();
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
		$this->schemaVersion = ($this->schemaVersion) ? $this->schemaVersion : Text::_('JNONE');
		$this->updateVersion = ($this->updateVersion) ? $this->updateVersion : Text::_('JNONE');
		$this->pagination    = $this->get('Pagination');
		$this->errorCount    = count($this->errors);
		$this->jversion      = $this->get('CompVersion');

		$this->updateJBSMVersion = $this->get('UpdateJBSMVersion');

		$jbsversion    = Installer::parseXMLInstallFile(JPATH_ADMINISTRATOR . '/components/com_proclaim/biblestudy.xml');
		$this->version = $jbsversion['version'];

		if ($this->schemaVersion != $this->changeSet->getSchema())
		{
			$this->errorCount++;
		}

		if (!$this->filterParams)
		{
			$this->errorCount++;
		}

		if (version_compare($this->updateVersion, $this->version) != 0)
		{
			$this->errorCount++;
		}

		if (version_compare($this->updateJBSMVersion, $this->version) != 0)
		{
			$this->errorCount++;
		}

		if ($this->errorCount === 0)
		{
			$app->enqueueMessage(Text::_('COM_INSTALLER_MSG_DATABASE_OK'), 'notice');
		}
		else
		{
			$app->enqueueMessage(Text::_('COM_INSTALLER_MSG_DATABASE_ERRORS'), 'warning');
		}

		$this->setLayout('edit');

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
	 * @throws \Exception
	 * @since 9.0.14
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		ToolbarHelper::title(Text::_('JBS_CMN_ADMINISTRATION'), 'administration');
		ToolbarHelper::preferences('com_proclaim', '600', '800', 'JBS_ADM_PERMISSIONS');
		ToolbarHelper::divider();
		ToolbarHelper::custom('cwmadmin.back', 'back', 'back', 'JTOOLBAR_BACK', false);
		ToolbarHelper::divider();
		ToolbarHelper::custom('cwmadmin.fix', 'refresh', 'refresh', 'JBS_ADM_DB_FIX', false);
		ToolbarHelper::divider();
		ToolbarHelper::help('biblestudy', true);
	}

	/**
	 * Add the page title to browser.
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since    9.0.14
	 */
	protected function setDocument()
	{
		$document = Factory::getApplication()->getDocument();
		$document->setTitle(Text::_('JBS_TITLE_ADMINISTRATION'));
	}
}
