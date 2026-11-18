/**
 * Tests for mod-youtube-status.es6.js
 * The YouTube module's live-status poll, as com_ajax answers it: the helper's
 * status is the data of a JsonResponse ({success, message, messages, data}).
 *
 * @package  Proclaim.Tests
 * @since    __DEPLOY_VERSION__
 */

describe('mod-youtube-status', () => {
    const pollInterval = 1000;

    beforeEach(() => {
        jest.resetModules();
        jest.useFakeTimers();

        global.Joomla = {
            getOptions: jest.fn((key) => (key === 'mod_proclaim_youtube'
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

        global.fetch = jest.fn();
    });

    afterEach(() => {
        jest.useRealTimers();
    });

    /**
     * Load the script and run its ready handler once. The handler is called
     * directly rather than by dispatching DOMContentLoaded, which would also
     * run the handlers earlier tests loaded and start a poller for each.
     */
    function load() {
        const addListener = jest.spyOn(document, 'addEventListener');

        require('../../build/media_source/js/mod-youtube-status.es6.js');

        const ready = addListener.mock.calls.find(([type]) => type === 'DOMContentLoaded')[1];

        addListener.mockRestore();
        ready();
    }

    function respondWith(body) {
        global.fetch.mockResolvedValue({ json: () => Promise.resolve(body) });
    }

    // Wait out one poll interval and let the request's promises settle.
    async function poll() {
        await jest.advanceTimersByTimeAsync(pollInterval);
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

        expect(global.fetch).toHaveBeenCalledTimes(1);
        expect(badge().innerHTML).toContain('Live now');
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

        expect(global.fetch).toHaveBeenCalledTimes(2);
        expect(badge().innerHTML).toContain('Upcoming');
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
        await jest.advanceTimersByTimeAsync(20000);

        expect(global.fetch).toHaveBeenCalledTimes(1);
        expect(badge().innerHTML).toContain('Upcoming');
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
        await jest.advanceTimersByTimeAsync(20000);

        expect(global.fetch).toHaveBeenCalledTimes(1);
        expect(badge().innerHTML).toContain('Upcoming');
    });
});
