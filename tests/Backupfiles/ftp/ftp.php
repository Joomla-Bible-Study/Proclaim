<?php
/**
 * Core Admin BibleStudy file
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Admin table class
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class JBSServerFtp extends JBSServer
{
	public $name = 'ftp';

	/**
	 * Construct
	 *
	 * @param   array  $options  ?
	 */
	protected function __construct($options)
	{

	}

	/**
	 * Upload
	 *
	 * @param   string  $target     ?
	 * @param   bool    $overwrite  ?
	 *
	 * @return void
	 */
	protected function upload($target, $overwrite = true)
	{
		// TODO: Implement upload() method.
	}
}
