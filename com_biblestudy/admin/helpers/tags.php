<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 **/
// No Direct Access
defined('_JEXEC') or die;

/**
 * Tags Helper
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 **/
class JBSMTags
{
	/**
	 * @var string
	 */
	public static $extension = 'com_biblestudy';

	/**
	 * Check to see if Duplicate
	 *
	 * @param   int  $study_id  ?
	 * @param   int  $topic_id  ?
	 *
	 * @return boolean
	 */
	public static function isDuplicate($study_id, $topic_id)
	{
		return true;
	}
}
