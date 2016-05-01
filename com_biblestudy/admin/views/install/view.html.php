<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JLoader::register('Com_BiblestudyInstallerScript', JPATH_ADMINISTRATOR . '/components/com_biblestudy/biblestudy.script.php');

/**
 * JView class for Install
 *
 * @package  BibleStudy.Admin
 * @since    7.1.0
 */
class BiblestudyViewInstall extends JViewLegacy
{

	/** @var int Total numbers of Steps */
	public $totalSteps = 0;

	/** @var int Numbers of Steps already processed */
	public $doneSteps = 0;

	/** @var string Running Now */
	public $running;

	/** @var array Call stack for the Visioning System. */
	public $callstack = array();

	/** @var string More */
	protected $more;

	/** @var  string Percentage */
	protected $percentage;

	/** @var string Starte of install */
	public $state;

	/** @var JObject Status */
	public $status;

	/** @var array The pre versions to process */
	private $_versionStack = array();

	/** @var array The pre versions sub sql array to process */
	private $_allupdates = array();

	/** @var array Array of Finish Task */
	private $_finish = array();

	/** @var array Array of Install Task */
	private $_install = array();

	/** @var int If was inported */
	private $_isimport = 0;

	/** @type string Type of process */
	protected $type = null;

	/**
	 * Display
	 *
	 * @param   string  $tpl  Template to display
	 *
	 * @return null|void
	 */
	public function display($tpl = null)
	{
		$input = new JInput;
		$input->set('hidemainmenu', true);
		$app   = JFactory::getApplication();
		$this->state = $app->input->getBool('scanstate', false);
		$layout = $app->input->get('layout', 'default');

		if ($this->state == 'start')
		{
			$db = JFactory::getDbo();

			// Check if JBSM can be found from the database
			$table = $db->getPrefix() . 'bsms_storage';
			$db->setQuery("SHOW TABLES LIKE {$db->quote($table)}");

			if ($db->loadResult() != $table)
			{
				$db->setQuery('DROP TABLE IF EXISTS `#__bsms_storage`;');
				$db->execute();
				$db->setQuery('CREATE TABLE `#__bsms_storage` (
							  `key` VARCHAR(255) NOT NULL,
							  `value` LONGTEXT NOT NULL,
							  PRIMARY KEY (`key`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
				$db->execute();
			}
		}
		$this->loadStack();

		if ($this->state)
		{
			if ($this->totalSteps > 0)
			{
				$percent = min(max(round(100 * $this->doneSteps / $this->totalSteps), 1), 100);
			}
			else
			{
				$percent = 0;
			}

			$more = true;
		}
		else
		{
			$percent = 100;
			$more    = false;
		}

		$this->more = $more;
		$this->setLayout($layout);

		$this->percentage = $percent;

		if ($more)
		{
			$script = "window.addEvent( 'domready' ,  function() {\n";
			$script .= "document.forms.adminForm.submit();\n";
			$script .= "});\n";
			JFactory::getDocument()->addScriptDeclaration($script);
		}

		JToolbarHelper::title(JText::_('JBS_MIG_TITLE'), 'administration');
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JBS_MIG_TITLE'));

		// Install systems setup files
		$this->installsetup();

		$this->addToolbar();

		// Set the document
		$this->setDocument();

		// Display the template
		return parent::display($tpl);
	}

	/**
	 * Loads the Versions/SQL/After stack from the session
	 *
	 * @return void
	 */
	private function loadStack()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select(array($db->qn('value')))
			->from($db->qn('#__bsms_storage'))
			->where($db->qn('key') . ' = ' . $db->q('migration_stack'));
		$db->setQuery($query);
		$stack = $db->loadResult();

		if (empty($stack))
		{
			$this->_versionStack = array();
			$this->_allupdates   = array();
			$this->_finish       = array();
			$this->_install      = array();
			$this->_subFiles     = array();
			$this->_subQuery     = array();
			$this->subSteps      = array();
			$this->_isimport     = 0;
			$this->callstack     = array();
			$this->totalSteps    = 0;
			$this->doneSteps     = 0;
			$this->running       = JText::_('JBS_MIG_STARTING');
			$this->type          = null;

			return;
		}

		$stack = json_decode($stack, true);

		$this->_versionStack = $stack['version'];
		$this->_allupdates   = $stack['allupdates'];
		$this->_finish       = $stack['finish'];
		$this->_install      = $stack['install'];
		$this->_subFiles     = $stack['subFiles'];
		$this->_subQuery     = $stack['subQuery'];
		$this->subSteps      = $stack['subSteps'];
		$this->_isimport     = $stack['isimport'];
		$this->callstack     = $stack['callstack'];
		$this->totalSteps    = $stack['total'];
		$this->doneSteps     = $stack['done'];
		$this->running       = $stack['run'];
		$this->type          = $stack['type'];

		return;

	}

	/**
	 * Add Toolbar to page
	 *
	 * @since 7.0.0
	 *
	 * @return null
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);
		JToolbarHelper::help('biblestudy', true);
		JToolbarHelper::title(JText::_('JBS_CMN_INSTALL'), 'administration');
	}

	/**
	 * Add the page title to browser.
	 *
	 * @since    7.1.0
	 *
	 * @return null
	 */
	protected function setDocument()
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::sprintf('JBS_TITLE_INSTALL', $this->percentage . '%', $this->running));
	}

	/**
	 * Setup Array for install System
	 *
	 * @since 7.0.0
	 *
	 * @return null
	 */
	protected function installsetup()
	{
		$installation_queue = array(
			// Example: modules => { (folder) => { (module) => { (position), (published) } }* }*
			'modules' => array(
				'admin' => array(),
				'site'  => array(
					'biblestudy'         => 0,
					'biblestudy_podcast' => 0,
				)
			),
			// Example: plugins => { (folder) => { (element) => (published) }* }*
			'plugins' => array(
				'finder' => array(
					'biblestudy' => 1,
				),
				'search' => array(
					'biblestudysearch' => 0,
				),
				'system' => array(
					'jbsbackup'  => 0,
					'jbspodcast' => 0,
				)
			)
		);

		// -- General settings
		jimport('joomla.installer.installer');
		$db                    = JFactory::getDbo();
		$this->status          = new JObject;
		$this->status->modules = array();
		$this->status->plugins = array();

		// Modules installation
		if (count($installation_queue['modules']))
		{
			foreach ($installation_queue['modules'] as $folder => $modules)
			{
				if (count($modules))
				{
					foreach ($modules as $module => $modulePreferences)
					{
						// Was the module already installed?
						$sql = $db->getQuery(true);
						$sql->select('COUNT(*)')->from('#__modules')->where('module=' . $db->Quote('mod_' . $module));
						$db->setQuery($sql);
						$result                  = $db->loadResult();
						$this->status->modules[] = array(
							'name'   => 'mod_' . $module,
							'client' => $folder,
							'result' => $result
						);
					}
				}
			}
		}
		// Plugins installation
		if (count($installation_queue['plugins']))
		{
			foreach ($installation_queue['plugins'] as $folder => $plugins)
			{
				if (count($plugins))
				{
					foreach ($plugins as $plugin => $published)
					{
						$query = $db->getQuery(true);
						$query->select('COUNT(*)')->from('#__extensions')->where('element=' . $db->q($plugin))->where('folder = ' . $db->q($folder));
						$db->setQuery($query);
						$result                  = $db->loadResult();
						$this->status->plugins[] = array(
							'name'   => 'plg_' . $plugin,
							'group'  => $folder,
							'result' => $result
						);
					}
				}
			}
		}
	}

}
