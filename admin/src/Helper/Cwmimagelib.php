<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Creates an instance of the necessary library class, as specified in the administrator
 * params.
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMImageLib
{
	/**
	 * Extension Name
	 *
	 * @var string
	 *
	 * @since 1.5
	 */
	public static $extension = 'com_proclaim';

	/**
	 * Get Series Podcast File
	 *
	 * @param   string  $img  Org Image File
	 *
	 * @return string
	 *
	 * @throws \Exception
	 * @since 9.0.18
	 */
	public static function getSeriesPodcast(string $img)
	{
		// Prep files
		$img_base    = pathinfo($img);
		$array       = explode('.', $img_base['basename']);
		$NewfileName = $img_base["dirname"] . '/' . $array[0] . '-200x112.' . $array[1];
		$new_sub     = JPATH_ROOT . '/' . $NewfileName;
		$img_sub     = JPATH_ROOT . '/' . $img;

		if (file_exists($img_sub) && !file_exists($new_sub))
		{
			if (function_exists('gd_info') && extension_loaded('gd'))
			{
				GDLib::resize_image($new_sub, $img_sub);

				return $NewfileName;
			}

			return $img;
		}

		return $NewfileName;
	}
}

/**
 * Abstraction layer for the ImageMagick PHP library
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class ImageMagickLib extends CWMImageLib
{
	/**
	 * Extension Name
	 *
	 * @var string
	 *
	 * @since 1.5
	 */
	public static $extension = 'com_proclaim';

	/**
	 * Resize Image
	 *
	 * @param   string  $targetFile    Target File Path
	 * @param   string  $originalFile  File
	 * @param   int     $newWidth      Image New Width
	 * @param   float   $canv_width    Image Canvas Width
	 * @param   float   $canv_height   Image Canvas Height
	 *
	 * @return void
	 *
	 * @since 9.0.18
	 */
	public static function resize_image(
		$targetFile,
		$originalFile,
		$newWidth = 300,
		$canv_width = 300,
		$canv_height = 169
	)
	{
		try
		{
			// Need to make
			return;
		}
		catch (\Exception $e)
		{
			echo $e->getMessage();
		}

		return null;
	}
}

/**
 * Abstraction layer for the GD PHP library
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class GDLib extends CWMImageLib
{
	/**
	 * Extension Name
	 *
	 * @var string
	 *
	 * @since 1.5
	 */
	public static $extension = 'com_proclaim';

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
	 * @param   float   $canv_width    Image Canvas Width
	 * @param   float   $canv_height   Image Canvas Height
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 9.0.18
	 */
	public static function resize_image(
		string $targetFile,
		string $originalFile,
		int $newWidth = 300,
		float $canv_width = 300,
		float $canv_height = 169
	): void
	{
		$info = getimagesize($originalFile);
		$mime = $info['mime'];

		switch ($mime)
		{
			case 'image/jpeg':
				$image_create_func = 'imagecreatefromjpeg';
				$image_save_func   = 'imagejpeg';
				$new_image_ext     = 'jpg';
				break;

			case 'image/png':
				$image_create_func = 'imagecreatefrompng';
				$image_save_func   = 'imagepng';
				$new_image_ext     = 'png';
				break;

			case 'image/gif':
				$image_create_func = 'imagecreatefromgif';
				$image_save_func   = 'imagegif';
				$new_image_ext     = 'gif';
				break;

			default:
				throw new \Exception('Unknown image type.');
		}

		$img = $image_create_func($originalFile);
		[$width, $height] = getimagesize($originalFile);

		$newHeight = ($height / $width) * $newWidth;
		$tmp       = imagecreatetruecolor($canv_width, $canv_height);
		imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

		if (file_exists($targetFile))
		{
			unlink($targetFile);
		}
		else
		{
			$image_save_func($tmp, $targetFile);
		}
	}
}
