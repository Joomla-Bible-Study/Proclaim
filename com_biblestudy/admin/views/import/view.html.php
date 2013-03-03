<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;


/**
 * View class for Import Finish
 *
 * @package  BibleStudy.Admin
 * @since    8.0.0
 */
class BiblestudyViewImport extends JViewLegacy
{
	protected $more;

	protected $percentage;

	protected $callstack;

	protected $jbsimport;

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
		$app             = JFactory::getApplication();

		if ($app->input->getInt('jbsimport') == 0)
		{
			JBSMDbHelper::resetdb();
			$app->enqueueMessage(JText::_('JBS_CMN_DATABASE_NOT_MIGRATED'), 'warning');
		}

		JToolBarHelper::title(JText::_('JBS_CMN_ADMINISTRATION'), 'administration');
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JBS_TITLE_ADMINISTRATION'));

		return parent::display($tpl);
	}
}
