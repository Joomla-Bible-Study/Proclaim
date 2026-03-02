/**
 * Timestamp seek handler for sermon study text
 *
 * Delegated click handler for `.cwm-timestamp` links embedded in study text.
 * Uses the YouTube IFrame postMessage API to seek the video to the specified time.
 *
 * @package  Proclaim
 * @since    10.1.0
 */
(() => {
    'use strict';

    document.addEventListener('click', (e) => {
        const link = e.target.closest('.cwm-timestamp');

        if (!link) {
            return;
        }

        e.preventDefault();
        const seconds = parseInt(link.dataset.seconds, 10);

        if (Number.isNaN(seconds)) {
            return;
        }

        // Find YouTube iframe on the page and seek via postMessage API
        const iframe = document.querySelector('iframe[src*="youtube.com"]');

        if (iframe) {
            iframe.contentWindow.postMessage(JSON.stringify({
                event: 'command',
                func: 'seekTo',
                args: [seconds, true],
            }), '*');
        }
    });
})();
