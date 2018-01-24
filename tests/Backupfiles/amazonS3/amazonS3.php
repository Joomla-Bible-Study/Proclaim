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
 * Class JBSServerAmazonS3
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class JBSServerAmazonS3 extends JBSServer
{
	public $name = 'amazonS3';

	/**
	 * Construct
	 *
	 * @param   array  $options  Array of Options
	 */
	protected function __construct($options)
	{
		$options['key']    = (isset($options['key'])) ? $options['key'] : '';
		$options['secret'] = (isset($options['secret'])) ? $options['secret'] : '';

		// Include the S3 class
		JLoader::register('S3', dirname(__FILE__) . '/S3.class.php');

		$this->connection = new S3($options['key'], $options['secret']);
	}

	/**
	 * Upload
	 *
	 * @param   JInput  $target     ?
	 * @param   bool    $overwrite  ?
	 *
	 * @return void
	 */
	protected function upload($target, $overwrite = true)
	{
		// TODO: Implement upload() method.
	}

	/**
	 * Test Function
	 *
	 * @return string
	 */
	public function test()
	{
		return "hello from amazon";
	}
}
