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
     * Read existing component-level ACL rules and detect which preset each group matches.
     *
     * Reverse-engineers the `#__assets` rules for `com_proclaim` and compares each
     * group's allowed actions against the known presets. Returns a map of groupId → preset
     * so the wizard can pre-select the correct radio button.
     *
     * @return  array<string, string>  groupId → 'full'|'editor'|'viewer'|'none'
     *
     * @since   10.1.0
     */
    public function getCurrentPermissions(): array
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('rules'))
            ->from($db->quoteName('#__assets'))
            ->where($db->quoteName('name') . ' = ' . $db->quote('com_proclaim'));

        $db->setQuery($query);
        $rulesJson = $db->loadResult();

        if (!$rulesJson) {
            return [];
        }

        $rules = json_decode($rulesJson, true) ?: [];

        // Preset definitions — must match applyPermissions()
        $presets = [
            'full' => [
                'core.manage'   => 1, 'core.create' => 1, 'core.edit' => 1,
                'core.edit.own' => 1, 'core.edit.state' => 1, 'core.delete' => 1,
            ],
            'editor' => [
                'core.manage'   => 1, 'core.create' => 1, 'core.edit' => 1,
                'core.edit.own' => 1,
            ],
            'viewer' => [
                'core.manage'   => 1, 'core.create' => 1, 'core.edit.own' => 1,
            ],
        ];

        // Collect all group IDs that appear in any action rule
        $allGroupIds = [];

        foreach ($rules as $actionRules) {
            if (\is_array($actionRules)) {
                foreach (array_keys($actionRules) as $gid) {
                    $allGroupIds[(string) $gid] = true;
                }
            }
        }

        $result = [];

        foreach (array_keys($allGroupIds) as $groupStr) {
            // Build this group's actual permission set
            $groupActions = [];

            foreach ($rules as $action => $actionRules) {
                if (\is_array($actionRules) && isset($actionRules[$groupStr]) && (int) $actionRules[$groupStr] === 1) {
                    $groupActions[$action] = 1;
                }
            }

            // Match against presets (check most specific first)
            $matched = 'none';

            foreach ($presets as $presetName => $presetActions) {
                if ($groupActions === $presetActions) {
                    $matched = $presetName;
                    break;
                }
            }

            $result[$groupStr] = $matched;
        }

        return $result;
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
     * filtering, marks the wizard as complete, and optionally sets component-level
     * ACL permissions for mapped groups.
     *
     * @param   array  $mapping      Group-to-location mapping: { locationId: [groupId...] }.
     * @param   array  $permissions  Permission presets per group: { groupId: 'full'|'editor'|'none' }.
     *
     * @return  bool  True on success.
     *
     * @since   10.1.0
     */
    public function applyWizard(array $mapping, array $permissions = []): bool
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

            // Apply component-level ACL permissions for mapped groups
            if (!empty($permissions)) {
                $this->applyPermissions($permissions);
            }

            // Reset per-request location cache so new mapping takes effect immediately
            CwmlocationHelper::resetCache();

            return true;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }
    }

    /**
     * Set component-level Joomla ACL permissions for mapped user groups.
     *
     * Permissions are set on the com_proclaim asset and cascade to all entity
     * types (messages, teachers, series, etc.) unless individually overridden.
     *
     * @param   array  $permissions  { groupId: 'full'|'editor'|'none', ... }
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private function applyPermissions(array $permissions): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Load current component asset rules
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('rules')])
            ->from($db->quoteName('#__assets'))
            ->where($db->quoteName('name') . ' = ' . $db->quote('com_proclaim'));

        $db->setQuery($query);
        $asset = $db->loadObject();

        if (!$asset) {
            return;
        }

        $rules = json_decode($asset->rules ?? '{}', true) ?: [];

        // Preset definitions — actions and their allowed values
        $presets = [
            'full' => [
                'core.manage'   => 1, 'core.create' => 1, 'core.edit' => 1,
                'core.edit.own' => 1, 'core.edit.state' => 1, 'core.delete' => 1,
            ],
            'editor' => [
                'core.manage'   => 1, 'core.create' => 1, 'core.edit' => 1,
                'core.edit.own' => 1,
            ],
            'viewer' => [
                'core.manage'   => 1, 'core.create' => 1, 'core.edit.own' => 1,
            ],
        ];

        foreach ($permissions as $groupId => $preset) {
            if ($preset === 'none' || !isset($presets[$preset])) {
                continue;
            }

            $groupStr = (string) $groupId;

            foreach ($presets[$preset] as $action => $value) {
                if (!isset($rules[$action])) {
                    $rules[$action] = [];
                }

                $rules[$action][$groupStr] = $value;
            }
        }

        // Save updated rules
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__assets'))
            ->set($db->quoteName('rules') . ' = ' . $db->quote(json_encode($rules)))
            ->where($db->quoteName('id') . ' = ' . (int) $asset->id);

        $db->setQuery($query);
        $db->execute();
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
