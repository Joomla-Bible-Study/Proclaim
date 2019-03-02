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

jimport('joomla.html.html');
jimport('joomla.access.access');
jimport('joomla.form.formfield');

/**
 * Form Field class for the Topics
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class JFormFieldTopics extends JFormField
{
	/**
	 * Set type to topics
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $type = 'Topics';

	/**
	 * Get input form form
	 *
	 * @return string
	 *
	 * @since 9.0.0
	 */
	protected function getInput()
	{
		return '<input type="hidden" id="topics" name="jform[topics]"/>';
	}
}
