/**
 * @package     Proclaim
 * @subpackage  JavaScript
 * @copyright   (C) 2026 CWM Team All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

'use strict';

/**
 * Repairs the modal iframes Joomla inserts without an accessible name.
 *
 * `bootstrap.renderModal` renders its iframe template with both `name` and
 * `title` (layouts/libraries/html/bootstrap/modal/iframe.php sets the two
 * together), but the client-side code that injects the live iframe when the
 * modal opens rebuilds it from the template and drops `title` on the floor.
 * axe reports the result as `frame-title` (WCAG 4.1.2): a screen reader
 * announcing the frame has nothing to call it.
 *
 * Seen on every Proclaim modal-picker field (e.g. the server-type selector on
 * the server form). The name attribute survives the trip, so the repair is to
 * copy it back into title. No-ops on iframes that already carry one, so this
 * retires silently if Joomla fixes the injection.
 */
(() => {
    /**
     * @param   {HTMLIFrameElement}  frame  The iframe missing its title.
     *
     * @returns {void}
     */
    const repair = (frame) => {
        if (!frame.hasAttribute('title') && frame.getAttribute('name')) {
            frame.setAttribute('title', frame.getAttribute('name'));
        }
    };

    /**
     * @param   {ParentNode}  root  Subtree to repair.
     *
     * @returns {void}
     */
    const repairWithin = (root) => {
        root.querySelectorAll('.modal-body iframe[name]:not([title])').forEach(repair);
    };

    document.addEventListener('DOMContentLoaded', () => {
        repairWithin(document);

        // The live iframe is injected when its modal first opens, so the page
        // is compliant until the administrator clicks the picker. Watch the
        // modals themselves rather than the whole document.
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        if (node.matches && node.matches('iframe[name]:not([title])')) {
                            repair(node);
                        } else if (node.querySelectorAll) {
                            repairWithin(node);
                        }
                    }
                });
            });
        });

        document
            .querySelectorAll('.joomla-modal, .modal')
            .forEach((modal) => observer.observe(modal, { childList: true, subtree: true }));
    });
})();
