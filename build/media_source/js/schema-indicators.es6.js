/**
 * Schema.org custom field indicators.
 *
 * Adds visual badges to schema fields that have been manually customized
 * by the admin. These fields won't auto-update via Smart Sync until the
 * user restores them to the auto-generated value.
 *
 * Reads from Joomla.getOptions('com_proclaim.schemaCustomFields') which
 * is an array of field names like ['headline', 'description'].
 *
 * @package    Proclaim.Admin
 * @subpackage Schema.org
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @since      10.2.1
 */
(() => {
    'use strict';

    function init() {
        const customFields = Joomla.getOptions('com_proclaim.schemaCustomFields') || [];

        if (!customFields.length) {
            return;
        }

        // Find the schema subform container (Sermon, Teacher, or Series)
        const schemaGroups = document.querySelectorAll('[data-subform-name="schema"]');

        if (!schemaGroups.length) {
            // Fallback: search within #jform_schema container
            applyIndicators(document, customFields);

            return;
        }

        schemaGroups.forEach(function (group) {
            applyIndicators(group, customFields);
        });
    }

    function applyIndicators(container, customFields) {
        customFields.forEach(function (fieldName) {
            // Schema subform fields use names like jform[schema][Sermon][headline]
            // Find by partial name match
            const inputs = container.querySelectorAll(
                '[name*="[' + fieldName + ']"], ' +
                '#jform_schema_Sermon_' + fieldName + ', ' +
                '#jform_schema_Teacher_' + fieldName + ', ' +
                '#jform_schema_Series_' + fieldName
            );

            inputs.forEach(function (input) {
                // Find the parent control group
                const controlGroup = input.closest('.control-group') || input.closest('.form-group');

                if (!controlGroup || controlGroup.dataset.schemaIndicator) {
                    return;
                }

                controlGroup.dataset.schemaIndicator = 'custom';

                // Add badge to the label
                const label = controlGroup.querySelector('label, .control-label, .form-label');

                if (label) {
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-info ms-2';
                    badge.style.fontSize = '0.7em';
                    badge.style.verticalAlign = 'middle';
                    badge.textContent = Joomla.Text._('PLG_SCHEMAORG_PROCLAIM_BADGE_CUSTOM') || 'Custom';
                    badge.title = Joomla.Text._('PLG_SCHEMAORG_PROCLAIM_BADGE_CUSTOM_DESC')
                        || 'This field was manually customized and won\'t auto-update. Clear the field to restore auto-sync.';
                    label.appendChild(badge);
                }

                // Add subtle left border to the control group
                controlGroup.style.borderLeft = '3px solid var(--bs-info, #0dcaf0)';
                controlGroup.style.paddingLeft = '0.75rem';
            });
        });
    }

    document.addEventListener('DOMContentLoaded', init);

    // Also handle Joomla subform lazy loading
    document.addEventListener('subform-row-add', function () {
        setTimeout(init, 100);
    });
})();
