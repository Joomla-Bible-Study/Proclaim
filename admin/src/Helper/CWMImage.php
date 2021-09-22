<?php
/**
 * Image Helper
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

// No Direct Access
use Joomla\CMS\Filesystem\File;

defined('_JEXEC') or die;

/**
 * Class for Joomla! Bible Study Image
 *
 * @package  Proclaim.Admin
 * @since    7.1.2
 */
class CWMImage
{
	/**
	 * Extension Name
	 *
	 * @var string
	 *
	 * @since 1.5
	 */
	public static string $extension = 'com_proclaim';

	/**
	 * Get Image
	 *
	 * @param   string  $path  Path to file
	 *
	 * @return object
	 *
	 * @since 7.0
	 */
	public static function getImage($path)
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$tmp       = new \stdClass;
		$tmp->path = $path;

		if (!empty($path))
		{
			$tmp->size = filesize($tmp->path);
			$ext       = strtolower(File::getExt($path));

			switch ($ext)
			{
				// Image
				case 'jpg':
				case 'png':
				case 'gif':
				case 'xcf':
				case 'odg':
				case 'bmp':
				case 'jpeg':
					$info        = getimagesize($tmp->path);
					$tmp->width  = $info[0];
					$tmp->height = $info[1];
					$tmp->type   = $info[2];
					$tmp->mime   = $info['mime'];

					if (!$tmp->width)
					{
						$tmp->width = 0;
					}

					if (!$tmp->height)
					{
						$tmp->height = 0;
					}
			}
		}

		return $tmp;
	}
}
