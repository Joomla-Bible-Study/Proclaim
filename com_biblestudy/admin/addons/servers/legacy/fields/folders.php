<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('groupedlist');

/**
 * Form Field class for the Joomla CMS.
 * Supports a select grouped list of template styles
 *
 * @package     Joomla.Libraries
 * @subpackage  Form
 * @since       1.6
 */
class JFormFieldFolders extends JFormFieldGroupedList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'Folders';

	/**
	 * Method to get the list of template style options
	 * grouped by template.
	 * Use the client attribute to specify a specific client.
	 * Use the template attribute to specify a specific template
	 *
	 * @return  array  The field option objects as a nested array in groups.
	 *
	 * @since   1.6
	 */
	protected function getGroups()
	{
		// Initialize variables.
		$groups = array();
		$lang   = JFactory::getLanguage();

		// Get the database object and a new query object.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Build the query.
		$query->select('folder.id, folder.server_id, server.server_name, folder.foldername as folder_name');
		$query->from('#__bsms_folders as folder');
		$query->where('folder.published = 1');
		$query->order('foldername');
		$query->join('LEFT', '#__bsms_servers as server on server.id = folder.server_id');
		$query->where('server.published=1');

		// Set the query and load the styles.
		$db->setQuery($query);
		$folders = $db->loadObjectList();

		// Build the grouped list array.
		if ($folders)
		{
			foreach ($folders as $folder)
			{
				$name = JText::_($folder->server_name);

				// Initialize the group if necessary.
				if (!isset($groups[$name]))
				{
					$groups[$name] = array();
				}

				$groups[$name][] = JHtml::_('select.option', $folder->server_id . '.' . $folder->id, $folder->folder_name);
			}
		}

		// Merge any additional groups in the XML definition.
		$groups = array_merge(parent::getGroups(), $groups);

		return $groups;
	}
}
