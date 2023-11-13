<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Lib;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * Asset Fix class
 *
 * @since  7.0.4
 */
class Cwmassets
{
	public static int $parent_id = 0;

	/**
	 * @var array
	 * @since 7.0.4
	 */
	public static array $query = array();

	/**
	 * @var integer
	 * @since 7.0.4
	 */
	public static int $count = 0;

	/**
	 * Build functions
	 *
	 * @return object
	 *
	 * @since 9.0.0
	 */
	public function build(): object
	{
		$db = Factory::getContainer()->get('DatabaseDriver');

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
			$results     = $db->loadObjectList();
			self::$count += count($results);
			self::$query = array_merge((array) self::$query, array($object['assetname'] => $results));
		}

		Log::add('Build fixAsset', Log::INFO, 'com_proclaim');

		$result        = new \stdClass();
		$result->count = self::$count;
		$result->query = self::$query;

		return $result;
	}

	/**
	 * Fix Assets function.
	 *
	 * @param   string  $key     Asset name to affect
	 * @param   ?object  $result  Assets to look at.
	 *
	 * @return boolean
	 *
	 * @since 9.0.0
	 */
	public static function fixAssets(string $key, ?object $result): bool
	{
		$result_object          = (object) $result;
		self::$parent_id = self::parentid();

		// If there is no asset_id it means that this has not been set and should be
		if (!$result_object->asset_id)
		{
			self::setAsset($result_object, $key);
			Log::add('Set Asset Under Key: ' . $key, Log::NOTICE, 'com_proclaim');
		}

		// If there is a asset_id but no match to the parent_id then a mismatch has occurred
		if ((self::$parent_id !== $result_object->parent_id || $result_object->rules === "") && $result_object->asset_id)
		{
			Log::add('Reset Asset ID: ' . $result_object->asset_id, Log::NOTICE, 'com_proclaim');
			$deletasset = self::deleteAsset($result_object);

			if ($deletasset)
			{
				self::setAsset($result_object, $key);
			}
		}

		return true;
	}

	/**
	 * Set Parent ID
	 *
	 * @return integer Parent ID
	 *
	 * @since 9.0.0
	 */
	public static function parentId(): int
	{
		if (!self::$parent_id)
		{
			$db = Factory::getContainer()->get('DatabaseDriver');

			// First get the new parent_id
			$query = $db->getQuery(true);
			$query->select('id')
				->from('#__assets')
				->where('name = ' . $db->q('com_proclaim'));
			$db->setQuery($query);
			self::$parent_id = $db->loadResult();
		}

		return (int) self::$parent_id;
	}

	/**
	 * Table list Array.
	 *
	 * @return array
	 *
	 * @since 9.0.0
	 */
	public static function getAssetObjects(): array
	{
		return array(
			array(
				'name'       => '#__bsms_servers',
				'titlefield' => 'server_name',
				'assetname'  => 'Server',
				'realname'   => 'JBS_CMN_SERVERS'
			),
			array(
				'name'       => '#__bsms_studies',
				'titlefield' => 'studytitle',
				'assetname'  => 'Message',
				'realname'   => 'JBS_CMN_STUDIES'
			),
			array(
				'name'       => '#__bsms_comments',
				'titlefield' => 'comment_date',
				'assetname'  => 'Comment',
				'realname'   => 'JBS_CMN_COMMENTS'
			),
			array(
				'name'       => '#__bsms_locations',
				'titlefield' => 'location_text',
				'assetname'  => 'Location',
				'realname'   => 'JBS_CMN_LOCATIONS'
			),
			array(
				'name'       => '#__bsms_mediafiles',
				'titlefield' => 'filename',
				'assetname'  => 'MediaFile',
				'realname'   => 'JBS_CMN_MEDIA_FILES'
			),
			array(
				'name'       => '#__bsms_message_type',
				'titlefield' => 'message_type',
				'assetname'  => 'MessageType',
				'realname'   => 'JBS_CMN_MESSAGETYPES'
			),
			array(
				'name'       => '#__bsms_podcast',
				'titlefield' => 'title',
				'assetname'  => 'Podcast',
				'realname'   => 'JBS_CMN_PODCASTS'
			),
			array(
				'name'       => '#__bsms_series',
				'titlefield' => 'series_text',
				'assetname'  => 'Serie',
				'realname'   => 'JBS_CMN_SERIES'
			),
			array(
				'name'       => '#__bsms_teachers',
				'titlefield' => 'teachername',
				'assetname'  => 'Teacher',
				'realname'   => 'JBS_CMN_TEACHERS'
			),
			array(
				'name'       => '#__bsms_templates',
				'titlefield' => 'title',
				'assetname'  => 'Template',
				'realname'   => 'JBS_CMN_TEMPLATES'
			),
			array(
				'name'       => '#__bsms_topics',
				'titlefield' => 'topic_text',
				'assetname'  => 'Topic',
				'realname'   => 'JBS_CMN_TOPICS'
			),
			array(
				'name'       => '#__bsms_templatecode',
				'titlefield' => 'filename',
				'assetname'  => 'TemplateCode',
				'realname'   => 'JBS_CMN_TEMPLATECODE'
			),
			array(
				'name'       => '#__bsms_admin',
				'titlefield' => 'id',
				'assetname'  => 'Admin',
				'realname'   => 'JBS_CMN_ADMINISTRATION'
			)
		);
	}

	/**
	 * Set Asset
	 *
	 * @param   object  $data       Data
	 * @param   string  $assetName  Asset Name
	 *
	 * @return void
	 *
	 * @since 9.0.0
	 */
	private static function setAsset(object $data, string $assetName): void
	{
		$db         = Factory::getContainer()->get('DatabaseDriver');
		$AssetTable = '\CWM\Component\Proclaim\Administrator\Table\CWM' . $assetName . 'Table';
		$table      = new $AssetTable($db);

		if ($data->id)
		{
			try
			{
				if ($assetName === 'MediaFile')
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
			catch (\Exception $e)
			{
				echo 'Caught exception: ', $e->getMessage(), "\n";

				return;
			}

			$table->store();
		}

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
	private static function deleteAsset(object $data): bool
	{
		$db = Factory::getContainer()->get('DatabaseDriver');

		if (isset($data->asset_id))
		{
			if ((int) $data->asset_id >= 2 && (int) $data->asset_id !== self::$parent_id)
			{
				$query = $db->getQuery(true);
				$query->delete('#__assets')
					->where('id = ' . $db->quote($data->asset_id));
				$db->setQuery($query);
				$db->execute();
			}

			return true;
		}

		return false;
	}
}
