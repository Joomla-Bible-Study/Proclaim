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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;

/**
 * Class for updating the alias in certain tables
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 */
class CWMAlias
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
	 * Update Alias
	 *
	 * @return string
	 * @since 7.1.0
	 */
	public static function updateAlias()
	{
		$done    = 0;
		$db      = Factory::getContainer()->get('DatabaseDriver');
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
					$alias = OutputFilter::stringURLSafe($r['title']);
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

		$db     = Factory::getContainer()->get('DatabaseDriver');
		$query  = $db->getQuery(true);
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
