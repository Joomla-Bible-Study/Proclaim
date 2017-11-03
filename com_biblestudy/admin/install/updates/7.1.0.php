<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2017 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Update for 7.1.0 class
 *
 * @package  Proclaim.Admin
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
	 *
	 * @since 7.0
	 */
	public function up($db)
	{
		$db->setQuery("ALTER TABLE `#__bsms_servers` ADD COLUMN `type` TINYINT(3) NOT NULL;
ALTER TABLE `#__bsms_servers` ADD COLUMN `ftphost` VARCHAR(100) NOT NULL;
ALTER TABLE `#__bsms_servers` ADD COLUMN `ftpuser` VARCHAR(250) NOT NULL;
ALTER TABLE `#__bsms_servers` ADD COLUMN `ftppassword` VARCHAR(250) NOT NULL;
ALTER TABLE `#__bsms_servers` ADD COLUMN `ftpport` VARCHAR(10) NOT NULL;
ALTER TABLE `#__bsms_servers` ADD COLUMN `aws_key` VARCHAR(100) NOT NULL;
ALTER TABLE `#__bsms_servers` ADD COLUMN `aws_secret` VARCHAR(100) NOT NULL;");

		$db->execute();

		return true;
	}

	/**
	 *  Set Empty templates
	 *
	 * @return void
	 *
	 * @since 7.0
	 */
	public static function setemptytemplates()
	{
		$db    = JFactory::getDbo();
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
				JLog::add(JText::sprintf('Caught exception: ', $e->getMessage()), JLog::WARNING, 'com_biblestudy');
			}
		}
	}
}
