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
     * JSON rule shapes we treat as "no real permissions" — a row with any
     * of these values contributes nothing to permission decisions because
     * Joomla will walk up to the `com_proclaim` parent asset. Keeping them
     * around is pure load-time waste: `Access::preload('com_proclaim')`
     * has to fetch and JSON-decode every single one on every request.
     *
     * @since 10.3.0
     */
    private const EMPTY_RULE_VARIANTS = [
        '',
        '{}',
        '[]',
        '{"core.delete":[],"core.edit":[],"core.edit.state":[]}',
        '{"core.delete":[],"core.edit":[],"core.create":[],"core.edit.state":[],"core.edit.own":[]}',
    ];

    /**
     * Clean ALL Proclaim assets: clean orphans, delete empty-rules rows,
     * fix parent_id on surviving rows, rebuild the nested-set tree.
     *
     * **Inverted from the pre-10.3.0 behavior**: this method no longer
     * creates an asset row for every Proclaim record with default rules.
     * On mature sites that produced thousands of `com_proclaim.*` rows
     * whose `rules` column was the empty template — Joomla still had to
     * load them all during `Access::preload()`, accounting for ~72% of
     * the admin page-load time on some installs.
     *
     * Net effect: Proclaim's asset tree now contains only the com_proclaim
     * parent plus any records where an admin has genuinely customised the
     * rules. Permission checks still work for everything else because
     * Joomla walks the ancestor chain up to the parent.
     *
     * @return void
     *
     * @since 10.1.0
     */
    public static function fixAllAssets(): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Step 1: Ensure the com_proclaim parent asset exists.
        $parentId = self::ensureParentAsset();

        if (!$parentId) {
            Log::add('Could not find or create com_proclaim parent asset', Log::WARNING, 'com_proclaim');

            return;
        }

        // Step 2: Remove orphaned Proclaim assets (rows whose record is gone).
        self::cleanOrphanedAssets($db);

        // Step 3: Delete every per-record asset whose rules are empty/default,
        // and null out the asset_id on the record that referenced it.
        self::pruneEmptyAssetRows($db);

        // Step 4: Fix parent_id on any surviving per-record asset rows. These
        // are the ones with real, customised rules — keep them, just make
        // sure they're linked to the Proclaim parent.
        self::reparentSurvivingAssets($db, $parentId);

        // Step 5: Rebuild the nested-set tree so lft/rgt are consistent
        // after the deletions.
        try {
            $assetTable = new \Joomla\CMS\Table\Asset($db);
            $assetTable->rebuild();
            Log::add('Asset tree rebuilt successfully', Log::INFO, 'com_proclaim');
        } catch (\Exception $e) {
            Log::add('Asset tree rebuild failed: ' . $e->getMessage(), Log::WARNING, 'com_proclaim');
        }
    }

    /**
     * Delete `#__assets` rows whose `rules` column is empty/default and
     * null the corresponding `asset_id` on the source record so it no
     * longer points at a now-missing row.
     *
     * @param   DatabaseInterface  $db  Database driver
     *
     * @return  int  Rows deleted
     *
     * @since   10.3.0
     */
    public static function pruneEmptyAssetRows(DatabaseInterface $db): int
    {
        $deleted     = 0;
        $emptyQuoted = implode(
            ',',
            array_map(static fn ($v) => $db->quote($v), self::EMPTY_RULE_VARIANTS)
        );

        // First, null out asset_id on source records that point at rows
        // we're about to delete. Doing this before the DELETE means we
        // don't leave dangling asset_id values behind.
        foreach (self::getAssetObjects() as $info) {
            $assetName = $info['assetname'];
            $sourceTbl = $info['name'];

            try {
                $query = $db->getQuery(true)
                    ->update($db->quoteName($sourceTbl, 's'))
                    ->innerJoin(
                        $db->quoteName('#__assets', 'a') . ' ON '
                        . $db->quoteName('s.asset_id') . ' = ' . $db->quoteName('a.id')
                    )
                    ->set($db->quoteName('s.asset_id') . ' = 0')
                    ->where($db->quoteName('a.name') . ' LIKE '
                        . $db->quote('com_proclaim.' . $assetName . '.%'))
                    ->where($db->quoteName('a.rules') . ' IN (' . $emptyQuoted . ')');
                $db->setQuery($query);
                $db->execute();
            } catch (\Exception $e) {
                Log::add(
                    'Null asset_id error for ' . $sourceTbl . ': ' . $e->getMessage(),
                    Log::WARNING,
                    'com_proclaim'
                );
            }
        }

        // Now delete the asset rows themselves.
        try {
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__assets'))
                ->where($db->quoteName('name') . ' LIKE ' . $db->quote('com_proclaim.%'))
                ->where($db->quoteName('name') . ' <> ' . $db->quote('com_proclaim'))
                ->where($db->quoteName('rules') . ' IN (' . $emptyQuoted . ')');
            $db->setQuery($query);
            $db->execute();
            $deleted = $db->getAffectedRows();

            if ($deleted > 0) {
                Log::add(
                    'Pruned ' . $deleted . ' empty-rules Proclaim assets',
                    Log::INFO,
                    'com_proclaim'
                );
            }
        } catch (\Exception $e) {
            Log::add(
                'Prune empty assets error: ' . $e->getMessage(),
                Log::WARNING,
                'com_proclaim'
            );
        }

        return $deleted;
    }

    /**
     * Ensure any surviving per-record asset rows (those with real,
     * customised rules) are parented to the com_proclaim component
     * asset. No-ops for rows already correctly linked.
     *
     * @param   DatabaseInterface  $db        Database driver
     * @param   int                $parentId  com_proclaim asset id
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public static function reparentSurvivingAssets(DatabaseInterface $db, int $parentId): void
    {
        if ($parentId <= 0) {
            return;
        }

        try {
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__assets'))
                ->set($db->quoteName('parent_id') . ' = ' . (int) $parentId)
                ->where($db->quoteName('name') . ' LIKE ' . $db->quote('com_proclaim.%'))
                ->where($db->quoteName('name') . ' <> ' . $db->quote('com_proclaim'))
                ->where($db->quoteName('parent_id') . ' <> ' . (int) $parentId);
            $db->setQuery($query);
            $db->execute();
        } catch (\Exception $e) {
            Log::add(
                'Reparent surviving assets error: ' . $e->getMessage(),
                Log::WARNING,
                'com_proclaim'
            );
        }
    }

    /**
     * Post-store hook: if Joomla auto-created an empty-rules asset row
     * for this record, delete it and null the record's asset_id. Called
     * from every Proclaim Table's `store()` override so empty rows never
     * accumulate in the first place.
     *
     * Safe to call whether the table tracks assets or not — if there's
     * no asset_id or the linked row has non-default rules, this is a
     * no-op.
     *
     * @param   \Joomla\CMS\Table\Table  $table  Just-stored Proclaim table instance
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public static function stripEmptyAssetRow(\Joomla\CMS\Table\Table $table): void
    {
        $assetId = (int) ($table->asset_id ?? 0);

        if ($assetId <= 0) {
            return;
        }

        try {
            $db = $table->getDbo();

            $query = $db->getQuery(true)
                ->select($db->quoteName('rules'))
                ->from($db->quoteName('#__assets'))
                ->where($db->quoteName('id') . ' = ' . $assetId);
            $db->setQuery($query);
            $rules = (string) $db->loadResult();

            if (!\in_array($rules, self::EMPTY_RULE_VARIANTS, true)) {
                return;
            }

            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__assets'))
                ->where($db->quoteName('id') . ' = ' . $assetId);
            $db->setQuery($query);
            $db->execute();

            // Null the record's asset_id so it stops pointing at the
            // row we just deleted. Use the table's own name/key so we
            // don't need a hard-coded table list here.
            $tableName = $table->getTableName();
            $keyName   = $table->getKeyName();

            if ($tableName && $keyName && !empty($table->$keyName)) {
                $query = $db->getQuery(true)
                    ->update($db->quoteName($tableName))
                    ->set($db->quoteName('asset_id') . ' = 0')
                    ->where($db->quoteName($keyName) . ' = ' . (int) $table->$keyName);
                $db->setQuery($query);
                $db->execute();

                // Keep the in-memory table instance consistent with the DB.
                $table->asset_id = 0;
            }
        } catch (\Exception $e) {
            Log::add(
                'stripEmptyAssetRow error: ' . $e->getMessage(),
                Log::WARNING,
                'com_proclaim'
            );
        }
    }

    /**
     * Repair a single record's asset link.
     *
     * **Behavior changed in 10.3.0**: this no longer creates a new asset
     * row for records that lack one. If a record has no `asset_id` (or
     * points at a missing row) and the only thing we could create would
     * be an empty-rules placeholder, we leave it alone — permission
     * checks fall through to the `com_proclaim` parent asset, which is
     * what we want. The old behavior accumulated thousands of empty rows
     * that slowed down `Access::preload('com_proclaim')` for no benefit.
     *
     * What it still does:
     *   - Relink a record to an existing, real-rules asset row by name.
     *   - Delete the record's asset row if the rules turned out to be
     *     empty/default, and null `asset_id` on the record.
     *   - Fix the parent_id on a surviving per-record row whose parent
     *     has drifted away from `com_proclaim`.
     *
     * @param   DatabaseInterface  $db         Database driver
     * @param   string             $tableName  Content table name (e.g. '#__bsms_studies')
     * @param   string             $assetName  Short asset name (e.g. 'message', 'teacher')
     * @param   object             $item       Record with id, asset_id, aid, parent_id, rules
     * @param   int                $parentId   com_proclaim parent asset ID
     *
     * @return  bool  True if a repair was applied, false if no change was needed
     *
     * @since   10.1.0
     */
    public static function fixSingleRecord(DatabaseInterface $db, string $tableName, string $assetName, object $item, int $parentId): bool
    {
        $assetFullName = 'com_proclaim.' . $assetName . '.' . $item->id;
        $assetExists   = !empty($item->aid);

        // Case 1 — record has no asset link. If a real-rules asset row
        // exists by name, relink; otherwise do nothing.
        if (empty($item->asset_id) || (int) $item->asset_id === 0 || !$assetExists) {
            $query = $db->getQuery(true)
                ->select($db->quoteName(['id', 'rules']))
                ->from($db->quoteName('#__assets'))
                ->where($db->quoteName('name') . ' = ' . $db->quote($assetFullName));
            $db->setQuery($query);
            $existing = $db->loadObject();

            if ($existing && !\in_array((string) $existing->rules, self::EMPTY_RULE_VARIANTS, true)) {
                $query = $db->getQuery(true)
                    ->update($db->quoteName($tableName))
                    ->set($db->quoteName('asset_id') . ' = ' . (int) $existing->id)
                    ->where($db->quoteName('id') . ' = ' . (int) $item->id);
                $db->setQuery($query);
                $db->execute();

                return true;
            }

            return false;
        }

        // Case 2 — record links to a real asset row whose rules are empty.
        // Delete the row and null the link; subsequent permission checks
        // inherit from the com_proclaim parent.
        if (\in_array((string) ($item->rules ?? ''), self::EMPTY_RULE_VARIANTS, true)) {
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__assets'))
                ->where($db->quoteName('id') . ' = ' . (int) $item->asset_id);
            $db->setQuery($query);
            $db->execute();

            $query = $db->getQuery(true)
                ->update($db->quoteName($tableName))
                ->set($db->quoteName('asset_id') . ' = 0')
                ->where($db->quoteName('id') . ' = ' . (int) $item->id);
            $db->setQuery($query);
            $db->execute();

            return true;
        }

        // Case 3 — real custom rules, but parent has drifted.
        if ((int) ($item->parent_id ?? 0) !== (int) $parentId) {
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__assets'))
                ->set($db->quoteName('parent_id') . ' = ' . (int) $parentId)
                ->set($db->quoteName('name') . ' = ' . $db->quote($assetFullName))
                ->where($db->quoteName('id') . ' = ' . (int) $item->asset_id);
            $db->setQuery($query);
            $db->execute();

            return true;
        }

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
