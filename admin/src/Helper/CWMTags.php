<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

// No Direct Access
use Joomla\CMS\Factory;

defined('_JEXEC') or die;

/**
 * Tags Helper
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 **/
class CWMTags
{
	/**
	 * Extension Name
	 *
	 * @var string
	 *
	 * @since 1.5
	 */
	public static $extension = 'com_proclaim';

	/**
	 * Check to see if Duplicate
	 *
	 * @param   int  $study_id  ?
	 * @param   int  $topic_id  ?
	 *
	 * @return boolean
	 *
	 * @since 7.0
	 */
	public static function isDuplicate($study_id, $topic_id)
	{
		Factory::getApplication()->enqueueMessage('Need to update this function', 'error');

		return true;
	}
}
