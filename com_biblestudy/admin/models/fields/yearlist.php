<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2017 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Location List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class JFormFieldYearlist extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var  string
	 *
	 * @since 9.0.0
	 */
	protected $type = 'Yearlist';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array  An array of JHtml options.
	 *
	 * @since 9.0.0
	 */
	protected function getOptions()
	{
		$options = array_merge(parent::getOptions(), JBSMBibleStudyHelper::getStudyYears());

		return $options;
	}
}
