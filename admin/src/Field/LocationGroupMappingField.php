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

namespace CWM\Component\Proclaim\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/**
 * Location-to-user-group mapping field.
 *
 * Renders a table with one row per published location.  Each row shows the
 * location name, its message count, and a set of checkboxes for every
 * published Joomla user group.  Checking a group checkbox means "members of
 * this group can see content at this location".
 *
 * The value is stored as a JSON object: { "locationId": [groupId, ...], ... }
 * Locations with no checked groups are omitted (they become "unrestricted").
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class LocationGroupMappingField extends FormField
{
    /**
     * @var string
     * @since 10.1.0
     */
    protected $type = 'LocationGroupMapping';

    /**
     * Render the mapping table HTML.
     *
     * @return  string
     *
     * @since   10.1.0
     */
    #[\Override]
    public function getInput(): string
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Load published locations with message counts
        $locQuery = $db->createQuery()
            ->select([
                $db->quoteName('l.id'),
                $db->quoteName('l.location_text'),
                'COUNT(' . $db->quoteName('s.id') . ') AS message_count',
            ])
            ->from($db->quoteName('#__bsms_locations', 'l'))
            ->join(
                'LEFT',
                $db->quoteName('#__bsms_studies', 's')
                . ' ON ' . $db->quoteName('s.location_id') . ' = ' . $db->quoteName('l.id')
            )
            ->where($db->quoteName('l.published') . ' = 1')
            ->group($db->quoteName('l.id'))
            ->order($db->quoteName('l.location_text'));

        $db->setQuery($locQuery);
        $locations = $db->loadObjectList() ?: [];

        if (empty($locations)) {
            return '<p class="alert alert-info">' . Text::_('JBS_CONFIG_LOCATION_NO_LOCATIONS') . '</p>';
        }

        // Load all published user groups
        $groupQuery = $db->createQuery()
            ->select([$db->quoteName('id'), $db->quoteName('title')])
            ->from($db->quoteName('#__usergroups'))
            ->order($db->quoteName('lft'));

        $db->setQuery($groupQuery);
        $groups = $db->loadObjectList() ?: [];

        // Parse current mapping value
        $currentMapping = [];

        if (!empty($this->value)) {
            if (\is_string($this->value)) {
                try {
                    $decoded = json_decode($this->value, true, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException) {
                    $decoded = null;
                }
            } else {
                $decoded = $this->value;
            }

            if (\is_array($decoded)) {
                $currentMapping = $decoded;
            }
        }

        $fieldName = $this->getName($this->fieldname);
        $fieldId   = $this->id;

        // Build the HTML table
        $html  = '<div class="cwm-location-mapping" id="' . $this->escape($fieldId) . '">';
        $html .= '<table class="table table-sm table-bordered">';
        $html .= '<thead><tr>';
        $html .= '<th>' . Text::_('JBS_CMN_LOCATION') . '</th>';
        $html .= '<th class="text-center">' . Text::_('JBS_CMN_MESSAGES') . '</th>';
        $html .= '<th>' . Text::_('JBS_CONFIG_LOCATION_USER_GROUPS') . '</th>';
        $html .= '</tr></thead>';
        $html .= '<tbody>';

        foreach ($locations as $location) {
            $locId      = (int) $location->id;
            $mappedGids = array_map('intval', $currentMapping[(string) $locId] ?? []);

            $html .= '<tr>';
            $html .= '<td><strong>' . $this->escape($location->location_text) . '</strong></td>';
            $html .= '<td class="text-center">' . (int) $location->message_count . '</td>';
            $html .= '<td>';

            // Hidden zero-value field so unchecked locations produce an empty array
            $html .= '<input type="hidden" name="' . $this->escape($fieldName)
                . '[' . $locId . '][]" value="" />';

            $html .= '<div class="d-flex flex-wrap gap-2">';

            foreach ($groups as $group) {
                $gid     = (int) $group->id;
                $checked = \in_array($gid, $mappedGids, true) ? ' checked' : '';
                $cbId    = $this->escape($fieldId) . '_loc' . $locId . '_grp' . $gid;

                $html .= '<div class="form-check form-check-inline">';
                $html .= '<input class="form-check-input" type="checkbox"'
                    . ' id="' . $cbId . '"'
                    . ' name="' . $this->escape($fieldName) . '[' . $locId . '][]"'
                    . ' value="' . $gid . '"'
                    . $checked . ' />';
                $html .= '<label class="form-check-label" for="' . $cbId . '">'
                    . $this->escape($group->title) . '</label>';
                $html .= '</div>';
            }

            $html .= '</div></td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        // Explanation note
        $html .= '<p class="form-text text-body-secondary mt-2">'
            . Text::_('JBS_CONFIG_LOCATION_MAPPING_NOTE') . '</p>';

        $html .= '</div>';

        // JSON serialisation: a small inline script converts the checkbox arrays
        // to the JSON format expected by the component params on save.
        $this->registerSerializationScript($fieldName, $fieldId);

        return $html;
    }

    /**
     * Return the stored value; the form submits checkbox arrays which must
     * be converted to JSON by the serialisation script on the client.
     *
     * @return  string
     *
     * @since   10.1.0
     */
    public function getValue(): string
    {
        return \is_string($this->value) ? $this->value : json_encode($this->value ?? new \stdClass());
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Escape a string for safe use in HTML output.
     *
     * @param   string  $value  Raw value.
     *
     * @return  string
     *
     * @since   10.1.0
     */
    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Register the inline script that serialises the checkbox arrays to JSON
     * before the config form is submitted, via WebAssetManager rather than a
     * raw <script> tag concatenated into the returned HTML.
     *
     * The component config form POSTs all fields; this script intercepts submit
     * and writes a single JSON string into a hidden input for the mapping field.
     * Registering it via WebAssetManager (instead of emitting it inline at the
     * point getInput() happens to run) is safe here because the whole handler
     * is gated on DOMContentLoaded -- it looks up #adminForm and the field
     * wrapper by id at ready-time, so where in the document the <script> tag
     * itself ends up rendered does not matter.
     *
     * @param   string  $fieldName  The field name attribute (e.g. "jform[location_group_mapping]").
     * @param   string  $fieldId    The field wrapper element ID.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function registerSerializationScript(string $fieldName, string $fieldId): void
    {
        // Encode the field name for use in JavaScript string literals
        $jsFieldName = json_encode($fieldName, JSON_UNESCAPED_SLASHES);
        $jsFieldId   = json_encode($fieldId, JSON_UNESCAPED_SLASHES);

        $js = <<<JS
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('adminForm');
        if (!form) { return; }

        form.addEventListener('submit', function () {
            var wrapper = document.getElementById({$jsFieldId});
            if (!wrapper) { return; }

            var mapping = {};
            wrapper.querySelectorAll('input[type="checkbox"]:checked').forEach(function (cb) {
                var matches = cb.name.match(/\[(\d+)\]\[\]$/);
                if (!matches) { return; }
                var locId = matches[1];
                if (!mapping[locId]) { mapping[locId] = []; }
                mapping[locId].push(parseInt(cb.value, 10));
            });

            // Replace checkbox inputs with a single hidden JSON field
            var existing = form.querySelector('input[type="hidden"][data-cwm-location-mapping="1"]');
            if (!existing) {
                existing = document.createElement('input');
                existing.type = 'hidden';
                existing.name = {$jsFieldName};
                existing.setAttribute('data-cwm-location-mapping', '1');
                form.appendChild(existing);
            }
            existing.value = JSON.stringify(mapping);

            // Disable the original checkboxes so they don't double-submit
            wrapper.querySelectorAll('input[type="checkbox"], input[type="hidden"]').forEach(function (el) {
                el.disabled = true;
            });
        });
    });
}());
JS;

        Factory::getApplication()->getDocument()->getWebAssetManager()->addInlineScript($js);
    }
}
