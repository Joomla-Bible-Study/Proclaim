<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Categories\CategoryNode;
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
	 * @param   integer  $id        The route of the content item.
	 * @param   integer  $catid     The category ID.
	 * @param   integer  $language  The language code.
	 * @param   string   $layout    The layout value.
	 *
	 * @return  string  The sermon route.
	 *
	 * @since   1.5
	 */
	public static function getSermonRoute($id, $catid = 0, $language = 0, $layout = null)
	{
		// Create the link
		$link = 'index.php?option=com_proclaim&view=cwmsermon&id=' . $id;

		if ((int) $catid > 1)
		{
			$link .= '&catid=' . $catid;
		}

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
	 * @param   integer  $catid     The category ID.
	 * @param   integer  $language  The language code.
	 * @param   string   $layout    The layout value.
	 *
	 * @return  string  The article route.
	 *
	 * @since   1.5
	 */
	public static function getSeriesRoute($seriesid, int $language = 0, $layout = null)
	{
		if ($seriesid instanceof CategoryNode)
		{
			$id = $seriesid->id;
		}
		else
		{
			$id = (int) $seriesid;
		}

		if ($id < 1)
		{
			return '';
		}

		$link = 'index.php?option=com_proclaim&view=cwmseriesdisplay&id=' . $id;

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
