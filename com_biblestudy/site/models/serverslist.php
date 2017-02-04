<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;
jimport('joomla.filesystem.folder');

// Base this model on the backend version.
JLoader::register('BiblestudyModelServers', JPATH_ADMINISTRATOR . '/components/com_biblestudy/models/servers.php');
/**
 * Servers model class
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyModelServerslist extends BiblestudyModelServers
{
	// Holder.
}
