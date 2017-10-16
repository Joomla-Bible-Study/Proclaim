<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2017 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Creates an instance of the necessary library class, as specified in the admin
 * params.
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class JBSMImageLib
{
	// Public abstract static function resize($img);

	/**
	 * Extension Name
	 *
	 * @var string
	 *
	 * @since 1.5
	 */
	public static $extension = 'com_biblestudy';

	/**
	 * Get Series Podcast File
	 *
	 * @param   string  $img  Org Image File
	 * @param   string  $new  New Image File
	 *
	 * @return string
	 *
	 * @since 9.0.14
	 */
	public static function getSeriesPodcast($img, $new)
	{
		// Prep files
		$new_sub = JPATH_ROOT . '/' . $new;
		$img_sub = JPATH_ROOT . '/' . $img;
		$return  = $img;

		if (file_exists($img_sub))
		{
			if (extension_loaded('gd') && function_exists('gd_info'))
			{
				GDLib::resize_image($new_sub, $img_sub);

				$return = $new;
			}
			else
			{
				$return = $img;
			}
		}

		return $return;
	}
}

/**
 * Abstraction layer for the ImageMagick PHP library
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class ImageMagickLib extends JBSMImageLib
{
	/**
	 * Extension Name
	 *
	 * @var string
	 *
	 * @since 1.5
	 */
	public static $extension = 'com_biblestudy';

	/**
	 * Resize Image
	 *
	 * @param   string  $targetFile    Target File Path
	 * @param   string  $originalFile  File
	 * @param   int     $newWidth      Image New Width
	 * @param   int     $canv_width    Image Canvas Width
	 * @param   int     $canv_height   Image Canvas Height
	 *
	 * @return void
	 *
	 * @since 1.5
	 */
	public static function resize_image(
		$targetFile,
		$originalFile,
		$newWidth = 200,
		$canv_width = 200,
		$canv_height = 200)
	{
		try
		{
			// Need to make
			return;
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}

		return null;
	}
}

/**
 * Abstraction layer for the GD PHP library
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class GDLib extends JBSMImageLib
{
	/**
	 * Extension Name
	 *
	 * @var string
	 *
	 * @since 1.5
	 */
	public static $extension = 'com_biblestudy';

	/**
	 * Construct System.
	 *
	 * @since 1.5
	 */
	public function __construct()
	{
		// Check that the library exists
		if (!function_exists("gd_info"))
		{
			die("GD is not found");
		}
	}

	/**
	 * Resize Image
	 *
	 * @param   string  $targetFile    Target File Path
	 * @param   string  $originalFile  File
	 * @param   int     $newWidth      Image New Width
	 * @param   int     $canv_width    Image Canvas Width
	 * @param   int     $canv_height   Image Canvas Height
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 9.0.14
	 */
	public static function resize_image(
		$targetFile,
		$originalFile,
		$newWidth = 200,
		$canv_width = 200,
		$canv_height = 200)
	{
		$info = getimagesize($originalFile);
		$mime = $info['mime'];

		switch ($mime)
		{
			case 'image/jpeg':
				$image_create_func = 'imagecreatefromjpeg';
				$image_save_func = 'imagejpeg';
				$new_image_ext = 'jpg';
				break;

			case 'image/png':
				$image_create_func = 'imagecreatefrompng';
				$image_save_func = 'imagepng';
				$new_image_ext = 'png';
				break;

			case 'image/gif':
				$image_create_func = 'imagecreatefromgif';
				$image_save_func = 'imagegif';
				$new_image_ext = 'gif';
				break;

			default:
				throw new Exception('Unknown image type.');
		}

		$img = $image_create_func($originalFile);
		list($width, $height) = getimagesize($originalFile);

		$newHeight = ($height / $width) * $newWidth;
		$tmp = imagecreatetruecolor($canv_width, $canv_height);
		imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

		if (file_exists($targetFile))
		{
			unlink($targetFile);
		}

		$image_save_func($tmp, "$targetFile");
	}
}
