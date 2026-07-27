/**
 * @package     Proclaim
 * @subpackage  JavaScript
 * @copyright   (C) 2026 CWM Team All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

'use strict';

/**
 * Non-drag reordering for Proclaim's admin lists (WCAG 2.2 SC 2.5.7).
 *
 * Joomla 4+ list ordering is drag-only: the ordering column renders a
 * `.sortable-handler` grip and nothing else, so a keyboard user — or any
 * single-pointer user who cannot drag — cannot reorder at all. (Joomla 3's
 * up/down arrows did not survive the rewrite.) SC 2.5.7 requires a
 * single-pointer, non-drag alternative for every dragging operation.
 *
 * This adds Move Up / Move Down buttons beside the grip on every Proclaim
 * list with ordering active, and persists the change through the same
 * endpoint the drag path uses: the container's saveOrderAjax URL, with the
 * form's data plus the recomputed order[] / cid[] pairs — mirroring
 * media/system/js/draggable.js, whose functions are closure-private and
 * cannot be called directly.
 *
 * Both of Joomla's ordering wirings converge here: tables rendered with a
 * `js-draggable` tbody carry data-url/data-direction attributes; tables
 * wired via HTMLHelper sortablelist publish the same values through the
 * 'draggable-list' script options. Both are handled.
 */
(() => {
    /**
     * Resolve the ordering container and its endpoint, the same two ways
     * core draggable.js does.
     *
     * @returns {{container: HTMLElement, url: string}|null}
     */
    const resolveContainer = () => {
        const options = (window.Joomla && Joomla.getOptions('draggable-list')) || null;

        // The container may exist under either wiring; the URL only lives
        // where that wiring put it. A sortablelist-wired table's tbody GAINS
        // the js-draggable class at runtime (core adds it) but never carries
        // data-url — the endpoint is in the script options.
        let container = document.querySelector('.js-draggable');

        if (!container && options) {
            container = document.querySelector(options.id);
        }

        const url = container ? (container.dataset.url || (options && options.url)) : null;

        if (!container || !url) {
            return null;
        }

        return { container, url };
    };

    /**
     * Post the current row order to the saveOrderAjax endpoint — the same
     * payload the drag path sends: the form's fields (minus task and any
     * stale order[]) plus one order[]/cid[] pair per row in DOM order.
     *
     * @param {HTMLElement} container
     * @param {string}      url
     *
     * @returns {void}
     */
    const persistOrder = (container, url) => {
        const form = container.closest('form');

        if (!form) {
            return;
        }

        const formData = new FormData(form);
        formData.delete('task');
        formData.delete('order[]');

        const pairs = [];
        container.querySelectorAll('[name="order[]"]').forEach((input) => {
            pairs.push(`order[]=${encodeURIComponent(input.value)}`);
        });
        container.querySelectorAll('[name="cid[]"]').forEach((input) => {
            pairs.push(`cid[]=${encodeURIComponent(input.value)}`);
        });

        Joomla.request({
            url,
            method: 'POST',
            data: `${new URLSearchParams(formData).toString()}&${pairs.join('&')}`,
            perform: true,
        });
    };

    /**
     * Move a row one position up or down, keep the order values attached
     * to the positions (rows move, position values stay), and persist.
     *
     * @param {HTMLElement} row        The <tr> to move.
     * @param {number}      direction  -1 up, +1 down.
     * @param {HTMLElement} container  The tbody.
     * @param {string}      url        saveOrderAjax endpoint.
     * @param {HTMLElement} announcer  aria-live region.
     *
     * @returns {void}
     */
    const moveRow = (row, direction, container, url, announcer) => {
        const sibling = direction < 0 ? row.previousElementSibling : row.nextElementSibling;

        if (!sibling) {
            return;
        }

        // Capture the position→value sequence before the move…
        const values = Array.from(container.querySelectorAll('[name="order[]"]'), (i) => i.value);

        if (direction < 0) {
            container.insertBefore(row, sibling);
        } else {
            container.insertBefore(sibling, row);
        }

        // …and re-stamp it in the new DOM order, so each position keeps its
        // ordering value and the rows carry their ids to new positions —
        // exactly the invariant the drag path maintains.
        container.querySelectorAll('[name="order[]"]').forEach((input, i) => {
            input.value = values[i];
        });

        persistOrder(container, url);

        // Keep focus on the button that was pressed — it moved with the row.
        const focusTarget = row.querySelector(direction < 0 ? '.proclaim-order-up' : '.proclaim-order-down');

        if (focusTarget) {
            focusTarget.focus();
        }

        const rows = Array.from(container.children);
        const position = rows.indexOf(row) + 1;
        announcer.textContent = Joomla.Text._('JBS_CMN_ORDER_MOVED', 'Moved to position %d of %d')
            .replace('%d', String(position))
            .replace('%d', String(rows.length));
    };

    document.addEventListener('DOMContentLoaded', () => {
        const resolved = resolveContainer();

        if (!resolved) {
            return;
        }

        const { container, url } = resolved;

        const announcer = document.createElement('div');
        announcer.setAttribute('aria-live', 'polite');
        announcer.className = 'visually-hidden';
        container.closest('table')?.insertAdjacentElement('afterend', announcer);

        const upLabel = Joomla.Text._('JBS_CMN_ORDER_MOVE_UP', 'Move up');
        const downLabel = Joomla.Text._('JBS_CMN_ORDER_MOVE_DOWN', 'Move down');

        // DOM construction rather than innerHTML: the labels are translation
        // strings, and attribute assignment needs no escaping thought at all.
        const makeButton = (className, iconClass, label) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = className;
            button.setAttribute('aria-label', label);
            button.title = label;

            const icon = document.createElement('span');
            icon.className = iconClass;
            icon.setAttribute('aria-hidden', 'true');
            button.appendChild(icon);

            return button;
        };

        container.querySelectorAll('tr').forEach((row) => {
            const handle = row.querySelector('.sortable-handler');

            if (!handle) {
                return;
            }

            const group = document.createElement('span');
            group.className = 'proclaim-order-buttons';
            group.appendChild(makeButton('proclaim-order-up', 'icon-chevron-up', upLabel));
            group.appendChild(makeButton('proclaim-order-down', 'icon-chevron-down', downLabel));
            handle.insertAdjacentElement('afterend', group);
        });

        container.addEventListener('click', (e) => {
            const up = e.target.closest('.proclaim-order-up');
            const down = e.target.closest('.proclaim-order-down');

            if (!up && !down) {
                return;
            }

            const row = e.target.closest('tr');

            if (row) {
                moveRow(row, up ? -1 : 1, container, url, announcer);
            }
        });
    });
})();
