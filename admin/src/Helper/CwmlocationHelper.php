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
use Joomla\Database\DatabaseInterface;
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
        $visible = self::getUserLocations($userId);

        // Empty = super admin (all locations allowed)
        if (empty($visible)) {
            return [];
        }

        // Ensure the currently-saved location is always present
        if ($currentId > 0 && !\in_array($currentId, $visible, true)) {
            $visible[] = $currentId;
            sort($visible);
        }

        return $visible;
    }

    /**
     * Apply a location visibility filter to a query.
     *
     * Does nothing when:
     *   - location filtering is disabled in component config, or
     *   - the user is a super admin.
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
        if (!self::isEnabled()) {
            return;
        }

        $locations = self::getUserLocations($userId);

        // Empty = super admin — no filter needed
        if (empty($locations)) {
            return;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query->whereIn($db->quoteName($alias . '.location_id'), $locations);
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
     * @param   int  $userId  Joomla user ID.
     *
     * @return  int[]  Location IDs.
     *
     * @since   10.1.0
     * @todo    Implement once a user_id column is added to #__bsms_teachers.
     *          When available, join: teachers.user_id = $userId
     *          → study_teachers.teacher_id → studies.location_id.
     */
    public static function getTeacherLocations(int $userId): array
    {
        // Stub: teacher-to-user linkage requires a user_id field on #__bsms_teachers
        // which is not present in the current schema. Return empty until Phase 2.
        return [];
    }

    /**
     * Determine whether a user is a teacher of a specific message.
     *
     * @param   int  $userId     Joomla user ID.
     * @param   int  $messageId  Message (study) ID.
     *
     * @return  bool
     *
     * @since   10.1.0
     * @todo    Implement once a user_id column is added to #__bsms_teachers.
     */
    public static function userIsTeacher(int $userId, int $messageId): bool
    {
        // Stub: see getTeacherLocations() note.
        return false;
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

        $query = $db->getQuery(true)
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

        $query = $db->getQuery(true)
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
            } catch (\JsonException) {
                return [];
            }

            return \is_array($decoded) ? $decoded : [];
        }

        // Registry::get() returns stdClass for nested objects — convert to array
        if ($raw instanceof \stdClass) {
            try {
                return json_decode(json_encode($raw, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR) ?: [];
            } catch (\JsonException) {
                return [];
            }
        }

        return \is_array($raw) ? $raw : [];
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
        $query = $db->getQuery(true)
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
