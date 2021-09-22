<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;


// Always load JBSM API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

if (file_exists($api))
{
	require_once $api;
}

/**
 * This is a dummy form element to load the components language file
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class JFormFieldLoadLanguageFile extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	protected $type = 'LoadLanguageFile';

	/**
	 * The hidden state for the form field.
	 *
	 * @var    boolean
	 */
	protected $hidden = true;

	/**
	 * Get Lable
	 *
	 * @return null;
	 */
	public function getLabel()
	{
		// Return an empty string; nothing to display
		return '';
	}

	/**
	 * Method to load the laguage file; nothing to display.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		// Get language file; english language as fallback
		$language = Factory::getLanguage();
		$language->load('com_proclaim', BIBLESTUDY_PATH_ADMIN, 'en-GB', true);
		$language->load('com_proclaim', BIBLESTUDY_PATH_ADMIN, null, true);

		// Return an empty string; nothing to display
		return '';
	}
}
