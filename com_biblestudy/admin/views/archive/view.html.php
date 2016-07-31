<?php
/**
 * View html
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

/**
 * View class for Archive
 *
 * @package  BibleStudy.Admin
 * @since    9.0.1
 */
class BiblestudyViewArchive extends JViewLegacy
{
	public $form;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @see     JViewLegacy::loadTemplate()
	 * @since   11.1
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
	 * @since 7.0.0
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		JToolbarHelper::title(JText::_('JBS_CMN_ARCHIVE'), 'archive');
		JToolbarHelper::preferences('com_biblestudy', '600', '800', 'JBS_ADM_PERMISSIONS');
		JToolbarHelper::custom('admin.back', 'back', 'back', 'JTOOLBAR_BACK', false);
		JToolbarHelper::help('biblestudy', true);
	}
}
