<?php
/**
 * View html
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JLoader::register('jbStats', BIBLESTUDY_PATH_ADMIN_LIB . '/biblestudy.stats.class.php');
if (!BIBLESTUDY_CHECKREL)
{
	JLoader::register('LiveUpdate', JPATH_COMPONENT_ADMINISTRATOR . '/liveupdate/liveupdate.php');
}

/**
 * JView class for Cpanel
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyViewCpanel extends JViewLegacy
{

	protected $state;

	public $version;

	public $versiondate;

	public $total_messages;

	public $sidebar;

	/**
	 * Display
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display ($tpl = null)
	{

		$this->state = $this->get('State');

		JHTML::stylesheet('media/com_biblestudy/css/cpanel.css');

		// Get version information
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__extensions');
		$query->where('element = "com_biblestudy" and type = "component"');
		$db->setQuery($query);
		$data = $db->loadObject();

		// Convert parameter fields to objects.
		$registry = new JRegistry;
		$registry->loadString($data->manifest_cache);

		if ($data)
		{
			$this->version     = $registry->get('version');
			$this->versiondate = $registry->get('creationDate');
		}

		$this->total_messages = jbStats::get_total_messages();

		$this->addToolbar();

		if (BIBLESTUDY_CHECKREL)
		{
			$this->sidebar = JHtmlSidebar::render();
		}

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
	protected function addToolbar ()
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
	protected function setDocument ()
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JBS_TITLE_CONTROL_PANEL'));
	}

}
