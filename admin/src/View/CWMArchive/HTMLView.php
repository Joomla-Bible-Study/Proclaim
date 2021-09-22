<?php
/**
 * View html
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMArchive;

// Check to ensure this file is included in Joomla!
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

defined('_JEXEC') or die;

/**
 * View class for Archive
 *
 * @package  Proclaim.Admin
 * @since    9.0.1
 */
class HTMLView extends BaseHtmlView
{
	public mixed $form;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @throws \Exception
	 * @since  11.1
	 * @see    JViewLegacy::loadTemplate()
	 */
	public function display($tpl = null)
	{
		$this->form = $this->get("Form");

		$this->setLayout('edit');

		// Set the toolbar
		$this->addToolbar();

		// Display the template
		return parent::display($tpl);
	}

	/**
	 * Add Toolbar
	 *
	 * @return null
	 *
	 * @throws Exception
	 * @since  7.0.0
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		JToolbarHelper::title(JText::_('JBS_CMN_ARCHIVE'), 'archive');
		JToolbarHelper::preferences('com_proclaim', '600', '800', 'JBS_ADM_PERMISSIONS');
		JToolbarHelper::custom('administration.back', 'back', 'back', 'JTOOLBAR_BACK', false);
		JToolbarHelper::help('biblestudy', true);
	}
}
