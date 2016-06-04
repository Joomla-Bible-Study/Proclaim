<?php
/**
 * Assets html
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
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

	/** @var int Total numbers of Steps */
	public $totalSteps = 0;

	/** @var int Numbers of Steps already processed */
	public $doneSteps = 0;

	/** @var array Call stack for the Visioning System. */
	public $callstack = array();

	public $version;

	public $step;

	public $assets;

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
		$app         = JFactory::getApplication();
		$this->state = $app->input->getBool('scanstate', false);
		$layout      = $app->input->get('layout', 'edit');
		$task        = $app->input->get('task', 'checkassets');

		$session      = JFactory::getSession();
		$this->assets = $session->get('checkassets', null, 'JBSM');
		$stack        = $session->get('asset_stack', '', 'JBSM');

		if (empty($stack))
		{
			$this->_versionStack  = array();
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

			$stack = json_decode($stack, true);

			$this->_versionStack  = $stack['version'];
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
			JFactory::getDocument()->addScriptDeclaration($script);
		}

		if ($task == 'browse' || $task == 'run')
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
		JToolbarHelper::title(JText::_('JBS_CMN_ADMINISTRATION'), 'administration');
		JToolbarHelper::custom('admin.back', 'back', 'back', 'JTOOLBAR_BACK', false);
		JToolbarHelper::help('biblestudy', true);
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
