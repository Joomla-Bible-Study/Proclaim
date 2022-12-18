<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMInstall;

// No Direct Access
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Input\Input;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Symfony\Component\Config\Loader\Loader;

defined('_JEXEC') or die;

/**
 * JView class for Install
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 */
class HtmlView extends BaseHtmlView
{
	/** @var integer Total numbers of Steps
	 * @since    7.0.0 */
	public int $totalSteps = 0;

	/** @var integer Numbers of Steps already processed
	 * @since    7.0.0 */
	public int $doneSteps = 0;

	/** @var string Running Now
	 * @since    7.0.0 */
	public string $running = '';

	/** @var array Call stack for the Visioning System.
	 * @since    7.0.0 */
	public array $callstack = [];

	public string $subSteps = '';

	public array $subQuery = [];

	public array $subFiles = [];

	public string $version = '0.0.0';

	public array $query = [];

	/** @var string More
	 * @since    7.0.0 */
	protected $more = null;

	/** @var  string Percentage
	 * @since    7.0.0 */
	protected $percentage;

	/** @var string Starte of install
	 * @since    7.0.0 */
	public $state;

	/** @var object Status
	 * @since    7.0.0 */
	public $status;

	/** @var array The pre versions to process
	 * @since    7.0.0 */
	private array $versionStack = [];

	/** @var array The pre versions to process
	 * @since    7.0.0 */
	private array $versionSwitch = [];

	/** @var array The pre versions sub sql array to process
	 * @since    7.0.0 */
	public array $allupdates = [];

	/** @var array Array of Finish Task
	 * @since    7.0.0 */
	private array $finish = [];

	/** @var array Array of Install Task
	 * @since    7.0.0 */
	public array $install = [];

	/** @var integer If was imported
	 * @since    7.0.0 */
	private int $isimport = 0;

	/** @var string Type of process
	 * @since    7.0.0 */
	protected string $type;

	/**
	 * Display
	 *
	 * @param   string  $tpl  Template to display
	 *
	 * @return null|void
	 *
	 * @throws  \Exception
	 * @since   7.0.0
	 */
	public function display($tpl = null)
	{
		$input = new Input;
		$input->set('hidemainmenu', true);
		$app   = Factory::getApplication();
		$this->state = $app->input->get('scanstate', false);
		$layout = $app->input->get('layout', 'default');

		$load = $this->loadStack();
		$more = true;
		$percent = 0;

		if ($this->state && $load)
		{
			if ($this->totalSteps > 0)
			{
				$percent = min(max(round(100 * $this->doneSteps / $this->totalSteps), 1), 100);
			}
		}
		elseif ($load)
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
			$app->getDocument()->addScriptDeclaration($script);
		}

		ToolbarHelper::title(Text::_('JBS_MIG_TITLE'), 'administration');
		$document = $app->getDocument();
		$document->setTitle(Text::_('JBS_MIG_TITLE'));

		// Install systems setup files
		// @todo need to move to a helper as this is call do many times.
		$this->installsetup();

		$this->addToolbar();

		// Set the document
		$this->setDocument();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Loads the Versions/SQL/After stack from the session
	 *
	 * @return boolean
	 *
	 * @throws \Exception
	 * @since    7.0.0
	 */
	private function loadStack()
	{
		$session = Factory::getApplication()->getSession();
		$stack   = $session->get('migration_stack', '', 'CWM');

		if (empty($stack))
		{
			return false;
		}

		if (function_exists('base64_encode') && function_exists('base64_decode'))
		{
			$stack = base64_decode($stack);

			if (function_exists('gzdeflate') && function_exists('gzinflate'))
			{
				$stack = gzinflate($stack);
			}
		}

		$stack = json_decode($stack, true);

		$this->version        = $stack['aversion'];
		$this->versionStack   = $stack['version'];
		$this->versionSwitch  = $stack['switch'];
		$this->allupdates     = $stack['allupdates'];
		$this->finish         = $stack['finish'];
		$this->start          = $stack['start'];
		$this->subFiles       = $stack['subFiles'];
		$this->subQuery       = $stack['subQuery'];
		$this->subSteps       = $stack['subSteps'];
		$this->isimport       = $stack['isimport'];
		$this->callstack      = $stack['callstack'];
		$this->totalSteps     = $stack['total'];
		$this->doneSteps      = $stack['done'];
		$this->running        = $stack['run'];
		$this->query          = $stack['query'];

		return true;
	}

	/**
	 * Add Toolbar to page
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since  7.0.0
	 *
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);
		ToolbarHelper::help('biblestudy', true);
		ToolbarHelper::title(Text::_('JBS_CMN_INSTALL'), 'administration');
	}

	/**
	 * Add the page title to browser.
	 *
	 * @return void
	 * @throws \Exception
	 * @since    7.1.0
	 *
	 */
	protected function setDocument()
	{
		$document = Factory::getApplication()->getDocument();
		$document->setTitle(Text::sprintf('JBS_TITLE_INSTALL', $this->percentage . '%', $this->running));
	}

	/**
	 * Setup Array for install System
	 *
	 * @since 7.0.0
	 *
	 * @return void
	 */
	protected function installsetup()
	{
		$installation_queue = array(
			// Example: modules => { (folder) => { (module) => { (position), (published) } }* }*
			'modules' => array(
				'administrator' => array(),
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
		$db                    = Factory::getContainer()->get('DatabaseDriver');
		$this->status          = new \stdClass;
		$this->status->cwmmodules = array();
		$this->status->cwmplugins = array();

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
						$sql->select('COUNT(*)')->from('#__modules')->where('module=' . $db->q('mod_' . $module));
						$db->setQuery($sql);
						$result                  = $db->loadResult();
						$this->status->cwmmodules[] = array(
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
						$this->status->cwmplugins[] = array(
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