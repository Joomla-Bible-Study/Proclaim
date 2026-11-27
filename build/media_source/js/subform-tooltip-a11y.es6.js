/**
 * @package     Proclaim
 * @subpackage  JavaScript
 * @copyright   (C) 2026 CWM Team All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

'use strict';

/**
 * Gives the tooltip icons in subform column headers a valid accessible name.
 *
 * `layouts/joomla/form/field/subform/repeatable-table.php` puts an icon and a
 * tooltip in the header of any subform column with a description. Joomla 5.4
 * and 6.1 render the icon focusable yet hidden from assistive technology, which
 * axe reports as `aria-hidden-focus`:
 *
 *     <span class="icon-info-circle" aria-hidden="true" tabindex="0"></span>
 *     <div role="tooltip" id="tip-jform_scriptures-…">Help text</div>
 *
 * Joomla 6.2 names the icon from its tooltip instead (joomla/joomla-cms#48499),
 * but on a span with no role, which axe reports as `aria-prohibited-attr`:
 *
 *     <span class="icon-info-circle" aria-labelledby="tip-…" tabindex="0"></span>
 *
 * Both fail WCAG 4.1.2. Either one becomes 6.2's design with the role it lacks:
 *
 *     <span class="icon-info-circle" role="img" aria-labelledby="tip-…" tabindex="0"></span>
 *
 * The icon stays focusable, because core shows the tooltip on the icon's
 * :focus, and a screen reader announces the tooltip text as the icon's name.
 * An icon that already has a role is left as it is, so this stops acting once
 * core gives it one.
 *
 * Chosen over overriding the layout in admin/layouts/. The override would mean
 * carrying a 125-line copy of a core file across Joomla versions, going quietly
 * stale whenever Joomla edits the original. This touches only the attributes
 * that are wrong.
 */
(() => {
    /**
     * Name one header icon from the tooltip that follows it.
     *
     * @param   {HTMLElement}  icon  A tooltip icon in a subform column header.
     *
     * @returns {void}
     */
    const repair = (icon) => {
        const tooltip = icon.nextElementSibling;

        // A role means core has already made the icon valid.
        if (icon.hasAttribute('role') || !tooltip || tooltip.getAttribute('role') !== 'tooltip' || !tooltip.id) {
            return;
        }

        icon.removeAttribute('aria-hidden');
        icon.setAttribute('aria-labelledby', tooltip.id);
        icon.setAttribute('role', 'img');
    };

    /**
     * Repair every tooltip icon in the subform column headers under a node.
     *
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
