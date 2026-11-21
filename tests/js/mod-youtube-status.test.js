'use strict';

/**
 * Tests for mod-youtube-status.es6.js
 * The YouTube module's live-status poll, as com_ajax answers it: the helper's
 * status is the data of a JsonResponse ({success, message, messages, data}).
 *
 * @package  Proclaim.Tests
 * @since    10.7.4
 */

require('./setup');

const assert = require('node:assert/strict');
const { describe, test, beforeEach, afterEach, mock } = require('node:test');
const { fn, resolving, spyOn, resetModules, advanceTimersByTimeAsync } = require('./helpers/mocks');
const { includes } = require('./helpers/assert');

describe('mod-youtube-status', () => {
    const pollInterval = 1000;

    beforeEach(() => {
        resetModules();
        mock.timers.enable({ apis: ['setTimeout', 'setInterval', 'Date'], now: Date.now() });

        global.Joomla = {
            getOptions: fn((key) => (key === 'mod_proclaim_youtube'
                ? [{
                    moduleId: 7,
                    pollInterval,
                    maxPollInterval: 8000,
                    pollWindowBefore: 2,
                    pollWindowAfter: 1,
                }]
                : undefined)),
        };

        const startsInHalfAnHour = new Date(Date.now() + 30 * 60000).toISOString();

        document.body.innerHTML = `
            <div id="mod-proclaim-youtube-badge-7"
                 data-server-id="6"
                 data-current-video="abc123"
                 data-is-live="0"
                 data-is-upcoming="1"
                 data-scheduled-start="${startsInHalfAnHour}"
                 data-token="t"
                 data-ajax-url="/index.php?option=com_ajax&amp;module=proclaim_youtube&amp;method=getStatus&amp;format=json"
                 data-label-live="Live now"
                 data-label-upcoming="Upcoming"><span>Upcoming</span></div>`;

        global.fetch = fn();
    });

    afterEach(() => {
        mock.timers.reset();
    });

    /**
     * Load the script and run its ready handler once. The handler is called
     * directly rather than by dispatching DOMContentLoaded, which would also
     * run the handlers earlier tests loaded and start a poller for each.
     */
    function load() {
        const addListener = spyOn(document, 'addEventListener');

        require('../../build/media_source/js/mod-youtube-status.es6.js');

        const ready = addListener.mock.calls.find(({ arguments: [type] }) => type === 'DOMContentLoaded').arguments[1];

        addListener.mock.restore();
        ready();
    }

    function respondWith(body) {
        global.fetch.mock.mockImplementation(resolving({ json: () => Promise.resolve(body) }));
    }

    // Wait out one poll interval and let the request's promises settle.
    async function poll() {
        await advanceTimersByTimeAsync(pollInterval);
    }

    function badge() {
        return document.getElementById('mod-proclaim-youtube-badge-7');
    }

    test('reads the status from the data com_ajax returns, and shows the video as live', async () => {
        respondWith({
            success: true,
            message: null,
            messages: null,
            data: { success: true, isLive: true, isUpcoming: false, videoId: 'abc123', quotaRemaining: 9000 },
        });

        load();
        await poll();

        assert.strictEqual(global.fetch.mock.callCount(), 1);
        includes(badge().innerHTML, 'Live now');
    });

    test('keeps polling while the status has not changed', async () => {
        respondWith({
            success: true,
            message: null,
            messages: null,
            data: { success: true, isLive: false, isUpcoming: true, videoId: 'abc123', quotaRemaining: 9000 },
        });

        load();
        await poll();
        await poll();

        assert.strictEqual(global.fetch.mock.callCount(), 2);
        includes(badge().innerHTML, 'Upcoming');
    });

    test('stops when com_ajax could not run the helper', async () => {
        respondWith({
            success: false,
            message: 'The file at mod_proclaim_youtube/helper.php does not exist.',
            messages: null,
            data: null,
        });

        load();
        await poll();
        await advanceTimersByTimeAsync(20000);

        assert.strictEqual(global.fetch.mock.callCount(), 1);
        includes(badge().innerHTML, 'Upcoming');
    });

    test('stops when the helper refuses the request', async () => {
        respondWith({
            success: true,
            message: null,
            messages: null,
            data: { success: false, error: 'Invalid token' },
        });

        load();
        await poll();
        await advanceTimersByTimeAsync(20000);

        assert.strictEqual(global.fetch.mock.callCount(), 1);
        includes(badge().innerHTML, 'Upcoming');
    });
});
