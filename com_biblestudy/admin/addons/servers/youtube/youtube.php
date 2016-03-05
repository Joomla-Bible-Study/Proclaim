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
 * Class JBSMAddonYouTube
 *
 * @package  BibleStudy.Admin
 * @since    9.0.0
 */
class JBSMAddonYouTube extends JBSMAddon
{
	protected $config;

	/**
	 * Construct
	 *
	 * @param   array  $config  Array of Options
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Upload
	 *
	 * @param   JInput  $data  Data to upload
	 *
	 * @return array
	 */
	public function upload($data)
	{
		// Holde for nothing
		return $data;
	}
}
