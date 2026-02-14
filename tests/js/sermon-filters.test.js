/**
 * @jest-environment jsdom
 */

/**
 * Tests for sermon-filters.es6.js
 * AJAX filtering and searching for frontend sermon listing
 */

const { validateFile } = require('./helpers/jshint-helper');

const SOURCE_FILE = 'build/media_source/js/sermon-filters.es6.js';

/**
 * Helper: set up the full DOM + mocks needed for the sermon-filters module.
 * Returns after module initialization is complete.
 */
function setupModule(fetchMock) {
    document.body.innerHTML = `
        <div id="proclaim-main-content">
            <form id="adminForm">
                <input name="filter_search" type="text" value="" />
                <select name="filter_teacher">
                    <option value="">All Teachers</option>
                    <option value="5">Pastor John</option>
                </select>
            </form>
            <div id="proclaim-active-filters"></div>
            <div id="proclaim-pagination-top" class="proclaim-pagination"></div>
            <div id="proclaim-sermon-list" aria-live="polite">
                <div>Original content</div>
            </div>
            <div id="proclaim-pagination-bottom" class="proclaim-pagination"></div>
        </div>
    `;

    global.Joomla = {
        getOptions: jest.fn().mockReturnValue({
            enabled: true,
            ajaxUrl: 'http://localhost/index.php?option=com_proclaim&task=cwmsermons.filterAjax&format=raw',
            csrfToken: 'testtoken',
        }),
        Text: { _: jest.fn((key, fallback) => fallback || key) },
    };

    if (!global.CSS) {
        global.CSS = {};
    }
    global.CSS.escape = jest.fn(s => s);

    global.AbortController = class {
        constructor() { this.signal = {}; }
        abort() {}
    };

    jest.spyOn(window.history, 'pushState').mockImplementation(() => {});
    jest.spyOn(window.history, 'replaceState').mockImplementation(() => {});

    global.fetch = fetchMock;

    Element.prototype.scrollIntoView = jest.fn();

    // Load and initialize the module
    require('../../build/media_source/js/sermon-filters.es6.js');
    document.dispatchEvent(new Event('DOMContentLoaded'));
}

