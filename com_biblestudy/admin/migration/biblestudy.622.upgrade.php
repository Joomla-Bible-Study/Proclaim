<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

JLoader::register('JBSMDbHelper', JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/dbhelper.php');

/**
 * Update from 6.2.2
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class Jbs622Install
{

	/**
	 * Upgrade Function
	 *
	 * @return string
	 */
	public function upgrade622()
	{
		$db    = JFactory::getDBO();
		$query = "SELECT `id`, `params` FROM #__bsms_mediafiles WHERE `params` LIKE '%podcast1%'";
		$db->setQuery($query);
		$db->query();
		$results = $db->loadObjectList();

		if ($results)
		{
			foreach ($results AS $result)
			{
				$old_params = $result->params;
				$new_params = str_replace('podcast1', 'podcasts', $old_params);
				$query      = "UPDATE #__bsms_mediafiles SET `params` = " . $db->quote($new_params) . " WHERE `id` = " .
					(int) $db->quote($result->id);

				if (!JBSMDbHelper::performdb($query, "Build 622: "))
				{
					return false;
				}
			}
		}
		$query = "INSERT INTO #__bsms_version SET `version` = '6.2.2', `installdate`='2010-10-25', `build`='622', " .
			"`versionname`='Judges', `versiondate`='2010-10-25'";

		if (!JBSMDbHelper::performdb($query, "Build 622: "))
		{
			return false;
		}

		return true;
	}

}
