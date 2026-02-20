<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmlocationHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmmigrationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;

/**
 * Location Setup Wizard model
 *
 * Provides data and apply/dismiss logic for the 7-step multi-campus
 * location setup wizard. All write operations are idempotent.
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class CwmlocationwizardModel extends BaseDatabaseModel
{
    /**
     * Return the auto-detected migration scenario.
     *
     * Delegates to CwmmigrationHelper so the detection logic stays in one place.
     *
     * @return  string  '2A' = access-based multi-campus, '2B' = already using locations,
     *                  '2C' = single campus.
     *
     * @since   10.1.0
     */
    public function getScenario(): string
    {
        return CwmmigrationHelper::detectMigrationScenario();
    }

    /**
     * Return all published locations ordered by title.
     *
     * @return  array  List of location objects with id, title, published, access.
     *
     * @since   10.1.0
     */
    public function getLocations(): array
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('location_text'), $db->quoteName('published'), $db->quoteName('access')])
            ->from($db->quoteName('#__bsms_locations'))
            ->order($db->quoteName('location_text') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Return all Joomla user groups ordered by title.
     *
     * @return  array  List of group objects with id and title.
     *
     * @since   10.1.0
     */
    public function getGroups(): array
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('title')])
            ->from($db->quoteName('#__usergroups'))
            ->order($db->quoteName('title') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Return the current group-to-location mapping from component params.
     *
     * Keys are location IDs (string), values are arrays of group IDs (int[]).
     *
     * @return  array<string, int[]>
     *
     * @since   10.1.0
     */
    public function getCurrentMapping(): array
    {
        $raw = ComponentHelper::getParams('com_proclaim')->get('location_group_mapping', '{}');

        if (\is_string($raw)) {
            $decoded = json_decode($raw, true);

            return \is_array($decoded) ? $decoded : [];
        }

        return \is_array($raw) ? $raw : [];
    }

    /**
     * Return teachers along with their user-account link status.
     *
     * Returns objects with: id, teacher, user_id (may be null / 0), user_name.
     *
     * @return  array
     *
     * @since   10.1.0
     */
    public function getTeachers(): array
    {
        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $columns = $db->getTableColumns('#__bsms_teachers');
        $hasLink = isset($columns['user_id']);

        $query = $db->getQuery(true)
            ->select([$db->quoteName('t.id'), $db->quoteName('t.teacher')])
            ->from($db->quoteName('#__bsms_teachers', 't'))
            ->where($db->quoteName('t.published') . ' = 1')
            ->order($db->quoteName('t.teacher') . ' ASC');

        if ($hasLink) {
            $query->select($db->quoteName('t.user_id'))
                ->select($db->quoteName('u.name', 'user_name'))
                ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('t.user_id'));
        } else {
            // No user_id column yet — return placeholder
            $query->select('0 AS user_id')->select('\'\' AS user_name');
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Return a preview summary of the changes the wizard will apply.
     *
     * Uses the pending mapping from the session (if available), falling
     * back to the saved mapping so the preview always shows something.
     *
     * @param   array  $pendingMapping  Group-to-location mapping from wizard form.
     *
     * @return  array{locations: int, groups: int, unmapped_locations: int}
     *
     * @since   10.1.0
     */
    public function getPreviewData(array $pendingMapping): array
    {
        $locations         = $this->getLocations();
        $mappedLocationIds = array_map('intval', array_keys($pendingMapping));
        $unmapped          = 0;

        foreach ($locations as $loc) {
            if (!\in_array((int) $loc->id, $mappedLocationIds, true)) {
                $unmapped++;
            }
        }

        $groupCount = 0;

        foreach ($pendingMapping as $groupIds) {
            $groupCount += \count((array) $groupIds);
        }

        return [
            'locations'          => \count($locations),
            'groups'             => $groupCount,
            'unmapped_locations' => $unmapped,
            'mapping'            => $pendingMapping,
        ];
    }

    /**
     * Apply the wizard configuration.
     *
     * Saves the group-to-location mapping to component params, enables location
     * filtering, and marks the wizard as complete.
     *
     * @param   array  $mapping  Group-to-location mapping: { locationId: [groupId...] }.
     *
     * @return  bool  True on success.
     *
     * @since   10.1.0
     */
    public function applyWizard(array $mapping): bool
    {
        try {
            $db     = Factory::getContainer()->get(DatabaseInterface::class);
            $params = ComponentHelper::getParams('com_proclaim');

            // Save the mapping as JSON
            $params->set('location_group_mapping', json_encode($mapping, JSON_THROW_ON_ERROR));
            $params->set('enable_location_filtering', 1);
            $params->set('location_system_dismissed', 0);

            $query = $db->getQuery(true)
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('params') . ' = ' . $db->quote($params->toString()))
                ->where($db->quoteName('element') . ' = ' . $db->quote('com_proclaim'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('component'));

            $db->setQuery($query);
            $db->execute();

            // Reset per-request location cache so new mapping takes effect immediately
            CwmlocationHelper::resetCache();

            return true;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }
    }

    /**
     * Dismiss the wizard without applying any changes.
     *
     * Sets location_system_dismissed = 1 in component params so the wizard
     * banner in the admin CPanel is hidden permanently.
     *
     * @return  bool  True on success.
     *
     * @since   10.1.0
     */
    public function dismiss(): bool
    {
        try {
            $db     = Factory::getContainer()->get(DatabaseInterface::class);
            $params = ComponentHelper::getParams('com_proclaim');

            $params->set('location_system_dismissed', 1);

            $query = $db->getQuery(true)
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('params') . ' = ' . $db->quote($params->toString()))
                ->where($db->quoteName('element') . ' = ' . $db->quote('com_proclaim'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('component'));

            $db->setQuery($query);
            $db->execute();

            return true;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }
    }

    /**
     * Return detection details for the welcome step.
     *
     * Collects the scenario, counts, and human-readable description in one call
     * so the template doesn't need to call multiple model methods.
     *
     * @return  array{scenario: string, locations: int, messages_with_location: int,
     *               has_mapping: bool, description: string}
     *
     * @since   10.1.0
     */
    public function getDetectionInfo(): array
    {
        $scenario              = $this->getScenario();
        $locationCount         = CwmlocationHelper::getPublishedLocationCount();
        $messagesWithLocations = CwmmigrationHelper::getMessagesWithLocations();
        $hasMapping            = !empty($this->getCurrentMapping());

        $descriptionKey = match ($scenario) {
            '2A'    => 'JBS_WIZARD_SCENARIO_2A_DESC',
            '2B'    => 'JBS_WIZARD_SCENARIO_2B_DESC',
            default => 'JBS_WIZARD_SCENARIO_2C_DESC',
        };

        return [
            'scenario'               => $scenario,
            'locations'              => $locationCount,
            'messages_with_location' => $messagesWithLocations,
            'has_mapping'            => $hasMapping,
            'description_key'        => $descriptionKey,
        ];
    }
}
