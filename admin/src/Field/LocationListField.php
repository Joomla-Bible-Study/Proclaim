<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmfilterHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmlocationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/**
 * Location List Form Field class for the Proclaim component.
 *
 * Role-aware behaviour:
 * - Super admins see all published locations, no auto-default.
 * - Single-campus users see their location as read-only text.
 * - Multi-campus users see a dropdown limited to their accessible locations with auto-default.
 * - When the location system is disabled, all users see every location (original behaviour).
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class LocationListField extends ListField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 7.0
     */
    protected $type = 'LocationList';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array   An array of JHtml options.
     *
     * @since 7.0
     */
    #[\Override]
    protected function getOptions(): array
    {
        $app       = Factory::getApplication();
        $user      = $app->getIdentity();
        $isAdmin   = $user->authorise('core.admin');
        $enabled   = CwmlocationHelper::isEnabled();
        $currentId = (int) $this->value;

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Frontend filter: only locations used by published, accessible messages
        if ($app->isClient('site')) {
            $groups = $user->getAuthorisedViewLevels();
            $query  = $db->getQuery(true)
                ->select('DISTINCT ' . $db->quoteName('loc.id') . ', ' . $db->quoteName('loc.location_text'))
                ->from($db->quoteName('#__bsms_locations', 'loc'))
                ->join(
                    'INNER',
                    $db->quoteName('#__bsms_studies', 's') . ' ON '
                    . $db->quoteName('s.location_id') . ' = ' . $db->quoteName('loc.id')
                )
                ->where($db->quoteName('loc.published') . ' = 1')
                ->whereIn($db->quoteName('s.published'), [1, 2])
                ->whereIn($db->quoteName('s.access'), $groups)
                ->order($db->quoteName('loc.location_text'));

            CwmfilterHelper::applyCrossFilters($query, 'location');

            $db->setQuery($query);
            $rows    = $db->loadObjectList() ?: [];
            $options = parent::getOptions();

            foreach ($rows as $row) {
                $options[] = HTMLHelper::_('select.option', (int) $row->id, $row->location_text);
            }

            return $options;
        }

        // Admin: show all published locations with campus-aware filtering
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('location_text')])
            ->from($db->quoteName('#__bsms_locations'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('location_text'));
        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];

        // Determine accessible location IDs for non-admin users when filtering is on
        $allowedIds = [];

        if ($enabled && !$isAdmin) {
            $allowedIds = CwmlocationHelper::getUserAccessibleLocationsForEdit(0, $currentId);
        }

        // Check if this field allows "global" (no location) — e.g. podcasts, servers
        $allowGlobal = ((string) ($this->element['global'] ?? '')) === 'true';

        // Auto-default for NEW records only (non-admin, location system enabled).
        // Skip when global is allowed — NULL/-1 is a valid "all campuses" choice.
        // Never auto-default for existing records — prevents silently reassigning
        // an unlocated record to the first editor's campus on save.
        $isNewRecord = empty($this->form->getValue('id'));

        if ($isNewRecord && empty($this->value) && $enabled && !$isAdmin && !empty($allowedIds) && !$allowGlobal) {
            $userLocations = CwmlocationHelper::getUserLocations();

            if (\count($userLocations) >= 1) {
                $this->value = $userLocations[0];
            }
        }

        $options = parent::getOptions();

        foreach ($rows as $row) {
            $id = (int) $row->id;

            if ($enabled && !$isAdmin && !empty($allowedIds)) {
                if (!\in_array($id, $allowedIds, true)) {
                    if ($id !== $currentId) {
                        continue;
                    }

                    // Show inaccessible current value as disabled so it is not silently lost
                    $opt          = HTMLHelper::_('select.option', $id, $row->location_text);
                    $opt->disable = true;
                    $options[]    = $opt;

                    continue;
                }
            }

            $options[] = HTMLHelper::_('select.option', $id, $row->location_text);
        }

        return $options;
    }

    /**
     * Render the field input.
     *
     * For single-campus users the location is shown as read-only text with a
     * hidden input so the value still submits with the form.
     *
     * @return  string  The field input markup.
     *
     * @since   10.1.0
     */
    #[\Override]
    protected function getInput(): string
    {
        $user    = Factory::getApplication()->getIdentity();
        $enabled = CwmlocationHelper::isEnabled();

        $allowGlobal = ((string) ($this->element['global'] ?? '')) === 'true';

        if ($enabled && !$user->authorise('core.admin') && !$allowGlobal) {
            $userLocations = CwmlocationHelper::getUserLocations();

            if (\count($userLocations) === 1) {
                $locationId  = (int) $userLocations[0];
                $isNewRecord = empty($this->form->getValue('id'));
                $currentVal  = (int) $this->value;

                // Show read-only for new records (auto-assign) or existing records
                // already at this campus.  Never force a campus on existing records
                // that currently have no location — show the dropdown instead.
                if ($isNewRecord || $currentVal === $locationId) {
                    $db    = Factory::getContainer()->get(DatabaseInterface::class);
                    $query = $db->getQuery(true)
                        ->select($db->quoteName('location_text'))
                        ->from($db->quoteName('#__bsms_locations'))
                        ->where($db->quoteName('id') . ' = ' . $locationId);
                    $db->setQuery($query);
                    $name = $db->loadResult() ?: Text::_('JBS_CMN_LOCATION');

                    $html  = '<input type="text" value="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" '
                           . 'class="form-control" readonly disabled />';
                    $html .= '<input type="hidden" name="' . $this->name . '" '
                           . 'value="' . $locationId . '" />';

                    return $html;
                }
            }
        }

        return parent::getInput();
    }
}
