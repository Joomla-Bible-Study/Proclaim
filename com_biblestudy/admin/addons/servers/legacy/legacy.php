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
 * Class JBSMAddonLegacy
 *
 * @package  BibleStudy.Admin
 * @since    9.0.0
 */
class JBSMAddonLegacy extends JBSMAddon
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
		// Convert back slashes to forward slashes
		$file  = str_replace('\\', '/', $data->get('path', null, 'STRING'));
		$slash = strrpos($file, '/');

		$path = substr($file, 0, $slash + 1);

		// Remove domain from path
		preg_match('/\/+.+/', $path, $matches);

		// Make filename safe and move it to correct folder
		$destFile = JFile::makeSafe($_FILES["file"]["name"]);
		$destFile = str_replace(' ', '_', $destFile);

		if (!JFile::upload($_FILES['file']['tmp_name'], JPATH_ROOT . $matches[0] . $destFile))
		{
			die('false');
		}

		return array(
			'data' => array(
				'filename' => $matches[0] . $destFile,
				'size'     => $_FILES['file']['size']
			)
		);
	}
}
