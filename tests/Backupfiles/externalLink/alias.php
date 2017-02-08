<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Class JBSServerAmazonS3
 *
 * @package  BibleStudy.Admin
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
	 * Test funciotn
	 *
	 * @return string
	 */
	public function test()
	{
		return "hello from amazon";
	}
}
