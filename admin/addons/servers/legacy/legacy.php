<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Class JBSMAddonLegacy
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class JBSMAddonLegacy extends JBSMAddon
{
	/**
	 * Upload
	 *
	 * @param   JInput|array  $data  Data to upload
	 *
	 * @return array|bool
	 *
	 * @since 9.0.0
	 */
	public function upload($data)
	{
		$upload = new JBSMUploadScript;

		return $upload->upload($data);
	}
}
