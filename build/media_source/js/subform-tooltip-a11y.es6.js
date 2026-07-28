/**
 * @package     Proclaim
 * @subpackage  JavaScript
 * @copyright   (C) 2026 CWM Team All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

'use strict';

/**
 * Repairs the subform header tooltips Joomla renders inaccessibly.
 *
 * `layouts/joomla/form/field/subform/repeatable-table.php` emits this for any
 * subform field carrying a description:
 *
 *     <span class="icon-info-circle" aria-hidden="true" tabindex="0"></span>
 *     <div role="tooltip" id="tip-jform_scriptures-…">Help text</div>
 *
 * The span is hidden from assistive technology and focusable at the same time,
 * which axe reports as `aria-hidden-focus` (WCAG 4.1.2). A keyboard user tabs
 * onto something a screen reader refuses to announce, and the tooltip it points
 * at is never associated with the column it describes — so the help text is
 * unreachable either way.
 *
 * Confirmed present in Joomla 5.4.7, 6.1.2, 6.2.0 and 7.0.0, and only in the
 * repeatable-table layout. Reported upstream as joomla/joomla-cms#48151
 * (https://github.com/joomla/joomla-cms/issues/48151); this runs until that
 * lands and the oldest supported Joomla carries it. Tracked in #1341.
 *
 * Chosen over overriding the layout in admin/layouts/. The override would mean
 * carrying a 125-line copy of a core file across four Joomla versions, going
 * quietly stale whenever Joomla edits the original. This touches only the two
 * attributes that are wrong, and turns itself into a no-op the day the fix
 * ships upstream — nothing to remember to remove.
 */
(() => {
    /**
     * Associate one tooltip with the header cell it describes.
     *
     * @param   {HTMLElement}  icon  The offending icon span.
     *
     * @returns {void}
     */
    const repair = (icon) => {
        // Leave it alone if Joomla has fixed this: no tabindex means nothing
        // focusable is hidden, and there is nothing for us to do.
        if (!icon.hasAttribute('tabindex')) {
            return;
        }

        // The icon is decorative. Keeping aria-hidden is correct; being
        // focusable is not.
        icon.removeAttribute('tabindex');

        const tooltip = icon.nextElementSibling;

        if (!tooltip || tooltip.getAttribute('role') !== 'tooltip') {
            return;
        }

        // The description belongs to the header cell, so the column's
        // accessible description carries it and screen readers announce the
        // help text with the column name.
        const header = icon.closest('th');

        if (!header || !tooltip.id) {
            return;
        }

        const described = (header.getAttribute('aria-describedby') || '')
            .split(/\s+/)
            .filter(Boolean);

        if (!described.includes(tooltip.id)) {
            described.push(tooltip.id);
            header.setAttribute('aria-describedby', described.join(' '));
        }
    };

    /**
     * @param   {ParentNode}  root  Subtree to repair.
     *
     * @returns {void}
     */
    const repairWithin = (root) => {
        root
            .querySelectorAll('.subform-repeatable-group th .icon-info-circle, table[id^="subfieldList_"] th .icon-info-circle')
            .forEach(repair);
    };

    document.addEventListener('DOMContentLoaded', () => {
        repairWithin(document);

        // Subform rows are added at runtime by joomla-field-subform, and a new
        // nested subform brings its own header with it. Without this, the page
        // is compliant until the administrator clicks "add".
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        repairWithin(node);
                    }
                });
            });
        });

        document
            .querySelectorAll('joomla-field-subform')
            .forEach((field) => observer.observe(field, { childList: true, subtree: true }));
    });
})();
