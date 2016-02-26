<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

jimport('joomla.html.html');
jimport('joomla.access.access');
jimport('joomla.form.formfield');

/**
 * Form Field class for the Topics
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class JFormFieldTopics extends JFormField
{

	/**
	 * Set type to topics
	 *
	 * @var string
	 */
	public $type = 'Topics';

	/**
	 * Get input form form
	 *
	 * @return string
	 */
	protected function getInput()
	{
		return '<input type="hidden" id="topics" name="jform[topics]"/>';
	}

}
