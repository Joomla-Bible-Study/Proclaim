<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 */
// No Direct Access
defined('_JEXEC') or die;

use \Joomla\Registry\Registry;

/**
 * Update for 7.1.0 class
 *
 * @package  BibleStudy.Admin
 * @since    7.1.0
 * @todo     need to update JError as it has been deprecated, but is still used in Joomla 3.3
 */
class Migration710
{

	/**
	 * Method to Update to 7.1.0
	 *
	 * @param   JDatabaseDriver  $db  Joomla Data bass driver
	 *
	 * @return boolean
	 */
	public function up($db)
	{

		return true;

		// End if no new css file
	}

	/**
	 *  Set Empty templates
	 *
	 * @return void
	 */
	public static function setemptytemplates()
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('id')->from('#__bsms_templates');
		$db->setQuery($query);
		$results = $db->loadObjectList();

		foreach ($results as $result)
		{
			// Store new Record so it can be seen.
			JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
			$table = JTable::getInstance('Template', 'Table', array('dbo' => $db));

			try
			{
				$table->load($result->id);

				// This is a Joomla bug for currentAssetId being missing in table.php. When fixed in Joomla should be removed
				@$table->store();

			}
			catch (Exception $e)
			{
				JLog::add(JText::sprintf('Caught exception: ', $e->getMessage()), JLog::WARNING, 'jerror');
			}
		}
	}

}
