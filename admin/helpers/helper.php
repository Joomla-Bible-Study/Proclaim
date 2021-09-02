<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Database\DatabaseFactory;

/**
 * Core Bible Study Helper
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 * */
class JBSMHelper
{
	/**
	 * Extension Name
	 *
	 * @var string
	 *
	 * @since 8.0.0
	 */
	public static $extension = 'com_biblestudy';

	/**
	 * Get tooltip.
	 *
	 * @param   object                    $row       JTable
	 * @param   Joomla\Registry\Registry  $params    Item Params
	 * @param   TableTemplate             $template  ID
	 *
	 * @return string
	 *
	 * @throws Exception
	 * @since  9.0.0
	 */
	public static function getTooltip($row, $params, $template)
	{
		$JBSMElements = new JBSMListing;

		$linktext = '<span class="hasTip" title="<strong>' . $params->get('tip_title') . '  :: ';

		$tip1 = $JBSMElements->getElement($params->get('tip_item1'), $row, $params, $template, $type = 0);
		$tip2 = $JBSMElements->getElement($params->get('tip_item2'), $row, $params, $template, $type = 0);
		$tip3 = $JBSMElements->getElement($params->get('tip_item3'), $row, $params, $template, $type = 0);
		$tip4 = $JBSMElements->getElement($params->get('tip_item4'), $row, $params, $template, $type = 0);
		$tip5 = $JBSMElements->getElement($params->get('tip_item5'), $row, $params, $template, $type = 0);

		$linktext .= '<strong>' . $params->get('tip_item1_title') . '</strong>: ' . $tip1 . '<br />';
		$linktext .= '<strong>' . $params->get('tip_item2_title') . '</strong>: ' . $tip2 . '<br />';
		$linktext .= '<strong>' . $params->get('tip_item3_title') . '</strong>: ' . $tip3 . '<br />';
		$linktext .= '<strong>' . $params->get('tip_item4_title') . '</strong>: ' . $tip4 . '<br />';
		$linktext .= '<strong>' . $params->get('tip_item5_title') . '</strong>: ' . $tip5;
		$linktext .= '">';

		return $linktext;
	}

	/**
	 * Get ShowHide.
	 *
	 * @return string
	 *
	 * @deprecated 7.1.8
	 *
	 * @since      8.2.0
	 */
	public static function getShowhide()
	{
		$showhide = '
        function HideContent(d) {
        document.getElementById(d).style.display = "none";
        }
        function ShowContent(d) {
        document.getElementById(d).style.display = "block";
        }
        function ReverseDisplay(d) {
        if(document.getElementById(d).style.display == "none") { document.getElementById(d).style.display = "block"; }
        else { document.getElementById(d).style.display = "none"; }
        }
        ';

		return $showhide;
	}

	/**
	 * Method to get file size
	 *
	 * @param   string  $url  URL
	 *
	 * @return  integer|boolean  Return size or false read.
	 *
	 * @since 9.0.0
	 */
	public static function getRemoteFileSize($url)
	{
		if ($url === '')
		{
			return 0;
		}

		if (substr_count($url, 'youtu.be') > 0)
		{
			return 0;
		}

		if (substr_count($url, 'youtube.com') > 0)
		{
			return 0;
		}

		// Removes a bad url problem in some DB's
		if (substr_count($url, '/http'))
		{
			$url = ltrim($url, '/');
		}

		if (!substr_count($url, 'http://') && !substr_count($url, 'https://'))
		{
			if (substr_count($url, '//'))
			{
				$url = 'http:' . $url;
			}
			elseif (!substr_count($url, '//'))
			{
				$url = 'http://' . $url;
			}
		}

		try
		{
			$headers = @get_headers($url, true);
		}
		catch (Exception $e)
		{
			return 0;
		}

		if (is_array($headers))
		{
			$head = array_change_key_case($headers);
		}
		else
		{
			return 0;
		}

		if (is_array($head['content-length']))
		{
			if (count($head['content-length']) >= 1)
			{
				$dif  = count($head['content-length']) - 1;
				$size = $head['content-length'][$dif];
			}
			else
			{
				$size = $head['content-length'][0];
			}
		}
		else
		{
			$size = $head['content-length'];
		}

		return $size;
	}

