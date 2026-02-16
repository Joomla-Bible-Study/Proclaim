/**
 * @jest-environment jsdom
 */

/**
 * Tests for sermon-filters.es6.js
 * AJAX filtering and searching for frontend sermon listing
 *
 * Note: innerHTML usage in setupModule() is safe — it builds DOM for
 * unit tests from static strings, not from external input.
 */

const { validateFile } = require('./helpers/jshint-helper');

const SOURCE_FILE = 'build/media_source/js/sermon-filters.es6.js';

// Track DOMContentLoaded handlers so we can clean them up between tests.
// jest.resetModules() does NOT remove event listeners from `document`.
var capturedDclHandler = null;
var origAddEventListener = document.addEventListener.bind(document);

/**
 * Helper: set up the full DOM + mocks needed for the sermon-filters module.
 * Returns after module initialization is complete.
 *
 * @param {Function} fetchMock   The mocked fetch function
 * @param {Object}   extraOpts  Additional options to merge into sermonFilters config
 */
function setupModule(fetchMock, extraOpts) {
    var paginationStyle = (extraOpts && extraOpts.paginationStyle) || 'pagination';

    var html = '<div id="proclaim-main-content">' +
            '<form id="adminForm">' +
                '<input name="filter_search" type="text" value="" />' +
                '<select name="filter_teacher">' +
                    '<option value="">All Teachers</option>' +
                    '<option value="5">Pastor John</option>' +
                '</select>' +
            '</form>';

    if (paginationStyle === 'pagination') {
        html += '<div id="proclaim-pagination-top" class="proclaim-pagination"></div>';
    }

    html += '<div id="proclaim-sermon-list" aria-live="polite">' +
                '<div class="row"><div class="col">Item 1</div><div class="col">Item 2</div></div>' +
            '</div>';

    if (paginationStyle === 'pagination') {
        html += '<div id="proclaim-pagination-bottom" class="proclaim-pagination"></div>';
    }

    if (paginationStyle === 'loadmore') {
        html += '<div class="proclaim-load-more" id="proclaim-load-more">' +
                    '<button type="button" class="btn btn-outline-primary">Load More</button>' +
                '</div>';
    }

    if (paginationStyle !== 'pagination') {
        html += '<div class="proclaim-item-counter" id="proclaim-item-counter"></div>' +
                '<div class="proclaim-scroll-sentinel" id="proclaim-scroll-sentinel"></div>';
    }

    html += '</div>';

    document.body.innerHTML = html;

    global.Joomla = {
        getOptions: jest.fn().mockReturnValue(Object.assign({
            enabled: true,
            ajaxUrl: 'http://localhost/index.php?option=com_proclaim&task=cwmsermons.filterAjax&format=raw',
            csrfToken: 'testtoken',
            paginationStyle: paginationStyle,
            limit: 2,
        }, extraOpts || {})),
        Text: { _: jest.fn(function (key, fallback) {
            return fallback || key;
        }) },
    };

    global.AbortController = class {
        constructor() { this.signal = {}; }
        abort() {}
    };

    // Mock IntersectionObserver
    global.IntersectionObserver = class {
        constructor(callback, options) {
            this._callback = callback;
            this._options = options;
            IntersectionObserver._lastInstance = this;
        }
        observe() {}
        unobserve() {}
        disconnect() {}
        trigger(entries) {
            this._callback(entries);
        }
    };

    jest.spyOn(window.history, 'pushState').mockImplementation(function () {});
    jest.spyOn(window.history, 'replaceState').mockImplementation(function () {});

    global.fetch = fetchMock;

    Element.prototype.scrollIntoView = jest.fn();

    // Capture the DOMContentLoaded handler so we can remove it in afterEach
    document.addEventListener = function (type, handler, options) {
        if (type === 'DOMContentLoaded') {
            capturedDclHandler = handler;
        }
        return origAddEventListener(type, handler, options);
    };

    // Load and initialize the module
    require('../../build/media_source/js/sermon-filters.es6.js');
    document.dispatchEvent(new Event('DOMContentLoaded'));

    // Restore original addEventListener
    document.addEventListener = origAddEventListener;
}

/** Standard AJAX response mock */
function mockAjaxResponse(overrides) {
    return {
        ok: true,
        json: function () {
            return Promise.resolve(Object.assign({
                success: true,
                html: '<div class="row"><div class="col">New Item 1</div><div class="col">New Item 2</div></div>',
                pagination: '',
                pagesCounter: '',
                total: 10,
                pagesTotal: 5,
            }, overrides || {}));
        },
    };
}

