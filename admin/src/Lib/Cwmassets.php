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
 * Centralised Proclaim asset management.
 *
 * Every operation that creates, fixes, or cleans assets in `#__assets`
 * for Proclaim content MUST go through this class.  There is exactly
 * ONE code-path for each operation — no duplicates in controllers,
 * models, or restore helpers.
 *
 * @since  7.0.4
 */
class Cwmassets
{
    /**
     * Cached com_proclaim parent asset ID.
     *
     * @var int
     * @since 7.0.4
     */
    public static int $parent_id = 0;

    // =========================================================================
    // Parent Asset Management
    // =========================================================================

    /**
     * Ensure the com_proclaim parent asset exists, create if missing.
     *
     * NOTE: This method does its own direct DB lookup instead of calling
     * parentId() to avoid infinite recursion (parentId → ensureParentAsset
     * → parentId → ...).
     *
     * @return int Parent asset ID, or 0 on failure
     *
     * @since 10.1.0
     */
    public static function ensureParentAsset(): int
    {
        // Check static cache first
        if (self::$parent_id > 0) {
            return self::$parent_id;
        }

        // Direct DB lookup (NOT via parentId() — that would cause recursion)
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__assets'))
            ->where($db->quoteName('name') . ' = ' . $db->quote('com_proclaim'));
        $db->setQuery($query);
        $existingId = (int) $db->loadResult();

        if ($existingId > 0) {
            self::$parent_id = $existingId;

            return $existingId;
        }

        // Parent asset doesn't exist - need to create it

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
     * Get the com_proclaim parent asset ID, creating it if missing.
     *
     * CRITICAL: This method MUST never return 0.  All 14 Table classes call
     * this from _getAssetParentId().  Returning 0 tells Joomla's nested-set
     * engine to insert the asset at the root level (parent_id = 0, level = 0).
     * When that malformed asset is later deleted, the cascading lft/rgt
     * adjustment wipes out every node in the tree — including root.1 —
     * destroying all Joomla permissions.
     *
     * Fallback chain: com_proclaim asset → create it → root asset (1).
     *
     * @return int Parent asset ID (always > 0)
     *
     * @since 9.0.0
     */
    public static function parentId(): int
    {
        if (self::$parent_id > 0) {
            return self::$parent_id;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__assets'))
            ->where($db->quoteName('name') . ' = ' . $db->quote('com_proclaim'));
        $db->setQuery($query);
        $result = (int) $db->loadResult();

        if ($result > 0) {
            self::$parent_id = $result;

            return self::$parent_id;
        }

        // Parent missing — try to create it
        $created = self::ensureParentAsset();

        if ($created > 0) {
            return $created;
        }

        // Last resort: use the Joomla root asset (id=1) so we never
        // return 0 and corrupt the nested-set tree
        Log::add(
            'com_proclaim parent asset missing and could not be created — falling back to root asset',
            Log::ERROR,
            'com_proclaim'
        );

        return 1;
    }

    // =========================================================================
    // Consolidated Asset Fixing (single source of truth)
    // =========================================================================

    /**
     * Fix ALL Proclaim assets: clean orphans, fix/create per record, rebuild tree.
     *
     * This is the ONE method that all callers (restore, install, upgrade,
     * manual fix) must use for a full asset repair cycle.
     *
     * @return void
     *
     * @since 10.1.0
     */
    public static function fixAllAssets(): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Step 1: Ensure the com_proclaim parent asset exists
        $parentId = self::ensureParentAsset();

        if (!$parentId) {
            Log::add('Could not find or create com_proclaim parent asset', Log::WARNING, 'com_proclaim');

            return;
        }

        // Step 2: Remove orphaned Proclaim assets (stale from previous state)
        self::cleanOrphanedAssets($db);

        // Step 3: Fix/create assets for every content record
        $assetTables = self::getAssetObjects();

        foreach ($assetTables as $tableInfo) {
            try {
                $offset = 0;

                while (true) {
                    $query = $db->getQuery(true);
                    $query->select(
                        $db->quoteName('j.id') . ', ' . $db->quoteName('j.asset_id') . ', '
                        . $db->quoteName('a.id', 'aid') . ', ' . $db->quoteName('a.parent_id') . ', ' . $db->quoteName('a.rules')
                    )
                        ->from($db->quoteName($tableInfo['name'], 'j'))
                        ->leftJoin($db->quoteName('#__assets', 'a') . ' ON (' . $db->quoteName('a.id') . ' = ' . $db->quoteName('j.asset_id') . ')')
                        ->setLimit(100, $offset);
                    $db->setQuery($query);
                    $results = $db->loadObjectList();

                    if (empty($results)) {
                        break;
                    }

                    foreach ($results as $item) {
                        self::fixSingleRecord($db, $tableInfo['name'], $tableInfo['assetname'], $item, $parentId);
                    }

                    unset($results);
                    $offset += 100;
                }

                Log::add('Fixed assets for ' . $tableInfo['name'], Log::INFO, 'com_proclaim');
            } catch (\Exception $e) {
                Log::add('Asset fix error for ' . $tableInfo['name'] . ': ' . $e->getMessage(), Log::WARNING, 'com_proclaim');
            }
        }

        // Step 4: Rebuild the entire nested-set tree
        try {
            $assetTable = new \Joomla\CMS\Table\Asset($db);
            $assetTable->rebuild();
            Log::add('Asset tree rebuilt successfully', Log::INFO, 'com_proclaim');
        } catch (\Exception $e) {
            Log::add('Asset tree rebuild failed: ' . $e->getMessage(), Log::WARNING, 'com_proclaim');
        }
    }

    /**
     * Fix the asset for a single content record.
     *
     * Uses Joomla's Asset Table ORM for inserts (proper lft/rgt positioning)
     * and direct SQL for lightweight updates.
     *
     * @param   DatabaseInterface  $db         Database driver
     * @param   string             $tableName  Content table name (e.g. '#__bsms_studies')
     * @param   string             $assetName  Short asset name (e.g. 'message', 'teacher')
     * @param   object             $item       Record with id, asset_id, aid, parent_id, rules
     * @param   int                $parentId   com_proclaim parent asset ID
     *
     * @return bool True if asset was fixed/created, false if already OK
     *
     * @since 10.1.0
     */
    public static function fixSingleRecord(DatabaseInterface $db, string $tableName, string $assetName, object $item, int $parentId): bool
    {
        $assetFullName = 'com_proclaim.' . $assetName . '.' . $item->id;
        $defaultRules  = '{"core.delete":[],"core.edit":[],"core.edit.state":[]}';

        // Check if asset actually exists (aid comes from LEFT JOIN)
        $assetExists = !empty($item->aid);

        // Case 1: No asset_id OR asset_id points to a non-existent asset
        if (empty($item->asset_id) || $item->asset_id == 0 || !$assetExists) {
            // Check if asset already exists by name
            $query = $db->getQuery(true);
            $query->select($db->quoteName('id'))
                ->from($db->quoteName('#__assets'))
                ->where($db->quoteName('name') . ' = ' . $db->quote($assetFullName));
            $db->setQuery($query);
            $existingAssetId = $db->loadResult();

            if ($existingAssetId) {
                // Asset exists by name — just link the record to it
                $query = $db->getQuery(true);
                $query->update($db->quoteName($tableName))
                    ->set($db->quoteName('asset_id') . ' = ' . (int) $existingAssetId)
                    ->where($db->quoteName('id') . ' = ' . (int) $item->id);
                $db->setQuery($query);
                $db->execute();
            } else {
                // Create a new asset via Joomla's nested-set API (NOT raw SQL)
                // to ensure correct lft/rgt positioning in the asset tree.
                // Raw INSERT with lft=0, rgt=0 creates a corruption bomb:
                // deleting such an asset runs DELETE WHERE lft BETWEEN 0 AND 0
                // which also deletes the root asset and destroys all permissions.
                $asset = new \Joomla\CMS\Table\Asset($db);
                $asset->setLocation($parentId, 'last-child');
                $asset->parent_id = $parentId;
                $asset->name      = $assetFullName;
                $asset->title     = $assetName . ' ' . $item->id;
                $asset->rules     = $defaultRules;

                if (!$asset->store()) {
                    Log::add('Failed to create asset: ' . $assetFullName, Log::WARNING, 'com_proclaim');

                    return false;
                }

                $newAssetId = (int) $asset->id;

                // Update the record with new asset_id
                $query = $db->getQuery(true);
                $query->update($db->quoteName($tableName))
                    ->set($db->quoteName('asset_id') . ' = ' . $newAssetId)
                    ->where($db->quoteName('id') . ' = ' . (int) $item->id);
                $db->setQuery($query);
                $db->execute();
            }

            return true;
        }

        // Case 2: Has valid asset_id with existing asset but parent_id mismatch or empty rules
        if ($item->parent_id != $parentId || empty($item->rules)) {
            $query = $db->getQuery(true);
            $query->update($db->quoteName('#__assets'))
                ->set($db->quoteName('parent_id') . ' = ' . (int) $parentId)
                ->set($db->quoteName('name') . ' = ' . $db->quote($assetFullName));

            if (empty($item->rules)) {
                $query->set($db->quoteName('rules') . ' = ' . $db->quote($defaultRules));
            }

            $query->where($db->quoteName('id') . ' = ' . (int) $item->asset_id);
            $db->setQuery($query);
            $db->execute();

            return true;
        }

        // Asset is already OK
        return false;
    }

    /**
     * Remove Proclaim-owned assets whose content records no longer exist.
     *
     * After a restore, the #__assets table still contains assets from the
     * previous state.  Their content records were overwritten by the restore,
     * so the assets are now orphans.  Removing them keeps the tree clean and
     * prevents stale references.
     *
     * @param   DatabaseInterface  $db  Database driver
     *
     * @return int Number of orphaned assets removed
     *
     * @since 10.1.0
     */
    public static function cleanOrphanedAssets(DatabaseInterface $db): int
    {
        $assetMap = [
            'com_proclaim.message.'      => '#__bsms_studies',
            'com_proclaim.mediafile.'    => '#__bsms_mediafiles',
            'com_proclaim.serie.'        => '#__bsms_series',
            'com_proclaim.teacher.'      => '#__bsms_teachers',
            'com_proclaim.server.'       => '#__bsms_servers',
            'com_proclaim.comment.'      => '#__bsms_comments',
            'com_proclaim.location.'     => '#__bsms_locations',
            'com_proclaim.messagetype.'  => '#__bsms_message_type',
            'com_proclaim.podcast.'      => '#__bsms_podcast',
            'com_proclaim.template.'     => '#__bsms_templates',
            'com_proclaim.templatecode.' => '#__bsms_templatecode',
            'com_proclaim.topic.'        => '#__bsms_topics',
            'com_proclaim.admin.'        => '#__bsms_admin',
        ];

        $totalRemoved = 0;

        foreach ($assetMap as $prefix => $sourceTable) {
            try {
                $query = $db->getQuery(true)
                    ->delete($db->quoteName('#__assets'))
                    ->where($db->quoteName('name') . ' LIKE ' . $db->quote($prefix . '%'))
                    ->where(
                        'CAST(SUBSTRING(' . $db->quoteName('name') . ', ' . (\strlen($prefix) + 1) . ') AS UNSIGNED)'
                        . ' NOT IN (SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName($sourceTable) . ')'
                    );
                $db->setQuery($query);
                $db->execute();
                $totalRemoved += $db->getAffectedRows();
            } catch (\Exception $e) {
                Log::add('Orphan cleanup error for ' . $prefix . ': ' . $e->getMessage(), Log::WARNING, 'com_proclaim');
            }
        }

        if ($totalRemoved > 0) {
            Log::add('Cleaned ' . $totalRemoved . ' orphaned Proclaim assets', Log::INFO, 'com_proclaim');
        }

        return $totalRemoved;
    }

