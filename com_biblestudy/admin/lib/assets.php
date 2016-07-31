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
 * Asset Fix class
 *
 * @since  7.0.4
 */
class JBSMAssets
{

	public static $parent_id = null;

	public $query = array();

	public $count = 0;

	/**
	 * Build functions
	 *
	 * @return boolean
	 *
	 * @since 9.0.0
	 */
	public function build ()
	{
		$db = JFactory::getDbo();

		// Get the names of the JBS tables
		$objects = self::getassetObjects();

		// Run through each table
		foreach ($objects as $object)
		{
			// Put the table into the return array
			// Get the total number of rows and collect the table into a query
			$query = $db->getQuery(true);
			$query->select('j.id, j.asset_id, a.id as aid, a.parent_id, a.rules')
				->from($db->qn($object['name']) . ' as j')
				->leftJoin('#__assets as a ON (a.id = j.asset_id)');
			$db->setQuery($query);
			$results = $db->loadObjectList();
			$this->count += count($results);
			$this->query = array_merge((array) $this->query, array($object['assetname'] => $results));
		}

		JLog::add('Build fixAsset', JLog::INFO, 'com_biblestudy');

		return true;
	}

	/**
	 * Fix Assets function.
	 *
	 * @param   string  $key     Asset name to affect
	 * @param   object  $result  Assets to look at.
	 *
	 * @return boolean
	 *
	 * @since 9.0.0
	 */
	public static function fixAssets ($key, $result)
	{
		$result = (Object) $result;
		self::parentid();

		// If there is no jasset_id it means that this has not been set and should be
		if (!$result->asset_id)
		{
			self::setasset($result, $key);
			JLog::add('Set Asset Under Key: ' . $key, JLog::NOTICE, 'com_biblestudy');
		}

		// If there is a jasset_id but no match to the parent_id then a mismatch has occured
		if ((self::$parent_id != $result->parent_id || $result->rules === "") && $result->asset_id)
		{
			JLog::add('Reset Asset ID: ' . $result->asset_id, JLog::NOTICE, 'com_biblstudy');
			$deletasset = self::deleteasset($result);

			if ($deletasset)
			{
				self::setasset($result, $key);
			}
		}

		return true;
	}

	/**
	 * Set Parent ID
	 *
	 * @return int Parent ID
	 *
	 * @since 9.0.0
	 */
	public static function parentid ()
	{
		if (!self::$parent_id)
		{
			$db = JFactory::getDbo();

			// First get the new parent_id
			$query = $db->getQuery(true);
			$query->select('id')
				->from('#__assets')
				->where('name = ' . $db->q('com_biblestudy'));
			$db->setQuery($query);
			self::$parent_id = $db->loadResult();
		}

		return self::$parent_id;
	}

	/**
	 * Table list Array.
	 *
	 * @return array
	 *
	 * @since 9.0.0
	 */
	protected static function getassetObjects ()
	{
		$objects = array(
				array(
						'name'       => '#__bsms_servers',
						'titlefield' => 'server_name',
						'assetname'  => 'server',
						'realname'   => 'JBS_CMN_SERVERS'
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
						'name'       => '#__bsms_mediafiles',
						'titlefield' => 'filename',
						'assetname'  => 'mediafile',
						'realname'   => 'JBS_CMN_MEDIA_FILES'
				),
				array(
						'name'       => '#__bsms_message_type',
						'titlefield' => 'message_type',
						'assetname'  => 'messagetype',
						'realname'   => 'JBS_CMN_MESSAGETYPES'
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
	 * @param   object  $data       Data
	 * @param   string  $assetName  Asset Name
	 *
	 * @return boolean
	 *
	 * @since 9.0.0
	 */
	private static function setasset ($data, $assetName)
	{
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
		$table = JTable::getInstance($assetName, 'Table');

		if ($data->id)
		{
			try
			{
				if ($assetName == 'mediafile')
				{
					$columns = array('media_image', 'special', 'filename', 'size', 'mime_type', 'mediacode', 'link_type',
							'docMan_id', 'article_id', 'virtueMart_id', 'player', 'popup', 'server', 'internal_viewer', 'path');

					foreach ($columns as $col)
					{
						unset($table->$col);
					}
				}

				$table->load($data->id, false);
			}
			catch (Exception $e)
			{
				echo 'Caught exception: ', $e->getMessage(), "\n";

				return false;
			}
			$table->store();
		}

		return true;
	}

	/**
	 * Delete assets
	 *
	 * @param   object  $data  Data
	 *
	 * @return boolean
	 *
	 * @since 9.0.0
	 */
	private static function deleteasset ($data)
	{
		$db = JFactory::getDbo();

		if (isset($data->asset_id))
		{
			if ($data->asset_id >= 2 && $data->asset_id != self::$parent_id)
			{
				$query = $db->getQuery(true);
				$query->delete('#__assets')
						->where('id = ' . $db->quote($data->asset_id));
				$db->setQuery($query);
				$db->execute();
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Check Assets
	 *
	 * @return array
	 *
	 * @since 9.0.0
	 */
	public static function checkAssets ()
	{
		$return = array();
		$db     = JFactory::getDbo();
		$result = new stdClass;

		// First get the new parent_id
		if (!self::$parent_id)
		{
			self::parentid();
		}

		// Get the names of the JBS tables
		$objects = self::getassetObjects();

		// Run through each table
		foreach ($objects as $object)
		{
			// Put the table into the return array
			// Get the total number of rows and collect the table into a query
			$query = $db->getQuery(true);
			$query->select('j.id as jid, j.asset_id as jasset_id, a.id as aid, a.rules as arules, a.parent_id')
					->from($db->qn($object['name']) . ' as j')
					->leftJoin('#__assets as a ON (a.id = j.asset_id)');
			$db->setQuery($query);
			$results     = $db->loadObjectList();
			$nullrows    = 0;
			$matchrows   = 0;
			$arulesrows  = 0;
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
				// If there is a jasset_id but no match to the parent_id then a mismatch has occurred
				if (self::$parent_id != $result->parent_id && $result->jasset_id)
				{
					$nomatchrows++;
				}
				// If $parent_id and $result->parent_id match and the Asset rules are not blank then everything is okay
				if (self::$parent_id == $result->parent_id && $result->arules !== "")
				{
					$matchrows++;
				}
				// If $parent_id and $result->parent_id match and the Asset rules is blank we need to fix
				if (self::$parent_id == $result->parent_id && $result->arules === "")
				{
					$arulesrows++;
				}
			}

			$return[] = array(
					'realname'         => $object['realname'],
					'numrows'          => $numrows,
					'nullrows'         => $nullrows,
					'matchrows'        => $matchrows,
					'arulesrows'       => $arulesrows,
					'nomatchrows'      => $nomatchrows,
					'parent_id'        => self::$parent_id,
					'result_parent_id' => $result->parent_id,
					'id'               => $result->jid,
					'assetid'          => $result->jasset_id
			);
		}

		return $return;
	}
}
