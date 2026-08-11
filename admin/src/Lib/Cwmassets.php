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

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\View\CanDo;
use Joomla\CMS\Table\Table;
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

    /**
     * Cached `com_proclaim.<section>` asset IDs, keyed by section name.
     *
     * @var array<string, int>
     * @since __DEPLOY_VERSION__
     */
    private static array $sectionIds = [];

    /**
     * Cached section names declared in admin/access.xml.
     *
     * @var list<string>|null
     * @since __DEPLOY_VERSION__
     */
    private static ?array $declaredSections = null;

    /**
     * Forget cached section ids and the parsed access.xml.
     *
     * `sectionId()` memoises name -> id for the request. That is safe while
     * assets only ever get created, but this class also repairs and removes
     * them — `fixAllAssets()`, `cleanOrphanedAssets()`, `pruneEmptyAssetRows()`
     * — after which a cached id points at a row that no longer exists, and
     * `seedSections()` would report success without recreating anything.
     *
     * Call this after any operation that deletes asset rows.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function clearSectionCache(): void
    {
        self::$sectionIds       = [];
        self::$declaredSections = null;
    }

    /**
     * Absolute path to admin/access.xml, or an empty string when it is missing.
     *
     * Resolved relative to this class rather than through JPATH_ADMINISTRATOR.
     * Both layouts put access.xml two levels above src/Lib —
     * administrator/components/com_proclaim/ when installed, admin/ in the
     * repository — so the same expression works under test, where
     * JPATH_ADMINISTRATOR points at the Joomla install rather than at us.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function accessFile(): string
    {
        $file = \dirname(__DIR__, 2) . '/access.xml';

        if (is_file($file)) {
            return $file;
        }

        $file = JPATH_ADMINISTRATOR . '/components/com_proclaim/access.xml';

        return is_file($file) ? $file : '';
    }

    /**
     * The permissions a user holds on `com_proclaim.<section>`.
     *
     * ⚠️ Use this, not `ContentHelper::getActions()`, for a section question:
     * core discards its `$section` argument unless an item id comes with it and
     * always reads the component's action list, so the two-argument form
     * returns exactly what the bare one returns.
     *
     * Falls back to the component for an undeclared section. A section asset
     * carrying empty rules inherits, so this is inert until a rule is set.
     *
     * @param   string  $section  Section name as declared in access.xml
     *
     * @return  CanDo
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function sectionActions(string $section): CanDo
    {
        if (!\in_array($section, self::declaredSections(), true)) {
            Log::add(
                "No '{$section}' section in access.xml; falling back to component permissions",
                Log::WARNING,
                'com_proclaim'
            );

            return self::componentActions();
        }

        return self::actionsFor($section, 'com_proclaim.' . $section) ?? self::componentActions();
    }

    /**
     * The permissions a user holds on `com_proclaim` itself.
     *
     * Equivalent to `ContentHelper::getActions('com_proclaim')`, but resolves
     * access.xml through `accessFile()`. ⚠️ Core reads only from
     * `JPATH_ADMINISTRATOR`, so under test it returns an all-null CanDo and any
     * comparison against it passes vacuously.
     *
     * @return  CanDo
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function componentActions(): CanDo
    {
        return self::actionsFor('component', 'com_proclaim')
            ?? ContentHelper::getActions('com_proclaim');
    }

    /**
     * Build a CanDo from one access.xml section, answered against one asset.
     *
     * @param   string  $section    Section name to read the action list from
     * @param   string  $assetName  Asset the actions are authorised against
     *
     * @return  CanDo|null  Null when access.xml cannot be read
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function actionsFor(string $section, string $assetName): ?CanDo
    {
        $file = self::accessFile();

        if ($file === '') {
            return null;
        }

        $actions = Access::getActionsFromFile($file, '/access/section[@name="' . $section . '"]/');

        if ($actions === false) {
            return null;
        }

        $result = new CanDo();
        $user   = Factory::getApplication()->getIdentity();

        foreach ($actions as $action) {
            $result->set($action->name, $user->authorise($action->name, $assetName));
        }

        return $result;
    }

    /**
     * Section names admin/access.xml declares, excluding `component`.
     *
     * Read from the manifest rather than hardcoded so the list cannot drift
     * from the file that defines the permissions UI.
     *
     * @return  list<string>
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function declaredSections(): array
    {
        if (self::$declaredSections !== null) {
            return self::$declaredSections;
        }

        self::$declaredSections = [];

        $file = self::accessFile();

        if ($file === '') {
            Log::add('access.xml not found; no section assets can be resolved', Log::ERROR, 'com_proclaim');

            return self::$declaredSections;
        }

        $doc = new \DOMDocument();

        if (!@$doc->load($file)) {
            Log::add('access.xml is not parseable; no section assets can be resolved', Log::ERROR, 'com_proclaim');

            return self::$declaredSections;
        }

        foreach ((new \DOMXPath($doc))->query('/access/section') as $section) {
            $name = $section->getAttribute('name');

            if ($name !== '' && $name !== 'component') {
                self::$declaredSections[] = $name;
            }
        }

        return self::$declaredSections;
    }

    /**
     * Find or create the `com_proclaim.<section>` asset, returning its id.
     *
     * Created with **empty rules**, which is what makes this inert: an empty
     * rule set inherits from `com_proclaim`, so every effective permission is
     * the same answer the fallback already produced. Verified on a development
     * database across 14 groups x 17 sections x 5 actions — 1190 cells, none
     * changed (#1653).
     *
     * Written through `Table\Asset::setLocation()`/`store()` rather than a raw
     * INSERT, so the nested set stays correct without a `rebuild()` pass. A
     * present-but-broken `#__assets` row is a known cause of 404s in this
     * component, which is why this path never hand-writes `lft`/`rgt`.
     *
     * ⚠️ A section asset resolving does NOT make item assets inherit through
     * it. `Access::getAssetRules()` falls back from an unresolved name straight
     * to the extension asset — everything before the first dot — so
     * `com_proclaim.teacher.5` reaches `com_proclaim`, never
     * `com_proclaim.teacher`. Section rules bite only where code asks for the
     * section name explicitly, or where a real item asset is parented beneath
     * it.
     *
     * @param   string  $section  Section name as declared in access.xml
     *
     * @return  int  Asset id, or 0 when the section is undeclared or creation failed
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function sectionId(string $section): int
    {
        if (isset(self::$sectionIds[$section])) {
            return self::$sectionIds[$section];
        }

        // Refuse to mint an asset for a name access.xml does not declare.
        // Permissions set on such a section would govern nothing, and the row
        // would be invisible to the UI that is supposed to manage it.
        if (!\in_array($section, self::declaredSections(), true)) {
            Log::add(
                "Refusing to create asset for undeclared section '{$section}' — add it to access.xml first",
                Log::WARNING,
                'com_proclaim'
            );

            return 0;
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $name  = 'com_proclaim.' . $section;
        $asset = new \Joomla\CMS\Table\Asset($db);

        if ($asset->loadByName($name)) {
            return self::$sectionIds[$section] = (int) $asset->id;
        }

        $parentId = self::parentId();

        if ($parentId < 1) {
            Log::add("Cannot create {$name}: com_proclaim asset is missing", Log::ERROR, 'com_proclaim');

            return 0;
        }

        $asset->name  = $name;
        $asset->title = $name;
        $asset->rules = '{}';
        $asset->setLocation($parentId, 'last-child');

        if (!$asset->check() || !$asset->store()) {
            // A concurrent request may have won the race — the unique index on
            // `name` is the arbiter. Re-read before reporting failure so the
            // loser returns the winner's id instead of 0.
            $retry = new \Joomla\CMS\Table\Asset($db);

            if ($retry->loadByName($name)) {
                return self::$sectionIds[$section] = (int) $retry->id;
            }

            Log::add("Failed to create {$name}: " . $asset->getError(), Log::ERROR, 'com_proclaim');

            return 0;
        }

        return self::$sectionIds[$section] = (int) $asset->id;
    }

    /**
     * Create every section asset access.xml declares.
     *
     * Idempotent — existing rows are loaded, not duplicated — so it is safe to
     * run on install and on every update.
     *
     * @return  int  Number of sections resolved (created or already present)
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function seedSections(): int
    {
        $resolved = 0;

        foreach (self::declaredSections() as $section) {
            if (self::sectionId($section) > 0) {
                $resolved++;
            }
        }

        return $resolved;
    }

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

        $query = $db->createQuery()
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
        $query = $db->createQuery();
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

        $query = $db->createQuery();
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
            // A concurrent first-run request may have won the race to
            // create this row (unique constraint on `name`). Re-query
            // before giving up so the loser doesn't return 0 and send
            // callers down the "parent missing" fallback path.
            $query = $db->createQuery()
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__assets'))
                ->where($db->quoteName('name') . ' = ' . $db->quote('com_proclaim'));
            $db->setQuery($query);
            $winnerId = (int) $db->loadResult();

            if ($winnerId > 0) {
                self::$parent_id = $winnerId;

                return $winnerId;
            }

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

        $query = $db->createQuery()
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
     * Public so backup / diagnostic tools can filter on the same set of
     * rule shapes we consider "empty".
     *
     * @since 10.3.0
     */
    public const array EMPTY_RULE_VARIANTS = [
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

        // Fast path: if nothing is drifted, skip the whole flow. Avoids a
        // full `Asset::rebuild()` on every call, which walks the entire
        // #__assets table and can take 10-30s on mature sites with many
        // third-party extensions.
        if (!self::hasAnyDrift($db, $parentId)) {
            Log::add('Proclaim assets already clean, skipping fix pass', Log::INFO, 'com_proclaim');

            return;
        }

        // Step 2: Remove orphaned Proclaim assets (rows whose record is gone).
        $orphanedRemoved = self::cleanOrphanedAssets($db);

        // Step 3: Delete every per-record asset whose rules are empty/default,
        // and null out the asset_id on the record that referenced it.
        $emptyPruned = self::pruneEmptyAssetRows($db);

        // Step 4: Fix parent_id on any surviving per-record asset rows. These
        // are the ones with real, customised rules — keep them, just make
        // sure they're linked to the Proclaim parent.
        $reparented = self::reparentSurvivingAssets($db, $parentId);

        // Step 5: Only rebuild the nested-set tree if we actually changed
        // structure. Deletions shift lft/rgt; reparenting moves rows under
        // a new parent which also invalidates the traversal ordering.
        // Access::getAssetRules() queries walk via lft/rgt, so a stale
        // tree produces wrong permission checks.
        if ($orphanedRemoved > 0 || $emptyPruned > 0 || $reparented > 0) {
            try {
                $assetTable = new \Joomla\CMS\Table\Asset($db);
                $assetTable->rebuild();
                Log::add('Asset tree rebuilt successfully', Log::INFO, 'com_proclaim');
            } catch (\Exception $e) {
                Log::add('Asset tree rebuild failed: ' . $e->getMessage(), Log::WARNING, 'com_proclaim');
            }
        }
    }

    /**
     * Detect whether any Proclaim asset row has drifted state worth fixing.
     *
     * Returns true when at least one of these conditions is met:
     *  - A `com_proclaim.*` asset has empty/default rules (prune candidate)
     *  - A `com_proclaim.*` asset's parent_id is not the com_proclaim parent
     *    (reparent candidate)
     *  - An orphan exists: asset row for a source record that no longer exists
     *
     * The orphan check does one COUNT per content table; bails at the first
     * hit so the common clean-site case returns after one query.
     *
     * @param   DatabaseInterface  $db        Database driver
     * @param   int                $parentId  com_proclaim parent asset id
     *
     * @return  bool  True when at least one drift condition is present
     *
     * @since   10.3.0
     */
    public static function hasAnyDrift(DatabaseInterface $db, int $parentId): bool
    {
        $emptyQuoted = implode(
            ',',
            array_map(static fn ($v) => $db->quote($v), self::EMPTY_RULE_VARIANTS)
        );

        try {
            $query = $db->createQuery()
                ->select('COUNT(*)')
                ->from($db->quoteName('#__assets'))
                ->where($db->quoteName('name') . ' LIKE ' . $db->quote('com_proclaim.%'))
                ->where($db->quoteName('name') . ' <> ' . $db->quote('com_proclaim'))
                ->where(
                    '(' . $db->quoteName('rules') . ' IN (' . $emptyQuoted . ')'
                    . ' OR ' . $db->quoteName('parent_id') . ' <> ' . (int) $parentId . ')'
                );
            $db->setQuery($query);

            if ((int) $db->loadResult() > 0) {
                return true;
            }
        } catch (\Exception $e) {
            // If the drift probe fails, fall through to the full fix path
            // rather than silently skipping it.
            Log::add('hasAnyDrift probe failed: ' . $e->getMessage(), Log::WARNING, 'com_proclaim');

            return true;
        }

        // Orphan probe — one COUNT per content table, bail at first hit.
        $orphanMap = [
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

        foreach ($orphanMap as $prefix => $sourceTable) {
            try {
                $query = $db->createQuery()
                    ->select('COUNT(*)')
                    ->from($db->quoteName('#__assets'))
                    ->where($db->quoteName('name') . ' LIKE ' . $db->quote($prefix . '%'))
                    ->where(
                        'CAST(SUBSTRING(' . $db->quoteName('name') . ', ' . (\strlen($prefix) + 1) . ') AS UNSIGNED)'
                        . ' NOT IN (SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName($sourceTable) . ')'
                    );
                $db->setQuery($query);

                if ((int) $db->loadResult() > 0) {
                    return true;
                }
            } catch (\Exception $e) {
                Log::add(
                    'hasAnyDrift orphan probe failed for ' . $prefix . ': ' . $e->getMessage(),
                    Log::WARNING,
                    'com_proclaim'
                );

                return true;
            }
        }

        return false;
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
                $query = $db->createQuery()
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
            $query = $db->createQuery()
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
     * @return  int  Rows reparented
     *
     * @since   10.3.0
     */
    public static function reparentSurvivingAssets(DatabaseInterface $db, int $parentId): int
    {
        if ($parentId <= 0) {
            return 0;
        }

        try {
            $query = $db->createQuery()
                ->update($db->quoteName('#__assets'))
                ->set($db->quoteName('parent_id') . ' = ' . (int) $parentId)
                ->where($db->quoteName('name') . ' LIKE ' . $db->quote('com_proclaim.%'))
                ->where($db->quoteName('name') . ' <> ' . $db->quote('com_proclaim'))
                ->where($db->quoteName('parent_id') . ' <> ' . (int) $parentId);
            $db->setQuery($query);
            $db->execute();

            return (int) $db->getAffectedRows();
        } catch (\Exception $e) {
            Log::add(
                'Reparent surviving assets error: ' . $e->getMessage(),
                Log::WARNING,
                'com_proclaim'
            );

            return 0;
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
     * @param   Table  $table  Just-stored Proclaim table instance
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public static function stripEmptyAssetRow(Table $table): void
    {
        $assetId = (int) ($table->asset_id ?? 0);

        if ($assetId <= 0) {
            return;
        }

        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            $query = $db->createQuery()
                ->select($db->quoteName('rules'))
                ->from($db->quoteName('#__assets'))
                ->where($db->quoteName('id') . ' = ' . $assetId);
            $db->setQuery($query);
            $rules = (string) $db->loadResult();

            if (!\in_array($rules, self::EMPTY_RULE_VARIANTS, true)) {
                return;
            }

            $query = $db->createQuery()
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
                $query = $db->createQuery()
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
     * what we want. Creating them instead accumulates thousands of empty rows
     * that slow `Access::preload('com_proclaim')` down for no benefit.
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
            $query = $db->createQuery()
                ->select($db->quoteName(['id', 'rules']))
                ->from($db->quoteName('#__assets'))
                ->where($db->quoteName('name') . ' = ' . $db->quote($assetFullName));
            $db->setQuery($query);
            $existing = $db->loadObject();

            if ($existing && !\in_array((string) $existing->rules, self::EMPTY_RULE_VARIANTS, true)) {
                $query = $db->createQuery()
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
            $query = $db->createQuery()
                ->delete($db->quoteName('#__assets'))
                ->where($db->quoteName('id') . ' = ' . (int) $item->asset_id);
            $db->setQuery($query);
            $db->execute();

            $query = $db->createQuery()
                ->update($db->quoteName($tableName))
                ->set($db->quoteName('asset_id') . ' = 0')
                ->where($db->quoteName('id') . ' = ' . (int) $item->id);
            $db->setQuery($query);
            $db->execute();

            return true;
        }

        // Case 3 — real custom rules, but parent has drifted. A raw
        // parent_id UPDATE leaves lft/rgt stale (still nested under the
        // old parent), so Access::getAssetRules()'s containment walk
        // (`b.lft <= a.lft AND b.rgt >= a.rgt`) never sees com_proclaim
        // as an ancestor -- an asset row that exists but is silently
        // broken, without a full Asset::rebuild() to catch it.
        // moveByReference() performs a proper nested-set node move.
        if ((int) ($item->parent_id ?? 0) !== (int) $parentId) {
            $assetTable = new \Joomla\CMS\Table\Asset($db);

            if (!$assetTable->moveByReference($parentId, 'last-child', (int) $item->asset_id)) {
                Log::add(
                    'fixSingleRecord: failed to move asset ' . $item->asset_id . ' under parent ' . $parentId,
                    Log::WARNING,
                    'com_proclaim'
                );

                return false;
            }

            $query = $db->createQuery()
                ->update($db->quoteName('#__assets'))
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
                $query = $db->createQuery()
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
            $query = $db->createQuery();
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

    /**
     * Collect per-table asset status counts using a small number of
     * aggregate queries. The shape is the new-model status used by the
     * admin Assets tool, not the legacy "needs fix" metrics.
     *
     * Returned per table:
     *   - numrows       Total records in the source table
     *   - inherited     Records with asset_id = 0 (inherit from com_proclaim parent, desired)
     *   - custom_rules  Records linked to an asset with real, non-default rules
     *   - needs_cleanup Records linked to an asset with empty/default rules (legacy rows to prune)
     *   - drifted       Records linked to an asset whose parent_id is not com_proclaim
     *   - orphans       Asset rows whose source record no longer exists
     *
     * @return  array<int, array<string, mixed>>
     *
     * @since   10.3.0
     */
    public static function getAssetStatus(): array
    {
        $db       = Factory::getContainer()->get(DatabaseInterface::class);
        $parentId = self::parentId();
        $status   = [];

        $emptyQuoted = implode(
            ',',
            array_map(static fn ($v) => $db->quote($v), self::EMPTY_RULE_VARIANTS)
        );

        foreach (self::getAssetObjects() as $info) {
            $sourceTbl = $info['name'];
            $assetName = $info['assetname'];

            // numrows/inherited/custom_rules/needs_cleanup/drifted collapsed
            // into one conditional-aggregation query per table (was 5).
            // The LEFT JOIN keeps COUNT(*) equal to the source row count —
            // a row with asset_id = 0 (or pointing at a deleted asset) joins
            // to nothing but still counts once.
            try {
                $db->setQuery(
                    $db->createQuery()
                        ->select(
                            'COUNT(*) AS numrows'
                            . ', SUM(CASE WHEN ' . $db->quoteName('s.asset_id') . ' = 0 THEN 1 ELSE 0 END) AS inherited'
                            . ', SUM(CASE WHEN ' . $db->quoteName('a.id') . ' IS NOT NULL AND '
                                . $db->quoteName('a.rules') . ' NOT IN (' . $emptyQuoted . ') THEN 1 ELSE 0 END) AS custom_rules'
                            . ', SUM(CASE WHEN ' . $db->quoteName('a.id') . ' IS NOT NULL AND '
                                . $db->quoteName('a.rules') . ' IN (' . $emptyQuoted . ') THEN 1 ELSE 0 END) AS needs_cleanup'
                            . ', SUM(CASE WHEN ' . $db->quoteName('a.id') . ' IS NOT NULL AND '
                                . $db->quoteName('a.parent_id') . ' <> ' . (int) $parentId . ' THEN 1 ELSE 0 END) AS drifted'
                        )
                        ->from($db->quoteName($sourceTbl, 's'))
                        ->leftJoin(
                            $db->quoteName('#__assets', 'a') . ' ON '
                            . $db->quoteName('s.asset_id') . ' = ' . $db->quoteName('a.id')
                        )
                );
                $counts       = (array) $db->loadAssoc();
                $numrows      = (int) ($counts['numrows'] ?? 0);
                $inherited    = (int) ($counts['inherited'] ?? 0);
                $customRules  = (int) ($counts['custom_rules'] ?? 0);
                $needsCleanup = (int) ($counts['needs_cleanup'] ?? 0);
                $drifted      = (int) ($counts['drifted'] ?? 0);
            } catch (\Exception $e) {
                Log::add('getAssetStatus aggregate query failed for ' . $sourceTbl . ': ' . $e->getMessage(), Log::WARNING, 'com_proclaim');
                $numrows = $inherited = $customRules = $needsCleanup = $drifted = 0;
            }

            // Orphans — asset row exists but the source record is gone.
            $prefix    = 'com_proclaim.' . $assetName . '.';
            $prefixLen = \strlen($prefix) + 1;

            try {
                $db->setQuery(
                    'SELECT COUNT(*) FROM ' . $db->quoteName('#__assets', 'a')
                    . ' LEFT JOIN ' . $db->quoteName($sourceTbl, 's')
                    . ' ON ' . $db->quoteName('s.id')
                    . ' = CAST(SUBSTRING(' . $db->quoteName('a.name') . ', ' . $prefixLen . ') AS UNSIGNED)'
                    . ' WHERE ' . $db->quoteName('a.name') . ' LIKE ' . $db->quote($prefix . '%')
                    . ' AND ' . $db->quoteName('s.id') . ' IS NULL'
                );
                $orphans = (int) $db->loadResult();
            } catch (\Exception) {
                $orphans = 0;
            }

            $status[] = [
                'realname'      => $info['realname'],
                'tablename'     => $sourceTbl,
                'assetname'     => $assetName,
                'numrows'       => $numrows,
                'inherited'     => $inherited,
                'custom_rules'  => $customRules,
                'needs_cleanup' => $needsCleanup,
                'drifted'       => $drifted,
                'orphans'       => $orphans,
                'parent_id'     => $parentId,
            ];
        }

        return $status;
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
