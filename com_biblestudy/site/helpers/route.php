<?php
/**
 * @version		$Id: route.php 21097 2011-04-07 15:38:03Z dextercowley $
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.helper');
jimport('joomla.application.categories');

/**
 * Biblestudy Component Route Helper
 *
 * @static
 * @package		Joomla Bible Study
 * @subpackage	com_biblestudy
 * @since 7.2
 */
abstract class BiblestudyHelperRoute
{
	protected static $lookup;

	/**
	 * @param	int	The route of the study item
	 */
	public static function getArticleRoute($id)
	{ 
		$needles = array(
			'article'  => array((int) $id)
		); //dump ($needles);
		//Create the link
		$link = 'index.php?option=com_biblestudy&view=studydetails&id='. $id;
        
		return $link;
	}

	/**
	 * @param	int	The route of the teacher item
	 */
	public static function getTeacherRoute($id)
	{
			$needles = array(
			'article'  => array((int) $id)
		);
		//Create the link
		$link = 'index.php?option=com_biblestudy&view=teacherdisplay&id='. $id;
        
		return $link;
	}

	public static function getSeriesRoute($id)
	{
			$needles = array(
			'article'  => array((int) $id)
		);
		//Create the link
		$link = 'index.php?option=com_biblestudy&view=seriesdetail&id='. $id;
        
		return $link;
	}

	protected static function _findItem($needles = null)
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');

		// Prepare the reverse lookup array.
		if (self::$lookup === null)
		{
			self::$lookup = array();

			$component	= JComponentHelper::getComponent('com_content');
			$items		= $menus->getItems('component_id', $component->id);
			foreach ($items as $item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view = $item->query['view'];
					if (!isset(self::$lookup[$view])) {
						self::$lookup[$view] = array();
					}
					if (isset($item->query['id'])) {
						self::$lookup[$view][$item->query['id']] = $item->id;
					}
				}
			}
		}

		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(self::$lookup[$view]))
				{
					foreach($ids as $id)
					{
						if (isset(self::$lookup[$view][(int)$id])) {
							return self::$lookup[$view][(int)$id];
						}
					}
				}
			}
		}
		else
		{
			$active = $menus->getActive();
			if ($active && $active->component == 'com_content') {
				return $active->id;
			}
		}

		return null;
	}
}
