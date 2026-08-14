/**
 * Opens Proclaim's media links in a sized popup window.
 *
 * These controls used to be `<a href="javascript:;" onclick="window.open(…)">`.
 * Both halves were a problem: a `javascript:` URL and an inline handler are
 * blocked by any Content-Security-Policy worth setting, which left a
 * CSP-hardened site unable to play anything, and an anchor whose href goes
 * nowhere cannot be middle-clicked, copied, or opened in a new tab, and is
 * announced to assistive technology as a link to a destination that does not
 * exist.
 *
 * They now carry the real URL in href and the window size in data attributes.
 * Without this script the link still works — it opens in the same tab — so the
 * popup is an enhancement rather than the only way through.
 *
 * @since 10.5.8
 */
((document, window) => {
    'use strict';

    /**
     * A modifier click means the visitor asked for a tab or window of their
     * own choosing, and that intent outranks ours.
     *
     * @param   {MouseEvent}  event  The click.
     * @returns {boolean}
     */
    const wantsOwnWindow = event => event.defaultPrevented
        || event.button !== 0
        || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey;

    /**
     * Only ever hand http(s) to window.open.
     *
     * ⚠️ The value comes from the DOM, and window.open('javascript:…') runs in the
     * opener's context. Relative values resolve against the page first, so an
     * ordinary index.php?… link still passes.
     *
     * @param   {string}  value  The href as written in the document.
     * @returns {string|null}    A safe absolute URL, or null to leave the click alone.
     */
    const safeUrl = (value) => {
        // Empty or a bare fragment names no destination. Both resolve to the
        // current page, so without this the control would pop open a copy of
        // the page it is already on.
        if (value === '' || value.charAt(0) === '#') {
            return null;
        }

        try {
            const resolved = new URL(value, window.location.href);

            return (resolved.protocol === 'http:' || resolved.protocol === 'https:')
                ? resolved.href
                : null;
        } catch {
            return null;
        }
    };

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-proclaim-popup]');

        if (!trigger || wantsOwnWindow(event)) {
            return;
        }

        const url = safeUrl(trigger.getAttribute('href') || '');

        // Nothing safe to open, so let the browser do whatever it would have
        // done — which for a scheme we refuse is the browser's own handling,
        // not ours.
        if (!url) {
            return;
        }

        const width = parseInt(trigger.getAttribute('data-popup-width'), 10);
        const height = parseInt(trigger.getAttribute('data-popup-height'), 10);
        const features = (width > 0 && height > 0) ? `width=${width},height=${height}` : '';

        const opened = window.open(url, 'proclaimmedia', features);

        // A blocked popup must not swallow the click: leaving the event alone
        // lets the browser follow the href, which is the same destination.
        if (opened) {
            event.preventDefault();
            opened.focus();
        }
    });
})(document, window);
