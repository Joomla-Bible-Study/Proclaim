<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2014 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

jimport('joomla.form.formfield');

/**
 * This is a dummy form element to load the components language file
 *
 * @package  BibleStudy.Admin
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

	public function getLabel() {
        // return an empty string; nothing to display
		return '';
	}

	/**
	 * Method to load the laguage file; nothing to display.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		// get language file; english language as fallback
		$language = JFactory::getLanguage();
		$language->load('com_biblestudy', JPATH_ADMINISTRATOR . '/components/com_biblestudy', 'en-GB', true);
		$language->load('com_biblestudy', JPATH_ADMINISTRATOR . '/components/com_biblestudy', null, true);

        // return an empty string; nothing to display
		return '';
	}
}
