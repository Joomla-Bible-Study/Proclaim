<?php

/**
 * Helper for Alias
 *
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link    http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

/**
 * Class for updating the alias in certain tables
 *
 * @package BibleStudy.Admin
 * @since   7.1.0
 */
class JBSMFixAlias
{
	/**
	 * @var string
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
		$done = 0;
		$db = JFactory::getDBO();
		$objects = JBSMFixAlias::getObjects();
		foreach ($objects as $object) {
			$results[] = JBSMFixAlias::getTableQuery($table = $object['name'], $title = $object['titlefield']);
		}

		foreach ($results as $result) {
			foreach ($result as $r) {
				if (!$r['title']) {
					//do nothing
				} else {
					$alias = JFilterOutput::stringURLSafe($r['title']);
					$query = 'UPDATE ' . $r['table'] . ' SET alias="' . $alias . '" WHERE id=' . $r['id'];
					$db->setQuery($query);
					$db->query();
					$done++;
				}
			}
		}

		return $done;
	}

	/**
	 * Get Table fields for updateing.
	 *
	 * @param string $table
	 * @param string $title
	 *
	 * @return boolean|array
	 */
	private static function getTableQuery($table, $title)
	{
		$data = array();
		if (!$table) {
			return false;
		}
		$db = JFactory::getDBO();
		$query = 'SELECT id, alias,' . $title . ' FROM ' . $table;
		$db->setQuery($query);
		$results = $db->loadObjectList();
		foreach ($results as $result) {
			if (!$result->alias) {
				$temp = array('id' => $result->id, 'title' => $result->$title, 'alias' => $result->alias, 'table' => $table);
				$data[] = $temp;
			}
		}
		return $data;
	}

	/**
	 * Get Opjects for tables
	 *
	 * @return array
	 */
	private static function getObjects()
	{
		$objects = array(array('name' => '#__bsms_series', 'titlefield' => 'series_text'),
			array('name' => '#__bsms_studies', 'titlefield' => 'studytitle'),
			array('name' => '#__bsms_message_type', 'titlefield' => 'message_type'),
			array('name' => '#__bsms_teachers', 'titlefield' => 'teachername'),
		);
		return $objects;
	}

}
