<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2017 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Class JBSMAddonYouTube
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class JBSMAddonYouTube extends JBSMAddon
{
	protected $config;

	/**
	 * Upload
	 *
	 * @param   JInput|array  $data  Data to upload
	 *
	 * @return array
	 *
	 * @since 9.0.0
	 */
	public function upload($data)
	{
		// Holde for nothing
		return $data;
	}
}
