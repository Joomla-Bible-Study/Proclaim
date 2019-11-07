<?php
/*
 * This is an example of a config class. To run tests,
 * copy this file to a file called "configdef.php" (in tests/system/server)
 * and set the variables according to the values in your test system.
 *
 * You can create more than one configuration file.
 *
 * You can select the desired config class at runtime with the following command:
 * phpunit --bootstrap servers\configtest.php tests\control_panel_menu.php
 * In this way, you can create multiple configurations and run them separately
 * using a batch file or shell script.
 */

class SeleniumConfig
{

	// $folder is the path to the apache root folder
	var $folder = '/home/bcordis/Sites/j3'; // typical windows example with XAMPP
//	var $folder = '/usr/local/apache/htdocs'; // typical linux example

	// $host is normally 'http://localhost'
	var $host = 'http://localhost/j3';

	// $path is the rest of the URL to the Joomla! home page
	// Example: Your full URL to Joomla! is http://localhost/joomla_16/index.php
	// then $path would be '/joomla_16/'
	var $path = '/joomla-dev/';
	
	// $baseURI set in contructor to the full path
	var $baseURI;

	// Set to true if you want to capture screenshots on failure (only for Firefox)
	var $captureScreenshotOnFailure = true;
	var $screenShotPath = 'tests/system/screenshots';

	// set the database host, database username, database pasword, and database name
	var $db_host = 'localhost';
	var $db_user = 'root';
	var $db_pass = 'root';
	var $db_name = 'j3';
	var $db_type = 'MySQLi';
	var $db_prefix = 'xxx_';
	
	// optional setting to install sample data
	// If not set or true, sample data is installed. Set to false to not install sample data
	// Note: This must be true for the standard tests to work!
	var $sample_data = true;

	// optional setting to select sample data
	// Set to partial text of sample data label on installation screen
	// Note: This must be 'Learn Joomla' for the standard tests to work!
	var $sample_data_file = 'Learn Joomla';

	// set the site name (keep to less than 14 characters)
	var $site_name = 'Joomla Tests';

	// set the admin login, admin password, and admin email address
	var $username = 'admin';
	var $password = 'admin';
	var $admin_email = 'test@localhost';

	// this setting will use the default browser for your system
	var $browser = '*chrome'; // for firefox (weird name!)
// 	var $browser = '*googlechrome';
// 	var $browser = '*iexplore';

	// optional setting to turn on Cache: values are off, on-basic, on-full
	// change this value to set the caching in the doInstall.php test
	var $cache = 'off';
	
	// optional setting to set administive template to hathor: set to 'hathor' to make hathor the default
	// var $adminTemplate = 'hathor';  

	// optional setting to set error reporting level
	var $errorReporting = 'maximum';

	// optional setting to set the initial window dimensions (Webdriver only)
	var $windowSize = array(1280, 1024);
	
	public function __construct() {
		$this->baseURI = $this->folder . $this->path;
	}

}
