<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Checks if the template is setup right.
 *
 * This check returns true Templates is not setup yet, meaning
 * that the message concerning it should be displayed.
 *
 * @return  integer
 *
 * @since   3.2
 */
function admin_postinstall_template_condition()
{
	return 1;
}

/**
 * Redirect the view to the Templates view
 *
 * @return  void
 *
 * @since   3.2
 */
function admin_postinstall_template_action()
{
	$url = 'index.php?option=com_biblestudy&view=templates';
	JFactory::getApplication()->redirect($url);
}
