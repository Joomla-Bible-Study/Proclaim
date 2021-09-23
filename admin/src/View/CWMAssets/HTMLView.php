<?php
/**
 * Assets html
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMAssets;

// Check to ensure this file is included in Joomla!
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

defined('_JEXEC') or die;

/**
 * View class for Admin
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HTMLView extends BaseHtmlView
{
	/** @var integer Total numbers of Steps
	 *
	 * @since 9.0.0 */
	public $totalSteps = 0;

	/** @var integer Numbers of Steps already processed
	 *
	 * @since 9.0.0 */
	public $doneSteps = 0;

	/** @var array Call stack for the Visioning System.
	 *
	 * @since 9.0.0 */
	public $callstack = array();

	public $version;

	public $step;

	public $assets;

	/** @var string More
	 *
	 * @since 9.0.0 */
	protected $more;

	/** @var  string Percentage
	 *
	 * @since 9.0.0 */
	protected $percentage;

	/** @var string Starte of install
	 *
	 * @since 9.0.0 */
	public $state;

	/** @var JObject Status
	 *
	 * @since 9.0.0 */
	public $status;

	/** @var array The pre versions to process
	 *
	 * @since 9.0.0 */
	private $versionStack = array();

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @see     fetch()
	 * @since   11.1
	 * @throws  \Exception
	 */
	public function display($tpl = null)
	{
		$app         = Factory::getApplication();
		$this->state = $app->input->getBool('scanstate', false);
		$layout      = $app->input->get('layout', 'edit');
		$task        = $app->input->get('task', 'checkassets');

		$session      = $app->getSession();
		$this->assets = $session->get('checkassets', null, 'JBSM');
		$stack        = $session->get('asset_stack', '', 'JBSM');

		if (empty($stack))
		{
			$this->versionStack   = array();
			$this->step           = null;
			$this->totalSteps     = 0;
			$this->doneSteps      = 0;
		}
		else
		{
			if (function_exists('base64_encode') && function_exists('base64_decode'))
			{
				$stack = base64_decode($stack);

				if (function_exists('gzdeflate') && function_exists('gzinflate'))
				{
					$stack = gzinflate($stack);
				}
			}

			$stack = json_decode($stack, true, 512, JSON_THROW_ON_ERROR);

			$this->versionStack   = $stack['version'];
			$this->step           = $stack['step'];
			$this->totalSteps     = $stack['total'];
			$this->doneSteps      = $stack['done'];
		}

		$percent = 0;

		if ($this->state)
		{
			if ($this->totalSteps > 0)
			{
				$percent = min(max(round(100 * $this->doneSteps / $this->totalSteps), 1), 100);
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
			$app->getDocument()->addScriptDeclaration($script);
		}

		if ($task === 'browse' || $task === 'run')
		{
			$this->setLayout('fix');
		}
		else
		{
			$this->setLayout('edit');
		}

		// Get data from the model
		$this->state  = $this->get("State");

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
		ToolbarHelper::title(Text::_('JBS_CMN_ADMINISTRATION'), 'administration');
		ToolbarHelper::custom('administration.back', 'back', 'back', 'JTOOLBAR_BACK', false);
		ToolbarHelper::help('biblestudy', true);
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
		$document = Factory::getApplication()->getDocument();
		$document->setTitle(Text::_('JBS_TITLE_ADMINISTRATION'));
	}
}
