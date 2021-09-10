<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Helper;

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Image\Image;

defined('_JEXEC') or die;

/**
 * Thumbnail helper class
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class CWMThumbnail
{
	/**
	 * Creates a thumbnail for an uploaded image
	 *
	 * @param   string  $file  File name
	 * @param   string  $path  Path to file
	 * @param   int     $size  Size of image with default of 100
	 *
	 * @return void
	 *
	 * @since 9.0.0
	 */
	public static function create($file, $path, $size = 100)
	{
		$name     = basename($file);
		$original = JPATH_ROOT . '/' . $file;
		$thumb    = JPATH_ROOT . '/' . $path . '/thumb_' . $name;

		// Delete destination folder if it exists
		if (Folder::exists(JPATH_ROOT . '/' . $path))
		{
			Folder::delete(JPATH_ROOT . '/' . $path);
		}

		// Move uploaded image to destination
		Folder::create(JPATH_ROOT . '/' . $path);

		// Create thumbnail
		$image     = new Image($original);
		$thumbnail = $image->resize($size, $size, true);
		$thumbnail->toFile($thumb, IMAGETYPE_JPEG);
	}

	/**
	 * Resize image
	 *
	 * @param   string  $path      Path to file
	 * @param   int     $new_size  New image size
	 *
	 * @return void
	 *
	 * @since 9.0
	 */
	public static function resize($path, $new_size)
	{
		$filename = str_replace('original_', '', basename($path));

		// Delete existing thumbnail
		$old_thumbs = Folder::files(dirname($path), 'thumb_', true, true);

		foreach ($old_thumbs as $thumb)
		{
			File::delete($thumb);
		}

		// Create new thumbnail
		$image     = new Image($path);
		$thumbnail = $image->resize($new_size, $new_size);
		$thumbnail->toFile(dirname($path) . '/thumb_' . $filename, IMAGETYPE_PNG);
	}

	/**
	 * Resize image
	 *
	 * @param   string  $path  Path to file
	 * @param   string  $file  file to check
	 *
	 * @return boolean
	 *
	 * @since 9.0
	 */
	public static function check($path, $file = null)
	{
		if (!Folder::exists($path))
		{
			return false;
		}

		if ($file)
		{
			return File::exists(JPATH_ROOT . $path . $file);
		}

		return true;
	}
}
