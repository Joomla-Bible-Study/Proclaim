<?php

/**
 * Live Update Package
 *
 * @package    LiveUpdate
 * @copyright  Copyright ©2011 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license    GNU LGPLv3 or later <http://www.gnu.org/copyleft/lesser.html>
 */
defined('_JEXEC') or die();

/**
 * Configuration class for your extension's updates. Override to your liking.
 *
 * @package  LiveUpdate
 * @since    8.0.0
 */
class LiveUpdateConfig extends LiveUpdateAbstractConfig
{
	/**
	 * Name of Component Folder
	 *
	 * @var string
	 */
	var $_extensionName = 'com_biblestudy';

	/**
	 * Title of Extension
	 *
	 * @var string
	 */
	var $_extensionTitle = 'Joomla Bible Study Component';

	/**
	 * Update URL
	 *
	 * @var string
	 */
	var $_updateURL = 'http://www.joomlabiblestudy.org/index.php?option=com_ars&view=update&format=ini&id=2';

	/**
	 * Requires Authorization
	 *
	 * @var bool
	 */
	var $_requiresAuthorization = false;

	/**
	 * How to compare Versions
	 *
	 * @var string
	 */
	var $_versionStrategy = 'vcompare';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->_cacerts = dirname(__FILE__) . '/../assets/cacert.pem';

		parent::__construct();
	}

}
