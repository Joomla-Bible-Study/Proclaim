<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
use Joomla\CMS\Factory;
// No direct access
defined('_JEXEC') or die;

/**
 * Biblestudy Component Route Helper
 *
 * @static
 * @package  BibleStudy.Site
 * @since    7.2
 */
abstract class CWMHelperRoute
{
	/**
	 * Lookup
	 *
	 * @var string
	 *
	 * @since    7.2
	 */
	protected static $lookup;

	/**
	 * Get Article Rout
	 *
	 * @param   int  $id        The route of the study item
	 * @param   int  $language  The state of language
	 *
	 * @return string
	 *
	 * @since    7.2
	 */
	public static function getArticleRoute($id, $language = 0)
	{
		$needles = array(
			'article' => array((int) $id)
		);

		// Create the link
		$link = 'index.php?option=com_biblestudy&view=sermon&id=' . $id;

		if ($language && $language !== "*" && JLanguageMultilang::isEnabled())
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('a.sef AS sef');
			$query->select('a.lang_code AS lang_code');
			$query->from('#__languages AS a');

			$db->setQuery($query);
			$langs = $db->loadObjectList();

			foreach ($langs as $lang)
			{
				if ($language == $lang->lang_code)
				{
					$link .= '&lang=' . $lang->sef;
					$needles['language'] = $language;
				}
			}
		}

		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid=' . $item;
		}
		elseif ($item = self::_findItem())
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}

	/**
	 * Find Item
	 *
	 * @param   string  $needles  ?
	 *
	 * @return mixed
	 *
	 * @since    7.2
	 */
	protected static function _findItem($needles = null)
	{
		$app   = JFactory::getApplication();
		$menus = $app->getMenu('site');

		// Prepare the reverse lookup array.
		if (self::$lookup === null)
		{
			self::$lookup = array();

			$component = JComponentHelper::getComponent('com_biblestudy');
			$items     = $menus->getItems('component_id', $component->id);

			foreach ($items as $item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view = $item->query['view'];

					if (!isset(self::$lookup[$view]))
					{
						self::$lookup[$view] = array();
					}

					if (isset($item->query['id']))
					{
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
					foreach ($ids as $id)
					{
						if (isset(self::$lookup[$view][(int) $id]))
						{
							return self::$lookup[$view][(int) $id];
						}
					}
				}
			}
		}
		else
		{
			$active = $menus->getActive();

			if ($active && $active->component === 'com_biblestudy')
			{
				return $active->id;
			}
		}

		return false;
	}

	/**
	 * Get Teacher Route
	 *
	 * @param   int  $id  The route of the teacher item
	 *
	 * @return string
	 *
	 * @since    7.2
	 */
	public static function getTeacherRoute($id)
	{
		// Create the link
		$link = 'index.php?option=com_biblestudy&view=teacher&id=' . $id;

		return $link;
	}

	/**
	 * Get Series Route
	 *
	 * @param   int  $id  ID
	 *
	 * @return string
	 *
	 * @since    7.2
	 */
	public static function getSeriesRoute($id)
	{
		// Create the link
		$link = 'index.php?option=com_biblestudy&view=seriesdisplay&id=' . $id;

		return $link;
	}

	/**
	 * Add Scheme to url
	 *
	 * @param   string  $url     URL of website
	 * @param   string  $scheme  Scheme that need to lead with.
	 *
	 * @return string  The fixed URL
	 *
	 * @since    7.2
	 * @deprecate 8.0.7
	 */
	public static function addScheme($url, $scheme = 'http://')
	{
		if (parse_url($url, PHP_URL_SCHEME) === null)
		{
			return $scheme . $url;
		}

		return $url;
	}
}