describe('sermon-filters.es6.js', () => {
    afterEach(() => {
        // Remove stacked DOMContentLoaded handler from previous test
        if (capturedDclHandler) {
            document.removeEventListener('DOMContentLoaded', capturedDclHandler);
            capturedDclHandler = null;
        }
        delete global.Joomla;
        delete global.IntersectionObserver;
        if (Element.prototype.scrollIntoView) {
            delete Element.prototype.scrollIntoView;
        }
        document.body.innerHTML = '';
        jest.restoreAllMocks();
        jest.resetModules();
    });

    describe('JSHint Validation', () => {
        test('should pass JSHint validation', () => {
            var result = validateFile(SOURCE_FILE);
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
            jest.spyOn(window.history, 'replaceState').mockImplementation(function () {});

            document.addEventListener = function (type, handler, options) {
                if (type === 'DOMContentLoaded') { capturedDclHandler = handler; }
                return origAddEventListener(type, handler, options);
            };
            require('../../build/media_source/js/sermon-filters.es6.js');
            document.addEventListener = origAddEventListener;
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
            jest.spyOn(window.history, 'replaceState').mockImplementation(function () {});

            document.addEventListener = function (type, handler, options) {
                if (type === 'DOMContentLoaded') { capturedDclHandler = handler; }
                return origAddEventListener(type, handler, options);
            };
            require('../../build/media_source/js/sermon-filters.es6.js');
            document.addEventListener = origAddEventListener;
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
            jest.spyOn(window.history, 'replaceState').mockImplementation(function () {});

            document.addEventListener = function (type, handler, options) {
                if (type === 'DOMContentLoaded') { capturedDclHandler = handler; }
                return origAddEventListener(type, handler, options);
            };
            require('../../build/media_source/js/sermon-filters.es6.js');
            document.addEventListener = origAddEventListener;
            document.dispatchEvent(new Event('DOMContentLoaded'));

            expect(window.history.replaceState).not.toHaveBeenCalled();
        });

        test('should initialize when all requirements are met', () => {
            var mockFetch = jest.fn().mockResolvedValue(mockAjaxResponse());
            setupModule(mockFetch);

            expect(window.history.replaceState).toHaveBeenCalledWith(
                { proclaimAjax: true },
                '',
                expect.any(String)
            );
        });
    });

    describe('Standard Pagination Mode', () => {
        test('should trigger AJAX fetch on form submit', async () => {
            var mockFetch = jest.fn().mockResolvedValue(mockAjaxResponse());
            setupModule(mockFetch);
            mockFetch.mockClear();

            var form = document.getElementById('adminForm');
            form.dispatchEvent(new Event('submit', { cancelable: true }));

            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });

            expect(mockFetch).toHaveBeenCalled();

            var fetchUrl = mockFetch.mock.calls[0][0];
            expect(fetchUrl).toContain('task=cwmsermons.filterAjax');
        });

        test('should include CSRF token in request', async () => {
            var mockFetch = jest.fn().mockResolvedValue(mockAjaxResponse());
            setupModule(mockFetch);
            mockFetch.mockClear();

            var form = document.getElementById('adminForm');
            form.dispatchEvent(new Event('submit', { cancelable: true }));

            await new Promise(function (r) { setTimeout(r, 0); });

            var fetchUrl = mockFetch.mock.calls[0][0];
            expect(fetchUrl).toContain('testtoken=1');
        });

        test('should update listing content after successful fetch', async () => {
            var mockFetch = jest.fn().mockResolvedValue(mockAjaxResponse());
            setupModule(mockFetch);
            mockFetch.mockClear();

            var form = document.getElementById('adminForm');
            form.dispatchEvent(new Event('submit', { cancelable: true }));

            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });

            var list = document.getElementById('proclaim-sermon-list');
            expect(list.innerHTML).toContain('New Item 1');
        });
    });

    describe('Search debounce', () => {
        test('should debounce search input by 350ms', () => {
            jest.useFakeTimers();

            var mockFetch = jest.fn().mockResolvedValue(mockAjaxResponse());
            setupModule(mockFetch);
            var baselineCount = mockFetch.mock.calls.length;

            var searchInput = document.querySelector('input[name="filter_search"]');

            searchInput.value = 'G';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.value = 'Go';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.value = 'God';
            searchInput.dispatchEvent(new Event('input'));

            jest.advanceTimersByTime(200);
            expect(mockFetch.mock.calls.length).toBe(baselineCount);

            jest.advanceTimersByTime(200);
            expect(mockFetch.mock.calls.length).toBeGreaterThan(baselineCount);

            jest.useRealTimers();
        });
    });

    describe('Load More Mode', () => {
        test('should show Load More button when in loadmore mode', () => {
            var mockFetch = jest.fn().mockResolvedValue(mockAjaxResponse());
            setupModule(mockFetch, { paginationStyle: 'loadmore' });

            var btn = document.getElementById('proclaim-load-more');
            expect(btn).not.toBeNull();
            expect(btn.querySelector('button')).not.toBeNull();
        });

        test('should append items on Load More click', async () => {
            var mockFetch = jest.fn().mockResolvedValue(mockAjaxResponse());
            setupModule(mockFetch, { paginationStyle: 'loadmore' });

            var list = document.getElementById('proclaim-sermon-list');
            var initialChildren = list.querySelector('.row').children.length;
            expect(initialChildren).toBe(2);

            mockFetch.mockClear();

            var btn = document.querySelector('#proclaim-load-more button');
            btn.click();

            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });

            // Items should be appended (2 original + 2 new = 4)
            var afterChildren = list.querySelector('.row').children.length;
            expect(afterChildren).toBe(4);
        });

        test('should update counter after load', async () => {
            var mockFetch = jest.fn().mockResolvedValue(mockAjaxResponse({ total: 10, pagesTotal: 5 }));
            setupModule(mockFetch, { paginationStyle: 'loadmore' });
            mockFetch.mockClear();

            var btn = document.querySelector('#proclaim-load-more button');
            btn.click();

            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });

            var counter = document.getElementById('proclaim-item-counter');
            expect(counter.textContent).toContain('Showing');
        });

        test('should hide Load More when all items loaded', async () => {
            var mockFetch = jest.fn().mockResolvedValue(mockAjaxResponse({ total: 2, pagesTotal: 1 }));
            setupModule(mockFetch, { paginationStyle: 'loadmore' });
            mockFetch.mockClear();

            var btn = document.querySelector('#proclaim-load-more button');
            btn.click();

            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });

            var container = document.getElementById('proclaim-load-more');
            expect(container.style.display).toBe('none');

            var counter = document.getElementById('proclaim-item-counter');
            expect(counter.textContent).toContain('All items loaded');
        });

        test('should reset to replace mode on filter change', async () => {
            var mockFetch = jest.fn().mockResolvedValue(mockAjaxResponse());
            setupModule(mockFetch, { paginationStyle: 'loadmore' });

            // First, do a load more to accumulate
            mockFetch.mockClear();
            var btn = document.querySelector('#proclaim-load-more button');
            btn.click();

            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });

            var list = document.getElementById('proclaim-sermon-list');
            expect(list.querySelector('.row').children.length).toBe(4);

            // Now change a filter — should replace, not append
            mockFetch.mockClear();
            var select = document.querySelector('select[name="filter_teacher"]');
            select.value = '5';
            select.dispatchEvent(new Event('change'));

            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });

            // After filter change, list should be replaced (2 items, not 6)
            expect(list.querySelector('.row').children.length).toBe(2);
        });
    });

    describe('Infinite Scroll Mode', () => {
        test('should create IntersectionObserver when in infinite mode', () => {
            var mockFetch = jest.fn().mockResolvedValue(mockAjaxResponse());
            setupModule(mockFetch, { paginationStyle: 'infinite' });

            expect(IntersectionObserver._lastInstance).toBeDefined();
        });

        test('should not show pagination containers in infinite mode', () => {
            var mockFetch = jest.fn().mockResolvedValue(mockAjaxResponse());
            setupModule(mockFetch, { paginationStyle: 'infinite' });

            expect(document.getElementById('proclaim-pagination-top')).toBeNull();
            expect(document.getElementById('proclaim-pagination-bottom')).toBeNull();
        });

        test('should have sentinel element for intersection', () => {
            var mockFetch = jest.fn().mockResolvedValue(mockAjaxResponse());
            setupModule(mockFetch, { paginationStyle: 'infinite' });

            var sentinel = document.getElementById('proclaim-scroll-sentinel');
            expect(sentinel).not.toBeNull();
        });

        test('should show counter element', () => {
            var mockFetch = jest.fn().mockResolvedValue(mockAjaxResponse());
            setupModule(mockFetch, { paginationStyle: 'infinite' });

            var counter = document.getElementById('proclaim-item-counter');
            expect(counter).not.toBeNull();
        });
    });

    describe('Error fallback', () => {
        test('should fallback to form.submit() on fetch error', async () => {
            jest.spyOn(console, 'warn').mockImplementation(function () {});

            // All fetch calls fail (no fetch happens during init)
            var mockFetch = jest.fn()
                .mockRejectedValue(new TypeError('Network error'));

            setupModule(mockFetch);

            var form = document.getElementById('adminForm');
            form.submit = jest.fn();

            form.dispatchEvent(new Event('submit', { cancelable: true }));

            for (var i = 0; i < 10; i++) {
                await new Promise(function (r) { setTimeout(r, 10); });
            }

            expect(form.submit).toHaveBeenCalled();
        }, 10000);
    });
});
