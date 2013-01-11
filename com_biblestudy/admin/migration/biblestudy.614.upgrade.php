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
 * Upgrade class from 6.1.4
 *
 * @package  BibleStudy.Admin
 * @since    7.0.2
 *
 * @todo need to redo to support other DB. BCC TOM
 */
class Jbs614Install
{

	/**
	 * Upgrade function
	 *
	 * @return string
	 */
	public function upgrade614()
	{
		$db    = JFactory::getDBO();
		$query = "CREATE TABLE IF NOT EXISTS `#__bsms_studytopics` (
				  `id` int(3) NOT NULL AUTO_INCREMENT,
				  `study_id` int(3) NOT NULL DEFAULT '0',
				  `topic_id` int(3) NOT NULL DEFAULT '0',
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `id` (`id`),
				  KEY `id_2` (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

		if (!JBSMDbHelper::performdb($query, "Build 614: "))
		{
			return false;
		}

		$query = "CREATE TABLE IF NOT EXISTS `#__bsms_timeset` (
                `timeset` VARCHAR(14) ,
                KEY `timeset` (`timeset`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

		if (!JBSMDbHelper::performdb($query, "Build 614: "))
		{
			return false;
		}
		$query = "ALTER TABLE #__bsms_teachers MODIFY `title` varchar(250)";

		if (!JBSMDbHelper::performdb($query, "Build 614: "))
		{
			return false;
		}
		$query = "ALTER TABLE #__bsms_mediafiles ADD COLUMN downloads int(10) DEFAULT 0";

		if (!JBSMDbHelper::performdb($query, "Build 614: "))
		{
			return false;
		}
		$query = "ALTER TABLE #__bsms_mediafiles ADD COLUMN plays int(10) DEFAULT 0";

		if (!JBSMDbHelper::performdb($query, "Build 614: "))
		{
			return false;
		}
		$query = $db->getQuery(true);
		$query->insert('#__bsms_timeset')->set('timeset = ' . 1281646339);

		if (!JBSMDbHelper::performdb($query, "Build 614: "))
		{
			return false;
		}

		// This updates the mediafiles table to reflect the new way of associating files to podcasts
		$query = $db->getQuery(true);
		$query->select('id, params, podcast_id')->from('#__bsms_mediafiles')->where('podcast_id > ' . 0);
		$db->setQuery($query);
		$num_rows = $db->getNumRows();

		if ($num_rows > 0)
		{
			$results = $db->loadObjectList();

			foreach ($results as $result)
			{
				// Added the \n
				$podcast = 'podcasts=' . $result->podcast_id . '\n';
				$params  = $result->params;
				$update  = $podcast . ' ' . $params;
				$query   = $db->getQuery(true);
				$query->update('#__bsms_mediafiles')->set('params = ' . $db->q($update) . 'podcast_id = ' . 0)->where('id = ' . (int) $result->id);

				if (!JBSMDbHelper::performdb($query, "Build 614: "))
				{
					return false;
				}
			}
		}

		$query = "INSERT INTO #__bsms_version SET `version` = '6.2.0', `installdate`='2010-09-06', " .
			"`build`='614', `versionname`='Deuteronomy', `versiondate`='2010-09-06'";

		if (!JBSMDbHelper::performdb($query, "Build 614: "))
		{
			return false;
		}

		return true;
	}

}
