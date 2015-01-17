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

/**
 * Controller class for Teacher
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyControllerTeacher extends JControllerLegacy
{

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *                          Recognized key values include 'name', 'default_task', 'model_path', and
	 *                          'view_path' (this list is not meant to be comprehensive).
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Register Extra tasks
	}

	/**
	 * display the edit form
	 *
	 * @return void
	 */
	public function view()
	{
		$input = new JInput;
		$input->set('view', 'teacher');
		$input->set('layout', 'default');

		parent::display();
	}

}
