<?php
/**
 * View html
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * JView class for Cpanel
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyViewCpanel extends JViewLegacy
{
	/**
	 * Data from Model
	 *
	 * @var object
	 * @since    7.0.0
	 */
	public $xml;

	/**
	 * Total Messages
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $total_messages;

	/**
	 * Side Bar
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $sidebar;

	/**
	 * State
	 *
	 * @var string
	 * @since    7.0.0
	 */
	protected $state;

	protected $hasPostInstallationMessages;

	protected $extension_id;

	/**
	 * Display
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since    7.0.0
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$model       = $this->getModel();
		$component = JPATH_ADMINISTRATOR . '/components/com_biblestudy/biblestudy.xml';

		if (file_exists($component))
		{
			$this->xml = simplexml_load_file($component);
		}

		$this->total_messages = JBSMStats::get_total_messages();

		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();

		// Post-installation messages information
		$this->hasPostInstallationMessages = $model->hasPostInstallMessages();
		$this->extension_id                = $this->state->get('extension_id', 0, 'int');

		// Set the document
		$this->setDocument();

		// Display the template
		return parent::display($tpl);
	}

	/**
	 * Add Toolbar to page
	 *
	 * @since 7.0.0
	 *
	 * @return void
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_('JBS_CMN_CONTROL_PANEL'), 'administration');
	}

	/**
	 * Add the page title to browser.
	 *
	 * @since    7.1.0
	 *
	 * @return void
	 */
	protected function setDocument()
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JBS_TITLE_CONTROL_PANEL'));
	}
}
