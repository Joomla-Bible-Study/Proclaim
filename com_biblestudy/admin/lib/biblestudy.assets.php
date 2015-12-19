<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;
jimport('joomla.access.rules');

/**
 * Asset Fix class
 *
 * @package  BibleStudy.Admin
 * @since    7.0.4
 */
class FixJBSAssets
{

	/**
	 * Fix Assets function.
	 *
	 * @return boolean
	 */
	public static function fixassets()
	{
		$db = JFactory::getDBO();

		/**
		 * Attempt to increase the maximum execution time for php scripts with check for safe_mode.
		 */
		if (!ini_get('safe_mode'))
		{
			set_time_limit(300);
		}

		// First get the new parent_id
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__assets')
			->where('name = ' . $db->q('com_biblestudy'));
		$db->setQuery($query);
		$parent_id = $db->loadResult();

		// Get the names of the JBS tables
		$objects = self::getassetObjects();

		// Run through each table
		foreach ($objects as $object)
		{
			// Put the table into the return array
			// Get the total number of rows and collect the table into a query
			$query = $db->getQuery(true);
			$query->select('j.id, j.asset_id, a.id as aid, a.parent_id')
				->from($db->qn($object['name']) . ' as j')
				->leftJoin('#__assets as a ON (a.id = j.asset_id)');
			$db->setQuery($query);
			$results = $db->loadObjectList();

			// Now go through each record to test it for asset id
			foreach ($results as $result)
			{
				// If there is no jasset_id it means that this has not been set and should be
				if (!$result->asset_id)
				{
					self::setasset($result, $object['assetname']);
				}

				// If there is a jasset_id but no match to the parent_id then a mismatch has occurred
				if ($parent_id != $result->parent_id && $result->asset_id)
				{
					self::deleteasset($result);
					self::setasset($result, $object['assetname']);
				}
			}
		}

		return true;
	}

	/**
	 * Check Assets
	 *
	 * @return array
	 */
	public static function checkAssets()
	{
		$return = array();
		$db     = JFactory::getDBO();
		$result = new stdClass;

		// First get the new parent_id
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__assets')
			->where('name = ' . $db->q('com_biblestudy'));
		$db->setQuery($query);
		$parent_id = $db->loadResult();

		// Get the names of the JBS tables
		$objects = self::getassetObjects();

		// Run through each table
		foreach ($objects as $object)
		{
			// Put the table into the return array
			// Get the total number of rows and collect the table into a query
			$query = $db->getQuery(true);
			$query->select('j.id as jid, j.asset_id as jasset_id, a.id as aid, a.parent_id')
				->from($db->qn($object['name']) . ' as j')
				->leftJoin('#__assets as a ON (a.id = j.asset_id)');
			$db->setQuery($query);
			$results     = $db->loadObjectList();
			$nullrows    = 0;
			$matchrows   = 0;
			$nomatchrows = 0;
			$numrows     = count($results);

			// Now go through each record to test it for asset id
			foreach ($results as $result)
			{
				// If there is no jasset_id it means that this has not been set and should be
				if (!$result->jasset_id)
				{
					$nullrows++;
				}
				// If there is a jasset_id but no match to the parent_id then a mismatch has occured
				if ($parent_id != $result->parent_id && $result->jasset_id)
				{
					$nomatchrows++;
				}
				// If $parent_id and $result->parent_id match then everything is okay
				if ($parent_id == $result->parent_id)
				{
					$matchrows++;
				}
			}
			$return[] = array(
				'realname'         => $object['realname'],
				'numrows'          => $numrows,
				'nullrows'         => $nullrows,
				'matchrows'        => $matchrows,
				'nomatchrows'      => $nomatchrows,
				'parent_id'        => $parent_id,
				'result_parent_id' => $result->parent_id,
				'id'               => $result->jid,
				'assetid'          => $result->jasset_id
			);
		}

		return $return;
	}

