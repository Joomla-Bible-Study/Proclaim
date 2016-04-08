<?php
/**
 * Controller for Seriesdisplay
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Controller for Seriesdisplay
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyControllerSeriesdisplay extends JControllerLegacy
{

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *                          Recognized key values include 'name', 'default_task', 'model_path', and
	 *                             'view_path' (this list is not meant to be comprehensive).
	 *
	 * @see JControllerLagacy
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

}
