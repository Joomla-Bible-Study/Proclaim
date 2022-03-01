<?php
/**
 * @package     Proclaim.Site
 * @subpackage  com_proclaim
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Multilanguage;

/**
 * Proclaim Component Route Helper.
 *
 * @since  1.5
 */
abstract class CWMRouteHelper
{
	/**
	 * Get the sermon route.
	 *
	 * @param   integer      $id        The route of the content item.
	 * @param   string       $language  The language code.
	 * @param   string|null  $layout    The layout value.
	 *
	 * @return  string  The sermon route.
	 *
	 * @since   1.5
	 */
	public static function getMessageRoute(int $id, string $language = '*', string $layout = null)
	{
		// Create the link
		$link = 'index.php?option=com_proclaim&view=cwmsermon&id=' . $id;

		if ($language && $language !== '*' && Multilanguage::isEnabled())
		{
			$link .= '&lang=' . $language;
		}

		if ($layout)
		{
			$link .= '&layout=' . $layout;
		}

		return $link;
	}

	/**
	 * Get the Series route.
	 *
	 * @param   integer      $seriesid  The Series ID.
	 * @param   integer      $language  The language code.
	 * @param   string|null  $layout    The layout value.
	 *
	 * @return  string  The article route.
	 *
	 * @since   1.5
	 */
	public static function getSeriesRoute(int $seriesid, int $language = 0, string $layout = null)
	{
		if ($seriesid < 1)
		{
			return '';
		}

		$link = 'index.php?option=com_proclaim&view=cwmseriesdisplay&id=' . $seriesid;

		if ($language && $language !== '*' && Multilanguage::isEnabled())
		{
			$link .= '&lang=' . $language;
		}

		if ($layout)
		{
			$link .= '&layout=' . $layout;
		}

		return $link;
	}

	/**
	 * Get the Series route.
	 *
	 * @param   integer      $seriesid  The Series ID.
	 * @param   integer      $language  The language code.
	 * @param   string|null  $layout    The layout value.
	 *
	 * @return  string  The article route.
	 *
	 * @since   1.5
	 */
	public static function getLocationsRoute(int $seriesid, int $language = 0, string $layout = null): string
	{
		if ($seriesid < 1)
		{
			return '';
		}

		$link = 'index.php?option=com_proclaim&view=cwmlocations&id=' . $seriesid;

		if ($language && $language !== '*' && Multilanguage::isEnabled())
		{
			$link .= '&lang=' . $language;
		}

		if ($layout)
		{
			$link .= '&layout=' . $layout;
		}

		return $link;
	}
	/**
	 * Get the Teacher route.
	 *
	 * @param   integer      $seriesid  The Series ID.
	 * @param   integer      $language  The language code.
	 * @param   string|null  $layout    The layout value.
	 *
	 * @return  string  The article route.
	 *
	 * @since   1.5
	 */
	public static function getTeachersRoute(int $id, int $language = 0, string $layout = null): string
	{
		if ($id < 1)
		{
			return '';
		}

		$link = 'index.php?option=com_proclaim&view=cwmteacher&id=' . $id;

		if ($language && $language !== '*' && Multilanguage::isEnabled())
		{
			$link .= '&lang=' . $language;
		}

		if ($layout)
		{
			$link .= '&layout=' . $layout;
		}

		return $link;
	}
	/**
	 * Get the form route.
	 *
	 * @param   integer  $id  The form ID.
	 *
	 * @return  string  The article route.
	 *
	 * @since   1.5
	 */
	public static function getFormRoute($id)
	{
		return 'index.php?option=com_proclaim&task=cwmmessageform.edit&a_id=' . (int) $id;
	}
}