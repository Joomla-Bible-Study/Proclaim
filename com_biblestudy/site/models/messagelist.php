<?php

/**
 * @package BibleStudy.Site
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link    http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Base this model on the backend version.
//require_once JPATH_ADMINISTRATOR . '/components/com_biblestudy/models/messages.php';
JLoader::register('BiblestudyModelMessagelist', JPATH_ADMINISTRATOR . '/components/com_biblestudy/models/messages.php');
/**
 * Model class for MessageList
 *
 * @package BibleStudy.Site
 * @since   8.0.0
 */
class BiblestudyModelMessagelist extends BiblestudyModelMessages
{

}