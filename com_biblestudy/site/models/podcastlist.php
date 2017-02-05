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

// Base this model on the backend version.
JLoader::register('BiblestudyModelMessages', JPATH_ADMINISTRATOR . '/components/com_biblestudy/models/messages.php');

/**
 * Model class for MessageList
 *
 * @package  BibleStudy.Site
 * @since    8.0.0
 */
class BiblestudyModelPodcastlist extends JModelList
{

	/**
	 * Build an SQL query to load the list data
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   7.0
	 */
	protected function getListQuery()
	{
		$user            = JFactory::getUser();
		$groups          = implode(',', $user->getAuthorisedViewLevels());
		$db              = $this->getDbo();
		$query           = parent::getListQuery();

		return $query;
	}
}
