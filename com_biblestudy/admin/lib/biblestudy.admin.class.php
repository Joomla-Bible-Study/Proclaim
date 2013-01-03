<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JLoader::register('JBSMParams', JPATH_ADMINISTRATOR . '/components/com_biblestudy/helper/params.php');

/**
 * Admin Class
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class JBSAdmin
{

	public static $extension = 'com_biblestudy';

	/**
	 * Get Media Player
	 *
	 * @return string
	 */
	public function getMediaPlayer()
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('#__components.name')
			->from('#__components')
			->where('#__components.name LIKE ' . $db->q('%AvReloaded%'));
		$db->setQuery($query);
		$db->query();
		$num_rows = $db->getNumRows();

		if ($num_rows)
		{
			$player = 'avr';
		}
		else
		{
			$player = false;
		}
		$query = $db->getQuery(true);
		$query->select('element, published')
				->from('#__plugins')
				->where('#__plugins.element LIKE ' . $db->q('%jw_allvideos%'));
		$db->setQuery($query);
		$db->query();
		$num_rows = $db->getNumRows();
		$isav     = $db->loadObject($query);

		if ($num_rows && $isav->published == 1)
		{
			$player = 'av';
		}

		return $player;
	}

	/**
	 * Get Permission
	 *
	 * @return boolean
	 */
	public function getPermission()
	{

		$results = array();

		// Get the level at which users can enter studies
		$admin        = JBSMParams::getAdmin();

		/** @var $params JRegistry */
		$params       = $admin->params;
		$entry_access = $params->get('entry_access');

		$allow_entry = $params->get('allow_entry_study', 0);

		if (!$allow_entry)
		{
			return false;
		}

		$user = JFactory::getUser();

		$usrid     = $user->get('id');
		$getGroups = JAccess::getGroupsByUser($usrid);

		if (!is_array($entry_access))
		{
			$entry_access = $params->get('entry_access');

			foreach ($getGroups AS $newgrpid)
			{

				if ($newgrpid == $entry_access)
				{
					$results[] = 2;
				}
				else
				{
					$results[] = 3;
				}
			} // End of for group ids
		}
		else
		{
			foreach ($entry_access AS $entry)
			{

				foreach ($getGroups AS $newgrpid)
				{

					if ($newgrpid == $entry)
					{
						$results[] = 2;
					}
					else
					{
						$results[] = 3;
					}
				} // End of for group ids

			} // End of foreach $entry_access as $entry

		} // End of else if not array $entry_access
		// Check $results to see if any are true
		if (in_array(2, $results))
		{
			return true;
		}
		else
		{
			return false;
		}
	} // End of Permission function

	/**
	 * Comments Permission.
	 *
	 * @param   object  $params  System Params
	 *
	 * @return bool|int
	 */
	public function commentsPermission($params)
	{
		$results        = array();
		$show_comments  = $params->get('show_comments');
		$enter_comments = $params->get('comment_access');

		// $comments 10 is view only, 11 is view and edit, 0 is no view or edit

		$user = JFactory::getUser();

		$usrid     = $user->get('id');
		$getGroups = JAccess::getGroupsByUser($usrid);

		foreach ($show_comments AS $entry)
		{

			foreach ($getGroups AS $newgrpid)
			{

				if ($newgrpid == $entry)
				{
					$results[] = 2;
				}
				else
				{
					$results[] = 3;
				}
			} // End of for group ids

		} //  End of foreach $entry_access as $entry
		// Check $results to see if any are true. A 2 means they are found in the list, a 3 means they are not
		if (in_array(2, $results))
		{
			$comments = 10;
		}
		else
		{
			$comments = 0;
		}
		if (!$comments)
		{
			return false;
		}
		// Now we check to see if they can add comments
		foreach ($enter_comments AS $entry)
		{

			foreach ($getGroups AS $newgrpid)
			{

				if ($newgrpid == $entry)
				{
					$results[] = 2;
				}
				else
				{
					$results[] = 3;
				}
			} // End of for group ids
		} // End of foreach $entry_access as $entry
		if (in_array(2, $results))
		{
			$comments = 11;
		}
		else
		{
			$comments = 10;
		}

		return $comments;
	}

	/**
	 * Get ShowLevel
	 *
	 * @param   object  $row  Row objects
	 *
	 * @return boolean
	 */
	public function getShowLevel($row)
	{
		$show = null;
		$user = JFactory::getUser();

		$usrid     = $user->get('id');
		$getGroups = JAccess::getGroupsByUser($usrid);

		if (substr_count($row->show_level, ','))
		{
			$showvar = explode(',', $row->show_level);
		}
		else
		{
			$showvar = $row->show_level;
		}
		$sum3 = count($showvar);

		for ($i = 0; $i < $sum3; $i++)
		{
			foreach ($getGroups AS $newgrpid)
			{
				if ($newgrpid == $showvar[$i])
				{
					$show = true;

					return $show;
				}
			}
		}

		return $show;
	}

	/**
	 * Show Rows
	 *
	 * @param   array  $results  Results to Pars
	 *
	 * @return object
	 */
	public function showRows($results)
	{
		$count = count($results);

		for ($i = 0; $i < $count; $i++)
		{
			$show_level = $this->getShowLevel($results[$i]);

			if (!$show_level)
			{
				unset($results[$i]);
			}
		}

		return $results;
	}

}
