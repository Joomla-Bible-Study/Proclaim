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
	public $running = 'Prepering';

	/** @var array Call stack for the Visioning System. */
	public $callstack = array();

	/** @var null Sub running processes */
	public $subrun = null;

	/** @var string More */
	protected $more;

	/** @var  string Percentage */
	protected $percentage;

	/** @var array The pre versions to process */
	private $_versionStack = array();

	/** @var array Array of Finish Task */
	private $_finish = array();

	/** @var array Array of Install Task */
	private $_install = array();

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
		$state = $app->input->getBool('scanstate', false);
		$this->loadStack();

		if ($state)
		{
			if ($this->totalSteps > 0)
			{
				$percent = round($this->doneSteps / $this->totalSteps * 100);
			}

			$more = true;
		}
		else
		{
			$percent = 100;
			$more    = false;
		}

		$this->more = &$more;
		$this->setLayout('default');

		$this->percentage = &$percent;

		if ($more)
		{
			$script = "window.addEvent( 'domready' ,  function() {\n";
			$script .= "document.forms.adminForm.submit();\n";
			$script .= "});\n";
			JFactory::getDocument()->addScriptDeclaration($script);
		}
		JToolBarHelper::title(JText::_('JBS_MIG_TITLE'), 'administration');
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JBS_MIG_TITLE'));

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
		$session = JFactory::getSession();
		$stack   = $session->get('migration_stack', '', 'biblestudy');

		if (empty($stack))
		{
			$this->_versionStack = array();
			$this->_finish       = array();
			$this->_install      = array();
			$this->totalSteps    = 0;
			$this->doneSteps     = 0;
			$this->running       = null;

			return;
		}
		if (empty($subrun))
		{
			$this->subrun = null;
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

		$this->_versionStack = $stack['version'];
		$this->_finish       = $stack['finish'];
		$this->_install      = $stack['install'];
		$this->totalSteps    = $stack['total'];
		$this->doneSteps     = $stack['done'];
		$this->running       = $stack['run'];

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
		JToolBarHelper::help('biblestudy', true);
		JToolBarHelper::title(JText::_('JBS_CMN_INSTALL'), 'administration');
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

}
