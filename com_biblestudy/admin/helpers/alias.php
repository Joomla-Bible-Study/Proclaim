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

/**
 * Class for updating the alias in certain tables
 *
 * @package  BibleStudy.Admin
 * @since    7.1.0
 */
class JBSMAlias
{
	/**
	 * Extension Name
	 *
	 * @var string
	 *
	 * @since 1.5
	 */
	public static $extension = 'com_biblestudy';

	/**
	 * Update Alias
	 *
	 * @since 7.1.0
	 * @return string
	 */
	public static function updateAlias()
	{
		$done    = 0;
		$db      = JFactory::getDbo();
		$objects = self::getObjects();
		$results = array();

		foreach ($objects as $object)
		{
			$results[] = self::getTableQuery($table = $object['name'], $title = $object['titlefield']);
		}

		foreach ($results as $result)
		{
			foreach ($result as $r)
			{
				if (!$r['title'])
				{
					// Do nothing
				}
				else
				{
					$alias = JFilterOutput::stringURLSafe($r['title']);
					$query = $db->getQuery(true);
					$query->update($db->qn($r['table']))
						->set('alias=' . $db->q($alias))
						->where('id=' . $db->q($r['id']));
					$db->setQuery($query);
					$db->execute();
					$done++;
				}
			}
		}

		return $done;
	}

	/**
	 * Get Object's for tables
	 *
	 * @return array
	 *
	 * @since 1.5
	 */
	private static function getObjects()
	{
		$objects = array(
			array('name' => '#__bsms_series', 'titlefield' => 'series_text'),
			array('name' => '#__bsms_studies', 'titlefield' => 'studytitle'),
			array('name' => '#__bsms_message_type', 'titlefield' => 'message_type'),
			array('name' => '#__bsms_teachers', 'titlefield' => 'teachername'),
		);

		return $objects;
	}

	/**
	 * Get Table fields for updating.
	 *
	 * @param   string  $table  Table
	 * @param   string  $title  Title
	 *
	 * @return boolean|array
	 *
	 * @since 1.5
	 */
	private static function getTableQuery($table, $title)
	{
		$data = array();

		if (!$table)
		{
			return false;
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, alias, ' . $title)
			->from($table);
		$db->setQuery($query);
		$results = $db->loadObjectList();

		foreach ($results as $result)
		{
			if (!$result->alias)
			{
				$temp   = array(
					'id'    => $result->id,
					'title' => $result->$title,
					'alias' => $result->alias,
					'table' => $table
				);
				$data[] = $temp;
			}
		}

		return $data;
	}
}