	/**
	 * Table list Array.
	 *
	 * @return array
	 */
	protected static function getassetObjects()
	{
		$objects = array(
			array(
				'name'       => '#__bsms_servers',
				'titlefield' => 'server_name',
				'assetname'  => 'server',
				'realname'   => 'JBS_CMN_SERVERS'
			),
			array(
				'name'       => '#__bsms_folders',
				'titlefield' => 'foldername',
				'assetname'  => 'folder',
				'realname'   => 'JBS_CMN_FOLDERS'
			),
			array(
				'name'       => '#__bsms_studies',
				'titlefield' => 'studytitle',
				'assetname'  => 'message',
				'realname'   => 'JBS_CMN_STUDIES'
			),
			array(
				'name'       => '#__bsms_comments',
				'titlefield' => 'comment_date',
				'assetname'  => 'comment',
				'realname'   => 'JBS_CMN_COMMENTS'
			),
			array(
				'name'       => '#__bsms_locations',
				'titlefield' => 'location_text',
				'assetname'  => 'location',
				'realname'   => 'JBS_CMN_LOCATIONS'
			),
			array(
				'name'       => '#__bsms_media',
				'titlefield' => 'media_text',
				'assetname'  => 'mediaimage',
				'realname'   => 'JBS_CMN_MEDIAIMAGES'
			),
			array(
				'name'       => '#__bsms_mediafiles',
				'titlefield' => 'filename',
				'assetname'  => 'mediafile',
				'realname'   => 'JBS_CMN_MEDIA_FILES'
			),
			array(
				'name'       => '#__bsms_message_type',
				'titlefield' => 'message_type',
				'assetname'  => 'messagetype',
				'realname'   => 'JBS_CMN_MESSAGE_TYPES'
			),
			array(
				'name'       => '#__bsms_mimetype',
				'titlefield' => 'mimetext',
				'assetname'  => 'mimetype',
				'realname'   => 'JBS_CMN_MIME_TYPES'
			),
			array(
				'name'       => '#__bsms_podcast',
				'titlefield' => 'title',
				'assetname'  => 'podcast',
				'realname'   => 'JBS_CMN_PODCASTS'
			),
			array(
				'name'       => '#__bsms_series',
				'titlefield' => 'series_text',
				'assetname'  => 'serie',
				'realname'   => 'JBS_CMN_SERIES'
			),
			array(
				'name'       => '#__bsms_share',
				'titlefield' => 'name',
				'assetname'  => 'share',
				'realname'   => 'JBS_CMN_SOCIAL_NETWORKING_LINKS'
			),
			array(
				'name'       => '#__bsms_teachers',
				'titlefield' => 'teachername',
				'assetname'  => 'teacher',
				'realname'   => 'JBS_CMN_TEACHERS'
			),
			array(
				'name'       => '#__bsms_templates',
				'titlefield' => 'title',
				'assetname'  => 'template',
				'realname'   => 'JBS_CMN_TEMPLATES'
			),
			array(
				'name'       => '#__bsms_topics',
				'titlefield' => 'topic_text',
				'assetname'  => 'topic',
				'realname'   => 'JBS_CMN_TOPICS'
			),
			array(
				'name'       => '#__bsms_styles',
				'titlefield' => 'filename',
				'assetname'  => 'style',
				'realname'   => 'JBS_CMN_STYLES'
			),
			array(
				'name'       => '#__bsms_templatecode',
				'titlefield' => 'filename',
				'assetname'  => 'templatecode',
				'realname'   => 'JBS_CMN_TEMPLATECODE'
			),
			array(
				'name'       => '#__bsms_admin',
				'titlefield' => 'id',
				'assetname'  => 'admin',
				'realname'   => 'JBS_CMN_ADMINISTRATION'
			)
		);

		return $objects;
	}

	/**
	 * Set Asset
	 *
	 * @param   object $data       Data
	 * @param   string $assetname  Asset Name
	 *
	 * @return boolean
	 */
	private static function setasset($data, $assetname)
	{
		$db = JFactory::getDBO();
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
		$table = JTable::getInstance($assetname, 'Table', array('dbo' => $db));

		if ($data->id)
		{
			try
			{
				$table->load($data->id);
			}
			catch (Exception $e)
			{
				echo 'Caught exception: ', $e->getMessage(), "\n";

				return false;
			}
			if (@!$table->store())
			{
				JFactory::getApplication()->enqueueMessage(JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true)), 'error');

				return false;
			}
		}

		return true;
	}

	/**
	 * Delete assets
	 *
	 * @param   object $data  Data
	 *
	 * @return boolean
	 */
	private static function deleteasset($data)
	{
		$db = JFactory::getDBO();
		if (isset($data->jasset_id))
		{
			if ($data->jasset_id >= 2)
			{
				$query = $db->getQuery(true);
				$query->delete('#__assets')
					->where('id = ' . $db->quote($data->jasset_id));
				$db->setQuery($query);

				if (!$db->execute())
				{
					JFactory::getApplication()->enqueueMessage(JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true)), 'error');

					return false;
				}
			}

			return true;
		}
		else
		{
			return false;
		}
	}

}
