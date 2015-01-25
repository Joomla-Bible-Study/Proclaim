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
 * View class for Migration
 *
 * @package  BibleStudy.Admin
 * @since    8.0.0
 */
class BiblestudyViewMigration extends JViewLegacy
{

	/** @var int Total numbers of Versions */
	public $totalVersions = 0;

	/** @var int Numbers of Versions already processed */
	public $doneVersions = 0;

	/** @var string Running Now */
	public $running = null;

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
		$input = new JInput;
		$input->set('hidemainmenu', true);
		$app   = JFactory::getApplication();
		$state = $app->input->getBool('scanstate', false);
		$this->loadStack();

		if ($state)
		{
			if ($this->totalVersions > 0)
			{
				$percent = round($this->doneVersions / $this->totalVersions * 100);
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
			$this->totalVersions = 0;
			$this->doneVersions  = 0;
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
		$this->totalVersions = $stack['total'];
		$this->doneVersions  = $stack['done'];
		$this->running       = $stack['run'];

	}
}
