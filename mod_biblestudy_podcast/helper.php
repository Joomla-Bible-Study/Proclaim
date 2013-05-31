<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// no direct access
defined('_JEXEC') or die;

/**
 * A module to display the podcast subscription table
 *
 * @package     BibleStudy
 * @subpackage  Model.Podcast
 * @since       7.1.0
 */
class ModBibleStudyPodcast
{

	/**
	 * Check to see if the component is enabled and the version
	 *
	 * @return boolean
	 */
	public static function checkforcombiblestudy()
	{
		$go    = null;
		$db    = JFactory::getDBO();
		$query = $db->getQuery('true');
		$query->select('element, enabled');
		$query->from('#__extensions');
		$query->where('element = "com_biblestudy"');
		$db->setQuery($query);
		$db->query();
		$results = $db->loadObjectList();

		if (!$results)
		{
			echo 'Extension Bible Study not found';
			$go = false;
		}
		else
		{
			foreach ($results as $result)
			{
				if ($result->enabled == '1')
				{
					$go = true;
				}
				else
				{
					$go = false;
				}
			}
		}

		return $go;
	}

	/**
	 * Get BibleStudy Template Params
	 *
	 * @param   object $params  ?
	 *
	 * @return \JRegistry
	 */
	public static function getTemplateParams($params)
	{
		$t     = $params->get('t', 1);
		$db    = JFactory::getDBO();
		$query = $db->getQuery('true');
		$query->select('*');
		$query->from('#__bsms_templates');
		$query->where('id = ' . $t);
		$db->setQuery($query);
		$db->query();
		$template = $db->loadObject();
		$registry = new JRegistry;
		$registry->loadString($template->params);

		return $registry;
	}

}
