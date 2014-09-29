<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2014 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No direct access
defined('_JEXEC') or die;
jimport('joomla.application.component.controller');
include_once(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_jbsmigration' . DIRECTORY_SEPARATOR . 'backup.php');

/**
 * JBS Export Migration Controller
 *
 * @package     BibleStudy
 * @subpackage  JBSMigration.Admin
 * @since       7.0.2
 */
class jbsmigrationController extends JController
{

	/**
	 * Method to display the view
	 *
	 * @access    public
	 *
	 * @return void
	 */
	public function display()
	{

		$application = JFactory::getApplication();
		$task        = JRequest::getWord('task', '', 'get');
		$run         = 0;
		$run         = JRequest::getInt('run', '', 'get');

		if ($task == 'export' && $run == 1)
		{
			$export = new JBSExport;
			$result = $export->exportdb();

			if ($result)
			{
				$application->enqueueMessage('' . JText::_('JBS_EI_SUCCESS') . '');
			}
			else
			{
				$application->enqueueMessage('' . JText::_('JBS_EI_FAILURE') . '');
			}
		}
		parent::display();
	}

	/**
	 * Perform DB Query
	 *
	 * @param   string $query  ?
	 *
	 * @return string|boolean
	 */
	public function performdb($query)
	{
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$db->query();

		if ($db->getErrorNum() != 0)
		{
			$results = JText::_('JBS_EI_DB_ERROR') . ': ' . $db->getErrorNum() . "<br /><font color=\"red\">";
			$results .= $db->stderr(true);
			$results .= "</font>";

			return $results;
		}

		return false;
	}

}

// end of class