    // =========================================================================
    // Backward-Compatible Wrappers (for progressive/timer-bounded callers)
    // =========================================================================

    /**
     * Fix a single asset record (backward-compatible wrapper).
     *
     * Used by CwminstallModel and CwmassetsModel in their progressive
     * timer-bounded loops.  Delegates to fixSingleRecord().
     *
     * @param   string   $key     Asset short name (e.g. 'message', 'teacher')
     * @param   ?object  $result  Record with id, asset_id, aid, parent_id, rules
     *
     * @return bool
     *
     * @since 9.0.0
     */
    public static function fixAssets(string $key, ?object $result): bool
    {
        $db       = Factory::getContainer()->get(DatabaseInterface::class);
        $parentId = self::ensureParentAsset();

        if (!$parentId) {
            Log::add('Could not find or create parent asset', Log::WARNING, 'com_proclaim');

            return false;
        }

        // Resolve table name from the asset short name
        $tableMap  = array_column(self::getAssetObjects(), 'name', 'assetname');
        $tableName = $tableMap[$key] ?? '';

        if (!$tableName) {
            Log::add('Unknown asset key: ' . $key, Log::WARNING, 'com_proclaim');

            return false;
        }

        return self::fixSingleRecord($db, $tableName, $key, (object) $result, $parentId);
    }

    /**
     * Build the asset-fix queue (backward-compatible wrapper).
     *
     * Queries all Proclaim content tables with a LEFT JOIN on #__assets to
     * identify records needing asset fixes.  Used by progressive models
     * (CwminstallModel, CwmassetsModel) that process records across
     * multiple HTTP requests.
     *
     * @return object Object with ->count (total records) and ->query (keyed array)
     *
     * @since 9.0.0
     */
    public static function build(): object
    {
        $db         = Factory::getContainer()->get(DatabaseInterface::class);
        $objects    = self::getAssetObjects();
        $allResults = [];
        $totalCount = 0;

        foreach ($objects as $object) {
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
            $totalCount += \count($results);
            $allResults[$object['assetname']] = $results;
        }

        Log::add('Build fixAsset queue: ' . $totalCount . ' records', Log::INFO, 'com_proclaim');

        $result        = new \stdClass();
        $result->count = $totalCount;
        $result->query = $allResults;

        return $result;
    }

    // =========================================================================
    // Asset Table Definitions
    // =========================================================================

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
