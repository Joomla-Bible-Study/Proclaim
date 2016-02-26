<?php
/**
 * View html
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
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
	 * @var string
	 */
	public $data;

	/**
	 * Total Messages
	 *
	 * @var string
	 */
	public $total_messages;

	/**
	 * Side Bar
	 *
	 * @var string
	 */
	public $sidebar;

	/**
	 * State
	 *
	 * @var string
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
	 */
	public function display($tpl = null)
	{

		$this->state = $this->get('State');
		$this->data  = $this->get('Data');
		$model       = $this->getModel();

		JHtml::stylesheet('media/com_biblestudy/css/cpanel.css');

		$this->total_messages = JBSMStats::get_total_messages();

		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();

		// Post-installation messages information
		$this->hasPostInstallationMessages = $model->hasPostInstallMessages();
		$this->extension_id                = $this->state->get('extension_id', 0, 'int');

		// Display the template
		parent::display($tpl);

		// Set the document
		$this->setDocument();
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
		JToolBarHelper::title(JText::_('JBS_CMN_CONTROL_PANEL'), 'administration');
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
