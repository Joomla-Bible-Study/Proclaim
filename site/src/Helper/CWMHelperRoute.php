<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Helper;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Proclaim Component Route Helper
 *
 * @static
 * @package  Procalaim.Site
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
	protected static string $lookup;

	/**
	 * Get Article Rout
	 *
	 * @param   string       $id        ID or ID:Alias for the route to build
	 * @param   string|null  $language  The state of language
	 *
	 * @return string
	 *
	 * @since    7.2
	 */
	public static function getArticleRoute(string $id, string $language = null): string
	{
		// Create the link
		$link = 'index.php?option=com_proclaim&view=cwmsermon&id=' . $id;

		if (!empty($language) && $language !== '*' && Multilanguage::isEnabled())
		{
			$link .= '&lang=' . $language;
		}

		return $link;
	}

	/**
	 * Find Item
	 *
	 * @param   array  $needles  ?
	 *
	 * @return mixed
	 *
	 * @throws \Exception
	 * @since    7.2
	 */
	protected static function _findItem(array $needles = array())
	{
		$app   = Factory::getApplication();
		$menus = $app->getMenu('site');

		// Prepare the reverse lookup array.
		if (self::$lookup === null)
		{
			self::$lookup = array();

			$component = ComponentHelper::getComponent('com_proclaim');
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
						$item->id = self::$lookup[$view][$item->query['id']];
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

			if ($active && $active->component === 'com_proclaim')
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
	public static function getTeacherRoute(int $id): string
	{
		// Create the link
		return 'index.php?option=com_proclaim&view=cwmteacher&id=' . $id;
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
	public static function getSeriesRoute($id): string
	{
		// Create the link
		return 'index.php?option=com_proclaim&view=cwmseriesdisplay&id=' . $id;
	}

	/**
	 * Add Scheme to url
	 *
	 * @param   string  $url     URL of website
	 * @param   string  $scheme  Scheme that need to lead with.
	 *
	 * @return string  The fixed URL
	 *
	 * @since     7.2
	 * @deprecate 8.0.7
	 */
	public static function addScheme(string $url, string $scheme = 'https://')
	{
		if (parse_url($url, PHP_URL_SCHEME) === null)
		{
			return $scheme . $url;
		}

		return $url;
	}
}
