<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Lib;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;

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
    public static array $query = [];

    /**
     * @var int
     * @since 7.0.4
     */
    public static int $count = 0;

    /**
     * Fix Assets function.
     *
     * @param   string   $key     Asset name to affect
     * @param   ?object  $result  Assets to look at.
     *
     * @return bool
     *
     * @since 9.0.0
     */
    public static function fixAssets(string $key, ?object $result): bool
    {
        $result_object   = (object) $result;
        self::$parent_id = self::ensureParentAsset();

        if (!self::$parent_id) {
            Log::add('Could not find or create parent asset', Log::WARNING, 'com_proclaim');

            return false;
        }

        // Check if the asset actually exists (aid comes from LEFT JOIN with #__assets)
        $assetExists = !empty($result_object->aid);

        // Case 1: No asset_id OR asset_id points to non-existent asset
        if (!$result_object->asset_id || !$assetExists) {
            // Delete stale asset_id reference if it exists but asset is missing
            if ($result_object->asset_id && !$assetExists) {
                Log::add('Stale asset_id ' . $result_object->asset_id . ' for ' . $key . ' ID ' . $result_object->id, Log::NOTICE, 'com_proclaim');
            }

            self::setAsset($result_object, $key);
            Log::add('Set Asset Under Key: ' . $key, Log::NOTICE, 'com_proclaim');

            return true;
        }

        // Case 2: Asset exists but parent_id mismatch or empty rules
        if ((self::$parent_id !== (int) $result_object->parent_id || $result_object->rules === "") && $result_object->asset_id) {
            Log::add('Reset Asset ID: ' . $result_object->asset_id, Log::NOTICE, 'com_proclaim');
            $deletasset = self::deleteAsset($result_object);

            if ($deletasset) {
                self::setAsset($result_object, $key);
            }
        }

        return true;
    }

    /**
     * Ensure the com_proclaim parent asset exists, create if missing
     *
     * @return int Parent asset ID, or 0 on failure
     *
     * @since 10.1.0
     */
    public static function ensureParentAsset(): int
    {
        // First try to find existing asset
        $parentId = self::parentId();

        if ($parentId) {
            return $parentId;
        }

        // Parent asset doesn't exist - need to create it
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Find the root asset to use as parent
        $query = $db->getQuery(true);
        $query->select($db->quoteName('id'))
            ->from($db->quoteName('#__assets'))
            ->where($db->quoteName('parent_id') . ' = 0')
            ->where($db->quoteName('level') . ' = 0');
        $db->setQuery($query);
        $rootId = (int) $db->loadResult();

        if (!$rootId) {
            $rootId = 1; // Fallback to id 1
        }

        // Create the com_proclaim parent asset
        $defaultRules = '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}';

        $query = $db->getQuery(true);
        $query->insert($db->quoteName('#__assets'))
            ->columns($db->quoteName(['parent_id', 'lft', 'rgt', 'level', 'name', 'title', 'rules']))
            ->values(
                (int) $rootId . ', 0, 0, 1, ' .
                $db->quote('com_proclaim') . ', ' .
                $db->quote('com_proclaim') . ', ' .
                $db->quote($defaultRules)
            );

        try {
            $db->setQuery($query);
            $db->execute();
            self::$parent_id = (int) $db->insertid();

            // Rebuild the asset tree to fix lft/rgt values
            $assetTable = new \Joomla\CMS\Table\Asset($db);
            $assetTable->rebuild();

            Log::add('Created com_proclaim parent asset with ID: ' . self::$parent_id, Log::INFO, 'com_proclaim');

            return self::$parent_id;
        } catch (\Exception $e) {
            Log::add('Failed to create parent asset: ' . $e->getMessage(), Log::ERROR, 'com_proclaim');

            return 0;
        }
    }

    /**
     * Set Parent ID
     *
     * @return int Parent ID
     *
     * @since 9.0.0
     */
    public static function parentId(): int
    {
        if (!self::$parent_id) {
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            // First, get the new parent_id
            $query = $db->getQuery(true);
            $query->select($db->quoteName('id'))
                ->from($db->quoteName('#__assets'))
                ->where($db->quoteName('name') . ' = ' . $db->q('com_proclaim'));
            $db->setQuery($query);
            self::$parent_id = $db->loadResult();
        }

        return (int)self::$parent_id;
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
        $db         = Factory::getContainer()->get(DatabaseInterface::class);
        $AssetTable = '\CWM\Component\Proclaim\Administrator\Table\Cwm' . $assetName . 'Table';
        $table      = new $AssetTable($db);

        if ($data->id) {
            try {
                if ($assetName === 'MediaFile') {
                    $columns = [
                        'media_image',
                        'special',
                        'filename',
                        'size',
                        'mime_type',
                        'mediacode',
                        'link_type',
                        'docMan_id',
                        'article_id',
                        'virtueMart_id',
                        'player',
                        'popup',
                        'server',
                        'internal_viewer',
                        'path',
                    ];

                    foreach ($columns as $col) {
                        unset($table->$col);
                    }
                }

                $table->load($data->id, false);
            } catch (\Exception $e) {
                echo 'Caught exception: ', $e->getMessage(), "\n";

                return;
            }

            $table->store();
        }
    }

    /**
     * Delete a Proclaim-owned asset.
     *
     * Only deletes the asset if it belongs to Proclaim (name starts with
     * "com_proclaim.").  This prevents accidental deletion of core Joomla
     * assets (com_content, com_users, etc.) if a record's asset_id column
     * ever points to a non-Proclaim row.
     *
     * @param   object  $data  Data with asset_id property
     *
     * @return bool
     *
     * @since 9.0.0
     */
    private static function deleteAsset(object $data): bool
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        if (!isset($data->asset_id) || (int) $data->asset_id < 2) {
            return false;
        }

        $assetId = (int) $data->asset_id;

        // Never delete the Proclaim parent asset itself
        if ($assetId === self::$parent_id) {
            return true;
        }

        // Verify this asset actually belongs to Proclaim before deleting
        $query = $db->getQuery(true)
            ->select($db->quoteName('name'))
            ->from($db->quoteName('#__assets'))
            ->where($db->quoteName('id') . ' = :assetId')
            ->bind(':assetId', $assetId, \Joomla\Database\ParameterType::INTEGER);
        $db->setQuery($query);
        $name = (string) $db->loadResult();

        if ($name === '' || !str_starts_with($name, 'com_proclaim.')) {
            Log::add(
                'Skipped deletion of non-Proclaim asset ID ' . $assetId . ' (name: ' . $name . ')',
                Log::WARNING,
                'com_proclaim'
            );

            return true;
        }

        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__assets'))
            ->where($db->quoteName('id') . ' = :assetId')
            ->bind(':assetId', $assetId, \Joomla\Database\ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();

        return true;
    }

    /**
     * Build functions
     *
     * @return object
     *
     * @since 9.0.0
     */
    public static function build(): object
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Get the names of the JBS tables
        $objects = self::getassetObjects();

        // Run through each table
        foreach ($objects as $object) {
            // Put the table into the return array
            // Get the total number of rows and collect the table into a query
            $query = $db->getQuery(true);
            $query->select(
                $db->quoteName('j.id') . ', ' .
                    $db->quoteName('j.asset_id') . ', ' .
                    $db->quoteName('a.id', 'aid') . ', ' .
                    $db->quoteName('a.parent_id') . ', ' .
                    $db->quoteName('a.rules')
            )
                ->from($db->quoteName($object['name'], 'j'))
                ->leftJoin($db->quoteName('#__assets', 'a') . ' ON (' . $db->quoteName('a.id') . ' = ' . $db->quoteName('j.asset_id') . ')');
            $db->setQuery($query);
            $results     = $db->loadObjectList();
            self::$count += \count($results);
            self::$query = array_merge((array)self::$query, [$object['assetname'] => $results]);
        }

        Log::add('Build fixAsset', Log::INFO, 'com_proclaim');

        $result        = new \stdClass();
        $result->count = self::$count;
        $result->query = self::$query;

        return $result;
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
        return [
            [
                'name'       => '#__bsms_servers',
                'titlefield' => 'server_name',
                'assetname'  => 'server',
                'realname'   => 'JBS_CMN_SERVERS',
            ],
            [
                'name'       => '#__bsms_studies',
                'titlefield' => 'studytitle',
                'assetname'  => 'message',
                'realname'   => 'JBS_CMN_STUDIES',
            ],
            [
                'name'       => '#__bsms_comments',
                'titlefield' => 'comment_date',
                'assetname'  => 'comment',
                'realname'   => 'JBS_CMN_COMMENTS',
            ],
            [
                'name'       => '#__bsms_locations',
                'titlefield' => 'location_text',
                'assetname'  => 'location',
                'realname'   => 'JBS_CMN_LOCATIONS',
            ],
            [
                'name'       => '#__bsms_mediafiles',
                'titlefield' => 'filename',
                'assetname'  => 'mediafile',
                'realname'   => 'JBS_CMN_MEDIA_FILES',
            ],
            [
                'name'       => '#__bsms_message_type',
                'titlefield' => 'message_type',
                'assetname'  => 'messagetype',
                'realname'   => 'JBS_CMN_MESSAGETYPES',
            ],
            [
                'name'       => '#__bsms_podcast',
                'titlefield' => 'title',
                'assetname'  => 'podcast',
                'realname'   => 'JBS_CMN_PODCASTS',
            ],
            [
                'name'       => '#__bsms_series',
                'titlefield' => 'series_text',
                'assetname'  => 'serie',
                'realname'   => 'JBS_CMN_SERIES',
            ],
            [
                'name'       => '#__bsms_teachers',
                'titlefield' => 'teachername',
                'assetname'  => 'teacher',
                'realname'   => 'JBS_CMN_TEACHERS',
            ],
            [
                'name'       => '#__bsms_templates',
                'titlefield' => 'title',
                'assetname'  => 'template',
                'realname'   => 'JBS_CMN_TEMPLATES',
            ],
            [
                'name'       => '#__bsms_topics',
                'titlefield' => 'topic_text',
                'assetname'  => 'topic',
                'realname'   => 'JBS_CMN_TOPICS',
            ],
            [
                'name'       => '#__bsms_templatecode',
                'titlefield' => 'filename',
                'assetname'  => 'templatecode',
                'realname'   => 'JBS_CMN_TEMPLATECODE',
            ],
            [
                'name'       => '#__bsms_admin',
                'titlefield' => 'id',
                'assetname'  => 'admin',
                'realname'   => 'JBS_CMN_ADMINISTRATION',
            ],
        ];
    }
}