describe('sermon-filters.es6.js', () => {
    afterEach(() => {
        delete global.Joomla;
        if (Element.prototype.scrollIntoView) {
            delete Element.prototype.scrollIntoView;
        }
        document.body.innerHTML = '';
        jest.restoreAllMocks();
        jest.resetModules();
    });

    describe('JSHint Validation', () => {
        test('should pass JSHint validation', () => {
            const result = validateFile(SOURCE_FILE);
            expect(result.valid).toBe(true);
            if (!result.valid) {
                console.error('JSHint errors:\n' + result.errorReport);
            }
        });
    });

    describe('Initialization', () => {
        test('should not initialize when sermonFilters options are missing', () => {
            document.body.innerHTML = '<div id="proclaim-sermon-list"></div>';

            global.Joomla = {
                getOptions: jest.fn().mockReturnValue(null),
                Text: { _: jest.fn() },
            };
            jest.spyOn(window.history, 'replaceState').mockImplementation(() => {});

            require('../../build/media_source/js/sermon-filters.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));

            expect(window.history.replaceState).not.toHaveBeenCalled();
        });

        test('should not initialize when enabled is false', () => {
            document.body.innerHTML = '<div id="proclaim-sermon-list"></div>';

            global.Joomla = {
                getOptions: jest.fn().mockReturnValue({
                    enabled: false,
                    ajaxUrl: 'index.php?option=com_proclaim&task=cwmsermons.filterAjax',
                    csrfToken: 'abc123',
                }),
                Text: { _: jest.fn() },
            };
            jest.spyOn(window.history, 'replaceState').mockImplementation(() => {});

            require('../../build/media_source/js/sermon-filters.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));

            expect(window.history.replaceState).not.toHaveBeenCalled();
        });

        test('should not initialize without sermon-list container', () => {
            document.body.innerHTML = '<div id="some-other-element"></div>';

            global.Joomla = {
                getOptions: jest.fn().mockReturnValue({
                    enabled: true,
                    ajaxUrl: 'index.php',
                    csrfToken: 'abc123',
                }),
                Text: { _: jest.fn() },
            };
            jest.spyOn(window.history, 'replaceState').mockImplementation(() => {});

            require('../../build/media_source/js/sermon-filters.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));

            expect(window.history.replaceState).not.toHaveBeenCalled();
        });

        test('should initialize when all requirements are met', () => {
            const mockFetch = jest.fn().mockResolvedValue({ ok: true, json: () => Promise.resolve({ success: true }) });
            setupModule(mockFetch);

            expect(window.history.replaceState).toHaveBeenCalledWith(
                { proclaimAjax: true },
                '',
                expect.any(String)
            );
        });
    });

    describe('Form submission interception', () => {
        test('should trigger AJAX fetch on form submit', async () => {
            const mockFetch = jest.fn().mockResolvedValue({
                ok: true,
                json: () => Promise.resolve({
                    success: true,
                    html: '<div>New listing HTML</div>',
                    pagination: '',
                    pagesCounter: '',
                    total: 10,
                    pagesTotal: 1,
                    activeFilters: {},
                }),
            });

            setupModule(mockFetch);

            // Clear calls from initialization
            mockFetch.mockClear();

            const form = document.getElementById('adminForm');
            form.dispatchEvent(new Event('submit', { cancelable: true }));

            // Wait for async fetch
            await new Promise(r => setTimeout(r, 0));
            await new Promise(r => setTimeout(r, 0));

            // The submit handler plus any change handlers may fire,
            // but at least one fetch should have been made
            expect(mockFetch).toHaveBeenCalled();

            const fetchUrl = mockFetch.mock.calls[0][0];
            expect(fetchUrl).toContain('task=cwmsermons.filterAjax');
        });

        test('should include CSRF token in request', async () => {
            const mockFetch = jest.fn().mockResolvedValue({
                ok: true,
                json: () => Promise.resolve({
                    success: true,
                    html: '<div>HTML</div>',
                    pagination: '',
                    pagesCounter: '',
                    total: 1,
                    pagesTotal: 1,
                    activeFilters: {},
                }),
            });

            setupModule(mockFetch);
            mockFetch.mockClear();

            const form = document.getElementById('adminForm');
            form.dispatchEvent(new Event('submit', { cancelable: true }));

            await new Promise(r => setTimeout(r, 0));

            const fetchUrl = mockFetch.mock.calls[0][0];
            expect(fetchUrl).toContain('testtoken=1');
        });

        test('should update listing content after successful fetch', async () => {
            const mockFetch = jest.fn().mockResolvedValue({
                ok: true,
                json: () => Promise.resolve({
                    success: true,
                    html: '<div>New listing HTML</div>',
                    pagination: '',
                    pagesCounter: '',
                    total: 10,
                    pagesTotal: 1,
                    activeFilters: {},
                }),
            });

            setupModule(mockFetch);
            mockFetch.mockClear();

            const form = document.getElementById('adminForm');
            form.dispatchEvent(new Event('submit', { cancelable: true }));

            // Wait for fetch + DOM update
            await new Promise(r => setTimeout(r, 0));
            await new Promise(r => setTimeout(r, 0));
            await new Promise(r => setTimeout(r, 0));

            const list = document.getElementById('proclaim-sermon-list');
            expect(list.innerHTML).toContain('New listing HTML');
        });
    });

    describe('Search debounce', () => {
        test('should debounce search input by 350ms', () => {
            jest.useFakeTimers();

            const mockFetch = jest.fn().mockResolvedValue({
                ok: true,
                json: () => Promise.resolve({
                    success: true,
                    html: '<div>Results</div>',
                    pagination: '',
                    pagesCounter: '',
                    total: 5,
                    pagesTotal: 1,
                    activeFilters: {},
                }),
            });

            setupModule(mockFetch);

            // Record baseline call count after init
            const baselineCount = mockFetch.mock.calls.length;

            const searchInput = document.querySelector('input[name="filter_search"]');

            // Type rapidly
            searchInput.value = 'G';
            searchInput.dispatchEvent(new Event('input'));

            searchInput.value = 'Go';
            searchInput.dispatchEvent(new Event('input'));

            searchInput.value = 'God';
            searchInput.dispatchEvent(new Event('input'));

            // Before debounce completes — no NEW fetch
            jest.advanceTimersByTime(200);
            expect(mockFetch.mock.calls.length).toBe(baselineCount);

            // After debounce completes — new fetches triggered
            jest.advanceTimersByTime(200);
            expect(mockFetch.mock.calls.length).toBeGreaterThan(baselineCount);

            jest.useRealTimers();
        });
    });

    describe('Error fallback', () => {
        test('should fallback to form.submit() on fetch error', async () => {
            // Suppress console.warn for this test
            jest.spyOn(console, 'warn').mockImplementation(() => {});

            // Set up with a fetch that succeeds initially (for setup) then fails
            const mockFetch = jest.fn()
                .mockResolvedValueOnce({
                    ok: true,
                    json: () => Promise.resolve({ success: true, html: '', pagination: '', pagesCounter: '', total: 0, pagesTotal: 0, activeFilters: {} }),
                })
                .mockRejectedValue(new TypeError('Network error'));

            setupModule(mockFetch);

            const form = document.getElementById('adminForm');
            form.submit = jest.fn(); // Mock standard submit

            form.dispatchEvent(new Event('submit', { cancelable: true }));

            // Wait for fetch rejection to propagate through microtasks
            for (let i = 0; i < 10; i++) {
                await new Promise(r => setTimeout(r, 10));
            }

            expect(form.submit).toHaveBeenCalled();
        }, 10000);
    });
});
