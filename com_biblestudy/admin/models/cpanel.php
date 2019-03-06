<?php
/**
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

use \Joomla\Registry\Registry;

/**
 * JModel class for Cpanel
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class BibleStudyModelCpanel extends JModelLegacy
{
	/**
	 * Get Data
	 *
	 * @return object
	 *
	 * @since 7.0
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

		try
		{
			$data = $db->loadObject();

			// Convert parameter fields to objects.
			$registry = new Registry;
			$registry->loadString($data->manifest_cache);

			if ($data)
			{
				$return->version     = $registry->get('version');
				$return->versiondate = $registry->get('creationDate');
			}
		}
		catch (\Exception $e)
		{
			$return = null;
		}

		return $return;
	}

	/**
	 * Returns true if we are installed in Joomla! 3.2 or later and we have post-installation messages for our component
	 * which must be showed to the user.
	 *
	 * Returns null if the com_postinstall component is broken because the user screwed up his Joomla! site following
	 * some idiot's advice. Apparently there's no shortage of idiots giving terribly bad advice to Joomla! users.
	 *
	 * @return bool|null
	 *
	 * @since 7.0
	 */
	public function hasPostInstallMessages()
	{
		// Make sure we have Joomla! 3.2.0 or later
		if (!version_compare(JVERSION, '3.2.0', 'ge'))
		{
			return false;
		}

		// Get the extension ID
		// Get the extension ID for our component
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where($db->qn('element') . ' = ' . $db->q('com_biblestudy'));
		$db->setQuery($query);

		try
		{
			$ids = $db->loadColumn();
		}
		catch (Exception $exc)
		{
			return false;
		}

		if (empty($ids))
		{
			return false;
		}

		$extension_id = array_shift($ids);

		$this->setState('extension_id', $extension_id);

		if (!defined('FOF_INCLUDED'))
		{
			include_once JPATH_SITE . '/libraries/fof/include.php';
		}

		if (!defined('FOF_INCLUDED'))
		{
			return false;
		}

		// Do I have messages?
		try
		{
			$pimModel = FOFModel::getTmpInstance('Messages', 'PostinstallModel');
			$pimModel->savestate(false);
			$pimModel->setState('eid', $extension_id);

			$list   = $pimModel->getList();
			$result = count($list) >= 1;
		}
		catch (\Exception $e)
		{
			$result = null;
		}

		return ($result);
	}
}
