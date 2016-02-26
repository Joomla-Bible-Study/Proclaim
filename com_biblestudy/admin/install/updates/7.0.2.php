<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

/**
 * Inserts some css code to fix pagination problem and add a tag for the captcha of comments
 *
 * @package  BibleStudy.Admin
 * @since    7.0.2
 */
class Migration702
{
	/**
	 * Update CSS for 7.0.2
	 *
	 * @param   JDatabaseDriver  $db  Joomla Data bass driver
	 *
	 * @return boolean
	 */
	public function up($db)
	{

		return true;
	}
}