	/**
	 * Set File Size for MediaFile
	 *
	 * @param   int  $id    ID of MediaFile
	 * @param   int  $size  Size of file in bits
	 *
	 * @return void
	 *
	 * @since 9.0.14
	 */
	public static function SetFileSize($id, $size)
	{
		$driver = new DatabaseFactory;
		$db     = $driver->getDriver();
		$query  = $db->getQuery(true);
		$query->select('id, params')
			->from('#__bsms_mediafiles')
			->where('id = ' . (int) $id);

		$db->setQuery($query);
		$media = $db->loadObject();

		$reg = new Joomla\Registry\Registry;
		$reg->loadString($media->params);
		$reg->set('size', $size);

		$update         = new stdClass;
		$update->id     = $id;
		$update->params = $reg->toString();

		$db->updateObject('#__bsms_mediafiles', $update, 'id');
	}

	/**
	 * Media Build URL Fix up for '/' and protocol.
	 *
	 * @param   string    $spath        Server Path
	 * @param   string    $path         File
	 * @param   Registry  $params       Parameters.
	 * @param   bool      $setProtocol  True add protocol els no
	 * @param   bool      $local        Local server
	 * @param   bool      $podcast      True if from a precast
	 *
	 * @return string Completed path.
	 *
	 * @since 9.0.3
	 */
	public static function MediaBuildUrl($spath, $path, $params, $setProtocol = false, $local = false, $podcast = false)
	{
		$spath    = rtrim($spath, '/');
		$path     = ltrim($path, '/');
		$host     = $_SERVER['HTTP_HOST'];
		$protocol = JUri::root();

		if (empty($path))
		{
			return false;
		}

		// To see if the server is local
		if (strpos($spath, $host) !== false)
		{
			$local = true;
		}

		if (substr_count($path, 'http://') && $podcast)
		{
			return str_replace('http://', "", $path);
		}

		if (substr_count($path, 'https://') && $podcast)
		{
			return str_replace('https://', "", $path);
		}

		if (!empty($spath) && $podcast)
		{
			return str_replace('//', "", $spath) . '/' . $path;
		}

		if (!substr_count($path, '://') && !substr_count($path, '//') && $setProtocol)
		{
			if (empty($spath))
			{
				return $protocol . $path;
			}

			$protocol = $params->get('protocol', 'http://');

			if ((substr_count($spath, '://') || substr_count($spath, '//')) && !empty($spath))
			{
				if (substr_count($spath, '//'))
				{
					$spath = substr($spath, 2);
				}

				return $protocol . $spath . '/' . $path;
			}

			// Set Protocol based on server status
			$path = $protocol . $spath . '/' . $path;
		}
		elseif ((!substr_count($spath, '://') || !substr_count($spath, '//')) && !empty($spath))
		{
			$path = $spath . '/' . $path;
		}

		return $path;
	}

	/**
	 * Clear Cache of JBSM
	 *
	 * @param   string  $state  Where to clean the cache from. Site or Admin.
	 *
	 * @return void
	 * @since 9.0.4
	 */
	public static function clearcache($state = 'site')
	{
		$conf    = JFactory::getApplication()->getConfig();
		$options = array();

		if ($state === 'admin')
		{
			$options = array(
				'defaultgroup' => 'com_biblestudy',
				'storage'      => $conf->get('cache_handler', ''),
				'caching'      => true,
				'cachebase'    => $conf->get('cache_path', JPATH_ADMINISTRATOR . '/cache')
			);
		}
		elseif ($state === 'site')
		{
			$options = array(
				'defaultgroup' => 'com_biblestudy',
				'storage'      => $conf->get('cache_handler', ''),
				'caching'      => true,
				'cachebase'    => $conf->get('cache_path', JPATH_SITE . '/cache')
			);
		}

		$cache = JCache::getInstance('', $options);
		$cache->clean();
	}

	/**
	 * Remove Http
	 *
	 * @param   string  $url  Url
	 *
	 * @return mixed
	 *
	 * @since 9.0.18
	 */
	public static function remove_http(string $url)
	{
		$disallowed = array('http://', 'https://');

		foreach ($disallowed as $d)
		{
			if (strpos($url, $d) === 0)
			{
				return str_replace($d, '', $url);
			}
		}

		return $url;
	}

	/**
	 * Get Simple View Sate
	 *
	 * @param   object  $params  AdminTable + parametors
	 *
	 * @return  object
	 *
	 * @throws Exception
	 * @since 9.1.6
	 */
	public static function getSimpleView($params = null)
	{
		$simple = new stdClass;

		if (is_null($params))
		{
			$params = JBSMParams::getAdmin();
		}

		$simple->mode    = (integer) $params->params->get('simple_mode');
		$simple->display = (integer) $params->params->get('simple_mode_display');

		return $simple;
	}
}
