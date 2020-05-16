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

/**
 * BibleStudy images class
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class JBSMImages
{
	/**
	 * Main Study Image
	 *
	 * @param   Registry  $params  Sermon Params
	 *
	 * @return object
	 *
	 * @since 7.0
	 */
	public static function mainStudyImage($params = null)
	{
		$path  = null;
		$image = null;

		if ($params === null)
		{
			$database = JFactory::getDbo();
			$query    = $database->getQuery(true);
			$query->select('*')->from('#__bsms_admin')->where('id = ' . 1);
			$database->setQuery($query);
			$params = $database->loadObject();

			// Convert parameter fields to objects.
			$registry = new Registry;
			$registry->loadString($params->params);
			$params = $registry;
		}


		if (!$params->get('default_main_image'))
		{
			$path = 'media/com_biblestudy/images/openbible.png';
		}
		else
		{
			$path = $params->get('default_main_image');
		}

		$mainimage = self::getImagePath($path);

		return $mainimage;
	}

	/**
	 * Get Image Path
	 *
	 * @param   string  $path  File path to image
	 *
	 * @return object
	 *
	 * @since 7.0
	 */
	public static function getImagePath($path)
	{
		$tmp = new stdClass;
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		if (JFile::exists(JPATH_ROOT . DIRECTORY_SEPARATOR . $path))
		{
			$tmp->path = $path;
			$tmp->size = filesize($tmp->path);
			$ext       = strtolower(JFile::getExt($path));

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
		else
		{
			$tmp->path   = null;
			$tmp->size   = null;
			$tmp->width  = 0;
			$tmp->height = 0;
			$tmp->type   = '';
			$tmp->mime   = '';
		}

		return $tmp;
	}

	/**
	 * Get Study Thumbnail
	 *
	 * @param   string  $image  file path to image
	 *
	 * @return object
	 *
	 * @since 7.0
	 */
	public static function getStudyThumbnail($image = 'openbible.png')
	{
		$folder = self::getStudiesImageFolder();
		$path   = $folder . '/' . $image;

		if (substr_count($image, '/'))
		{
			$path = $image;
		}

		$imagepath = self::getImagePath($path);

		return $imagepath;
	}

	/**
	 * Get StudiesImage Folder
	 *
	 * @return string
	 *
	 * @since 7.0
	 */
	private static function getStudiesImageFolder()
	{
		$studiesimagefolder = 'images';

		return $studiesimagefolder;
	}

	/**
	 * Get Series Thumbnail
	 *
	 * @param   string  $image  Image file
	 *
	 * @return object
	 *
	 * @since 7.0
	 */
	public static function getSeriesThumbnail($image = 'openbible.png')
	{
		$folder = self::getSeriesImageFolder();
		$path   = $folder . '/' . $image;

		if (substr_count($image, '/'))
		{
			$path = $image;
		}

		$imagepath = self::getImagePath($path);

		return $imagepath;
	}

	/**
	 * Get SeriesImage Folder
	 *
	 * @return string
	 *
	 * @since 7.0
	 */
	private static function getSeriesImageFolder()
	{
		$seriesimagefolder = 'images';

		return $seriesimagefolder;
	}

	/**
	 * Get Teacher Thumbnail
	 *
	 * @param   string  $image1  ?
	 * @param   string  $image2  ?
	 *
	 * @return object
	 *
	 * @since 7.0
	 */
	public static function getTeacherThumbnail($image1 = null, $image2 = null)
	{
		$folder = self::getTeacherImageFolder();

		if (!$image1 || $image1 == '0' || strncmp($image1, '- ', 2) == 0)
		{
			$path = $image2;

			if (!substr_count($path, '/'))
			{
				$path = $folder . '/' . $image2;
			}
		}
		else
		{
			$path = $folder . '/' . $image1;

			if (substr_count($image1, '/') > 0)
			{
				$path = $image1;
			}
		}

		$imagepath = self::getImagePath($path);

		return $imagepath;
	}

	/**
	 * Get TeacherImage Folder
	 *
	 * @return string
	 *
	 * @since 7.0
	 */
	private static function getTeacherImageFolder()
	{
		$teacherimagefolder = 'images';

		return $teacherimagefolder;
	}

	/**
	 * Get Teacher Image
	 *
	 * @param   string  $image1  ?
	 * @param   string  $image2  ?
	 *
	 * @return object
	 *
	 * @since 7.0
	 */
	public static function getTeacherImage($image1 = null, $image2 = null)
	{
		$folder = self::getTeacherImageFolder();
		$path   = null;

		if (!$image1 || $image1 == '0' || strncmp($image1, '- ', 2) == 0)
		{
			$path = $image2;

			if (!substr_count($path, '/'))
			{
				$path = $folder . '/' . $image2;
			}
		}
		elseif ($image1)
		{
			$path = $folder . '/' . $image1;

			if (substr_count($image1, '/') > 0)
			{
				$path = $image1;
			}
		}

		$imagepath = self::getImagePath($path);

		return $imagepath;
	}

	/**
	 * Get Media Image
	 *
	 * @param   string  $media1  ?
	 * @param   string  $media2  ?
	 *
	 * @return object
	 *
	 * @since 7.0
	 */
	public static function getMediaImage($media1 = null, $media2 = null)
	{
		$folder = self::getMediaImageFolder();
		$path   = null;

		if (!$media1 || $media1 == '0' || strncmp($media1, '- ', 2) == 0)
		{
			$path = $media2;

			if (!substr_count($path, '/'))
			{
				$path = $folder . '/' . $media2;
			}
		}
		else
		{
			$path = $folder . '/' . $media1;

			if (substr_count($media1, '/') > 0)
			{
				$path = $media1;
			}
		}

		$imagepath = self::getImagePath($path);

		return $imagepath;
	}

	/**
	 * Get MediaImage Folder
	 *
	 * @return string
	 *
	 * @since 7.0
	 */
	private static function getMediaImageFolder()
	{
		$mediaimagefolder = 'media/com_biblestudy/images';

		return $mediaimagefolder;
	}

	/**
	 * Get Show Hide
	 *
	 * @return object
	 *
	 * @since 7.0
	 */
	public static function getShowHide()
	{
		$admin = JBSMParams::getAdmin();

		if (!$admin->params->get('default_showHide_image'))
		{
			$path = 'media/com_biblestudy/images/showhide.gif';
		}
		else
		{
			$path = $admin->params->get('default_showHide_image');
		}

		$imagepath = self::getImagePath($path);

		return $imagepath;
	}
}
