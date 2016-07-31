<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Controller class for Teacher
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyControllerTeacher extends JControllerLegacy
{
	/**
	 * display the edit form
	 *
	 * @return void
	 *
	 * @since 7.0
	 */
	public function view()
	{
		$input = new JInput;
		$input->set('view', 'teacher');
		$input->set('layout', 'default');

		parent::display();
	}
}
