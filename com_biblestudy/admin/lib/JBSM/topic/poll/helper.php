<?php
/**
 * Kunena Component
 * @package Kunena.Framework
 * @subpackage Forum.Topic.Poll
 *
 * @copyright (C) 2008 - 2015 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

/**
 * Class JBSMTopicPollHelper
 */
abstract class JBSMTopicPollHelper
{
	protected static $_instances = array();

	/**
	 * Returns JBSMTopic object.
	 *
	 * @param int  $identifier	The poll to load - Can be only an integer.
	 * @param bool $reload
	 *
	 * @return JBSMTopicPoll
	 */
	static public function get($identifier = null, $reload = false)
	{
		if ($identifier instanceof JBSMTopicPoll)
		{
			return $identifier;
		}

		$id = intval ( $identifier );

		if ($id < 1)
		{
			return new JBSMTopicPoll ();
		}

		if ($reload || empty ( self::$_instances [$id] ))
		{
			self::$_instances [$id] = new JBSMTopicPoll ( $id );
		}

		return self::$_instances [$id];
	}

	static public function recount()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->update('#__kunena_topics AS a')
			->innerJoin('#__kunena_polls AS b ON a.id=b.threadid')
			->set('a.poll_id=b.id');

		$db->setQuery($query);
		$db->execute();
	}
}
