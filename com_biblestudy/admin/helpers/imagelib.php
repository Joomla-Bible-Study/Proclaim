<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
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
	 */
	public static $extension = 'com_biblestudy';

}

/**
 * Abstraction layer for the ImageMagick PHP library
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class ImageMagickLib extends ImageLib
{
	/**
	 * Extension Name
	 *
	 * @var string
	 */
	public static $extension = 'com_biblestudy';

	/**
	 * Resize Image
	 *
	 * @param   string  $image  Image Path
	 *
	 * @return void
	 *
	 * @todo look like this is not working yet. bcc
	 */
	public static function resize($image)
	{
		try
		{
			/* ** a file that does not exist ** */
			$image = '$image';

			/* * * a new imagick object ** */
			$im = new Imagick($image);
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
	}

}

/**
 * Abstraction layer for the GD PHP library
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class GDLib extends ImageLib
{
	/**
	 * Extension Name
	 *
	 * @var string
	 */
	public static $extension = 'com_biblestudy';

	/**
	 * Construct System.
	 */
	public function __construct()
	{
		// Check that the library exists
		if (!function_exists("gd_info"))
		{
			die("GD is not found");
		}
	}

}
