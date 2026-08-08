<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * @since      10.1.0
 */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;

/**
 * Location-based multi-campus access helper.
 *
 * Determines which locations a user can see using a three-source model:
 *   1. Group Locations       — locations mapped to the user's Joomla user groups
 *   2. Teacher Locations     — locations where the user has a linked teacher record
 *   3. Unrestricted Locations — locations not mapped to any group
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class CwmlocationHelper
{
    /**
     * Static per-request cache keyed by user ID.
     *
     * @var array<int, int[]>
     * @since 10.1.0
     */
    private static array $locationCache = [];

    /**
     * Whether an unreadable location_group_mapping has already been reported
     * this request.
     *
     * getGroupMapping() runs once per user per request from two call sites, so
     * without this a corrupt param would write the same line to the log
     * repeatedly on a busy site.
     *
     * @var    bool
     *
     * @since  10.5.6
     */
    private static bool $mappingWarned = false;

    /**
     * Return the location IDs visible to a user.
     *
     * Super admins receive an empty array (meaning: no filter, see everything).
     * All other users receive the union of group locations, teacher locations,
     * and unrestricted locations.
     *
     * @param   int  $userId  Joomla user ID (0 = current user).
     *
     * @return  int[]  Location IDs the user may see; empty = see all.
     *
     * @since   10.1.0
     */
    public static function getUserLocations(int $userId = 0): array
    {
        $app = Factory::getApplication();

        if ($userId > 0) {
            $user = Factory::getContainer()->get('user.factory')->loadUserById($userId);
        } else {
            $user   = $app->getIdentity();
            $userId = (int) $user->id;
        }

        // Super admins bypass all filtering
        if ($user->authorise('core.admin')) {
            return [];
        }

        if (isset(self::$locationCache[$userId])) {
            return self::$locationCache[$userId];
        }

        $params  = ComponentHelper::getParams('com_proclaim');
        $mapping = self::getGroupMapping($params);

        // Source 1: locations this user's groups are mapped to
        $userGroups     = $user->groups ?? [];
        $groupLocations = [];

        foreach ($mapping as $locationId => $groupIds) {
            foreach ($groupIds as $gid) {
                if (\in_array((int) $gid, array_map('intval', array_keys($userGroups)), true)
                    || \in_array((int) $gid, array_map('intval', $userGroups), true)) {
                    $groupLocations[] = (int) $locationId;
                    break;
                }
            }
        }

        // Source 2: teacher locations (stub — requires user_id on #__bsms_teachers)
        $teacherLocations = self::getTeacherLocations($userId);

        // Source 3: locations not mapped to any group at all
        $mappedLocationIds     = array_map('intval', array_keys($mapping));
        $unrestrictedLocations = self::getUnrestrictedLocations($mappedLocationIds);

        $visible = array_unique(array_merge($groupLocations, $teacherLocations, $unrestrictedLocations));
        sort($visible);

        self::$locationCache[$userId] = $visible;

        return $visible;
    }

    /**
     * Return locations a user is allowed to assign to records when editing.
     *
     * Equivalent to getUserLocations() but always returns at least the
     * current record's location even if the user cannot normally see it,
     * so that saving an existing record does not silently strip the location.
     *
     * @param   int  $userId      Joomla user ID (0 = current user).
     * @param   int  $currentId   Currently assigned location_id (0 = none).
     *
     * @return  int[]  Location IDs available in the dropdown.
     *
     * @since   10.1.0
     */
    public static function getUserAccessibleLocationsForEdit(int $userId = 0, int $currentId = 0): array
    {
        if (self::isSuperAdmin($userId)) {
            return [];
        }

        $visible = self::getUserLocations($userId);

        // Ensure the currently-saved location is always present
        if ($currentId > 0 && !\in_array($currentId, $visible, true)) {
            $visible[] = $currentId;
            sort($visible);
        }

        return $visible;
    }

    /**
     * Validate that a user may assign a record to the given location, throwing
     * if not.
     *
     * The edit-form dropdown (LocationListField) restricts what a non-admin
     * user can pick, but that is client-side only: a restricted user can
     * submit any location_id directly, via devtools or a raw POST. This is the
     * server-side enforcement, so call it at the top of save() before any data
     * is written.
     *
     * Looking up the record's current location_id (when $recordId > 0) means
     * saving a record without changing its location is never blocked just
     * because the user lacks access to that location -- only an actual
     * attempt to move it to an inaccessible location is denied, matching
     * getUserAccessibleLocationsForEdit()'s existing "always include the
     * current value" semantics.
     *
     * @param   string  $table       Table name owning the location_id column, e.g. '#__bsms_studies'.
     * @param   int     $recordId    Existing record ID (0 = new record).
     * @param   int     $locationId  Submitted location_id (0 or less = none, always allowed).
     * @param   int     $userId      Joomla user ID (0 = current user).
     *
     * @return  void
     *
     * @throws  \RuntimeException  If the user may not assign this location.
     *
     * @since   10.5.6
     */
    public static function assertLocationAssignable(
        string $table,
        int $recordId,
        int $locationId,
        int $userId = 0
    ): void {
        if ($locationId <= 0 || !self::isEnabled() || self::isSuperAdmin($userId)) {
            return;
        }

        $currentId = 0;

        if ($recordId > 0) {
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            $currentId = (int) $db->setQuery(
                $db->createQuery()
                    ->select($db->quoteName('location_id'))
                    ->from($db->quoteName($table))
                    ->where($db->quoteName('id') . ' = :id')
                    ->bind(':id', $recordId, ParameterType::INTEGER)
            )->loadResult();
        }

        $allowed = self::getUserAccessibleLocationsForEdit($userId, $currentId);

        if (!\in_array($locationId, $allowed, true)) {
            throw new \RuntimeException(Text::_('JBS_BAT_LOCATION_ACCESS_DENIED'));
        }
    }

    /**
     * Apply a location visibility filter to a query.
     *
     * Does nothing when:
     *   - location filtering is disabled in component config, or
     *   - the user is a super admin.
     *
     * A non-admin user with zero accessible locations (a real, valid config --
     * not the same thing as a super admin, see isSuperAdmin()) is restricted to
     * records with no location assigned at all, rather than seeing every
     * location. See #1561.
     *
     * @param   QueryInterface  $query   The query to filter.
     * @param   string          $alias   Table alias owning the location_id column.
     * @param   int             $userId  Joomla user ID (0 = current user).
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public static function applyLocationFilter(QueryInterface $query, string $alias, int $userId = 0): void
    {
        if (!self::isEnabled() || self::isSuperAdmin($userId)) {
            return;
        }

        $locations = self::getUserLocations($userId);
        $db        = Factory::getContainer()->get(DatabaseInterface::class);

        if (empty($locations)) {
            $query->where($db->quoteName($alias . '.location_id') . ' IS NULL');

            return;
        }

        $query->whereIn($db->quoteName($alias . '.location_id'), $locations);
    }

    /**
     * Determine whether a user is a super admin, exempt from all location filtering.
     *
     * getUserLocations()/getUserAccessibleLocationsForEdit() also return an
     * empty array for an authenticated non-admin with zero accessible
     * locations -- a real, valid configuration, not a super admin. Conflating
     * the two let every consumer fail open (treat "zero access" as "no
     * restriction"). Callers that need to distinguish the two cases must
     * check this method directly rather than inferring admin status from an
     * empty location array. See #1561.
     *
     * @param   int  $userId  Joomla user ID (0 = current user).
     *
     * @return  bool
     *
     * @since   10.5.6
     */
    public static function isSuperAdmin(int $userId = 0): bool
    {
        $user = $userId > 0
            ? Factory::getContainer()->get('user.factory')->loadUserById($userId)
            : Factory::getApplication()->getIdentity();

        return $user->authorise('core.admin');
    }

    /**
     * Apply hybrid security filter (location + Joomla access level).
     *
     * Combines location-based filtering with the standard Joomla view-level
     * access check so that multi-site sync via access levels continues to work
     * alongside the location system.
     *
     * @param   QueryInterface  $query   The query to filter.
     * @param   string          $alias   Table alias owning location_id and access columns.
     * @param   int             $userId  Joomla user ID (0 = current user).
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public static function applySecurityFilter(QueryInterface $query, string $alias, int $userId = 0): void
    {
        // Apply location filter
        self::applyLocationFilter($query, $alias, $userId);

        // Apply standard Joomla view-level access filter
        $app  = Factory::getApplication();
        $user = $userId ? Factory::getContainer()->get('user.factory')->loadUserById($userId) : $app->getIdentity();

        if (!$user->authorise('core.admin')) {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query->whereIn($db->quoteName($alias . '.access'), $user->getAuthorisedViewLevels());
        }
    }

    /**
     * Return location IDs where the user has an associated teacher record.
     *
     * Joins through both the many-to-many study_teachers table and the legacy
     * teacher_id column on studies to cover all assignment paths.
     *
     * @param   int  $userId  Joomla user ID.
     *
     * @return  int[]  Location IDs.
     *
     * @since   10.3.0
     */
    public static function getTeacherLocations(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Many-to-many path: teachers → study_teachers → studies
        $query1 = $db->createQuery()
            ->select('DISTINCT ' . $db->quoteName('s.location_id'))
            ->from($db->quoteName('#__bsms_teachers', 't'))
            ->innerJoin(
                $db->quoteName('#__bsms_study_teachers', 'st')
                . ' ON ' . $db->quoteName('st.teacher_id') . ' = ' . $db->quoteName('t.id')
            )
            ->innerJoin(
                $db->quoteName('#__bsms_studies', 's')
                . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('st.study_id')
            )
            ->where($db->quoteName('t.user_id') . ' = :userId')
            ->where($db->quoteName('s.location_id') . ' IS NOT NULL')
            ->where($db->quoteName('s.location_id') . ' > 0');

        // Legacy path: teachers → studies.teacher_id
        $query2 = $db->createQuery()
            ->select('DISTINCT ' . $db->quoteName('s.location_id'))
            ->from($db->quoteName('#__bsms_teachers', 't'))
            ->innerJoin(
                $db->quoteName('#__bsms_studies', 's')
                . ' ON ' . $db->quoteName('s.teacher_id') . ' = ' . $db->quoteName('t.id')
            )
            ->where($db->quoteName('t.user_id') . ' = :userId')
            ->where($db->quoteName('s.location_id') . ' IS NOT NULL')
            ->where($db->quoteName('s.location_id') . ' > 0');

        // Both branches share the single :userId placeholder, bound once here.
        // DatabaseQuery::union() only merges SQL text, not bound parameters
        // (getBounded() returns whichever query object is passed to setQuery()),
        // so query2 must never carry its own separately-bound placeholder name --
        // that value would silently be lost. See #1561.
        $query1->union($query2)->bind(':userId', $userId, ParameterType::INTEGER);

        $db->setQuery($query1);

        return array_map('intval', $db->loadColumn() ?: []);
    }

    /**
     * Determine whether a user is a teacher of a specific message.
     *
     * Checks both the many-to-many study_teachers table and the legacy
     * teacher_id column on the study record.
     *
     * @param   int  $userId     Joomla user ID.
     * @param   int  $messageId  Message (study) ID.
     *
     * @return  bool
     *
     * @since   10.3.0
     */
    public static function userIsTeacher(int $userId, int $messageId): bool
    {
        if ($userId <= 0 || $messageId <= 0) {
            return false;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Check many-to-many path
        $query = $db->createQuery()
            ->select('1')
            ->from($db->quoteName('#__bsms_teachers', 't'))
            ->innerJoin(
                $db->quoteName('#__bsms_study_teachers', 'st')
                . ' ON ' . $db->quoteName('st.teacher_id') . ' = ' . $db->quoteName('t.id')
            )
            ->where($db->quoteName('t.user_id') . ' = :userId')
            ->where($db->quoteName('st.study_id') . ' = :studyId')
            ->bind(':userId', $userId, ParameterType::INTEGER)
            ->bind(':studyId', $messageId, ParameterType::INTEGER);

        $db->setQuery($query, 0, 1);

        if ($db->loadResult()) {
            return true;
        }

        // Check legacy teacher_id path
        $query2 = $db->createQuery()
            ->select('1')
            ->from($db->quoteName('#__bsms_teachers', 't'))
            ->innerJoin(
                $db->quoteName('#__bsms_studies', 's')
                . ' ON ' . $db->quoteName('s.teacher_id') . ' = ' . $db->quoteName('t.id')
            )
            ->where($db->quoteName('t.user_id') . ' = :userId2')
            ->where($db->quoteName('s.id') . ' = :studyId2')
            ->bind(':userId2', $userId, ParameterType::INTEGER)
            ->bind(':studyId2', $messageId, ParameterType::INTEGER);

        $db->setQuery($query2, 0, 1);

        return (bool) $db->loadResult();
    }

    /**
     * Return message, series, and podcast counts for a given location.
     *
     * Used by the setup wizard and deletion safety checks to prevent
     * accidentally orphaning content.
     *
     * @param   int  $locationId  The location ID to query.
     *
     * @return  array{messages: int}  Count keyed by entity type.
     *
     * @since   10.1.0
     */
    public static function getLocationUsage(int $locationId): array
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__bsms_studies'))
            ->where($db->quoteName('location_id') . ' = ' . (int) $locationId);

        $db->setQuery($query);
        $messageCount = (int) $db->loadResult();

        return [
            'messages' => $messageCount,
        ];
    }

    /**
     * Determine whether the location setup wizard should be shown.
     *
     * Returns true when:
     *   - location filtering is enabled in component config, AND
     *   - no group-to-location mappings have been configured yet, AND
     *   - the wizard has not been dismissed.
     *
     * @return  bool
     *
     * @since   10.1.0
     */
    public static function shouldShowWizard(): bool
    {
        $params = ComponentHelper::getParams('com_proclaim');

        if (!$params->get('enable_location_filtering', 0)) {
            return false;
        }

        if ($params->get('location_system_dismissed', 0)) {
            return false;
        }

        $mapping = self::getGroupMapping($params);

        return empty($mapping);
    }

    /**
     * Return the count of published locations.
     *
     * @return  int
     *
     * @since   10.1.0
     */
    public static function getPublishedLocationCount(): int
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__bsms_locations'))
            ->where($db->quoteName('published') . ' = 1');

        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    /**
     * Return whether location filtering is enabled in component configuration.
     *
     * @return  bool
     *
     * @since   10.1.0
     */
    public static function isEnabled(): bool
    {
        return (bool) ComponentHelper::getParams('com_proclaim')->get('enable_location_filtering', 0);
    }

    /**
     * Reset the per-request cache.
     *
     * Useful in tests and after saving configuration changes.
     *
     * @param   int|null  $userId  Clear only for this user ID, or null to clear all.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public static function resetCache(?int $userId = null): void
    {
        if ($userId === null) {
            self::$locationCache = [];
        } else {
            unset(self::$locationCache[$userId]);
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Return the group-to-location mapping array from component params.
     *
     * Stored as JSON: { "locationId": [groupId, groupId, ...], ... }
     *
     * @param   \Joomla\Registry\Registry  $params  Component params.
     *
     * @return  array<string, int[]>
     *
     * @since   10.1.0
     */
    private static function getGroupMapping(\Joomla\Registry\Registry $params): array
    {
        $raw = $params->get('location_group_mapping', '{}');

        if (\is_string($raw)) {
            try {
                $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                self::reportUnreadableMapping($e);

                return [];
            }

            return \is_array($decoded) ? $decoded : [];
        }

        // Registry::get() returns stdClass for nested objects — convert to array
        if ($raw instanceof \stdClass) {
            try {
                return json_decode(json_encode($raw, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR) ?: [];
            } catch (\JsonException $e) {
                self::reportUnreadableMapping($e);

                return [];
            }
        }

        return \is_array($raw) ? $raw : [];
    }

    /**
     * Record that location_group_mapping could not be read.
     *
     * The empty array this accompanies is deliberately unchanged. An empty
     * mapping is also the legitimate "campuses not configured yet" state, and
     * getUserLocations() treats every unmapped location as unrestricted — so an
     * unreadable param silently opens every campus to every user rather than
     * closing them. Failing closed instead is not an option: it would lock out
     * every site that has never configured campuses, which is most of them, and
     * the two cases cannot be told apart by value.
     *
     * What can be fixed is the silence.
     *
     * @param   \JsonException  $e  The decode failure.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    private static function reportUnreadableMapping(\JsonException $e): void
    {
        if (self::$mappingWarned) {
            return;
        }

        self::$mappingWarned = true;

        CwmlogHelper::error(
            'The location_group_mapping component parameter could not be read, so campus filtering is '
            . 'inactive and every location is visible to every user. Re-save Proclaim\'s Location '
            . 'Management options to rebuild it. ' . $e->getMessage()
        );
    }

    /**
     * Return location IDs that are NOT present in the group mapping.
     *
     * These "unrestricted" locations are visible to all authenticated users
     * regardless of their group memberships.
     *
     * @param   int[]  $mappedIds  Location IDs that ARE in the mapping.
     *
     * @return  int[]
     *
     * @since   10.1.0
     */
    private static function getUnrestrictedLocations(array $mappedIds): array
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__bsms_locations'))
            ->where($db->quoteName('published') . ' = 1');

        if (!empty($mappedIds)) {
            $query->whereNotIn($db->quoteName('id'), $mappedIds);
        }

        $db->setQuery($query);

        return array_map('intval', $db->loadColumn() ?: []);
    }
}
