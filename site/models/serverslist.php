<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;
jimport('joomla.filesystem.folder');

// Base this model on the backend version.
JLoader::register('BiblestudyModelServers', JPATH_ADMINISTRATOR . '/components/com_biblestudy/models/ServersController.php');
/**
 * Servers model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class BiblestudyModelServerslist extends BiblestudyModelServers
{
	// Holder.
}
