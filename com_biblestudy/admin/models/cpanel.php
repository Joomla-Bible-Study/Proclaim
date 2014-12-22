<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2014 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */

/**
 * JModel class for Cpanel
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BibleStudyModelCpanel extends JModelLegacy
{

	/**
	 * Get Data
	 *
	 * @return object
	 */
	public function getData()
	{
		// Get version information
		$db     = JFactory::getDbo();
		$return = new stdClass;
		$query  = $db->getQuery(true);
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
			$return->version   = $registry->get('version');
			$return->versiondate = $registry->get('creationDate');
		}
		return $return;
	}

}
